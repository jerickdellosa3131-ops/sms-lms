<?php
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

$user = Auth::user();
$student_id = $user ? $user->user_id : null;

// Get the actual student_id from students table
$student = null;
$enrolledClasses = collect();
$myFeedback = collect();

if ($student_id) {
    $student = DB::table('students')->where('user_id', $student_id)->first();
    
    if ($student) {
        // Get enrolled classes for feedback submission
        $enrolledClasses = DB::table('class_enrollments')
            ->join('classes', 'class_enrollments.class_id', '=', 'classes.class_id')
            ->leftJoin('teachers', 'classes.teacher_id', '=', 'teachers.teacher_id')
            ->leftJoin('users as teacher_users', 'teachers.user_id', '=', 'teacher_users.user_id')
            ->where('class_enrollments.student_id', $student->student_id)
            ->where('class_enrollments.status', 'enrolled')
            ->select(
                'classes.*',
                'teacher_users.first_name as teacher_first_name',
                'teacher_users.last_name as teacher_last_name'
            )
            ->get();
        
        // Get previously submitted feedback (check if table exists)
        try {
            $myFeedback = DB::table('student_feedback')
                ->join('classes', 'student_feedback.class_id', '=', 'classes.class_id')
                ->where('student_feedback.student_id', $student->student_id)
                ->select(
                    'student_feedback.*',
                    'classes.section_name',
                    'classes.class_code'
                )
                ->orderBy('student_feedback.created_at', 'desc')
                ->get();
        } catch (\Exception $e) {
            // Table doesn't exist yet, use empty collection
            $myFeedback = collect();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="<?php echo csrf_token(); ?>">
  <title>SMS3 - Submit Feedback</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="<?php echo asset('style.css'); ?>">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
  
  <!-- SweetAlert2 -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  
  <style>
    @import url("../../style.css");

    /* Make sidebar scrollable in landscape mode on mobile devices */
    @media (max-width: 768px) and (orientation: landscape) {
      .sidebar {
        max-height: 100vh;
        overflow-y: auto;
      }
    }
    
    .class-card {
      transition: transform 0.2s ease, box-shadow 0.2s ease;
      cursor: pointer;
    }
    
    .class-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 10px 25px rgba(0,0,0,0.15);
    }
    
    .rating-stars {
      font-size: 2rem;
      color: #ddd;
      cursor: pointer;
    }
    
    .rating-stars i.filled {
      color: #ffc107;
    }
    
    .rating-stars i:hover,
    .rating-stars i:hover ~ i {
      color: #ffc107;
    }
  </style>
</head>

<body>

  <!-- navbar -->
  <?php include resource_path('views/includes/sidenav_student.php'); ?>

  <!-- Main Content -->
  <div class="main-content flex-grow-1">
    <div class="container my-5">
      <div class="card shadow-lg border-0 rounded-4">
        <div class="card-body p-4">

          <!-- Header -->
          <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="d-flex align-items-center">
              <i class="bi bi-chat-left-text-fill text-success fs-1 me-3"></i>
              <div>
                <h3 class="mb-0 fw-bold">Submit Feedback</h3>
                <p class="text-muted small mb-0">Share your thoughts about your classes and learning experience</p>
              </div>
            </div>
            <a href="<?php echo route('student.performance'); ?>" class="btn btn-outline-secondary">
              <i class="bi bi-arrow-left me-2"></i> Back to Performance
            </a>
          </div>

          <!-- Tab Navigation -->
          <ul class="nav nav-tabs mb-4" role="tablist">
            <li class="nav-item" role="presentation">
              <button class="nav-link active" id="submit-tab" data-bs-toggle="tab" data-bs-target="#submit" type="button" role="tab">
                <i class="bi bi-pencil-square me-2"></i>Submit New Feedback
              </button>
            </li>
            <li class="nav-item" role="presentation">
              <button class="nav-link" id="history-tab" data-bs-toggle="tab" data-bs-target="#history" type="button" role="tab">
                <i class="bi bi-clock-history me-2"></i>My Feedback History (<?php echo $myFeedback->count(); ?>)
              </button>
            </li>
          </ul>

          <!-- Tab Content -->
          <div class="tab-content">
            
            <!-- Submit Feedback Tab -->
            <div class="tab-pane fade show active" id="submit" role="tabpanel">
              <div class="alert alert-info mb-4">
                <i class="bi bi-info-circle me-2"></i>
                <strong>Note:</strong> Your feedback helps us improve the quality of education. Please be honest and constructive.
              </div>

              <?php if($enrolledClasses->count() > 0): ?>
                <h5 class="mb-3 fw-bold">Select a Class to Give Feedback</h5>
                <div class="row g-4">
                  <?php foreach($enrolledClasses as $class): ?>
                  <div class="col-md-6">
                    <div class="card class-card h-100 border-0 shadow-sm" onclick="showFeedbackForm(<?php echo $class->class_id; ?>, '<?php echo addslashes($class->section_name); ?>', '<?php echo addslashes($class->teacher_first_name . ' ' . $class->teacher_last_name); ?>')">
                      <div class="card-body">
                        <div class="d-flex align-items-start">
                          <div class="icon-wrapper bg-primary bg-opacity-10 rounded-circle p-3 me-3">
                            <i class="bi bi-book-fill text-primary fs-3"></i>
                          </div>
                          <div class="flex-grow-1">
                            <h5 class="fw-bold mb-2"><?php echo htmlspecialchars($class->section_name); ?></h5>
                            <p class="text-muted mb-2">
                              <i class="bi bi-code-square me-1"></i><?php echo htmlspecialchars($class->class_code); ?>
                            </p>
                            <?php if($class->teacher_first_name): ?>
                              <p class="text-muted mb-0">
                                <i class="bi bi-person-fill me-1"></i><?php echo htmlspecialchars($class->teacher_first_name . ' ' . $class->teacher_last_name); ?>
                              </p>
                            <?php endif; ?>
                          </div>
                          <i class="bi bi-chevron-right text-muted"></i>
                        </div>
                      </div>
                    </div>
                  </div>
                  <?php endforeach; ?>
                </div>
              <?php else: ?>
                <div class="alert alert-warning text-center py-5">
                  <i class="bi bi-exclamation-triangle fs-1 d-block mb-3"></i>
                  <h5>No Classes Found</h5>
                  <p class="mb-0">You are not enrolled in any classes yet.</p>
                </div>
              <?php endif; ?>
            </div>

            <!-- Feedback History Tab -->
            <div class="tab-pane fade" id="history" role="tabpanel">
              <?php if($myFeedback->count() > 0): ?>
                <div class="row g-4">
                  <?php foreach($myFeedback as $feedback): ?>
                  <div class="col-12">
                    <div class="card border-start border-4 border-success">
                      <div class="card-body">
                        <div class="row">
                          <div class="col-md-8">
                            <h5 class="fw-bold mb-2">
                              <i class="bi bi-chat-left-text text-success me-2"></i>
                              <?php echo htmlspecialchars($feedback->section_name); ?>
                            </h5>
                            <p class="mb-2"><strong>Feedback Type:</strong> <?php echo ucfirst($feedback->feedback_type); ?></p>
                            <?php if($feedback->rating): ?>
                              <p class="mb-2">
                                <strong>Rating:</strong> 
                                <?php for($i = 1; $i <= 5; $i++): ?>
                                  <i class="bi bi-star-fill <?php echo $i <= $feedback->rating ? 'text-warning' : 'text-muted'; ?>"></i>
                                <?php endfor; ?>
                              </p>
                            <?php endif; ?>
                            <div class="bg-light p-3 rounded mt-3">
                              <p class="mb-0"><?php echo nl2br(htmlspecialchars($feedback->comment)); ?></p>
                            </div>
                          </div>
                          <div class="col-md-4 text-md-end">
                            <small class="text-muted d-block mb-2">
                              <i class="bi bi-calendar-event me-1"></i>
                              <?php echo date('M d, Y g:i A', strtotime($feedback->created_at)); ?>
                            </small>
                            <span class="badge bg-success">Submitted</span>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                  <?php endforeach; ?>
                </div>
              <?php else: ?>
                <div class="alert alert-info text-center py-5">
                  <i class="bi bi-inbox fs-1 d-block mb-3"></i>
                  <h5>No Feedback Submitted Yet</h5>
                  <p class="mb-0">You haven't submitted any feedback yet. Go to the "Submit New Feedback" tab to share your thoughts!</p>
                </div>
              <?php endif; ?>
            </div>

          </div>

        </div>
      </div>
    </div>
  </div>

  <footer class="mt-auto bg-light">
    <div class="container text-center py-3">
      <p class="text-muted small mb-0">© 2025 Student Management System. All rights reserved.</p>
    </div>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
  
  <script>
    let selectedRating = 0;
    
    function showFeedbackForm(classId, className, teacherName) {
      selectedRating = 0;
      
      Swal.fire({
        title: 'Submit Feedback',
        html: `
          <div class="text-start">
            <p class="mb-3"><strong>Class:</strong> ${className}</p>
            <p class="mb-4"><strong>Teacher:</strong> ${teacherName}</p>
            
            <div class="mb-3">
              <label class="form-label fw-bold">Feedback Type</label>
              <select class="form-select" id="feedbackType">
                <option value="general">General Feedback</option>
                <option value="teaching">Teaching Quality</option>
                <option value="content">Course Content</option>
                <option value="facilities">Facilities & Resources</option>
                <option value="suggestion">Suggestion</option>
              </select>
            </div>
            
            <div class="mb-3">
              <label class="form-label fw-bold">Rate Your Experience (Optional)</label>
              <div class="rating-stars d-flex justify-content-center" id="ratingStars">
                <i class="bi bi-star me-2" data-rating="1" onclick="setRating(1)"></i>
                <i class="bi bi-star me-2" data-rating="2" onclick="setRating(2)"></i>
                <i class="bi bi-star me-2" data-rating="3" onclick="setRating(3)"></i>
                <i class="bi bi-star me-2" data-rating="4" onclick="setRating(4)"></i>
                <i class="bi bi-star" data-rating="5" onclick="setRating(5)"></i>
              </div>
            </div>
            
            <div class="mb-3">
              <label class="form-label fw-bold">Your Comments/Feedback</label>
              <textarea class="form-control" id="feedbackComment" rows="5" placeholder="Share your thoughts, suggestions, or concerns..."></textarea>
            </div>
            
            <div class="form-check">
              <input class="form-check-input" type="checkbox" id="anonymousFeedback">
              <label class="form-check-label" for="anonymousFeedback">
                Submit anonymously
              </label>
            </div>
          </div>
        `,
        width: 600,
        showCancelButton: true,
        confirmButtonText: '<i class="bi bi-send me-2"></i>Submit Feedback',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#198754',
        preConfirm: () => {
          const feedbackType = document.getElementById('feedbackType').value;
          const comment = document.getElementById('feedbackComment').value;
          const anonymous = document.getElementById('anonymousFeedback').checked;
          
          if (!comment.trim()) {
            Swal.showValidationMessage('Please enter your feedback');
            return false;
          }
          
          return {
            class_id: classId,
            feedback_type: feedbackType,
            rating: selectedRating,
            comment: comment,
            is_anonymous: anonymous
          };
        }
      }).then((result) => {
        if (result.isConfirmed) {
          submitFeedback(result.value);
        }
      });
    }
    
    function setRating(rating) {
      selectedRating = rating;
      const stars = document.querySelectorAll('#ratingStars i');
      stars.forEach((star, index) => {
        if (index < rating) {
          star.classList.remove('bi-star');
          star.classList.add('bi-star-fill', 'filled');
        } else {
          star.classList.remove('bi-star-fill', 'filled');
          star.classList.add('bi-star');
        }
      });
    }
    
    function submitFeedback(data) {
      Swal.fire({
        title: 'Submitting...',
        text: 'Please wait while we submit your feedback',
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading()
      });
      
      const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
      
      fetch('/student/feedback/submit', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrfToken,
          'Accept': 'application/json'
        },
        body: JSON.stringify(data)
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          Swal.fire({
            icon: 'success',
            title: 'Feedback Submitted!',
            text: 'Thank you for your feedback. It helps us improve!',
            showConfirmButton: true
          }).then(() => {
            location.reload();
          });
        } else {
          Swal.fire({
            icon: 'error',
            title: 'Error',
            text: data.message || 'Failed to submit feedback'
          });
        }
      })
      .catch(error => {
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: 'Failed to submit feedback. Please try again.'
        });
        console.error('Error:', error);
      });
    }
  </script>
</body>

</html>
