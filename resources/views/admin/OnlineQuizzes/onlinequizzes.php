<?php
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

// Get authenticated user
$user = Auth::user();

// Fetch quizzes with attempt statistics (handle missing quiz_submissions table)
try {
    $quizzes = DB::table('quizzes')
        ->join('classes', 'quizzes.class_id', '=', 'classes.class_id')
        ->leftJoin(DB::raw('(SELECT quiz_id, COUNT(*) as attempts, AVG(score) as avg_score FROM quiz_submissions GROUP BY quiz_id) as stats'), 
            'quizzes.quiz_id', '=', 'stats.quiz_id')
        ->select(
            'quizzes.*',
            'classes.section_name',
            'classes.class_code',
            DB::raw('COALESCE(stats.attempts, 0) as total_attempts'),
            DB::raw('COALESCE(stats.avg_score, 0) as average_score')
        )
        ->orderBy('quizzes.created_at', 'desc')
        ->get();
} catch (\Exception $e) {
    // If quiz_submissions table doesn't exist, fetch without stats
    $quizzes = DB::table('quizzes')
        ->join('classes', 'quizzes.class_id', '=', 'classes.class_id')
        ->select(
            'quizzes.*',
            'classes.section_name',
            'classes.class_code',
            DB::raw('0 as total_attempts'),
            DB::raw('0 as average_score')
        )
        ->orderBy('quizzes.created_at', 'desc')
        ->get();
}

// Calculate statistics
$totalQuizzes = $quizzes->count();
$totalAttempts = $quizzes->sum('total_attempts');
$avgScore = $quizzes->where('total_attempts', '>', 0)->avg('average_score') ?? 0;
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
  
  <!-- SheetJS for Excel Export -->
  <script src="https://cdn.sheetjs.com/xlsx-0.20.0/package/dist/xlsx.full.min.js"></script>
  
  <!-- SweetAlert2 for Alerts -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  
  <!-- Admin Actions Library -->
  <script src="<?php echo asset('js/admin-actions.js'); ?>"></script>
  
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

  <?php include resource_path('views/includes/sidenav_admin.php'); ?>
  <div class="main-content flex-grow-1">

    <div class="container my-4">
      <div class="card shadow-lg border-0 rounded-4">
        <div class="card-body p-4">
          <!-- Header -->
          <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="d-flex align-items-center">
              <i class="bi bi-question-circle-fill text-primary fs-1 me-3"></i>
              <h3 class="mb-0 fw-bold">Online Quizzes</h3>
            </div>
            <div class="btn-group">
              <button class="btn btn-primary text-white" data-bs-toggle="modal" data-bs-target="#createQuizModal">
                <i class="bi bi-plus-circle me-2"></i> Create New Quiz
              </button>
              <button class="btn btn-success text-white" onclick="exportCurrentTable('quizzesTable', 'quizzes')">
                <i class="bi bi-file-earmark-excel me-2"></i> Export
              </button>
            </div>
          </div>

          <!-- Quiz Overview Stats -->
          <div class="row g-3 text-center mb-4">
            <div class="col-md-3">
              <div class="border rounded-3 p-3 bg-light">
                <i class="bi bi-journal-check fs-2 text-success"></i>
                <h5 class="fw-bold mt-2"><?php echo number_format($totalQuizzes); ?></h5>
                <p class="small text-muted mb-0">Quizzes Created</p>
              </div>
            </div>
            <div class="col-md-3">
              <div class="border rounded-3 p-3 bg-light">
                <i class="bi bi-people-fill fs-2 text-info"></i>
                <h5 class="fw-bold mt-2"><?php echo number_format($totalAttempts); ?></h5>
                <p class="small text-muted mb-0">Total Attempts</p>
              </div>
            </div>
            <div class="col-md-3">
              <div class="border rounded-3 p-3 bg-light">
                <i class="bi bi-graph-up-arrow fs-2 text-warning"></i>
                <h5 class="fw-bold mt-2"><?php echo round($avgScore, 1); ?>%</h5>
                <p class="small text-muted mb-0">Avg. Score</p>
              </div>
            </div>
            <div class="col-md-3">
              <div class="border rounded-3 p-3 bg-light">
                <i class="bi bi-calendar-check fs-2 text-primary"></i>
                <h5 class="fw-bold mt-2"><?php echo $quizzes->where('status', 'published')->count(); ?></h5>
                <p class="small text-muted mb-0">Active Quizzes</p>
              </div>
            </div>
          </div>

          <!-- Table of Quizzes -->
          <h5 class="fw-bold mb-3">Recent Quizzes</h5>
          <div class="table-responsive">
            <table id="quizzesTable" class="table table-hover align-middle">
              <thead class="table-primary">
                <tr>
                  <th>Quiz Title</th>
                  <th>Created On</th>
                  <th>Attempts</th>
                  <th>Average Score</th>
                  <th>Deadline</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php if($quizzes->count() > 0): ?>
                  <?php foreach($quizzes as $quiz): ?>
                  <tr>
                    <td>
                      <strong><?php echo htmlspecialchars($quiz->quiz_title ?? $quiz->title ?? 'Untitled Quiz'); ?></strong><br>
                      <small class="text-muted"><?php echo htmlspecialchars($quiz->section_name); ?></small>
                    </td>
                    <td><?php echo date('M d, Y', strtotime($quiz->created_at)); ?></td>
                    <td><span class="badge bg-info"><?php echo $quiz->total_attempts; ?></span></td>
                    <td>
                      <?php if($quiz->total_attempts > 0): ?>
                        <span class="badge bg-<?php echo $quiz->average_score >= 75 ? 'success' : 'warning'; ?>">
                          <?php echo round($quiz->average_score, 1); ?>%
                        </span>
                      <?php else: ?>
                        <span class="text-muted">No attempts</span>
                      <?php endif; ?>
                    </td>
                    <td><?php echo isset($quiz->deadline) && $quiz->deadline ? date('M d, Y', strtotime($quiz->deadline)) : 'No deadline'; ?></td>
                    <td>
                      <button class="btn btn-sm btn-outline-info me-1" title="View" 
                        onclick="viewRecord(<?php echo $quiz->quiz_id; ?>, 'Quiz', {
                          'Quiz Title': '<?php echo addslashes($quiz->quiz_title ?? $quiz->title ?? 'Untitled'); ?>',
                          'Section': '<?php echo addslashes($quiz->section_name); ?>',
                          'Created': '<?php echo date('M d, Y', strtotime($quiz->created_at)); ?>',
                          'Attempts': '<?php echo $quiz->total_attempts; ?>',
                          'Average Score': '<?php echo round($quiz->average_score, 1); ?>%',
                          'Deadline': '<?php echo isset($quiz->deadline) && $quiz->deadline ? date('M d, Y', strtotime($quiz->deadline)) : 'No deadline'; ?>'
                        })">
                        <i class="bi bi-eye"></i>
                      </button>
                      <button class="btn btn-sm btn-outline-warning me-1" title="Edit"
                        onclick="editRecord(<?php echo $quiz->quiz_id; ?>, 'Quiz', {
                          'title': '<?php echo addslashes($quiz->quiz_title ?? $quiz->title ?? ''); ?>',
                          'deadline': '<?php echo $quiz->deadline ?? ''; ?>'
                        })">
                        <i class="bi bi-pencil"></i>
                      </button>
                      <button class="btn btn-sm btn-outline-danger" title="Delete"
                        onclick="deleteRecord(<?php echo $quiz->quiz_id; ?>, 'Quiz', '<?php echo addslashes($quiz->quiz_title ?? $quiz->title ?? 'Untitled Quiz'); ?>')">
                        <i class="bi bi-trash"></i>
                      </button>
                    </td>
                  </tr>
                  <?php endforeach; ?>
                <?php else: ?>
                  <tr>
                    <td colspan="6" class="text-center text-muted">No quizzes created yet</td>
                  </tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>

        
        </div>
      </div>
    </div>
  </div>



 


  </style>

  <!-- Create Quiz Modal -->
  <div class="modal fade" id="createQuizModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
      <div class="modal-content">
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title"><i class="bi bi-patch-question-fill me-2"></i>Create New Quiz</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <form id="createQuizForm">
            <!-- Basic Info -->
            <div class="row mb-3">
              <div class="col-md-8">
                <label for="quizTitle" class="form-label fw-bold">Quiz Title <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="quizTitle" placeholder="e.g., Math Quiz 3" required>
              </div>
              <div class="col-md-4">
                <label for="quizSubject" class="form-label fw-bold">Subject</label>
                <select class="form-select" id="quizSubject">
                  <option value="math">Mathematics</option>
                  <option value="science">Science</option>
                  <option value="english">English</option>
                  <option value="cs">Computer Science</option>
                </select>
              </div>
            </div>

            <div class="mb-3">
              <label for="quizDescription" class="form-label fw-bold">Description</label>
              <textarea class="form-control" id="quizDescription" rows="2" placeholder="Brief description of the quiz"></textarea>
            </div>

            <!-- Settings -->
            <div class="row mb-3">
              <div class="col-md-3">
                <label for="quizDuration" class="form-label fw-bold">Duration (min)</label>
                <input type="number" class="form-control" id="quizDuration" value="30" min="1">
              </div>
              <div class="col-md-3">
                <label for="quizTotalPoints" class="form-label fw-bold">Total Points</label>
                <input type="number" class="form-control" id="quizTotalPoints" value="100" min="1">
              </div>
              <div class="col-md-3">
                <label for="quizPassingScore" class="form-label fw-bold">Passing (%)</label>
                <input type="number" class="form-control" id="quizPassingScore" value="60" min="0" max="100">
              </div>
              <div class="col-md-3">
                <label for="quizAttempts" class="form-label fw-bold">Max Attempts</label>
                <input type="number" class="form-control" id="quizAttempts" value="1" min="1">
              </div>
            </div>

            <!-- Availability -->
            <div class="row mb-3">
              <div class="col-md-6">
                <label for="quizStartDate" class="form-label fw-bold">Available From</label>
                <input type="datetime-local" class="form-control" id="quizStartDate">
              </div>
              <div class="col-md-6">
                <label for="quizEndDate" class="form-label fw-bold">Available Until</label>
                <input type="datetime-local" class="form-control" id="quizEndDate">
              </div>
            </div>

            <!-- Options -->
            <div class="mb-3">
              <label class="form-label fw-bold">Quiz Options</label>
              <div class="form-check">
                <input class="form-check-input" type="checkbox" id="randomizeQuestions">
                <label class="form-check-label" for="randomizeQuestions">
                  Randomize question order for each student
                </label>
              </div>
              <div class="form-check">
                <input class="form-check-input" type="checkbox" id="showAnswers" checked>
                <label class="form-check-label" for="showAnswers">
                  Show correct answers after submission
                </label>
              </div>
              <div class="form-check">
                <input class="form-check-input" type="checkbox" id="allowReview">
                <label class="form-check-label" for="allowReview">
                  Allow students to review their answers
                </label>
              </div>
              <div class="form-check">
                <input class="form-check-input" type="checkbox" id="showScoreImmediately" checked>
                <label class="form-check-label" for="showScoreImmediately">
                  Show score immediately after submission
                </label>
              </div>
            </div>

            <!-- Question Bank -->
            <div class="border rounded p-3 bg-light">
              <h6 class="fw-bold mb-3"><i class="bi bi-list-check me-2"></i>Quick Add Questions</h6>
              <div class="row">
                <div class="col-md-4">
                  <label class="form-label">Multiple Choice</label>
                  <input type="number" class="form-control" placeholder="0" min="0">
                </div>
                <div class="col-md-4">
                  <label class="form-label">True/False</label>
                  <input type="number" class="form-control" placeholder="0" min="0">
                </div>
                <div class="col-md-4">
                  <label class="form-label">Short Answer</label>
                  <input type="number" class="form-control" placeholder="0" min="0">
                </div>
              </div>
              <small class="text-muted d-block mt-2">
                <i class="bi bi-info-circle me-1"></i>You can add detailed questions after creating the quiz
              </small>
            </div>

          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
            <i class="bi bi-x-lg me-2"></i>Cancel
          </button>
          <button type="button" class="btn btn-primary" onclick="handleCreateQuiz()">
            <i class="bi bi-check-lg me-2"></i>Create Quiz
          </button>
        </div>
      </div>
    </div>
  </div>

</body>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
  function handleCreateQuiz() {
    const title = document.getElementById('quizTitle').value;
    if (!title) {
      Swal.fire('Error', 'Please enter a quiz title', 'error');
      return;
    }
    
    Swal.fire({ title: 'Creating Quiz...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
    
    fetch('/admin/quizzes/store', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
      },
      body: JSON.stringify({ title, duration: 30, total_points: 100 })
    })
    .then(r => r.json())
    .then(d => {
      if (d.success) {
        Swal.fire('Success!', 'Quiz created! You can now add questions.', 'success').then(() => location.reload());
      } else {
        Swal.fire('Error', d.message, 'error');
      }
    })
    .catch(() => Swal.fire('Error', 'Failed to create quiz', 'error'));
  }

  // Export Quizzes to Excel
  function exportQuizzesExcel() {
    const wb = XLSX.utils.book_new();
    
    const data = [
      ['Online Quizzes Report'],
      [''],
      ['Quiz Title', 'Created On', 'Attempts', 'Average Score', 'Deadline'],
      ['Math Quiz 3', '2025-08-05', '40', '82%', '2025-08-10'],
      ['Science Quiz 2', '2025-08-02', '35', '76%', '2025-08-07']
    ];
    
    const ws = XLSX.utils.aoa_to_sheet(data);
    ws['!cols'] = [{ wch: 20 }, { wch: 15 }, { wch: 10 }, { wch: 15 }, { wch: 15 }];
    
    XLSX.utils.book_append_sheet(wb, ws, 'Quizzes');
    XLSX.writeFile(wb, 'online-quizzes-report.xlsx');
    
    alert('Quiz report exported successfully!');
  }
</script>

</html>