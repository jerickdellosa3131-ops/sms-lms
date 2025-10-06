<?php
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

$user = Auth::user();
$teacher_id = $user ? $user->user_id : null;

if ($teacher_id) {
    // Fetch quizzes created by this teacher
    $quizzes = DB::table('quizzes')
        ->join('classes', 'quizzes.class_id', '=', 'classes.class_id')
        ->leftJoin('quiz_attempts', 'quizzes.quiz_id', '=', 'quiz_attempts.quiz_id')
        ->where('quizzes.teacher_id', $teacher_id)
        ->select(
            'quizzes.*',
            'classes.section_name',
            'classes.class_code',
            DB::raw('COUNT(DISTINCT quiz_attempts.attempt_id) as total_attempts'),
            DB::raw('AVG(quiz_attempts.score) as avg_score')
        )
        ->groupBy('quizzes.quiz_id')
        ->orderBy('quizzes.created_at', 'desc')
        ->get();
    
    // Fetch recent quiz attempts for student performance
    $recentAttempts = DB::table('quiz_attempts')
        ->join('quizzes', 'quiz_attempts.quiz_id', '=', 'quizzes.quiz_id')
        ->join('users', 'quiz_attempts.student_id', '=', 'users.user_id')
        ->where('quizzes.teacher_id', $teacher_id)
        ->select(
            'quiz_attempts.*',
            'quizzes.quiz_title',
            'quizzes.total_points',
            'users.first_name',
            'users.last_name'
        )
        ->orderBy('quiz_attempts.end_time', 'desc')
        ->limit(10)
        ->get();
} else {
    $quizzes = collect();
    $recentAttempts = collect();
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
  
  <!-- SheetJS for Excel Export -->
  <script src="https://cdn.sheetjs.com/xlsx-0.20.0/package/dist/xlsx.full.min.js"></script>
  
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
            <i class="bi bi-question-circle-fill text-success fs-1 me-3"></i>
            <h3 class="mb-0 fw-bold">Online Quizzes</h3>
          </div>
          <div class="btn-group">
            <button class="btn btn-primary" onclick="quickCreateQuiz()">
              <i class="bi bi-plus-circle me-2"></i> Create New Quiz
            </button>
            <button class="btn btn-success" onclick="exportTableToExcel('quiz-table', 'Quiz_List')">
              <i class="bi bi-file-earmark-excel me-2"></i> Export
            </button>
          </div>
        </div>

        <!-- Create Quiz -->
        <h5 class="fw-bold mb-3">Create New Quiz</h5>
        <form action="#" method="post" class="mb-5">
          <div class="row g-3">
            <!-- Quiz Title -->
            <div class="col-md-6">
              <label for="quizTitle" class="form-label fw-bold">Quiz Title</label>
              <input type="text" id="quizTitle" class="form-control" placeholder="Enter quiz title" required>
            </div>

            <!-- Subject/Class -->
            <div class="col-md-6">
              <label for="quizClass" class="form-label fw-bold">Class</label>
              <select id="quizClass" class="form-select" required>
                <option value="" disabled selected>-- Select Class --</option>
                <option>Math 101</option>
                <option>Science 202</option>
                <option>English 303</option>
              </select>
            </div>

            <!-- Deadline -->
            <div class="col-md-6">
              <label for="quizDeadline" class="form-label fw-bold">Deadline</label>
              <input type="datetime-local" id="quizDeadline" class="form-control" required>
            </div>

            <!-- Question Type -->
            <div class="col-md-6">
              <label for="quizType" class="form-label fw-bold">Question Type</label>
              <select id="quizType" class="form-select" required>
                <option value="" disabled selected>-- Select Type --</option>
                <option>Multiple Choice</option>
                <option>True/False</option>
                <option>Essay</option>
              </select>
            </div>
          </div>

          <!-- Submit -->
          <div class="mt-4 text-end">
            <button type="reset" class="btn btn-outline-secondary me-2">
              <i class="bi bi-x-circle me-1"></i> Clear
            </button>
            <button type="submit" class="btn btn-success">
              <i class="bi bi-plus-circle me-1"></i> Create Quiz
            </button>
          </div>
        </form>

        <!-- Quiz List -->
        <h5 class="fw-bold mb-3">Existing Quizzes (<?php echo $quizzes->count(); ?>)</h5>
        <div class="table-responsive">
          <table id="quiz-table" class="table table-hover align-middle">
            <thead class="table-success">
              <tr>
                <th>Quiz Title</th>
                <th>Class</th>
                <th>Time Limit</th>
                <th>Total Points</th>
                <th>Attempts</th>
                <th>Avg Score</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php if($quizzes->count() > 0): ?>
                <?php foreach($quizzes as $quiz): ?>
                <tr>
                  <td>
                    <strong><?php echo htmlspecialchars($quiz->quiz_title); ?></strong>
                    <?php if($quiz->quiz_description): ?>
                      <br><small class="text-muted"><?php echo htmlspecialchars(substr($quiz->quiz_description, 0, 40)); ?>...</small>
                    <?php endif; ?>
                  </td>
                  <td><?php echo htmlspecialchars($quiz->section_name); ?></td>
                  <td><?php echo $quiz->time_limit; ?> mins</td>
                  <td><?php echo $quiz->total_points; ?></td>
                  <td><span class="badge bg-info"><?php echo $quiz->total_attempts; ?></span></td>
                  <td>
                    <?php if($quiz->avg_score): ?>
                      <strong class="<?php echo $quiz->avg_score >= 75 ? 'text-success' : 'text-warning'; ?>">
                        <?php echo round($quiz->avg_score, 1); ?>%
                      </strong>
                    <?php else: ?>
                      <span class="text-muted">N/A</span>
                    <?php endif; ?>
                  </td>
                  <td>
                    <?php if($quiz->status == 'active'): ?>
                      <span class="badge bg-success">Active</span>
                    <?php else: ?>
                      <span class="badge bg-secondary">Inactive</span>
                    <?php endif; ?>
                  </td>
                  <td>
                    <button class="btn btn-sm btn-outline-primary" onclick="viewRecord(<?php echo $quiz->quiz_id; ?>, 'Quiz', {title: '<?php echo addslashes($quiz->quiz_title); ?>', attempts: <?php echo $quiz->total_attempts; ?>})">
                      <i class="bi bi-eye"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-warning" onclick="editRecord(<?php echo $quiz->quiz_id; ?>, 'Quiz', {title: '<?php echo addslashes($quiz->quiz_title); ?>', points: <?php echo $quiz->total_points; ?>})">
                      <i class="bi bi-pencil"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-danger" onclick="deleteRecord(<?php echo $quiz->quiz_id; ?>, 'Quiz', '<?php echo addslashes($quiz->quiz_title); ?>')">
                      <i class="bi bi-trash"></i>
                    </button>
                  </td>
                </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr>
                  <td colspan="8" class="text-center text-muted py-4">
                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                    No quizzes created yet. Create your first quiz above!
                  </td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>

        <!-- Student Performance -->
        <h5 class="fw-bold mt-5 mb-3">Recent Quiz Attempts (<?php echo $recentAttempts->count(); ?>)</h5>
        <div class="table-responsive">
          <table class="table table-hover align-middle">
            <thead class="table-light">
              <tr>
                <th>Student Name</th>
                <th>Quiz</th>
                <th>Score</th>
                <th>Percentage</th>
                <th>Status</th>
                <th>Completed</th>
              </tr>
            </thead>
            <tbody>
              <?php if($recentAttempts->count() > 0): ?>
                <?php foreach($recentAttempts as $attempt): 
                  $percentage = ($attempt->score / $attempt->total_points) * 100;
                  $passed = $percentage >= 75;
                ?>
                <tr>
                  <td><?php echo htmlspecialchars($attempt->first_name . ' ' . $attempt->last_name); ?></td>
                  <td><?php echo htmlspecialchars($attempt->quiz_title); ?></td>
                  <td><?php echo $attempt->score; ?>/<?php echo $attempt->total_points; ?></td>
                  <td>
                    <strong class="<?php echo $passed ? 'text-success' : 'text-danger'; ?>">
                      <?php echo round($percentage, 1); ?>%
                    </strong>
                  </td>
                  <td>
                    <?php if($attempt->status == 'completed'): ?>
                      <span class="badge <?php echo $passed ? 'bg-success' : 'bg-danger'; ?>">
                        <?php echo $passed ? 'Passed' : 'Failed'; ?>
                      </span>
                    <?php else: ?>
                      <span class="badge bg-warning">In Progress</span>
                    <?php endif; ?>
                  </td>
                  <td><?php echo $attempt->end_time ? date('M d, Y g:i A', strtotime($attempt->end_time)) : 'N/A'; ?></td>
                </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr>
                  <td colspan="6" class="text-center text-muted py-4">
                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                    No quiz attempts yet.
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
               


  <footer class="mt-auto bg-light">
    <div class="container text-center">
      <p class="text-muted">© 2023 Your Company. All rights reserved.</p>
    </div>
  </footer>


  </style>
</body>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>

</html>