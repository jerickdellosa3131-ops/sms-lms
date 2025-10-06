<?php
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

$user = Auth::user();
$student_id = $user ? $user->user_id : null;

$feedbacks = collect(); // Empty by default

// Note: This requires a feedback/comments table
// Uncomment when your feedback table exists:
// if ($student_id) {
//     $feedbacks = DB::table('student_feedback')
//         ->join('classes', 'student_feedback.class_id', '=', 'classes.class_id')
//         ->join('users as teachers', 'classes.teacher_id', '=', 'teachers.user_id')
//         ->where('student_feedback.student_id', $student_id)
//         ->select(
//             'student_feedback.*',
//             'classes.section_name',
//             DB::raw("CONCAT(teachers.first_name, ' ', teachers.last_name) as teacher_name")
//         )
//         ->orderBy('student_feedback.created_at', 'desc')
//         ->get();
// }
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Student Feedback</title>

  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="<?php echo asset('style.css'); ?>">
  
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

  <style>
    @import url("../../style.css");

    /* Custom styles for a cleaner look */
    body {
        background-color: #f8f9fa; /* Light gray background */
    }
    .main-content {
        padding-top: 2rem;
        padding-bottom: 2rem;
    }
    .card {
        transition: box-shadow 0.3s ease-in-out;
    }
    .card:hover {
        box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important;
    }
    .feedback-item {
        border-bottom: 1px solid #dee2e6;
        padding-top: 1.25rem;
        padding-bottom: 1.25rem;
    }
    .feedback-item:last-child {
        border-bottom: none;
    }
  </style>
</head>

<body>

  <?php include resource_path('views/includes/sidenav_student.php'); ?>

  <div class="main-content flex-grow-1">
    <div class="container">
      <div class="card shadow-sm border-0 rounded-4">
        <div class="card-body p-4 p-md-5">

          <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
            <div class="d-flex align-items-center mb-3 mb-md-0">
              <i class="bi bi-chat-quote-fill text-primary" style="font-size: 2.5rem;"></i>
              <div class="ms-3">
                <h2 class="mb-0 fw-bold">Give Feedback to Teachers</h2>
                <p class="text-muted mb-0">Submit feedback/comments on your professors by subject.</p>
              </div>
            </div>
            <a href="<?php echo route('student.dashboard'); ?>" class="btn btn-outline-secondary">
              <i class="bi bi-arrow-left me-2"></i>Back to Dashboard
            </a>
          </div>

          <form class="mb-5">
            <div class="row g-3">
              <div class="col-md-6">
                <label for="subjectSelect" class="form-label fw-bold">Subject</label>
                <select id="subjectSelect" class="form-select">
                  <option selected>Choose subject...</option>
                  <option>Big Data</option>
                  <option>IT ELECTIVE 4</option>
                  <option>Capstone Project 1</option>
                  <option>OJT - Practicum 1</option>
                </select>
              </div>
              <div class="col-md-6">
                <label for="professorSelect" class="form-label fw-bold">Professor</label>
                <select id="professorSelect" class="form-select">
                  <option selected>Choose professor...</option>
                  <option>Prof. Santos</option>
                  <option>Prof. Dela Cruz</option>
                  <option>Prof. Villanueva</option>
                </select>
              </div>
              <div class="col-12">
                <label for="feedbackText" class="form-label fw-bold">Your Feedback</label>
                <textarea id="feedbackText" class="form-control" rows="4" placeholder="Write your comments or suggestions..."></textarea>
              </div>
              <div class="col-12 text-end">
                <button type="submit" class="btn btn-primary px-4">
                  <i class="bi bi-send-fill me-2"></i>Submit Feedback
                </button>
              </div>
            </div>
          </form>

          <hr>

          <div class="mt-4">
            <h4 class="fw-bold mb-3">My Submitted Feedback (<?php echo $feedbacks->count(); ?>)</h4>
            
            <?php if($feedbacks->count() > 0): ?>
              <?php foreach($feedbacks as $feedback): ?>
              <div class="feedback-item">
                <div class="d-flex justify-content-between align-items-start">
                  <div>
                    <h6 class="fw-bold mb-1"><?php echo htmlspecialchars($feedback->section_name); ?> – <?php echo htmlspecialchars($feedback->teacher_name); ?></h6>
                    <small class="text-muted"><?php echo date('M d, Y', strtotime($feedback->created_at)); ?></small>
                  </div>
                  <span class="badge bg-success">Submitted</span>
                </div>
                <p class="mt-2 mb-0 fst-italic text-secondary">"<?php echo htmlspecialchars($feedback->feedback_text); ?>"</p>
              </div>
              <?php endforeach; ?>
            <?php else: ?>
              <div class="alert alert-info text-center">
                <i class="bi bi-chat-left-text fs-1 d-block mb-3"></i>
                <h5>No Feedback Submitted Yet</h5>
                <p class="mb-0">You haven't submitted any feedback. Share your thoughts using the form above!</p>
                <small class="text-muted d-block mt-2">
                  This feature requires a <code>student_feedback</code> table. 
                  Uncomment the database query at the top of this file to enable it.
                </small>
              </div>
            <?php endif; ?>

          </div>

        </div>
      </div>
    </div>
  </div>



  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>