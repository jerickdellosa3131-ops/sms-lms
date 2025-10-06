<?php
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

$user = Auth::user();
$teacher_id = $user ? $user->user_id : null;

// Initialize default values
$activeStudents = 0;
$completionRate = 0;
$avgQuizScore = 0;
$totalAssignments = 0;
$pendingSubmissions = 0;
$totalQuizzes = 0;
$classPerformance = collect();

if ($teacher_id) {
    // Get teacher's classes
    $teacherClasses = DB::table('classes')
        ->where('teacher_id', $teacher_id)
        ->pluck('class_id');
    
    if ($teacherClasses->count() > 0) {
        // Count active students in teacher's classes
        $activeStudents = DB::table('class_enrollments')
            ->whereIn('class_id', $teacherClasses)
            ->where('status', 'enrolled')
            ->distinct('student_id')
            ->count('student_id');
        
        // Calculate average quiz scores
        $avgQuizScore = DB::table('quiz_attempts')
            ->join('quizzes', 'quiz_attempts.quiz_id', '=', 'quizzes.quiz_id')
            ->whereIn('quizzes.class_id', $teacherClasses)
            ->avg('quiz_attempts.score') ?? 0;
        
        // Count total assignments
        $totalAssignments = DB::table('assignments')
            ->whereIn('class_id', $teacherClasses)
            ->count();
        
        // Count pending submissions
        $pendingSubmissions = DB::table('assignment_submissions')
            ->join('assignments', 'assignment_submissions.assignment_id', '=', 'assignments.assignment_id')
            ->whereIn('assignments.class_id', $teacherClasses)
            ->where('assignment_submissions.status', 'submitted')
            ->count();
        
        // Count total quizzes
        $totalQuizzes = DB::table('quizzes')
            ->whereIn('class_id', $teacherClasses)
            ->count();
        
        // Get class performance data
        $classPerformance = DB::table('classes')
            ->leftJoin('class_enrollments', 'classes.class_id', '=', 'class_enrollments.class_id')
            ->leftJoin('subjects', 'classes.subject_id', '=', 'subjects.subject_id')
            ->where('classes.teacher_id', $teacher_id)
            ->select(
                'classes.section_name',
                'classes.class_code',
                'subjects.subject_name',
                DB::raw('COUNT(DISTINCT class_enrollments.student_id) as student_count')
            )
            ->groupBy('classes.class_id', 'classes.section_name', 'classes.class_code', 'subjects.subject_name')
            ->get();
    }
}

// Round the average quiz score
$avgQuizScore = round($avgQuizScore, 1);
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
            <i class="bi bi-graph-up-arrow text-primary fs-1 me-3"></i>
            <h3 class="mb-0 fw-bold">LMS Analytics – Professor View</h3>
          </div>
          <a href="#" class="btn btn-primary text-white">
            <i class="bi bi-download me-2"></i> Export Analytics
          </a>
        </div>

        <!-- Overview Stats -->
        <div class="row g-3 text-center mb-4">
          <div class="col-md-3">
            <div class="border rounded-3 p-3 bg-light">
              <i class="bi bi-people-fill fs-2 text-success"></i>
              <h5 class="fw-bold mt-2"><?php echo number_format($activeStudents); ?></h5>
              <p class="small text-muted mb-0">Active Students</p>
            </div>
          </div>
          <div class="col-md-3">
            <div class="border rounded-3 p-3 bg-light">
              <i class="bi bi-journal-check fs-2 text-warning"></i>
              <h5 class="fw-bold mt-2"><?php echo number_format($totalAssignments); ?></h5>
              <p class="small text-muted mb-0">Total Assignments</p>
            </div>
          </div>
          <div class="col-md-3">
            <div class="border rounded-3 p-3 bg-light">
              <i class="bi bi-pencil-square fs-2 text-info"></i>
              <h5 class="fw-bold mt-2"><?php echo $avgQuizScore > 0 ? $avgQuizScore . '%' : 'N/A'; ?></h5>
              <p class="small text-muted mb-0">Avg. Quiz Scores</p>
            </div>
          </div>
          <div class="col-md-3">
            <div class="border rounded-3 p-3 bg-light">
              <i class="bi bi-clipboard-check fs-2 text-danger"></i>
              <h5 class="fw-bold mt-2"><?php echo number_format($pendingSubmissions); ?></h5>
              <p class="small text-muted mb-0">Pending Submissions</p>
            </div>
          </div>
        </div>

        <!-- Class Performance Table -->
        <h5 class="fw-bold mb-3">My Classes Overview</h5>
        <div class="table-responsive">
          <table class="table table-hover align-middle">
            <thead class="table-primary">
              <tr>
                <th>Class Name</th>
                <th>Subject</th>
                <th>Class Code</th>
                <th>Enrolled Students</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              <?php if($classPerformance->count() > 0): ?>
                <?php foreach($classPerformance as $class): ?>
                  <tr>
                    <td>
                      <strong><?php echo htmlspecialchars($class->section_name); ?></strong>
                    </td>
                    <td><?php echo htmlspecialchars($class->subject_name ?? 'N/A'); ?></td>
                    <td>
                      <code><?php echo htmlspecialchars($class->class_code); ?></code>
                    </td>
                    <td>
                      <span class="badge bg-primary">
                        <?php echo number_format($class->student_count); ?> Students
                      </span>
                    </td>
                    <td>
                      <span class="badge bg-success">Active</span>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr>
                  <td colspan="5" class="text-center text-muted py-4">
                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                    No classes assigned yet.
                  </td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>

        <!-- Quick Stats Cards -->
        <div class="row g-3 mt-4">
          <div class="col-md-6">
            <div class="card border-0 shadow-sm">
              <div class="card-body">
                <h6 class="fw-bold mb-3">
                  <i class="bi bi-clipboard-check text-warning me-2"></i>Assignments Summary
                </h6>
                <div class="d-flex justify-content-between align-items-center mb-2">
                  <span>Total Assignments</span>
                  <strong><?php echo number_format($totalAssignments); ?></strong>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                  <span>Pending Grading</span>
                  <strong class="text-danger"><?php echo number_format($pendingSubmissions); ?></strong>
                </div>
              </div>
            </div>
          </div>
          <div class="col-md-6">
            <div class="card border-0 shadow-sm">
              <div class="card-body">
                <h6 class="fw-bold mb-3">
                  <i class="bi bi-patch-question text-info me-2"></i>Quizzes Summary
                </h6>
                <div class="d-flex justify-content-between align-items-center mb-2">
                  <span>Total Quizzes</span>
                  <strong><?php echo number_format($totalQuizzes); ?></strong>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                  <span>Average Score</span>
                  <strong class="text-success"><?php echo $avgQuizScore > 0 ? $avgQuizScore . '%' : 'N/A'; ?></strong>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Insights -->
        <div class="mt-4">
          <h6 class="fw-bold">Insights</h6>
          <ul class="list-unstyled text-muted">
            <li><i class="bi bi-check-circle-fill text-success me-2"></i> Students with high engagement show better quiz performance.</li>
            <li><i class="bi bi-lightbulb-fill text-info me-2"></i> Medium-engagement students may need more interactive content.</li>
            <li><i class="bi bi-exclamation-circle-fill text-warning me-2"></i> At-risk students identified with low module completion and time spent.</li>
          </ul>
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