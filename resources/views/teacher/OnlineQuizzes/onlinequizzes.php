<?php
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

$user = Auth::user();
$teacher_id = $user ? $user->user_id : null;

// Get teacher's teacher_id from teachers table
$teacher = null;
$quizzes = collect();
$recentAttempts = collect();

if ($teacher_id) {
    $teacher = DB::table('teachers')->where('user_id', $teacher_id)->first();
    
    if ($teacher) {
        // Fetch quizzes created by this teacher
        $quizzes = DB::table('quizzes')
            ->join('classes', 'quizzes.class_id', '=', 'classes.class_id')
            ->leftJoin('quiz_attempts', 'quizzes.quiz_id', '=', 'quiz_attempts.quiz_id')
            ->where('quizzes.teacher_id', $teacher->teacher_id)
        ->select(
            'quizzes.quiz_id',
            'quizzes.class_id',
            'quizzes.module_id',
            'quizzes.teacher_id',
            'quizzes.quiz_title',
            'quizzes.quiz_description',
            'quizzes.instructions',
            'quizzes.total_points',
            'quizzes.passing_score',
            'quizzes.time_limit',
            'quizzes.start_date',
            'quizzes.end_date',
            'quizzes.attempts_allowed',
            'quizzes.show_correct_answers',
            'quizzes.shuffle_questions',
            'quizzes.status',
            'quizzes.created_at',
            'quizzes.updated_at',
            'classes.section_name',
            'classes.class_code',
            DB::raw('COUNT(DISTINCT quiz_attempts.attempt_id) as total_attempts'),
            DB::raw('AVG(quiz_attempts.score) as avg_score')
        )
        ->groupBy(
            'quizzes.quiz_id',
            'quizzes.class_id',
            'quizzes.module_id',
            'quizzes.teacher_id',
            'quizzes.quiz_title',
            'quizzes.quiz_description',
            'quizzes.instructions',
            'quizzes.total_points',
            'quizzes.passing_score',
            'quizzes.time_limit',
            'quizzes.start_date',
            'quizzes.end_date',
            'quizzes.attempts_allowed',
            'quizzes.show_correct_answers',
            'quizzes.shuffle_questions',
            'quizzes.status',
            'quizzes.created_at',
            'quizzes.updated_at',
            'classes.section_name',
            'classes.class_code'
        )
        ->orderBy('quizzes.created_at', 'desc')
        ->get();
    
        // Fetch recent quiz attempts for student performance
        $recentAttempts = DB::table('quiz_attempts')
            ->join('quizzes', 'quiz_attempts.quiz_id', '=', 'quizzes.quiz_id')
            ->join('users', 'quiz_attempts.student_id', '=', 'users.user_id')
            ->where('quizzes.teacher_id', $teacher->teacher_id)
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
    }
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
  
  <!-- SheetJS for Excel Export -->
  <script src="https://cdn.sheetjs.com/xlsx-0.20.0/package/dist/xlsx.full.min.js"></script>
  
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
        <form id="createQuizForm" onsubmit="event.preventDefault(); handleCreateQuizWithQuestions();" class="mb-5">
          
          <!-- Quiz Basic Info -->
          <div class="card mb-4">
            <div class="card-header bg-primary text-white">
              <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>Quiz Information</h6>
            </div>
            <div class="card-body">
              <div class="row g-3">
                <!-- Quiz Title -->
                <div class="col-md-6">
                  <label for="quizTitle" class="form-label fw-bold">Quiz Title <span class="text-danger">*</span></label>
                  <input type="text" id="quizTitle" class="form-control" placeholder="Enter quiz title" required>
                </div>

                <!-- Time Limit -->
                <div class="col-md-6">
                  <label for="quizDuration" class="form-label fw-bold">Time Limit (minutes) <span class="text-danger">*</span></label>
                  <input type="number" id="quizDuration" class="form-control" placeholder="30" min="5" value="30" required>
                </div>

                <!-- Deadline -->
                <div class="col-md-6">
                  <label for="quizDeadline" class="form-label fw-bold">Deadline <span class="text-danger">*</span></label>
                  <input type="datetime-local" id="quizDeadline" class="form-control" required>
                </div>

                <!-- Total Points -->
                <div class="col-md-6">
                  <label for="quizPoints" class="form-label fw-bold">Total Points <span class="text-danger">*</span></label>
                  <input type="number" id="quizPoints" class="form-control" placeholder="100" min="1" value="100" required>
                </div>

                <!-- Description -->
                <div class="col-12">
                  <label for="quizDescription" class="form-label fw-bold">Description (Optional)</label>
                  <textarea id="quizDescription" class="form-control" rows="2" placeholder="Brief description of the quiz"></textarea>
                </div>

                <!-- Instructions -->
                <div class="col-12">
                  <label for="quizInstructions" class="form-label fw-bold">Instructions (Optional)</label>
                  <textarea id="quizInstructions" class="form-control" rows="2" placeholder="Instructions for students"></textarea>
                </div>
              </div>
            </div>
          </div>

          <!-- Questions Section -->
          <div class="card mb-4">
            <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
              <h6 class="mb-0"><i class="bi bi-question-circle me-2"></i>Questions</h6>
              <button type="button" class="btn btn-sm btn-light" onclick="addQuestion()">
                <i class="bi bi-plus-circle me-1"></i>Add Question
              </button>
            </div>
            <div class="card-body" id="questionsContainer">
              <div class="alert alert-info text-center">
                <i class="bi bi-arrow-up-circle fs-1 d-block mb-2"></i>
                Click "Add Question" to start adding questions to your quiz
              </div>
            </div>
          </div>

          <!-- Submit -->
          <div class="text-end">
            <button type="reset" class="btn btn-outline-secondary me-2" onclick="resetQuizForm()">
              <i class="bi bi-x-circle me-1"></i> Clear All
            </button>
            <button type="submit" class="btn btn-success btn-lg">
              <i class="bi bi-check-circle me-1"></i> Create Quiz with Questions
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

<script>
let questionCount = 0;

function addQuestion() {
  questionCount++;
  const container = document.getElementById('questionsContainer');
  
  // Remove the info message if it's the first question
  if (questionCount === 1) {
    container.innerHTML = '';
  }
  
  const questionHTML = `
    <div class="card mb-3 border-primary" id="question-${questionCount}">
      <div class="card-header bg-light d-flex justify-content-between align-items-center">
        <h6 class="mb-0"><i class="bi bi-hash me-2"></i>Question ${questionCount}</h6>
        <button type="button" class="btn btn-sm btn-danger" onclick="removeQuestion(${questionCount})">
          <i class="bi bi-trash"></i> Remove
        </button>
      </div>
      <div class="card-body">
        <div class="row g-3">
          <!-- Question Text -->
          <div class="col-12">
            <label class="form-label fw-bold">Question Text <span class="text-danger">*</span></label>
            <textarea class="form-control question-text" rows="2" placeholder="Enter your question" required></textarea>
          </div>
          
          <!-- Question Type -->
          <div class="col-md-4">
            <label class="form-label fw-bold">Question Type <span class="text-danger">*</span></label>
            <select class="form-select question-type" onchange="handleQuestionTypeChange(${questionCount})" required>
              <option value="">-- Select Type --</option>
              <option value="multiple_choice">Multiple Choice</option>
              <option value="true_false">True/False</option>
              <option value="short_answer">Short Answer</option>
            </select>
          </div>
          
          <!-- Points -->
          <div class="col-md-4">
            <label class="form-label fw-bold">Points <span class="text-danger">*</span></label>
            <input type="number" class="form-control question-points" min="1" value="1" required>
          </div>
          
          <!-- Options Container -->
          <div class="col-12" id="options-container-${questionCount}">
            <!-- Options will be added dynamically -->
          </div>
        </div>
      </div>
    </div>
  `;
  
  container.insertAdjacentHTML('beforeend', questionHTML);
}

function removeQuestion(questionId) {
  const questionElement = document.getElementById(`question-${questionId}`);
  if (questionElement) {
    questionElement.remove();
    questionCount--;
    
    // Show info message if all questions are removed
    if (questionCount === 0) {
      const container = document.getElementById('questionsContainer');
      container.innerHTML = `
        <div class="alert alert-info text-center">
          <i class="bi bi-arrow-up-circle fs-1 d-block mb-2"></i>
          Click "Add Question" to start adding questions to your quiz
        </div>
      `;
    }
  }
}

function handleQuestionTypeChange(questionId) {
  const questionCard = document.getElementById(`question-${questionId}`);
  const questionType = questionCard.querySelector('.question-type').value;
  const optionsContainer = document.getElementById(`options-container-${questionId}`);
  
  if (questionType === 'multiple_choice') {
    optionsContainer.innerHTML = `
      <label class="form-label fw-bold">Answer Options <span class="text-danger">*</span></label>
      <div id="multiple-options-${questionId}">
        <div class="input-group mb-2">
          <span class="input-group-text">
            <input type="radio" name="correct-answer-${questionId}" value="0" required>
          </span>
          <input type="text" class="form-control" placeholder="Option A" required>
        </div>
        <div class="input-group mb-2">
          <span class="input-group-text">
            <input type="radio" name="correct-answer-${questionId}" value="1" required>
          </span>
          <input type="text" class="form-control" placeholder="Option B" required>
        </div>
        <div class="input-group mb-2">
          <span class="input-group-text">
            <input type="radio" name="correct-answer-${questionId}" value="2" required>
          </span>
          <input type="text" class="form-control" placeholder="Option C" required>
        </div>
        <div class="input-group mb-2">
          <span class="input-group-text">
            <input type="radio" name="correct-answer-${questionId}" value="3" required>
          </span>
          <input type="text" class="form-control" placeholder="Option D" required>
        </div>
      </div>
      <button type="button" class="btn btn-sm btn-outline-primary mt-2" onclick="addMoreOption(${questionId})">
        <i class="bi bi-plus"></i> Add More Option
      </button>
      <small class="d-block text-muted mt-2">Select the radio button next to the correct answer</small>
    `;
  } else if (questionType === 'true_false') {
    optionsContainer.innerHTML = `
      <label class="form-label fw-bold">Correct Answer <span class="text-danger">*</span></label>
      <div class="form-check">
        <input class="form-check-input" type="radio" name="correct-answer-${questionId}" value="true" id="true-${questionId}" required>
        <label class="form-check-label" for="true-${questionId}">True</label>
      </div>
      <div class="form-check">
        <input class="form-check-input" type="radio" name="correct-answer-${questionId}" value="false" id="false-${questionId}" required>
        <label class="form-check-label" for="false-${questionId}">False</label>
      </div>
    `;
  } else if (questionType === 'short_answer') {
    optionsContainer.innerHTML = `
      <div class="alert alert-info">
        <i class="bi bi-info-circle me-2"></i>
        Short answer questions will require manual grading by the teacher.
      </div>
    `;
  } else {
    optionsContainer.innerHTML = '';
  }
}

function addMoreOption(questionId) {
  const container = document.getElementById(`multiple-options-${questionId}`);
  const optionCount = container.children.length;
  const optionHTML = `
    <div class="input-group mb-2">
      <span class="input-group-text">
        <input type="radio" name="correct-answer-${questionId}" value="${optionCount}" required>
      </span>
      <input type="text" class="form-control" placeholder="Option ${String.fromCharCode(65 + optionCount)}" required>
      <button type="button" class="btn btn-outline-danger" onclick="this.parentElement.remove()">
        <i class="bi bi-x"></i>
      </button>
    </div>
  `;
  container.insertAdjacentHTML('beforeend', optionHTML);
}

function resetQuizForm() {
  document.getElementById('createQuizForm').reset();
  questionCount = 0;
  const container = document.getElementById('questionsContainer');
  container.innerHTML = `
    <div class="alert alert-info text-center">
      <i class="bi bi-arrow-up-circle fs-1 d-block mb-2"></i>
      Click "Add Question" to start adding questions to your quiz
    </div>
  `;
}

function handleCreateQuizWithQuestions() {
  // Collect quiz data
  const quizData = {
    title: document.getElementById('quizTitle').value,
    duration: document.getElementById('quizDuration').value,
    deadline: document.getElementById('quizDeadline').value,
    total_points: document.getElementById('quizPoints').value,
    description: document.getElementById('quizDescription').value,
    instructions: document.getElementById('quizInstructions').value,
    questions: []
  };
  
  // Collect all questions
  const questionCards = document.querySelectorAll('[id^="question-"]');
  
  if (questionCards.length === 0) {
    Swal.fire({
      icon: 'warning',
      title: 'No Questions',
      text: 'Please add at least one question to the quiz'
    });
    return;
  }
  
  questionCards.forEach((card, index) => {
    const questionText = card.querySelector('.question-text').value;
    const questionType = card.querySelector('.question-type').value;
    const points = card.querySelector('.question-points').value;
    
    const questionData = {
      question_text: questionText,
      question_type: questionType,
      points: points,
      options: [],
      correct_answer: null
    };
    
    // Get correct answer and options based on type
    if (questionType === 'multiple_choice') {
      const correctAnswerRadio = card.querySelector('input[type="radio"]:checked');
      if (correctAnswerRadio) {
        questionData.correct_answer = correctAnswerRadio.value;
      }
      
      const optionInputs = card.querySelectorAll('#multiple-options-' + (index + 1) + ' input[type="text"]');
      optionInputs.forEach(input => {
        if (input.value.trim()) {
          questionData.options.push(input.value.trim());
        }
      });
    } else if (questionType === 'true_false') {
      const correctAnswerRadio = card.querySelector('input[type="radio"]:checked');
      if (correctAnswerRadio) {
        questionData.correct_answer = correctAnswerRadio.value;
      }
    }
    
    quizData.questions.push(questionData);
  });
  
  // Validate all questions have correct answers (except short answer)
  let hasError = false;
  quizData.questions.forEach((q, idx) => {
    if ((q.question_type === 'multiple_choice' || q.question_type === 'true_false') && !q.correct_answer) {
      Swal.fire({
        icon: 'error',
        title: 'Missing Correct Answer',
        text: `Please select the correct answer for Question ${idx + 1}`
      });
      hasError = true;
      return;
    }
  });
  
  if (hasError) return;
  
  // Show loading
  Swal.fire({
    title: 'Creating Quiz...',
    text: 'Please wait while we create your quiz with all questions',
    allowOutsideClick: false,
    didOpen: () => Swal.showLoading()
  });
  
  // Get CSRF token
  const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
  
  // Send to server
  fetch('/teacher/quizzes/store', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': csrfToken,
      'Accept': 'application/json'
    },
    body: JSON.stringify(quizData)
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      Swal.fire({
        icon: 'success',
        title: 'Quiz Created!',
        text: `Successfully created quiz with ${quizData.questions.length} questions`,
        showConfirmButton: true
      }).then(() => {
        resetQuizForm();
        location.reload();
      });
    } else {
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: data.message || 'Failed to create quiz'
      });
    }
  })
  .catch(error => {
    Swal.fire({
      icon: 'error',
      title: 'Error',
      text: 'Failed to create quiz. Please try again.'
    });
    console.error('Error:', error);
  });
}
</script>

</html>