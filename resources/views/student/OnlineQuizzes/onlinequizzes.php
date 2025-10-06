<?php
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

$user = Auth::user();
$user_id = $user ? $user->user_id : null;

// Get the actual student_id from students table
$student = null;
if ($user_id) {
    $student = DB::table('students')->where('user_id', $user_id)->first();
}

if ($student) {
    $student_id = $student->student_id;
    
    // Fetch available quizzes for enrolled classes
    $quizzes = DB::table('quizzes')
        ->join('classes', 'quizzes.class_id', '=', 'classes.class_id')
        ->join('class_enrollments', 'classes.class_id', '=', 'class_enrollments.class_id')
        ->leftJoin('quiz_attempts', function($join) use ($student_id) {
            $join->on('quizzes.quiz_id', '=', 'quiz_attempts.quiz_id')
                 ->where('quiz_attempts.student_id', '=', $student_id);
        })
        ->where('class_enrollments.student_id', $student_id)
        ->where('quizzes.status', 'active')
        ->select(
            'quizzes.*',
            'classes.section_name',
            'quiz_attempts.score',
            'quiz_attempts.status as attempt_status'
        )
        ->get();
    
    // Fetch quiz history (completed attempts)
    $quizHistory = DB::table('quiz_attempts')
        ->join('quizzes', 'quiz_attempts.quiz_id', '=', 'quizzes.quiz_id')
        ->join('classes', 'quizzes.class_id', '=', 'classes.class_id')
        ->where('quiz_attempts.student_id', $student_id)
        ->where('quiz_attempts.status', 'completed')
        ->select(
            'quiz_attempts.*',
            'quizzes.quiz_title',
            'quizzes.total_points',
            'classes.section_name'
        )
        ->orderBy('quiz_attempts.end_time', 'desc')
        ->limit(10)
        ->get();
} else {
    $quizzes = collect();
    $quizHistory = collect();
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
  
  <!-- Student Actions Library -->
  <script src="<?php echo asset('student-actions.js'); ?>"></script>
  
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
            <i class="bi bi-journal-text text-primary fs-1 me-3"></i>
            <div>
              <h3 class="mb-0 fw-bold">Online Quizzes</h3>
              <p class="text-muted small mb-0">Choose a subject and start your quiz</p>
            </div>
          </div>
          <a href="<?php echo route('student.class-portal'); ?>" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i> Back to Courses
          </a>
        </div>

        <!-- Available Quizzes -->
        <h5 class="fw-bold mb-3">Available Quizzes (<?php echo $quizzes->count(); ?>)</h5>
        
        <?php if($quizzes->count() > 0): ?>
          <div class="row g-3">
            <?php 
            $colors = ['primary', 'success', 'warning', 'info', 'danger'];
            $icons = ['bi-journal-check', 'bi-lightbulb', 'bi-calculator', 'bi-book', 'bi-clipboard'];
            $colorIndex = 0;
            foreach($quizzes as $quiz): 
              $color = $colors[$colorIndex % 5];
              $icon = $icons[$colorIndex % 5];
              $colorIndex++;
            ?>
            <div class="col-md-4">
              <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                  <i class="bi <?php echo $icon; ?> fs-1 text-<?php echo $color; ?> mb-2"></i>
                  <h6 class="fw-bold"><?php echo htmlspecialchars($quiz->quiz_title); ?></h6>
                  <p class="text-muted small mb-1">
                    <?php echo $quiz->time_limit; ?> mins | <?php echo $quiz->total_points; ?> pts
                  </p>
                  <p class="small text-muted"><?php echo htmlspecialchars($quiz->section_name); ?></p>
                  
                  <?php if($quiz->attempt_status == 'completed'): ?>
                    <div class="mb-2">
                      <span class="badge bg-success">Completed</span>
                      <?php if($quiz->score): ?>
                        <br><small>Score: <?php echo $quiz->score; ?>/<?php echo $quiz->total_points; ?></small>
                      <?php endif; ?>
                    </div>
                    <button class="btn btn-outline-<?php echo $color; ?> btn-sm w-100" disabled>
                      Already Taken
                    </button>
                  <?php else: ?>
                    <button class="btn btn-outline-<?php echo $color; ?> btn-sm w-100" 
                            onclick="startQuiz(<?php echo $quiz->quiz_id; ?>, '<?php echo addslashes($quiz->quiz_title); ?>')">
                      <i class="bi bi-play-fill me-1"></i>Start Quiz
                    </button>
                  <?php endif; ?>
                </div>
              </div>
            </div>
            <?php endforeach; ?>
          </div>
        <?php else: ?>
          <div class="alert alert-info text-center">
            <i class="bi bi-inbox fs-1 d-block mb-2"></i>
            No quizzes available at the moment. Check back later!
          </div>
        <?php endif; ?>

        <!-- Quiz History -->
        <div class="mt-5">
          <h6 class="fw-bold mb-3">Previous Quiz Results (<?php echo $quizHistory->count(); ?>)</h6>
          <div class="table-responsive">
            <table class="table table-hover align-middle">
              <thead class="table-primary">
                <tr>
                  <th>Class</th>
                  <th>Quiz</th>
                  <th>Date Taken</th>
                  <th>Score</th>
                  <th>Percentage</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                <?php if($quizHistory->count() > 0): ?>
                  <?php foreach($quizHistory as $history): 
                    $percentage = ($history->score / $history->total_points) * 100;
                    $passed = $percentage >= 75;
                  ?>
                  <tr>
                    <td><?php echo htmlspecialchars($history->section_name); ?></td>
                    <td><?php echo htmlspecialchars($history->quiz_title); ?></td>
                    <td><?php echo date('M d, Y', strtotime($history->end_time)); ?></td>
                    <td><?php echo $history->score; ?>/<?php echo $history->total_points; ?></td>
                    <td>
                      <strong class="<?php echo $passed ? 'text-success' : 'text-danger'; ?>">
                        <?php echo round($percentage, 1); ?>%
                      </strong>
                    </td>
                    <td>
                      <span class="badge <?php echo $passed ? 'bg-success' : 'bg-danger'; ?>">
                        <?php echo $passed ? 'Passed' : 'Failed'; ?>
                      </span>
                    </td>
                  </tr>
                  <?php endforeach; ?>
                <?php else: ?>
                  <tr>
                    <td colspan="6" class="text-center text-muted py-4">
                      <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                      No quiz history yet. Take a quiz to see your results here!
                    </td>
                  </tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
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