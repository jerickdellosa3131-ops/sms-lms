<?php
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

$user = Auth::user();
$teacher_id = $user ? $user->user_id : null;

$moduleProgress = collect(); // Empty by default

if ($teacher_id) {
    // Fetch student progress for teacher's classes
    // Uncomment when class_progress table exists:
    // $moduleProgress = DB::table('class_progress')
    //     ->join('class_enrollments', 'class_progress.enrollment_id', '=', 'class_enrollments.enrollment_id')
    //     ->join('classes', 'class_enrollments.class_id', '=', 'classes.class_id')
    //     ->join('modules', 'class_progress.module_id', '=', 'modules.module_id')
    //     ->join('users as students', 'class_enrollments.student_id', '=', 'students.user_id')
    //     ->where('classes.teacher_id', $teacher_id)
    //     ->select(
    //         'class_progress.*',
    //         'modules.module_title',
    //         'classes.section_name',
    //         DB::raw("CONCAT(students.first_name, ' ', students.last_name) as student_name")
    //     )
    //     ->orderBy('students.last_name', 'asc')
    //     ->get();
}
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
            <i class="bi bi-journal-check text-primary fs-1 me-3"></i>
            <h3 class="mb-0 fw-bold">Module Completion Tracking</h3>
          </div>
          <button onclick="exportTableToExcel('progress-table', 'Module_Progress')" class="btn btn-primary text-white">
            <i class="bi bi-download me-2"></i> Export Progress Report
          </button>
        </div>

        <!-- Module Progress Table -->
        <h5 class="fw-bold mb-3">Student Progress Overview (<?php echo $moduleProgress->count(); ?>)</h5>
        
        <?php if($moduleProgress->count() > 0): ?>
          <div class="table-responsive">
            <table id="progress-table" class="table table-hover align-middle">
              <thead class="table-primary">
                <tr>
                  <th>Student Name</th>
                  <th>Class</th>
                  <th>Module</th>
                  <th>Progress</th>
                  <th>Status</th>
                  <th>Last Activity</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach($moduleProgress as $progress): 
                  $percentage = $progress->completion_percentage ?? 0;
                  $progressColor = $percentage >= 80 ? 'bg-success' : ($percentage >= 50 ? 'bg-warning' : 'bg-danger');
                  $statusBadge = $percentage >= 100 ? 'bg-success' : ($percentage >= 50 ? 'bg-warning text-dark' : 'bg-danger');
                  $statusText = $percentage >= 100 ? 'Completed' : ($percentage >= 50 ? 'In Progress' : 'At Risk');
                ?>
                <tr>
                  <td><?php echo htmlspecialchars($progress->student_name); ?></td>
                  <td><?php echo htmlspecialchars($progress->section_name); ?></td>
                  <td><?php echo htmlspecialchars($progress->module_title); ?></td>
                  <td>
                    <div class="progress" style="height: 20px;">
                      <div class="progress-bar <?php echo $progressColor; ?>" style="width: <?php echo $percentage; ?>%;">
                        <?php echo $percentage; ?>%
                      </div>
                    </div>
                  </td>
                  <td><span class="badge <?php echo $statusBadge; ?>"><?php echo $statusText; ?></span></td>
                  <td><?php echo $progress->last_activity ? date('M d, Y', strtotime($progress->last_activity)) : 'N/A'; ?></td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php else: ?>
          <div class="alert alert-info text-center">
            <i class="bi bi-clipboard-data fs-1 d-block mb-3"></i>
            <h5>No Module Progress Data</h5>
            <p class="mb-0">Module completion tracking data is not available.</p>
            <small class="text-muted d-block mt-2">
              This feature requires a <code>class_progress</code> table. 
              Uncomment the database query at the top of this file to enable tracking.
            </small>
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

</html>