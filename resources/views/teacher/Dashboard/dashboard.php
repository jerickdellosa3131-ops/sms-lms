<?php
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

$user = Auth::user();
$teacher_id = $user ? $user->user_id : null;

if ($teacher_id) {
    // Fetch classes taught by this teacher
    $myClasses = DB::table('classes')
        ->leftJoin('subjects', 'classes.subject_id', '=', 'subjects.subject_id')
        ->where('classes.teacher_id', $teacher_id)
        ->select('classes.*', 'subjects.subject_name')
        ->get();
    
    // Count total assignments created
    $totalAssignments = DB::table('assignments')
        ->where('teacher_id', $teacher_id)
        ->count();
    
    // Count pending submissions (submitted but not graded)
    $pendingSubmissions = DB::table('assignment_submissions')
        ->join('assignments', 'assignment_submissions.assignment_id', '=', 'assignments.assignment_id')
        ->where('assignments.teacher_id', $teacher_id)
        ->where('assignment_submissions.status', 'submitted')
        ->count();
    
    // Count total students across all classes
    $totalStudents = DB::table('class_enrollments')
        ->whereIn('class_id', $myClasses->pluck('class_id'))
        ->where('status', 'enrolled')
        ->distinct('student_id')
        ->count('student_id');
    
    // Count lesson materials uploaded
    $totalMaterials = DB::table('lesson_materials')
        ->where('teacher_id', $teacher_id)
        ->count();
} else {
    $myClasses = collect();
    $totalAssignments = 0;
    $pendingSubmissions = 0;
    $totalStudents = 0;
    $totalMaterials = 0;
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
  
  <!-- SweetAlert2 for Alerts -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  
  <!-- Modal Handlers Library -->
  <script src="<?php echo asset('js/modal-handlers.js'); ?>"></script>
  
  <!-- Teacher Actions Library -->
  <script src="<?php echo asset('teacher-actions.js'); ?>"></script>
  
  <style>
    @import url("../../style.css");

    /* Make sidebar scrollable in landscape mode on mobile devices */
    @media (max-width: 768px) and (orientation: landscape) {
      .sidebar {
        max-height: 100vh;
        overflow-y: auto;
      }
    }
  </style>
</head>

<body>

  <!-- navbar -->

  <?php include resource_path('views/includes/sidenav_teacher.php'); ?>
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
          <i class="bi bi-mortarboard-fill text-success fs-1 me-3"></i>
          <h3 class="mb-0 fw-bold">Teacher Dashboard</h3>
        </div>
        <button class="btn btn-success" onclick="quickCreateQuiz()">
          <i class="bi bi-plus-circle me-2"></i> Create New Class
        </button>
      </div>

      <!-- Overview Cards -->
      <div class="row g-3 mb-4 text-center">
        <div class="col-md-3">
          <div class="border rounded-3 p-3 bg-light">
            <i class="bi bi-people-fill fs-2 text-primary"></i>
            <h5 class="fw-bold mt-2"><?php echo $totalStudents; ?></h5>
            <p class="small text-muted mb-0">Active Students</p>
          </div>
        </div>
        <div class="col-md-3">
          <div class="border rounded-3 p-3 bg-light">
            <i class="bi bi-journal-text fs-2 text-info"></i>
            <h5 class="fw-bold mt-2"><?php echo $totalMaterials; ?></h5>
            <p class="small text-muted mb-0">Uploaded Lessons</p>
          </div>
        </div>
        <div class="col-md-3">
          <div class="border rounded-3 p-3 bg-light">
            <i class="bi bi-pencil-square fs-2 text-warning"></i>
            <h5 class="fw-bold mt-2"><?php echo $pendingSubmissions; ?></h5>
            <p class="small text-muted mb-0">Pending Submissions</p>
          </div>
        </div>
        <div class="col-md-3">
          <div class="border rounded-3 p-3 bg-light">
            <i class="bi bi-file-text-fill fs-2 text-success"></i>
            <h5 class="fw-bold mt-2"><?php echo $totalAssignments; ?></h5>
            <p class="small text-muted mb-0">Total Assignments</p>
          </div>
        </div>
      </div>

      <!-- Quick Actions -->
      <h5 class="fw-bold mb-3">Quick Actions</h5>
      <div class="d-flex flex-wrap gap-2 mb-4">
        <a href="<?php echo route('teacher.lesson-materials'); ?>" class="btn btn-outline-primary">
          <i class="bi bi-upload me-2"></i> Upload Materials
        </a>
        <button class="btn btn-outline-success" onclick="quickCreateQuiz()">
          <i class="bi bi-pencil-square me-2"></i> Create Quiz
        </button>
        <a href="<?php echo route('teacher.grading'); ?>" class="btn btn-outline-warning">
          <i class="bi bi-clipboard-check me-2"></i> Grade Submissions
        </a>
        <a href="<?php echo route('teacher.analytics'); ?>" class="btn btn-outline-info">
          <i class="bi bi-chart-line me-2"></i> View Analytics
        </a>
      </div>

      <!-- My Classes Overview -->
      <h5 class="fw-bold mb-3">My Classes (<?php echo $myClasses->count(); ?>)</h5>
      <?php if($myClasses->count() > 0): ?>
        <div class="row g-3">
          <?php foreach($myClasses->take(4) as $class): ?>
          <div class="col-md-6">
            <div class="card border-0 shadow-sm">
              <div class="card-body">
                <h6 class="fw-bold text-primary"><?php echo htmlspecialchars($class->section_name); ?></h6>
                <p class="text-muted mb-2"><?php echo htmlspecialchars($class->subject_name ?? $class->class_code); ?></p>
                <small class="text-muted">
                  <i class="bi bi-calendar3 me-1"></i><?php echo htmlspecialchars($class->schedule_days); ?> 
                  <?php echo date('g:i A', strtotime($class->schedule_time_start)); ?>
                </small>
                <br>
                <small class="text-muted">
                  <i class="bi bi-geo-alt me-1"></i><?php echo htmlspecialchars($class->room); ?>
                </small>
              </div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <div class="alert alert-info">
          <i class="bi bi-info-circle me-2"></i>No classes assigned yet.
        </div>
      <?php endif; ?>

              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

</body>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>

</html>