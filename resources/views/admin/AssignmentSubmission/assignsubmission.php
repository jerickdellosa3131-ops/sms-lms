<?php
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

// Get authenticated user
$user = Auth::user();

// Fetch assignment submissions with student and assignment details
$submissions = DB::table('assignment_submissions')
    ->join('assignments', 'assignment_submissions.assignment_id', '=', 'assignments.assignment_id')
    ->join('users', 'assignment_submissions.student_id', '=', 'users.user_id')
    ->join('classes', 'assignments.class_id', '=', 'classes.class_id')
    ->select(
        'assignment_submissions.*',
        'assignments.title as assignment_title',
        'assignments.due_date',
        'users.first_name',
        'users.last_name',
        'classes.section_name',
        'classes.class_code'
    )
    ->orderBy('assignment_submissions.submitted_at', 'desc')
    ->get();

// Calculate statistics
$totalSubmissions = $submissions->count();
$onTimeSubmissions = $submissions->filter(function($s) {
    return strtotime($s->submitted_at) <= strtotime($s->due_date);
})->count();
$lateSubmissions = $totalSubmissions - $onTimeSubmissions;
$gradedSubmissions = $submissions->where('status', 'graded')->count();
$pendingGrading = $totalSubmissions - $gradedSubmissions;
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
        <div class="container">
            <div class="row">
                <div class="col">
                    <div class="container my-5">
                        <div class="card shadow-lg border-0 rounded-4">
                            <div class="card-body p-4">
                                <!-- Header -->

                                <!-- Header -->
                                <div class="container my-4">

                                    <div class="card-body p-4">
                                        <!-- Header -->
                                        <div class="d-flex justify-content-between align-items-center mb-4">
                                            <div class="d-flex align-items-center">
                                                <i class="bi bi-upload text-info fs-1 me-3"></i>
                                                <h3 class="mb-0 fw-bold">Assignment Submissions</h3>
                                            </div>
                                            <div class="btn-group">
                                                <button class="btn btn-info text-white" data-bs-toggle="modal" data-bs-target="#createAssignmentModal">
                                                    <i class="bi bi-cloud-arrow-up me-2"></i> Create New Assignment
                                                </button>
                                                <button class="btn btn-success text-white" onclick="exportCurrentTable('submissionsTable', 'assignment_submissions')">
                                                    <i class="bi bi-file-earmark-excel me-2"></i> Export
                                                </button>
                                            </div>
                                        </div>

                                        <!-- Submission Overview -->
                                        <div class="row g-3 text-center mb-4">
                                            <div class="col-md-4">
                                                <div class="border rounded-3 p-3 bg-light">
                                                    <i class="bi bi-check-circle-fill fs-2 text-success"></i>
                                                    <h5 class="fw-bold mt-2"><?php echo $onTimeSubmissions; ?></h5>
                                                    <p class="small text-muted mb-0">On-Time Submissions</p>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="border rounded-3 p-3 bg-light">
                                                    <i class="bi bi-clock-fill fs-2 text-warning"></i>
                                                    <h5 class="fw-bold mt-2"><?php echo $lateSubmissions; ?></h5>
                                                    <p class="small text-muted mb-0">Late Submissions</p>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="border rounded-3 p-3 bg-light">
                                                    <i class="bi bi-hourglass-split fs-2 text-info"></i>
                                                    <h5 class="fw-bold mt-2"><?php echo $pendingGrading; ?></h5>
                                                    <p class="small text-muted mb-0">Pending Grading</p>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Table of Submissions -->
                                        <h5 class="fw-bold mb-3">Recent Submissions</h5>
                                        <div class="table-responsive">
                                            <table id="submissionsTable" class="table table-hover align-middle">
                                                <thead class="table-info">
                                                    <tr>
                                                        <th>Student Name</th>
                                                        <th>Assignment</th>
                                                        <th>Submitted On</th>
                                                        <th>Status</th>
                                                        <th>File</th>
                                                        <th>Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php if($submissions->count() > 0): ?>
                                                        <?php foreach($submissions as $sub): 
                                                            $studentName = $sub->first_name . ' ' . $sub->last_name;
                                                            $isLate = strtotime($sub->submitted_at) > strtotime($sub->due_date);
                                                            $statusBadge = $isLate ? 'bg-warning text-dark' : 'bg-success';
                                                            $statusText = $isLate ? 'Late' : 'On Time';
                                                            if($sub->status == 'graded') {
                                                                $statusBadge = 'bg-info';
                                                                $statusText = 'Graded';
                                                            }
                                                        ?>
                                                        <tr>
                                                            <td><?php echo htmlspecialchars($studentName); ?></td>
                                                            <td>
                                                                <strong><?php echo htmlspecialchars($sub->assignment_title); ?></strong><br>
                                                                <small class="text-muted"><?php echo htmlspecialchars($sub->section_name); ?></small>
                                                            </td>
                                                            <td><?php echo date('M d, Y', strtotime($sub->submitted_at)); ?></td>
                                                            <td><span class="badge <?php echo $statusBadge; ?>"><?php echo $statusText; ?></span></td>
                                                            <td>
                                                                <?php if($sub->file_path): ?>
                                                                    <a href="#" class="btn btn-sm btn-outline-primary"><i class="bi bi-download"></i> Download</a>
                                                                <?php else: ?>
                                                                    <span class="text-muted">No file</span>
                                                                <?php endif; ?>
                                                            </td>
                                                            <td>
                                                                <button class="btn btn-sm btn-outline-info me-1" title="View"
                                                                    onclick="viewRecord(<?php echo $sub->submission_id; ?>, 'Submission', {
                                                                        'Student': '<?php echo addslashes($studentName); ?>',
                                                                        'Assignment': '<?php echo addslashes($sub->assignment_title); ?>',
                                                                        'Section': '<?php echo addslashes($sub->section_name); ?>',
                                                                        'Submitted': '<?php echo date('M d, Y', strtotime($sub->submitted_at)); ?>',
                                                                        'Status': '<?php echo $statusText; ?>',
                                                                        'Score': '<?php echo $sub->score !== null ? round($sub->score, 1) . '%' : 'Not Graded'; ?>'
                                                                    })">
                                                                    <i class="bi bi-eye"></i>
                                                                </button>
                                                                <button class="btn btn-sm btn-outline-success me-1" title="Grade"
                                                                    onclick="editRecord(<?php echo $sub->submission_id; ?>, 'Grade Submission', {
                                                                        'student': '<?php echo addslashes($studentName); ?>',
                                                                        'assignment': '<?php echo addslashes($sub->assignment_title); ?>',
                                                                        'score': '<?php echo $sub->score ?? ''; ?>'
                                                                    })">
                                                                    <i class="bi bi-pencil"></i>
                                                                </button>
                                                                <button class="btn btn-sm btn-outline-danger" title="Delete"
                                                                    onclick="deleteRecord(<?php echo $sub->submission_id; ?>, 'Submission', '<?php echo addslashes($studentName . ' - ' . $sub->assignment_title); ?>')">
                                                                    <i class="bi bi-trash"></i>
                                                                </button>
                                                            </td>
                                                        </tr>
                                                        <?php endforeach; ?>
                                                    <?php else: ?>
                                                        <tr>
                                                            <td colspan="6" class="text-center text-muted">No submissions yet</td>
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
                </div>
            </div>
        </div>
    </div>


    


    </style>

<!-- Create Assignment Modal -->
<div class="modal fade" id="createAssignmentModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-info text-white">
        <h5 class="modal-title"><i class="bi bi-file-earmark-plus me-2"></i>Create New Assignment</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="createAssignmentForm">
          <div class="mb-3">
            <label for="assignmentTitle" class="form-label fw-bold">Assignment Title <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="assignmentTitle" placeholder="e.g., Chapter 5 Essay" required>
          </div>
          
          <div class="row mb-3">
            <div class="col-md-6">
              <label for="assignmentSubject" class="form-label fw-bold">Subject</label>
              <select class="form-select" id="assignmentSubject">
                <option value="english">English</option>
                <option value="math">Mathematics</option>
                <option value="science">Science</option>
                <option value="history">History</option>
              </select>
            </div>
            <div class="col-md-6">
              <label for="assignmentClass" class="form-label fw-bold">Class</label>
              <select class="form-select" id="assignmentClass">
                <option value="grade-7">Grade 7</option>
                <option value="grade-8">Grade 8</option>
                <option value="grade-9">Grade 9</option>
                <option value="grade-10">Grade 10</option>
              </select>
            </div>
          </div>

          <div class="mb-3">
            <label for="assignmentInstructions" class="form-label fw-bold">Instructions</label>
            <textarea class="form-control" id="assignmentInstructions" rows="4" placeholder="Provide detailed instructions for students..."></textarea>
          </div>

          <div class="row mb-3">
            <div class="col-md-4">
              <label for="assignmentPoints" class="form-label fw-bold">Total Points</label>
              <input type="number" class="form-control" id="assignmentPoints" value="100" min="1">
            </div>
            <div class="col-md-4">
              <label for="assignmentDueDate" class="form-label fw-bold">Due Date <span class="text-danger">*</span></label>
              <input type="date" class="form-control" id="assignmentDueDate" required>
            </div>
            <div class="col-md-4">
              <label for="assignmentDueTime" class="form-label fw-bold">Due Time</label>
              <input type="time" class="form-control" id="assignmentDueTime" value="23:59">
            </div>
          </div>

          <div class="mb-3">
            <label for="assignmentFile" class="form-label fw-bold">Attach File (Optional)</label>
            <input type="file" class="form-control" id="assignmentFile">
            <small class="text-muted">Upload reference materials, rubrics, or templates</small>
          </div>

          <div class="mb-3">
            <label class="form-label fw-bold">Submission Settings</label>
            <div class="form-check">
              <input class="form-check-input" type="checkbox" id="allowLateSubmission">
              <label class="form-check-label" for="allowLateSubmission">
                Allow late submissions (with penalty)
              </label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="checkbox" id="allowResubmission">
              <label class="form-check-label" for="allowResubmission">
                Allow students to resubmit
              </label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="checkbox" id="notifyStudents" checked>
              <label class="form-check-label" for="notifyStudents">
                Send email notification to students
              </label>
            </div>
          </div>

        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
          <i class="bi bi-x-lg me-2"></i>Cancel
        </button>
        <button type="button" class="btn btn-info text-white" onclick="handleCreateAssignment()">
          <i class="bi bi-check-lg me-2"></i>Create Assignment
        </button>
      </div>
    </div>
  </div>
</div>

</body>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
  function handleCreateAssignment() {
    const title = document.getElementById('assignmentTitle').value;
    const dueDate = document.getElementById('assignmentDueDate').value;
    if (!title || !dueDate) {
      alert('Please fill in all required fields');
      return;
    }
    alert('Assignment "' + title + '" created successfully! Students will be notified. (Demo mode)');
    bootstrap.Modal.getInstance(document.getElementById('createAssignmentModal')).hide();
    document.getElementById('createAssignmentForm').reset();
  }

  // Export Assignments to Excel
  function exportAssignmentsExcel() {
    const wb = XLSX.utils.book_new();
    
    const data = [
      ['Assignment Submissions Report'],
      [''],
      ['Student Name', 'Assignment', 'Submission Date', 'Status'],
      ['John Doe', 'Math Assignment 1', '2025-08-08', 'On Time'],
      ['Mark Santos', 'Math Assignment 1', '2025-08-09', 'Late'],
      ['Maria Lopez', 'Math Assignment 1', '2025-08-08', 'On Time']
    ];
    
    const ws = XLSX.utils.aoa_to_sheet(data);
    ws['!cols'] = [{ wch: 20 }, { wch: 25 }, { wch: 18 }, { wch: 12 }];
    
    XLSX.utils.book_append_sheet(wb, ws, 'Submissions');
    XLSX.writeFile(wb, 'assignment-submissions.xlsx');
    
    alert('Assignment submissions exported successfully!');
  }
</script>

</html>