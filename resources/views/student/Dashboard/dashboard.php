<?php
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

// Get logged in user
$user = Auth::user();
$student_id = $user ? $user->user_id : null;

// Only fetch data if user is logged in
if ($student_id) {
    // Fetch enrolled classes
    $enrolledClasses = DB::table('class_enrollments')
        ->join('classes', 'class_enrollments.class_id', '=', 'classes.class_id')
        ->join('users as teachers', 'classes.teacher_id', '=', 'teachers.user_id')
        ->leftJoin('subjects', 'classes.subject_id', '=', 'subjects.subject_id')
        ->where('class_enrollments.student_id', $student_id)
        ->where('class_enrollments.status', 'enrolled')
        ->select('classes.*', 'teachers.first_name as teacher_first', 'teachers.last_name as teacher_last', 'subjects.subject_name')
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
} else {
    // Default empty collections if not logged in
    $enrolledClasses = collect();
    $upcomingAssignments = collect();
    $recentGrades = collect();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SMS3</title>

  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="<?php echo asset('style.css'); ?>">

  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
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
                <div class="card-header bg-info text-white">
                  <i class="bi bi-megaphone-fill me-2"></i> Announcements
                </div>
                <div class="card-body">
                  <div class="alert alert-info text-center" role="alert">
                    <i class="bi bi-bell fs-1 d-block mb-2"></i>
                    No new announcements at this time.
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
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>