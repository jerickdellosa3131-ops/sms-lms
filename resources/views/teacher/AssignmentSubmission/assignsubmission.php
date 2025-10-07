<?php
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

$user = Auth::user();
$teacher_id = $user ? $user->user_id : null;

// Get teacher's teacher_id and fetch assignments
$teacher = null;
$assignments = collect();
$submissions = collect();
$pendingCount = 0;
$gradedCount = 0;

if ($teacher_id) {
    $teacher = DB::table('teachers')->where('user_id', $teacher_id)->first();
    
    if ($teacher) {
        // Fetch assignments created by this teacher
        $assignments = DB::table('assignments')
            ->join('classes', 'assignments.class_id', '=', 'classes.class_id')
            ->where('assignments.teacher_id', $teacher->teacher_id)
            ->select(
                'assignments.*',
                'classes.section_name',
                'classes.class_code'
            )
            ->orderBy('assignments.created_at', 'desc')
            ->get();
        
        // Fetch student submissions with student details
        $submissions = DB::table('assignment_submissions')
            ->join('assignments', 'assignment_submissions.assignment_id', '=', 'assignments.assignment_id')
            ->join('students', 'assignment_submissions.student_id', '=', 'students.student_id')
            ->join('users', 'students.user_id', '=', 'users.user_id')
            ->where('assignments.teacher_id', $teacher->teacher_id)
            ->select(
                'assignment_submissions.*',
                'assignments.title as assignment_title',
                'assignments.total_points',
                'assignments.due_date',
                'users.first_name',
                'users.last_name',
                'students.student_number'
            )
            ->orderBy('assignment_submissions.submitted_at', 'desc')
            ->get();
        
        // Count submissions by status
        $pendingCount = $submissions->where('status', 'submitted')->count();
        $gradedCount = $submissions->where('status', 'graded')->count();
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
            <i class="bi bi-clipboard-check text-primary fs-1 me-3"></i>
            <div>
              <h3 class="mb-0 fw-bold">Assignment Management</h3>
              <p class="text-muted small mb-0">Create assignments and grade student submissions</p>
            </div>
          </div>
          <a href="<?php echo route('teacher.class-portal'); ?>" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i> Back to Class Portal
          </a>
        </div>

        <!-- Statistics Cards -->
        <div class="row g-3 mb-4">
          <div class="col-md-4">
            <div class="card border-0 shadow-sm">
              <div class="card-body text-center">
                <i class="bi bi-clock-history text-warning" style="font-size: 2.5rem;"></i>
                <h3 class="mt-2 mb-0 fw-bold"><?php echo $pendingCount ?? 0; ?></h3>
                <p class="text-muted small mb-0">Pending Grading</p>
              </div>
            </div>
          </div>
          <div class="col-md-4">
            <div class="card border-0 shadow-sm">
              <div class="card-body text-center">
                <i class="bi bi-check-circle text-success" style="font-size: 2.5rem;"></i>
                <h3 class="mt-2 mb-0 fw-bold"><?php echo $gradedCount ?? 0; ?></h3>
                <p class="text-muted small mb-0">Graded</p>
              </div>
            </div>
          </div>
          <div class="col-md-4">
            <div class="card border-0 shadow-sm">
              <div class="card-body text-center">
                <i class="bi bi-file-earmark-text text-primary" style="font-size: 2.5rem;"></i>
                <h3 class="mt-2 mb-0 fw-bold"><?php echo $submissions->count(); ?></h3>
                <p class="text-muted small mb-0">Total Submissions</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Create Assignment Form -->
        <h5 class="fw-bold mb-3">Create New Assignment</h5>
        <form id="createAssignmentForm" onsubmit="event.preventDefault(); handleCreateAssignment();" enctype="multipart/form-data" class="mb-5">
          <div class="row g-3">

            <!-- Title -->
            <div class="col-md-6">
              <label for="assignmentTitle" class="form-label fw-bold">Assignment Title</label>
              <input type="text" id="assignmentTitle" class="form-control" placeholder="Enter assignment title" required>
            </div>

            <!-- Deadline -->
            <div class="col-md-6">
              <label for="deadline" class="form-label fw-bold">Deadline</label>
              <input type="datetime-local" id="deadline" class="form-control" required>
            </div>

            <!-- Description -->
            <div class="col-12">
              <label for="assignmentDesc" class="form-label fw-bold">Description</label>
              <textarea id="assignmentDesc" rows="3" class="form-control" placeholder="Assignment details or instructions"></textarea>
            </div>

            <!-- File Upload -->
            <div class="col-md-6">
              <label for="assignmentFile" class="form-label fw-bold">Attach File (Optional)</label>
              <input type="file" id="assignmentFile" class="form-control" accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.zip,.jpg,.jpeg,.png">
              <small class="text-muted d-block">Upload PDF, DOCX, PPT, or reference materials (Max 10MB)</small>
              <small id="fileSelectedInfo" class="text-success fw-bold" style="display: none;">
                <i class="bi bi-check-circle me-1"></i><span id="fileName"></span>
              </small>
            </div>

          </div>

          <!-- Submit -->
          <div class="mt-4 text-end">
            <button type="reset" class="btn btn-outline-secondary me-2">
              <i class="bi bi-x-circle me-1"></i> Clear
            </button>
            <button type="submit" class="btn btn-primary">
              <i class="bi bi-plus-circle me-1"></i> Create Assignment
            </button>
          </div>
        </form>

        <!-- Submissions Table -->
        <h5 class="fw-bold mb-3">Student Submissions</h5>
        <div class="table-responsive">
          <table class="table table-hover align-middle">
            <thead class="table-primary">
              <tr>
                <th>Student Name</th>
                <th>Assignment</th>
                <th>Submission Date</th>
                <th>Status</th>
                <th>Grade</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php if($submissions->count() > 0): ?>
                <?php foreach($submissions as $sub): ?>
                  <?php
                    $statusBadge = match($sub->status) {
                      'submitted' => 'bg-warning text-dark',
                      'graded' => 'bg-success',
                      'late' => 'bg-danger',
                      'returned' => 'bg-info',
                      default => 'bg-secondary'
                    };
                    $studentName = $sub->first_name . ' ' . $sub->last_name;
                    $submissionDate = $sub->submitted_at ? date('M d, Y', strtotime($sub->submitted_at)) : '--';
                    $grade = $sub->score !== null ? $sub->score . '/' . $sub->total_points : '--';
                  ?>
                  <tr>
                    <td><?php echo htmlspecialchars($studentName); ?></td>
                    <td><?php echo htmlspecialchars($sub->assignment_title); ?></td>
                    <td><?php echo $submissionDate; ?></td>
                    <td>
                      <span class="badge <?php echo $statusBadge; ?>">
                        <?php echo ucfirst($sub->status); ?>
                      </span>
                    </td>
                    <td><?php echo $grade; ?></td>
                    <td>
                      <button class="btn btn-sm btn-outline-info" title="View Submission" 
                        onclick="viewSubmission(<?php echo htmlspecialchars(json_encode([
                          'submission_id' => $sub->submission_id,
                          'student_name' => $studentName,
                          'student_number' => $sub->student_number,
                          'assignment_title' => $sub->assignment_title,
                          'submitted_at' => $sub->submitted_at,
                          'submission_text' => $sub->submission_text,
                          'file_path' => $sub->file_path,
                          'score' => $sub->score,
                          'total_points' => $sub->total_points,
                          'feedback' => $sub->feedback,
                          'status' => $sub->status
                        ])); ?>)">
                        <i class="bi bi-eye"></i> View
                      </button>
                      <?php if($sub->status === 'submitted'): ?>
                        <button class="btn btn-sm btn-success" title="Grade Submission"
                          onclick="gradeSubmission(<?php echo htmlspecialchars(json_encode([
                            'submission_id' => $sub->submission_id,
                            'student_name' => $studentName,
                            'assignment_title' => $sub->assignment_title,
                            'total_points' => $sub->total_points
                          ])); ?>)">
                          <i class="bi bi-check2-square"></i> Grade
                        </button>
                      <?php else: ?>
                        <button class="btn btn-sm btn-outline-secondary" title="Edit Grade"
                          onclick="gradeSubmission(<?php echo htmlspecialchars(json_encode([
                            'submission_id' => $sub->submission_id,
                            'student_name' => $studentName,
                            'assignment_title' => $sub->assignment_title,
                            'total_points' => $sub->total_points,
                            'current_score' => $sub->score,
                            'current_feedback' => $sub->feedback
                          ])); ?>)">
                          <i class="bi bi-pencil"></i> Edit
                        </button>
                      <?php endif; ?>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr>
                  <td colspan="6" class="text-center text-muted py-4">
                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                    No student submissions yet.
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
  // Show selected filename
  document.getElementById('assignmentFile')?.addEventListener('change', function(e) {
    const fileInfo = document.getElementById('fileSelectedInfo');
    const fileName = document.getElementById('fileName');
    
    if (this.files.length > 0) {
      fileName.textContent = this.files[0].name;
      fileInfo.style.display = 'block';
    } else {
      fileInfo.style.display = 'none';
    }
  });

  // View Submission Details
  function viewSubmission(data) {
    const fileLink = data.file_path ? 
      `<a href="/storage/${data.file_path}" target="_blank" class="btn btn-sm btn-primary">
        <i class="bi bi-download me-1"></i>Download Submission File
      </a>` : 
      '<p class="text-muted">No file attached</p>';

    Swal.fire({
      title: 'Submission Details',
      html: `
        <div class="text-start">
          <div class="mb-3">
            <strong>Student:</strong> ${data.student_name}
            ${data.student_number ? `<span class="text-muted">(${data.student_number})</span>` : ''}
          </div>
          <div class="mb-3">
            <strong>Assignment:</strong> ${data.assignment_title}
          </div>
          <div class="mb-3">
            <strong>Submitted:</strong> ${new Date(data.submitted_at).toLocaleString()}
          </div>
          <div class="mb-3">
            <strong>Status:</strong> <span class="badge bg-${data.status === 'graded' ? 'success' : 'warning'}">${data.status}</span>
          </div>
          ${data.score !== null ? `
            <div class="mb-3">
              <strong>Grade:</strong> ${data.score}/${data.total_points} (${Math.round((data.score/data.total_points)*100)}%)
            </div>
          ` : ''}
          ${data.submission_text ? `
            <div class="mb-3">
              <strong>Submission Text:</strong>
              <div class="bg-light p-3 rounded mt-2" style="max-height: 200px; overflow-y: auto;">
                ${data.submission_text}
              </div>
            </div>
          ` : ''}
          <div class="mb-3">
            <strong>Attached File:</strong><br>
            ${fileLink}
          </div>
          ${data.feedback ? `
            <div class="mb-3">
              <strong>Teacher Feedback:</strong>
              <div class="bg-light p-3 rounded mt-2">
                ${data.feedback}
              </div>
            </div>
          ` : ''}
        </div>
      `,
      width: 600,
      showCloseButton: true,
      confirmButtonText: 'Close'
    });
  }

  // Grade Submission
  function gradeSubmission(data) {
    Swal.fire({
      title: 'Grade Submission',
      html: `
        <div class="text-start">
          <p><strong>Student:</strong> ${data.student_name}</p>
          <p><strong>Assignment:</strong> ${data.assignment_title}</p>
          
          <div class="mb-3">
            <label class="form-label fw-bold">Score <span class="text-danger">*</span></label>
            <div class="input-group">
              <input type="number" id="gradeScore" class="form-control" 
                     min="0" max="${data.total_points}" step="0.01"
                     value="${data.current_score || ''}" 
                     placeholder="Enter score" required>
              <span class="input-group-text">/ ${data.total_points}</span>
            </div>
          </div>
          
          <div class="mb-3">
            <label class="form-label fw-bold">Feedback/Comments</label>
            <textarea id="gradeFeedback" class="form-control" rows="4" 
                      placeholder="Provide feedback to the student...">${data.current_feedback || ''}</textarea>
          </div>
        </div>
      `,
      width: 600,
      showCancelButton: true,
      confirmButtonText: '<i class="bi bi-check-circle me-2"></i>Submit Grade',
      cancelButtonText: 'Cancel',
      confirmButtonColor: '#28a745',
      preConfirm: () => {
        const score = document.getElementById('gradeScore').value;
        const feedback = document.getElementById('gradeFeedback').value;
        
        if (!score || score < 0 || score > data.total_points) {
          Swal.showValidationMessage(`Score must be between 0 and ${data.total_points}`);
          return false;
        }
        
        return {
          submission_id: data.submission_id,
          score: parseFloat(score),
          feedback: feedback
        };
      }
    }).then((result) => {
      if (result.isConfirmed) {
        submitGrade(result.value);
      }
    });
  }

  // Submit Grade to Backend
  function submitGrade(gradeData) {
    Swal.fire({
      title: 'Submitting Grade...',
      text: 'Please wait',
      allowOutsideClick: false,
      didOpen: () => Swal.showLoading()
    });

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    fetch('/teacher/assignments/grade', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken,
        'Accept': 'application/json'
      },
      body: JSON.stringify(gradeData)
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        Swal.fire({
          icon: 'success',
          title: 'Grade Submitted!',
          text: 'The student will be notified of their grade.',
          showConfirmButton: true
        }).then(() => {
          location.reload();
        });
      } else {
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: data.message || 'Failed to submit grade'
        });
      }
    })
    .catch(error => {
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: 'Failed to submit grade. Please try again.'
      });
      console.error('Error:', error);
    });
  }
</script>

</html>