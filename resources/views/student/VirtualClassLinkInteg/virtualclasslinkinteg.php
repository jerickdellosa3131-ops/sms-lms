<?php
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

$user = Auth::user();
$student_id = $user ? $user->user_id : null;

// Note: This page shows virtual class links
// If you don't have a virtual_class_sessions table, this will show empty state
$virtualClasses = collect(); // Empty by default

if ($student_id) {
    // Uncomment when virtual_class_sessions table exists:
    // $virtualClasses = DB::table('virtual_class_sessions')
    //     ->join('classes', 'virtual_class_sessions.class_id', '=', 'classes.class_id')
    //     ->join('class_enrollments', 'classes.class_id', '=', 'class_enrollments.class_id')
    //     ->join('users as teachers', 'classes.teacher_id', '=', 'teachers.user_id')
    //     ->where('class_enrollments.student_id', $student_id)
    //     ->where('virtual_class_sessions.session_date', '>=', date('Y-m-d'))
    //     ->select(
    //         'virtual_class_sessions.*',
    //         'classes.section_name',
    //         DB::raw("CONCAT(teachers.first_name, ' ', teachers.last_name) as teacher_name")
    //     )
    //     ->orderBy('virtual_class_sessions.session_date', 'asc')
    //     ->get();
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
  <style>
    @import url("../../style.css");

    /* Make sidebar scrollable in landscape mode on mobile devices */
    @media (max-width: 768px) and (orientation: landscape) {
      .sidebar {
        max-height: 100vh;
        overflow-y: auto;
      }
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
 
        
      

        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
          <div class="d-flex align-items-center">
            <i class="bi bi-camera-video-fill text-success fs-1 me-3"></i>
            <div>
              <h3 class="mb-0 fw-bold">Virtual Classes</h3>
              <p class="text-muted small mb-0">Access your online class links by subject</p>
            </div>
          </div>
          <a href="<?php echo route('student.class-portal'); ?>" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i> Courses
          </a>
        </div>

        <!-- Virtual Class Sessions -->
        <h5 class="fw-bold mb-3">Upcoming Virtual Classes (<?php echo $virtualClasses->count(); ?>)</h5>
        
        <?php if($virtualClasses->count() > 0): ?>
          <div class="table-responsive">
            <table class="table table-hover align-middle">
              <thead class="table-success">
                <tr>
                  <th>Class</th>
                  <th>Topic</th>
                  <th>Date & Time</th>
                  <th>Teacher</th>
                  <th>Platform</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach($virtualClasses as $session): ?>
                <tr>
                  <td><?php echo htmlspecialchars($session->section_name); ?></td>
                  <td><?php echo htmlspecialchars($session->topic ?? $session->session_title); ?></td>
                  <td>
                    <?php echo date('M d, Y', strtotime($session->session_date)); ?><br>
                    <small class="text-muted"><?php echo date('g:i A', strtotime($session->session_time)); ?></small>
                  </td>
                  <td><?php echo htmlspecialchars($session->teacher_name); ?></td>
                  <td><span class="badge bg-primary"><?php echo htmlspecialchars($session->platform ?? 'Zoom'); ?></span></td>
                  <td>
                    <?php if($session->meeting_link): ?>
                      <a href="<?php echo htmlspecialchars($session->meeting_link); ?>" target="_blank" class="btn btn-success btn-sm">
                        <i class="bi bi-box-arrow-in-right me-1"></i> Join
                      </a>
                    <?php else: ?>
                      <span class="text-muted">No link yet</span>
                    <?php endif; ?>
                  </td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php else: ?>
          <div class="alert alert-info text-center">
            <i class="bi bi-camera-video fs-1 d-block mb-3"></i>
            <h5>No Virtual Classes Scheduled</h5>
            <p class="mb-0">There are currently no upcoming virtual class sessions.</p>
            <small class="text-muted d-block mt-2">
              Check back later or contact your teachers for virtual class schedules.
            </small>
          </div>
        <?php endif; ?>
        
        <div class="alert alert-warning mt-4">
          <i class="bi bi-info-circle me-2"></i>
          <strong>Note:</strong> This feature requires a <code>virtual_class_sessions</code> table in your database. 
          Uncomment the database query in the PHP section at the top of this file to enable it.
        </div>
        

      </div>
    </div>
  </div>
</div>

  <footer class="mt-auto bg-light">
    <div class="container text-center">
      <p class="text-muted">© 2023 Your Company. All rights reserved.</p>
    </div>
  </footer>


  </style>
</body>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>

</html>