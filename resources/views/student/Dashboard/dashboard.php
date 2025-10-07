<?php
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

// Get logged in user
$user = Auth::user();
$user_id = $user ? $user->user_id : null;

// Get the actual student_id from students table
$student = null;
if ($user_id) {
    $student = DB::table('students')->where('user_id', $user_id)->first();
}

// Only fetch data if student record exists
if ($student) {
    $student_id = $student->student_id;
    
    // Fetch enrolled classes
    $enrolledClasses = DB::table('class_enrollments')
        ->join('classes', 'class_enrollments.class_id', '=', 'classes.class_id')
        ->join('teachers', 'classes.teacher_id', '=', 'teachers.teacher_id')
        ->join('users as teacher_users', 'teachers.user_id', '=', 'teacher_users.user_id')
        ->leftJoin('subjects', 'classes.subject_id', '=', 'subjects.subject_id')
        ->where('class_enrollments.student_id', $student_id)
        ->where('class_enrollments.status', 'enrolled')
        ->select(
            'classes.*', 
            'teacher_users.first_name as teacher_first', 
            'teacher_users.last_name as teacher_last', 
            'subjects.subject_name'
        )
        ->get();

    // Fetch upcoming assignments
    $upcomingAssignments = DB::table('assignments')
        ->join('classes', 'assignments.class_id', '=', 'classes.class_id')
        ->join('class_enrollments', 'classes.class_id', '=', 'class_enrollments.class_id')
        ->leftJoin('assignment_submissions', function($join) use ($student_id) {
            $join->on('assignments.assignment_id', '=', 'assignment_submissions.assignment_id')
                 ->where('assignment_submissions.student_id', '=', $student_id);
        })
        ->where('class_enrollments.student_id', $student_id)
        ->where('assignments.due_date', '>', now())
        ->whereNull('assignment_submissions.assignment_id')
        ->select('assignments.*', 'classes.section_name', 'classes.class_code')
        ->orderBy('assignments.due_date')
        ->limit(5)
        ->get();

    // Fetch recent grades
    $recentGrades = DB::table('grades')
        ->join('classes', 'grades.class_id', '=', 'classes.class_id')
        ->where('grades.student_id', $student_id)
        ->orderBy('grades.graded_at', 'desc')
        ->limit(5)
        ->select('grades.*', 'classes.section_name', 'classes.class_code')
        ->get();
        
    // Fetch unread notifications
    $unreadNotifications = DB::table('notifications')
        ->where('user_id', $user_id)
        ->where('is_read', 0)
        ->orderBy('created_at', 'desc')
        ->get();
        
    // Fetch recent notifications (last 10)
    $recentNotifications = DB::table('notifications')
        ->where('user_id', $user_id)
        ->orderBy('created_at', 'desc')
        ->limit(10)
        ->get();
        
    // Fetch lesson materials from enrolled classes
    $lessonMaterials = DB::table('lesson_materials')
        ->join('modules', 'lesson_materials.module_id', '=', 'modules.module_id')
        ->join('classes', 'modules.class_id', '=', 'classes.class_id')
        ->join('class_enrollments', 'classes.class_id', '=', 'class_enrollments.class_id')
        ->where('class_enrollments.student_id', $student_id)
        ->where('class_enrollments.status', 'enrolled')
        ->orderBy('lesson_materials.created_at', 'desc')
        ->limit(10)
        ->select('lesson_materials.*', 'classes.section_name', 'classes.class_code')
        ->get();
} else {
    // Default empty collections if not logged in
    $enrolledClasses = collect();
    $recentGrades = collect();
    $unreadNotifications = collect();
    $recentNotifications = collect();
    $lessonMaterials = collect();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="<?php echo csrf_token(); ?>">
  <title>SMS3</title>

  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="<?php echo asset('style.css'); ?>">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
  
  <!-- SweetAlert2 for Notifications -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <style>
    @import url("../../style.css");

    /* Make sidebar scrollable in landscape mode on mobile devices */
    @media (max-width: 768px) and (orientation: landscape) {
      .sidebar {
        max-height: 100vh;
        overflow-y: auto;
      }
    }
      /* Global Styles & Typography */
    body {
      font-family: 'Poppins', sans-serif;
      background-color: #f0f2f5;
    }
    
    /* Main Content Area */
    .main-content {
      padding: 40px;
    }

    /* General Card Styling */
    .card {
      border: none;
      border-radius: 1rem;
      transition: transform 0.3s ease-in-out, box-shadow 0.3s ease-in-out;
      box-shadow: 0 10px 20px rgba(0, 0, 0, 0.05);
    }
    
    .card:hover {
      transform: translateY(-5px);
      box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
    }

    .card-header {
      border-radius: 1rem 1rem 0 0 !important;
      font-weight: 600;
      font-size: 1.1rem;
      padding: 1rem 1.5rem;
      border-bottom: none;
    }

    .card-body {
      padding: 1.5rem;
    }

    /* Specific Card Colors */
    .bg-primary { background-color: #007bff !important; }
    .bg-success { background-color: #28a745 !important; }
    .bg-info { background-color: #17a2b8 !important; }
    .bg-warning { background-color: #ffc107 !important; }

    /* Progress Bars */
    .progress {
      height: 8px;
      border-radius: 50px;
      background-color: #e9ecef;
    }
    .progress-bar {
      border-radius: 60px;
    }

    /* Badges */
    .badge {
      font-weight: 500;
      padding: 0.4em 0.8em;
      border-radius: 50px;
    }

    /* Alerts */
    .alert {
      border-radius: 0.75rem;
      border: 1px solid transparent;
    }

    .alert-primary {
      color: #004085;
      background-color: #cce5ff;
      border-color: #b8daff;
    }

    .alert-warning {
      color: #856404;
      background-color: #fff3cd;
      border-color: #ffeeba;
    }

    .alert-success {
      color: #155724;
      background-color: #d4edda;
      border-color: #c3e6cb;
    }

   /* 2. Layouts */
    .main-content {
      padding: 40px;
      flex-grow: 1;
    }

    .sidebar {
      background-color: #084d92ff;
      color: #fff;
    }
    .sidebar .nav-link {
      color: #fff;
      transition: background-color 0.2s ease;
    }
    .sidebar .nav-link:hover,
    .sidebar .nav-link.active {
      background-color: #ffffffff;
    }

  </style>
</head>

<body>

  <!-- navbar -->

  <?php include resource_path('views/includes/sidenav_student.php'); ?>
  <div class="main-content flex-grow-1">
    <div class="container">
      <div class="row">
        <div class="col">
          <div class="container my-5">
            <div class="card shadow-lg border-0 rounded-4">
              <div class="card-body p-4">

  <div class="dashboard-content">
    <div class="container-fluid">
      <div class="row">
        <div class="col-12">
          <div class="mb-5">
            <h1 class="fw-bold text-dark"><i class="bi bi-speedometer2 text-primary me-3"></i>Student Dashboard</h1>
            <p class="text-muted fs-5">Quick access to your courses, deadlines, grades, and announcements.</p>
          </div>

          <div class="row g-4">
            <div class="col-lg-6">
              <div class="card h-100">
                <div class="card-header bg-primary text-white">
                  <i class="bi bi-journal-bookmark-fill me-2"></i> My Active Courses
                </div>
                <div class="card-body">
                  <?php if($enrolledClasses->count() > 0): ?>
                    <?php foreach($enrolledClasses as $class): ?>
                    <div class="d-flex align-items-center mb-3 pb-3 border-bottom">
                      <div class="flex-grow-1">
                        <h6 class="fw-bold"><?php echo htmlspecialchars($class->section_name); ?> – <?php echo htmlspecialchars($class->subject_name ?? $class->class_code); ?></h6>
                        <p class="text-muted small mb-0">Professor: <?php echo htmlspecialchars($class->teacher_first . ' ' . $class->teacher_last); ?></p>
                        <small class="text-muted"><?php echo $class->schedule_days . ' ' . date('g:i A', strtotime($class->schedule_time_start)); ?></small>
                      </div>
                    </div>
                    <?php endforeach; ?>
                  <?php else: ?>
                    <p class="text-muted text-center">No enrolled classes yet.</p>
                  <?php endif; ?>
                </div>
              </div>
            </div>

            <div class="col-lg-6">
              <div class="card h-100">
                <div class="card-header bg-warning">
                  <i class="bi bi-calendar-event-fill me-2"></i> Upcoming Deadlines
                </div>
                <div class="card-body">
                  <ul class="list-group list-group-flush">
                    <?php if($upcomingAssignments->count() > 0): ?>
                      <?php foreach($upcomingAssignments as $assignment): ?>
                      <li class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                          <h6 class="mb-0"><?php echo htmlspecialchars($assignment->title); ?> <small class="text-muted fw-normal">(<?php echo htmlspecialchars($assignment->section_name); ?>)</small></h6>
                          <small class="text-muted">Due <?php echo date('M d, Y', strtotime($assignment->due_date)); ?></small>
                        </div>
                        <span class="badge bg-warning">Pending</span>
                      </li>
                      <?php endforeach; ?>
                    <?php else: ?>
                      <li class="list-group-item text-center text-muted">No upcoming assignments</li>
                    <?php endif; ?>
                  </ul>
                </div>
              </div>
            </div>

            <div class="col-lg-6">
              <div class="card h-100">
                <div class="card-header bg-success text-white">
                  <i class="bi bi-clipboard-check-fill me-2"></i> Recent Grades
                </div>
                <div class="card-body">
                  <ul class="list-group list-group-flush">
                    <?php if($recentGrades->count() > 0): ?>
                      <?php foreach($recentGrades as $grade): ?>
                      <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span><?php echo htmlspecialchars($grade->section_name); ?></span>
                        <span class="badge <?php echo $grade->percentage >= 75 ? 'bg-success' : 'bg-warning'; ?> fw-bold p-2"><?php echo round($grade->percentage, 1); ?>%</span>
                      </li>
                      <?php endforeach; ?>
                    <?php else: ?>
                      <li class="list-group-item text-center text-muted">No grades yet</li>
                    <?php endif; ?>
                  </ul>
                </div>
              </div>
            </div>

            <div class="col-lg-6">
              <div class="card h-100">
                <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                  <span><i class="bi bi-bell-fill me-2"></i> Notifications</span>
                  <?php if($unreadNotifications->count() > 0): ?>
                    <span class="badge bg-danger"><?php echo $unreadNotifications->count(); ?> new</span>
                  <?php endif; ?>
                </div>
                <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                  <?php if($recentNotifications->count() > 0): ?>
                    <ul class="list-group list-group-flush">
                      <?php foreach($recentNotifications as $notification): ?>
                      <li class="list-group-item <?php echo $notification->is_read ? '' : 'bg-light border-start border-primary border-3'; ?> px-2">
                        <div class="d-flex align-items-start">
                          <i class="bi bi-<?php echo $notification->notification_type == 'lesson_material' ? 'file-earmark-text' : 'bell'; ?>-fill text-primary fs-4 me-3 mt-1"></i>
                          <div class="flex-grow-1">
                            <h6 class="mb-1 <?php echo $notification->is_read ? '' : 'fw-bold'; ?>"><?php echo htmlspecialchars($notification->title); ?></h6>
                            <p class="mb-1 small text-muted"><?php echo htmlspecialchars($notification->message); ?></p>
                            <small class="text-muted"><i class="bi bi-clock"></i> <?php echo date('M d, Y g:i A', strtotime($notification->created_at)); ?></small>
                          </div>
                        </div>
                      </li>
                      <?php endforeach; ?>
                    </ul>
                  <?php else: ?>
                    <div class="alert alert-info text-center" role="alert">
                      <i class="bi bi-bell fs-1 d-block mb-2"></i>
                      No notifications at this time.
                    </div>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Lesson Materials Section -->
          <div class="row g-4 mt-2">
            <div class="col-12">
              <div class="card">
                <div class="card-header bg-primary text-white">
                  <i class="bi bi-file-earmark-text-fill me-2"></i> Recent Lesson Materials
                </div>
                <div class="card-body">
                  <?php if($lessonMaterials->count() > 0): ?>
                    <div class="table-responsive">
                      <table class="table table-hover align-middle">
                        <thead class="table-light">
                          <tr>
                            <th>Material Title</th>
                            <th>Class</th>
                            <th>Type</th>
                            <th>Date Uploaded</th>
                            <th>Action</th>
                          </tr>
                        </thead>
                        <tbody>
                          <?php foreach($lessonMaterials as $material): ?>
                          <tr>
                            <td>
                              <i class="bi bi-<?php 
                                echo match($material->material_type) {
                                  'pdf' => 'file-pdf',
                                  'doc' => 'file-word',
                                  'video' => 'camera-video',
                                  'link' => 'link-45deg',
                                  'image' => 'image',
                                  default => 'file-earmark'
                                }; 
                              ?>-fill text-primary me-2"></i>
                              <strong><?php echo htmlspecialchars($material->material_title); ?></strong>
                              <?php if($material->material_description): ?>
                                <br><small class="text-muted"><?php echo htmlspecialchars(substr($material->material_description, 0, 60)); ?>...</small>
                              <?php endif; ?>
                            </td>
                            <td>
                              <span class="badge bg-info"><?php echo htmlspecialchars($material->section_name ?? $material->class_code); ?></span>
                            </td>
                            <td>
                              <span class="badge bg-secondary"><?php echo strtoupper($material->material_type); ?></span>
                            </td>
                            <td><?php echo date('M d, Y', strtotime($material->created_at)); ?></td>
                            <td>
                              <?php if($material->material_type === 'link' && $material->external_link): ?>
                                <a href="<?php echo htmlspecialchars($material->external_link); ?>" target="_blank" class="btn btn-sm btn-primary">
                                  <i class="bi bi-box-arrow-up-right"></i> Open
                                </a>
                              <?php elseif($material->file_path): ?>
                                <a href="<?php echo asset('storage/' . $material->file_path); ?>" target="_blank" class="btn btn-sm btn-success" download>
                                  <i class="bi bi-download"></i> Download
                                </a>
                              <?php else: ?>
                                <span class="text-muted small">No file</span>
                              <?php endif; ?>
                            </td>
                          </tr>
                          <?php endforeach; ?>
                        </tbody>
                      </table>
                    </div>
                  <?php else: ?>
                    <div class="alert alert-info text-center" role="alert">
                      <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                      No lesson materials available yet.
                    </div>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          </div>
          
        </div>
      </div>
    </div>
      </div>
    </div>
  </div>

              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
  
  <script>
    // Show notification popup on page load if there are unread notifications
    document.addEventListener('DOMContentLoaded', function() {
      <?php if($unreadNotifications->count() > 0): ?>
        const unreadNotifications = <?php echo json_encode($unreadNotifications->toArray()); ?>;
        
        // Show popup with unread notifications
        let notificationHTML = '<div class="text-start" style="max-height: 400px; overflow-y: auto;">';
        
        unreadNotifications.forEach(notification => {
          const icon = notification.notification_type === 'lesson_material' ? 'file-earmark-text' : 'bell';
          const date = new Date(notification.created_at).toLocaleString();
          
          notificationHTML += `
            <div class="alert alert-info mb-2">
              <div class="d-flex align-items-start">
                <i class="bi bi-${icon}-fill fs-4 me-3"></i>
                <div>
                  <h6 class="fw-bold mb-1">${notification.title}</h6>
                  <p class="mb-1 small">${notification.message}</p>
                  <small class="text-muted"><i class="bi bi-clock"></i> ${date}</small>
                </div>
              </div>
            </div>
          `;
        });
        
        notificationHTML += '</div>';
        
        Swal.fire({
          title: '<i class="bi bi-bell-fill text-primary me-2"></i>New Notifications',
          html: notificationHTML,
          icon: 'info',
          confirmButtonText: 'Got it!',
          width: '600px',
          customClass: {
            popup: 'notification-popup'
          }
        }).then((result) => {
          if (result.isConfirmed) {
            // Mark all notifications as read
            fetch('/student/notifications/mark-read', {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
              }
            })
            .then(response => response.json())
            .then(data => {
              if (data.success) {
                // Reload page to update notification badge
                location.reload();
              }
            })
            .catch(error => console.error('Error marking notifications as read:', error));
          }
        });
      <?php endif; ?>
    });
  </script>
</body>
</html>