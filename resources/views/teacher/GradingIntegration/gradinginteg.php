<?php
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

$user = Auth::user();
$teacher_id = $user ? $user->user_id : null;

if ($teacher_id) {
    // Fetch submissions that need grading
    $submissions = DB::table('assignment_submissions')
        ->join('assignments', 'assignment_submissions.assignment_id', '=', 'assignments.assignment_id')
        ->join('classes', 'assignments.class_id', '=', 'classes.class_id')
        ->join('users as students', 'assignment_submissions.student_id', '=', 'students.user_id')
        ->where('assignments.teacher_id', $teacher_id)
        ->whereIn('assignment_submissions.status', ['submitted', 'graded'])
        ->select(
            'assignment_submissions.*',
            'assignments.title as assignment_title',
            'assignments.total_points',
            'classes.section_name',
            'classes.class_code',
            'students.first_name as student_first',
            'students.last_name as student_last'
        )
        ->orderBy('assignment_submissions.submitted_at', 'desc')
        ->get();
} else {
    $submissions = collect();
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
            <i class="bi bi-clipboard-check-fill text-success fs-1 me-3"></i>
            <h3 class="mb-0 fw-bold">Grading Integration Management</h3>
          </div>
          <button onclick="exportTableToExcel('grading-table', 'Grade_Reports')" class="btn btn-success text-white">
            <i class="bi bi-download me-2"></i> Export Grade Reports
          </button>
        </div>

        <!-- Overview Stats -->
        <div class="row g-3 text-center mb-4">
          <div class="col-md-3">
            <div class="border rounded-3 p-3 bg-light">
              <i class="bi bi-clipboard-check fs-2 text-primary"></i>
              <h5 class="fw-bold mt-2"><?php echo $submissions->where('status', 'graded')->count(); ?></h5>
              <p class="small text-muted mb-0">Graded Submissions</p>
            </div>
          </div>
          <div class="col-md-3">
            <div class="border rounded-3 p-3 bg-light">
              <i class="bi bi-hourglass-split fs-2 text-warning"></i>
              <h5 class="fw-bold mt-2"><?php echo $submissions->where('status', 'submitted')->count(); ?></h5>
              <p class="small text-muted mb-0">Pending Grading</p>
            </div>
          </div>
          <div class="col-md-3">
            <div class="border rounded-3 p-3 bg-light">
              <i class="bi bi-file-earmark-text fs-2 text-success"></i>
              <h5 class="fw-bold mt-2"><?php echo $submissions->count(); ?></h5>
              <p class="small text-muted mb-0">Total Submissions</p>
            </div>
          </div>
          <div class="col-md-3">
            <div class="border rounded-3 p-3 bg-light">
              <i class="bi bi-graph-up fs-2 text-info"></i>
              <h5 class="fw-bold mt-2"><?php echo $submissions->where('status', 'graded')->where('score', '>=', 75)->count(); ?></h5>
              <p class="small text-muted mb-0">Passing Grades</p>
            </div>
          </div>
        </div>

        <!-- Grading Table -->
        <h5 class="fw-bold mb-3">Grade Records Overview</h5>
        <div class="table-responsive">
          <table id="grading-table" class="table table-hover align-middle">
            <thead class="table-success">
              <tr>
                <th>Student Name</th>
                <th>Class</th>
                <th>Assignment</th>
                <th>Submitted</th>
                <th>Max Points</th>
                <th>Score</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php if($submissions->count() > 0): ?>
                <?php foreach($submissions as $submission): ?>
                <tr>
                  <td><?php echo htmlspecialchars($submission->student_first . ' ' . $submission->student_last); ?></td>
                  <td><?php echo htmlspecialchars($submission->section_name); ?></td>
                  <td>
                    <strong><?php echo htmlspecialchars($submission->assignment_title); ?></strong>
                  </td>
                  <td><?php echo $submission->submitted_at ? date('M d, Y', strtotime($submission->submitted_at)) : 'N/A'; ?></td>
                  <td><?php echo $submission->total_points; ?></td>
                  <td>
                    <?php if($submission->score !== null): ?>
                      <strong class="<?php echo $submission->score >= 75 ? 'text-success' : 'text-warning'; ?>"><?php echo $submission->score; ?></strong>
                    <?php else: ?>
                      <span class="text-muted">Not graded</span>
                    <?php endif; ?>
                  </td>
                  <td>
                    <?php if($submission->status == 'graded'): ?>
                      <span class="badge bg-success">Graded</span>
                    <?php else: ?>
                      <span class="badge bg-warning">Pending</span>
                    <?php endif; ?>
                  </td>
                  <td>
                    <button class="btn btn-sm btn-outline-primary" onclick="gradeSubmission(<?php echo $submission->submission_id; ?>, '<?php echo addslashes($submission->student_first . ' ' . $submission->student_last); ?>', '<?php echo addslashes($submission->assignment_title); ?>', <?php echo $submission->total_points; ?>, <?php echo $submission->score ?? 0; ?>)">
                      <i class="bi bi-pencil-square"></i> Grade
                    </button>
                    <button class="btn btn-sm btn-outline-success" onclick="viewRecord(<?php echo $submission->submission_id; ?>, 'Submission', {student: '<?php echo addslashes($submission->student_first . ' ' . $submission->student_last); ?>', assignment: '<?php echo addslashes($submission->assignment_title); ?>', score: <?php echo $submission->score ?? 'null'; ?>})">
                      <i class="bi bi-eye"></i>
                    </button>
                  </td>
                </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr>
                  <td colspan="8" class="text-center text-muted py-4">
                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                    No submissions to grade yet.
                  </td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>

        <!-- Insights -->
        <div class="mt-4">
          <h6 class="fw-bold">Insights</h6>
          <ul class="list-unstyled text-muted">
            <li><i class="bi bi-check-circle-fill text-success me-2"></i> Majority of students achieved a passing grade.</li>
            <li><i class="bi bi-lightbulb-fill text-info me-2"></i> Consider offering remedial classes for subjects with high fail rates.</li>
            <li><i class="bi bi-exclamation-circle-fill text-warning me-2"></i> 5% of students have grade disputes requiring admin attention.</li>
          </ul>
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