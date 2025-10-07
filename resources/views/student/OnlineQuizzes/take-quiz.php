<?php
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

$user = Auth::user();
$user_id = $user ? $user->user_id : null;

// Get quiz_id from route
$quiz_id = request()->route('quiz_id');

// Get student info
$student = null;
if ($user_id) {
    $student = DB::table('students')->where('user_id', $user_id)->first();
}

if (!$student) {
    abort(403, 'Student record not found');
}

$student_id = $student->student_id;

// Fetch quiz details
$quiz = DB::table('quizzes')
    ->join('classes', 'quizzes.class_id', '=', 'classes.class_id')
    ->where('quizzes.quiz_id', $quiz_id)
    ->where('quizzes.status', 'published')
    ->select('quizzes.*', 'classes.section_name')
    ->first();

if (!$quiz) {
    abort(404, 'Quiz not found or not available');
}

// Verify student is enrolled in this class
$enrollment = DB::table('class_enrollments')
    ->where('student_id', $student_id)
    ->where('class_id', $quiz->class_id)
    ->where('status', 'enrolled')
    ->first();

if (!$enrollment) {
    abort(403, 'You are not enrolled in this class');
}

// Check if student has already taken this quiz
$existingAttempt = DB::table('quiz_attempts')
    ->where('quiz_id', $quiz_id)
    ->where('student_id', $student_id)
    ->whereIn('status', ['submitted', 'graded'])
    ->first();

if ($existingAttempt) {
    header('Location: ' . route('student.quizzes'));
    exit;
}

// Fetch quiz questions with options
$questions = DB::table('quiz_questions')
    ->where('quiz_id', $quiz_id)
    ->orderBy('question_order')
    ->get();

$questionsWithOptions = [];
foreach ($questions as $question) {
    $options = DB::table('quiz_question_options')
        ->where('question_id', $question->question_id)
        ->orderBy('option_order')
        ->get();
    
    $question->options = $options;
    $questionsWithOptions[] = $question;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="<?php echo csrf_token(); ?>">
  <title>Take Quiz - <?php echo htmlspecialchars($quiz->quiz_title); ?></title>

  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="<?php echo asset('style.css'); ?>">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
  
  <!-- SweetAlert2 -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  
  <style>
    @import url("../../style.css");
    
    .quiz-container {
      max-width: 900px;
      margin: 0 auto;
    }
    
    .quiz-header {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
      padding: 2rem;
      border-radius: 15px;
      margin-bottom: 2rem;
    }
    
    .timer-box {
      background: rgba(255, 255, 255, 0.2);
      padding: 1rem;
      border-radius: 10px;
      text-align: center;
    }
    
    .question-card {
      background: white;
      border-radius: 15px;
      padding: 2rem;
      margin-bottom: 1.5rem;
      box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    
    .option-item {
      padding: 1rem;
      border: 2px solid #e0e0e0;
      border-radius: 10px;
      margin-bottom: 0.75rem;
      cursor: pointer;
      transition: all 0.3s ease;
    }
    
    .option-item:hover {
      border-color: #667eea;
      background-color: #f8f9ff;
    }
    
    .option-item input[type="radio"] {
      margin-right: 0.75rem;
    }
    
    .option-item.selected {
      border-color: #667eea;
      background-color: #f0f3ff;
    }
    
    .progress-tracker {
      position: fixed;
      top: 100px;
      right: 20px;
      background: white;
      padding: 1rem;
      border-radius: 10px;
      box-shadow: 0 5px 15px rgba(0,0,0,0.1);
      max-width: 200px;
    }
    
    @media (max-width: 768px) {
      .progress-tracker {
        position: static;
        max-width: 100%;
        margin-bottom: 1rem;
      }
    }
  </style>
</head>

<body>
  <?php include resource_path('views/includes/sidenav_student.php'); ?>
  
  <div class="main-content flex-grow-1">
    <div class="container quiz-container my-5">
      
      <!-- Quiz Header -->
      <div class="quiz-header">
        <div class="row align-items-center">
          <div class="col-md-8">
            <h2 class="mb-0"><i class="bi bi-clipboard-check me-2"></i><?php echo htmlspecialchars($quiz->quiz_title); ?></h2>
            <p class="mb-0 mt-2"><?php echo htmlspecialchars($quiz->section_name); ?></p>
            <?php if($quiz->quiz_description): ?>
              <p class="mb-0 mt-2"><?php echo htmlspecialchars($quiz->quiz_description); ?></p>
            <?php endif; ?>
          </div>
          <div class="col-md-4">
            <div class="timer-box">
              <h5 class="mb-0"><i class="bi bi-clock me-2"></i>Time Left</h5>
              <?php if(count($questionsWithOptions) > 0): ?>
                <h3 class="mb-0 mt-2" id="timer"><?php echo $quiz->time_limit ?? 30; ?>:00</h3>
              <?php else: ?>
                <h3 class="mb-0 mt-2 text-muted">--:--</h3>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>

      <!-- Instructions -->
      <?php if($quiz->instructions): ?>
      <div class="alert alert-info mb-4">
        <h6><i class="bi bi-info-circle me-2"></i>Instructions</h6>
        <p class="mb-0"><?php echo nl2br(htmlspecialchars($quiz->instructions)); ?></p>
      </div>
      <?php endif; ?>

      <!-- Quiz Form -->
      <form id="quizForm" method="POST">
        <input type="hidden" name="quiz_id" value="<?php echo $quiz_id; ?>">
        <input type="hidden" name="start_time" id="start_time" value="<?php echo date('Y-m-d H:i:s'); ?>">
        
        <?php if(count($questionsWithOptions) > 0): ?>
          <?php foreach($questionsWithOptions as $index => $question): ?>
          <div class="question-card">
            <div class="d-flex justify-content-between align-items-start mb-3">
              <h5 class="fw-bold">Question <?php echo $index + 1; ?></h5>
              <span class="badge bg-primary"><?php echo $question->points; ?> points</span>
            </div>
            
            <p class="mb-3"><?php echo htmlspecialchars($question->question_text); ?></p>
            
            <?php if($question->question_type === 'multiple_choice'): ?>
              <div class="options-container">
                <?php foreach($question->options as $option): ?>
                <label class="option-item">
                  <input type="radio" 
                         name="question_<?php echo $question->question_id; ?>" 
                         value="<?php echo $option->option_id; ?>"
                         required>
                  <span><?php echo htmlspecialchars($option->option_text); ?></span>
                </label>
                <?php endforeach; ?>
              </div>
            <?php elseif($question->question_type === 'true_false'): ?>
              <div class="options-container">
                <label class="option-item">
                  <input type="radio" 
                         name="question_<?php echo $question->question_id; ?>" 
                         value="true"
                         required>
                  <span>True</span>
                </label>
                <label class="option-item">
                  <input type="radio" 
                         name="question_<?php echo $question->question_id; ?>" 
                         value="false"
                         required>
                  <span>False</span>
                </label>
              </div>
            <?php elseif($question->question_type === 'short_answer'): ?>
              <textarea class="form-control" 
                        name="question_<?php echo $question->question_id; ?>" 
                        rows="3" 
                        required
                        placeholder="Type your answer here..."></textarea>
            <?php endif; ?>
          </div>
          <?php endforeach; ?>
          
          <!-- Submit Button -->
          <div class="text-center mt-4">
            <button type="button" class="btn btn-lg btn-success px-5" onclick="submitQuiz()">
              <i class="bi bi-check-circle me-2"></i>Submit Quiz
            </button>
          </div>
        <?php else: ?>
          <div class="alert alert-warning text-center">
            <i class="bi bi-exclamation-triangle fs-1 d-block mb-2"></i>
            <p>This quiz has no questions yet. Please contact your teacher.</p>
          </div>
        <?php endif; ?>
      </form>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
  
  <script>
    // Timer
    let timeLimit = <?php echo $quiz->time_limit ?? 30; ?> * 60; // Convert to seconds
    let timeRemaining = timeLimit;
    
    function startTimer() {
      const timerInterval = setInterval(function() {
        timeRemaining--;
        
        const minutes = Math.floor(timeRemaining / 60);
        const seconds = timeRemaining % 60;
        
        document.getElementById('timer').textContent = 
          `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
        
        // Warning when 5 minutes left
        if (timeRemaining === 300) {
          Swal.fire({
            icon: 'warning',
            title: '5 Minutes Remaining!',
            text: 'You have 5 minutes left to complete the quiz.',
            timer: 3000
          });
        }
        
        // Auto-submit when time is up
        if (timeRemaining <= 0) {
          clearInterval(timerInterval);
          Swal.fire({
            icon: 'info',
            title: 'Time is Up!',
            text: 'Your quiz will be submitted automatically.',
            timer: 2000
          }).then(() => {
            submitQuiz();
          });
        }
      }, 1000);
    }
    
    // Start timer on page load only if there are questions
    <?php if(count($questionsWithOptions) > 0): ?>
    startTimer();
    <?php endif; ?>
    
    // Highlight selected option
    document.querySelectorAll('.option-item input[type="radio"]').forEach(radio => {
      radio.addEventListener('change', function() {
        // Remove selected class from all options in this question
        this.closest('.options-container').querySelectorAll('.option-item').forEach(item => {
          item.classList.remove('selected');
        });
        // Add selected class to chosen option
        this.closest('.option-item').classList.add('selected');
      });
    });
    
    // Submit quiz function
    function submitQuiz() {
      Swal.fire({
        title: 'Submit Quiz?',
        text: 'Are you sure you want to submit your answers? This cannot be undone.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, Submit',
        cancelButtonText: 'Continue Quiz'
      }).then((result) => {
        if (result.isConfirmed) {
          // Show loading
          Swal.fire({
            title: 'Submitting...',
            text: 'Please wait while we process your answers.',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
          });
          
          // Prepare form data
          const formData = new FormData(document.getElementById('quizForm'));
          formData.append('end_time', new Date().toISOString());
          formData.append('time_spent', Math.floor((timeLimit - timeRemaining) / 60));
          
          // Submit via AJAX
          fetch('<?php echo route("student.quiz.submit"); ?>', {
            method: 'POST',
            headers: {
              'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
              'Accept': 'application/json'
            },
            body: formData
          })
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              Swal.fire({
                icon: 'success',
                title: 'Quiz Submitted!',
                html: `
                  <p>Your quiz has been submitted successfully.</p>
                  <h3>Score: ${data.score}/${data.total_points}</h3>
                  <h4>Percentage: ${data.percentage}%</h4>
                `,
                confirmButtonText: 'View Results'
              }).then(() => {
                window.location.href = '<?php echo route("student.quizzes"); ?>';
              });
            } else {
              Swal.fire({
                icon: 'error',
                title: 'Submission Failed',
                text: data.message || 'An error occurred while submitting your quiz.'
              });
            }
          })
          .catch(error => {
            Swal.fire({
              icon: 'error',
              title: 'Error',
              text: 'Failed to submit quiz. Please try again.'
            });
            console.error('Error:', error);
          });
        }
      });
    }
    
    // Prevent accidental page close only if there are questions
    <?php if(count($questionsWithOptions) > 0): ?>
    window.addEventListener('beforeunload', function (e) {
      e.preventDefault();
      e.returnValue = '';
    });
    <?php endif; ?>
  </script>
</body>
</html>
