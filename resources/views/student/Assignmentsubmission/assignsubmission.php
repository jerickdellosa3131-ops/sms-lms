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
    
    // Fetch assignments for enrolled classes
    $assignments = DB::table('assignments')
        ->join('classes', 'assignments.class_id', '=', 'classes.class_id')
        ->join('class_enrollments', 'classes.class_id', '=', 'class_enrollments.class_id')
        ->leftJoin('assignment_submissions', function($join) use ($student_id) {
            $join->on('assignments.assignment_id', '=', 'assignment_submissions.assignment_id')
                 ->where('assignment_submissions.student_id', '=', $student_id);
        })
        ->where('class_enrollments.student_id', $student_id)
        ->where('assignments.status', 'published')
        ->select(
            'assignments.*',
            'classes.section_name',
            'classes.class_code',
            'assignment_submissions.status as submission_status',
            'assignment_submissions.submitted_at',
            'assignment_submissions.score'
        )
        ->orderBy('assignments.due_date', 'asc')
        ->get();
} else {
    $assignments = collect();
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
  
  <!-- SweetAlert2 -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script></script>
  
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

    <?php include resource_path('views/includes/sidenav_student.php'); ?>


    <!-- Header -->
    <div class="main-content flex-grow-1">
        <div class="container my-5">
            <div class="card shadow-lg border-0 rounded-4">
                <div class="card-body p-4">

                    <!-- Header -->
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-pencil-square text-success fs-1 me-3"></i>
                            <div>
                                <h3 class="mb-0 fw-bold">Assignment Submission</h3>
                                <p class="text-muted small mb-0">Submit your assignment files below</p>
                            </div>
                        </div>
                        <a href="<?php echo route('student.class-portal'); ?>" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-2"></i> Back to Class Portal
                        </a>
                    </div>

                    <!-- Assignments List -->
                    <?php if($assignments->count() > 0): ?>
                        <div class="row g-4">
                            <?php foreach($assignments as $assignment): 
                                $isOverdue = strtotime($assignment->due_date) < time();
                                $statusBadge = 'bg-warning';
                                $statusText = 'Pending';
                                $borderColor = 'border-warning';
                                
                                if ($assignment->submission_status == 'graded') {
                                    $statusBadge = 'bg-success';
                                    $statusText = 'Graded';
                                    $borderColor = 'border-success';
                                } elseif ($assignment->submission_status == 'submitted') {
                                    $statusBadge = 'bg-info';
                                    $statusText = 'Submitted';
                                    $borderColor = 'border-info';
                                } elseif ($isOverdue && !$assignment->submission_status) {
                                    $statusBadge = 'bg-danger';
                                    $statusText = 'Overdue';
                                    $borderColor = 'border-danger';
                                }
                            ?>
                            <div class="col-12">
                                <div class="card h-100 shadow-sm border-start border-4 <?php echo $borderColor; ?>">
                                    <div class="card-body">
                                        <!-- Header Row -->
                                        <div class="row mb-3">
                                            <div class="col-md-8">
                                                <h5 class="card-title fw-bold mb-2">
                                                    <i class="bi bi-file-earmark-text text-primary me-2"></i>
                                                    <?php echo htmlspecialchars($assignment->title); ?>
                                                </h5>
                                                <span class="badge bg-secondary me-2"><?php echo htmlspecialchars($assignment->section_name); ?></span>
                                                <span class="badge <?php echo $statusBadge; ?>"><?php echo $statusText; ?></span>
                                            </div>
                                            <div class="col-md-4 text-md-end">
                                                <div class="mb-2">
                                                    <strong class="text-primary fs-4"><?php echo $assignment->total_points; ?></strong>
                                                    <small class="text-muted">points</small>
                                                </div>
                                                <?php if($assignment->score): ?>
                                                    <div>
                                                        <span class="badge bg-success">Score: <?php echo $assignment->score; ?>/<?php echo $assignment->total_points; ?></span>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>

                                        <!-- Description -->
                                        <?php if($assignment->description): ?>
                                        <div class="mb-3">
                                            <h6 class="fw-bold text-muted small mb-2">DESCRIPTION</h6>
                                            <p class="mb-0"><?php echo nl2br(htmlspecialchars($assignment->description)); ?></p>
                                        </div>
                                        <?php endif; ?>

                                        <!-- Instructions -->
                                        <?php if($assignment->instructions): ?>
                                        <div class="mb-3">
                                            <h6 class="fw-bold text-muted small mb-2">INSTRUCTIONS</h6>
                                            <div class="alert alert-light border">
                                                <?php echo nl2br(htmlspecialchars($assignment->instructions)); ?>
                                            </div>
                                        </div>
                                        <?php endif; ?>

                                        <!-- Assignment Details -->
                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <div class="d-flex align-items-center mb-2">
                                                    <i class="bi bi-calendar-event text-danger me-2"></i>
                                                    <div>
                                                        <small class="text-muted d-block">Due Date</small>
                                                        <strong><?php echo date('M d, Y g:i A', strtotime($assignment->due_date)); ?></strong>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="d-flex align-items-center mb-2">
                                                    <i class="bi bi-<?php echo $assignment->late_submission_allowed ? 'check-circle text-success' : 'x-circle text-danger'; ?> me-2"></i>
                                                    <div>
                                                        <small class="text-muted d-block">Late Submission</small>
                                                        <strong><?php echo $assignment->late_submission_allowed ? 'Allowed' : 'Not Allowed'; ?></strong>
                                                        <?php if($assignment->late_submission_allowed && $assignment->late_penalty_percentage > 0): ?>
                                                            <small class="text-warning d-block">(<?php echo $assignment->late_penalty_percentage; ?>% penalty)</small>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Teacher's Attachment -->
                                        <div class="mb-3">
                                            <h6 class="fw-bold text-muted small mb-2"><i class="bi bi-paperclip me-2"></i>TEACHER'S ATTACHMENT</h6>
                                            <?php if($assignment->file_attachment): ?>
                                                <div class="card border border-primary bg-light">
                                                    <div class="card-body py-3">
                                                        <div class="row align-items-center">
                                                            <div class="col-auto">
                                                                <?php 
                                                                $fileExt = strtolower(pathinfo($assignment->file_attachment, PATHINFO_EXTENSION));
                                                                $iconClass = match($fileExt) {
                                                                    'pdf' => 'bi-file-pdf text-danger',
                                                                    'doc', 'docx' => 'bi-file-word text-primary',
                                                                    'xls', 'xlsx' => 'bi-file-excel text-success',
                                                                    'ppt', 'pptx' => 'bi-file-ppt text-warning',
                                                                    'zip', 'rar' => 'bi-file-zip text-secondary',
                                                                    'jpg', 'jpeg', 'png', 'gif' => 'bi-file-image text-info',
                                                                    default => 'bi-file-earmark text-muted'
                                                                };
                                                                ?>
                                                                <i class="bi <?php echo $iconClass; ?> fs-1"></i>
                                                            </div>
                                                            <div class="col">
                                                                <strong class="d-block mb-1"><?php echo basename($assignment->file_attachment); ?></strong>
                                                                <span class="badge bg-secondary"><?php echo strtoupper($fileExt); ?> File</span>
                                                            </div>
                                                            <div class="col-auto">
                                                                <div class="d-flex gap-2">
                                                                    <a href="<?php echo asset('storage/' . $assignment->file_attachment); ?>" 
                                                                       target="_blank" 
                                                                       class="btn btn-outline-primary btn-sm"
                                                                       title="View file in new tab">
                                                                        <i class="bi bi-eye me-1"></i>View
                                                                    </a>
                                                                    <a href="<?php echo route('student.assignment.download', ['assignment_id' => $assignment->assignment_id]); ?>" 
                                                                       class="btn btn-success btn-sm"
                                                                       title="Download file to your device">
                                                                        <i class="bi bi-download me-1"></i>Download
                                                                    </a>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php else: ?>
                                                <div class="alert alert-light border">
                                                    <i class="bi bi-info-circle text-muted me-2"></i>
                                                    <span class="text-muted">No file attachment provided by teacher</span>
                                                </div>
                                            <?php endif; ?>
                                        </div>

                                        <!-- Submission Info -->
                                        <?php if($assignment->submitted_at): ?>
                                        <div class="alert alert-success mb-3">
                                            <i class="bi bi-check-circle-fill me-2"></i>
                                            <strong>Submitted on:</strong> <?php echo date('M d, Y g:i A', strtotime($assignment->submitted_at)); ?>
                                        </div>
                                        <?php endif; ?>

                                        <!-- Action Buttons -->
                                        <div class="d-flex gap-2">
                                            <?php if(!$assignment->submission_status): ?>
                                                <button class="btn btn-primary" onclick="submitAssignment(<?php echo $assignment->assignment_id; ?>, '<?php echo addslashes($assignment->title); ?>')">
                                                    <i class="bi bi-upload me-2"></i>Submit Assignment
                                                </button>
                                            <?php else: ?>
                                                <button class="btn btn-success" disabled>
                                                    <i class="bi bi-check-circle me-2"></i>Submitted
                                                </button>
                                            <?php endif; ?>
                                            
                                            <?php if($assignment->submission_status): ?>
                                                <button class="btn btn-outline-secondary" onclick="viewSubmission(<?php echo $assignment->assignment_id; ?>)">
                                                    <i class="bi bi-eye me-2"></i>View My Submission
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info text-center">
                            <i class="bi bi-info-circle fs-1 d-block mb-2"></i>
                            <p class="mb-0">No assignments available yet.</p>
                        </div>
                    <?php endif; ?>

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
  // Submit Assignment Function
  function submitAssignment(assignmentId, assignmentTitle) {
    Swal.fire({
      title: 'Submit Assignment',
      html: `
        <div class="text-start">
          <p><strong>Assignment:</strong> ${assignmentTitle}</p>
          
          <div class="mb-3">
            <label class="form-label fw-bold">Submission Text/Answer</label>
            <textarea id="submissionText" class="form-control" rows="5" 
                      placeholder="Enter your answer or description here..."></textarea>
          </div>
          
          <div class="mb-3">
            <label class="form-label fw-bold">Upload File (Optional)</label>
            <input type="file" id="submissionFile" class="form-control" 
                   accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.zip,.jpg,.jpeg,.png">
            <small class="text-muted">Accepted: PDF, DOCX, PPT, XLSX, ZIP, Images (Max 10MB)</small>
          </div>
          
          <div class="alert alert-warning">
            <i class="bi bi-exclamation-triangle me-2"></i>
            <strong>Note:</strong> Once submitted, you cannot edit your submission. Please review before submitting.
          </div>
        </div>
      `,
      width: 600,
      showCancelButton: true,
      confirmButtonText: '<i class="bi bi-upload me-2"></i>Submit Assignment',
      cancelButtonText: 'Cancel',
      confirmButtonColor: '#28a745',
      preConfirm: () => {
        const text = document.getElementById('submissionText').value;
        const file = document.getElementById('submissionFile').files[0];
        
        if (!text && !file) {
          Swal.showValidationMessage('Please provide either text submission or upload a file');
          return false;
        }
        
        if (file && file.size > 10 * 1024 * 1024) {
          Swal.showValidationMessage('File size must be less than 10MB');
          return false;
        }
        
        return {
          assignment_id: assignmentId,
          submission_text: text,
          file: file
        };
      }
    }).then((result) => {
      if (result.isConfirmed) {
        handleSubmission(result.value);
      }
    });
  }

  // Handle Assignment Submission
  function handleSubmission(data) {
    Swal.fire({
      title: 'Submitting...',
      text: 'Please wait while we submit your assignment',
      allowOutsideClick: false,
      didOpen: () => Swal.showLoading()
    });

    const formData = new FormData();
    formData.append('assignment_id', data.assignment_id);
    formData.append('submission_text', data.submission_text);
    if (data.file) {
      formData.append('file', data.file);
    }

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    fetch('/student/assignments/submit', {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': csrfToken,
        'Accept': 'application/json'
      },
      body: formData
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        Swal.fire({
          icon: 'success',
          title: 'Assignment Submitted!',
          text: 'Your assignment has been submitted successfully. You will be notified when it\'s graded.',
          showConfirmButton: true
        }).then(() => {
          location.reload();
        });
      } else {
        Swal.fire({
          icon: 'error',
          title: 'Submission Failed',
          text: data.message || 'Failed to submit assignment. Please try again.'
        });
      }
    })
    .catch(error => {
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: 'An error occurred while submitting. Please try again.'
      });
      console.error('Error:', error);
    });
  }

  // View Submission Function
  function viewSubmission(assignmentId) {
    Swal.fire({
      title: 'Loading...',
      text: 'Fetching your submission',
      allowOutsideClick: false,
      didOpen: () => Swal.showLoading()
    });

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    fetch(`/student/assignments/${assignmentId}/submission`, {
      method: 'GET',
      headers: {
        'X-CSRF-TOKEN': csrfToken,
        'Accept': 'application/json'
      }
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        const sub = data.submission;
        const fileLink = sub.file_path ? 
          `<a href="/storage/${sub.file_path}" target="_blank" class="btn btn-sm btn-primary mt-2">
            <i class="bi bi-download me-1"></i>Download My File
          </a>` : 
          '<p class="text-muted">No file attached</p>';

        Swal.fire({
          title: 'My Submission',
          html: `
            <div class="text-start">
              <div class="mb-3">
                <strong>Submitted:</strong> ${new Date(sub.submitted_at).toLocaleString()}
              </div>
              <div class="mb-3">
                <strong>Status:</strong> <span class="badge bg-${sub.status === 'graded' ? 'success' : 'info'}">${sub.status}</span>
              </div>
              ${sub.score !== null ? `
                <div class="mb-3">
                  <strong>Score:</strong> <span class="badge bg-success">${sub.score}/${sub.total_points}</span>
                </div>
              ` : ''}
              ${sub.submission_text ? `
                <div class="mb-3">
                  <strong>My Answer:</strong>
                  <div class="bg-light p-3 rounded mt-2" style="max-height: 200px; overflow-y: auto;">
                    ${sub.submission_text}
                  </div>
                </div>
              ` : ''}
              <div class="mb-3">
                <strong>Attached File:</strong><br>
                ${fileLink}
              </div>
              ${sub.feedback ? `
                <div class="alert alert-info">
                  <strong><i class="bi bi-chat-left-quote me-2"></i>Teacher's Feedback:</strong>
                  <p class="mb-0 mt-2">${sub.feedback}</p>
                </div>
              ` : ''}
            </div>
          `,
          width: 600,
          showCloseButton: true,
          confirmButtonText: 'Close'
        });
      } else {
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: 'Failed to load submission'
        });
      }
    })
    .catch(error => {
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: 'Failed to load submission'
      });
      console.error('Error:', error);
    });
  }
</script>

</html>