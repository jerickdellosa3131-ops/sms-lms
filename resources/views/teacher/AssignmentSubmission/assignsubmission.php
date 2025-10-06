<?php
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

$user = Auth::user();
$teacher_id = $user ? $user->user_id : null;

// Get teacher's teacher_id and fetch assignments
$teacher = null;
$assignments = collect();
$submissions = collect();

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
        
        // Fetch student submissions
        $submissions = DB::table('assignment_submissions')
            ->join('assignments', 'assignment_submissions.assignment_id', '=', 'assignments.assignment_id')
            ->join('users', 'assignment_submissions.student_id', '=', 'users.user_id')
            ->where('assignments.teacher_id', $teacher->teacher_id)
            ->select(
                'assignment_submissions.*',
                'assignments.title as assignment_title',
                'assignments.total_points',
                'users.first_name',
                'users.last_name'
            )
            ->orderBy('assignment_submissions.submitted_at', 'desc')
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
            <h3 class="mb-0 fw-bold">Assignment Management</h3>
          </div>
          <a href="#" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i> Back to Class Portal
          </a>
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
              <label for="assignmentFile" class="form-label fw-bold">Attach File</label>
              <input type="file" id="assignmentFile" class="form-control">
              <small class="text-muted">Optional: Upload PDF, DOCX, or reference materials.</small>
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
                      <button class="btn btn-sm btn-outline-info" title="View"
                        onclick="viewRecord(<?php echo $sub->submission_id; ?>, 'Submission', {
                          'student': '<?php echo addslashes($studentName); ?>',
                          'assignment': '<?php echo addslashes($sub->assignment_title); ?>'
                        })">
                        <i class="bi bi-eye"></i>
                      </button>
                      <?php if($sub->status === 'submitted'): ?>
                        <button class="btn btn-sm btn-outline-success" title="Grade"
                          onclick="gradeAssignment(<?php echo $sub->submission_id; ?>, '<?php echo addslashes($studentName); ?>', '<?php echo addslashes($sub->assignment_title); ?>')">
                          <i class="bi bi-check2-square"></i>
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

</html>