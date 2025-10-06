<?php
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

$user = Auth::user();
$teacher_id = $user ? $user->user_id : null;

// Get teacher's teacher_id
$teacher = null;
$moduleProgress = collect();
$classStats = collect();

if ($teacher_id) {
    $teacher = DB::table('teachers')->where('user_id', $teacher_id)->first();
    
    if ($teacher) {
        // Fetch modules from teacher's classes with completion stats
        $moduleProgress = DB::table('modules')
            ->join('classes', 'modules.class_id', '=', 'classes.class_id')
            ->leftJoin('module_completions', 'modules.module_id', '=', 'module_completions.module_id')
            ->leftJoin('users', 'module_completions.student_id', '=', 'users.user_id')
            ->where('classes.teacher_id', $teacher->teacher_id)
            ->select(
                'modules.*',
                'classes.section_name',
                'classes.class_code',
                'users.first_name',
                'users.last_name',
                'module_completions.completion_percentage',
                'module_completions.completed_at'
            )
            ->orderBy('modules.module_order', 'asc')
            ->get();
        
        // Get class statistics
        $classStats = DB::table('classes')
            ->leftJoin('class_enrollments', 'classes.class_id', '=', 'class_enrollments.class_id')
            ->where('classes.teacher_id', $teacher->teacher_id)
            ->select(
                'classes.section_name',
                'classes.class_code',
                DB::raw('COUNT(DISTINCT class_enrollments.student_id) as total_students')
            )
            ->groupBy('classes.class_id', 'classes.section_name', 'classes.class_code')
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
                  $studentName = $progress->first_name && $progress->last_name 
                    ? $progress->first_name . ' ' . $progress->last_name 
                    : 'N/A';
                  $percentage = $progress->completion_percentage ?? 0;
                  $progressColor = $percentage >= 80 ? 'bg-success' : ($percentage >= 50 ? 'bg-warning' : 'bg-danger');
                  $statusBadge = $percentage >= 100 ? 'bg-success' : ($percentage >= 50 ? 'bg-warning text-dark' : 'bg-danger');
                  $statusText = $percentage >= 100 ? 'Completed' : ($percentage >= 50 ? 'In Progress' : 'Not Started');
                  $lastActivity = $progress->completed_at ? date('M d, Y', strtotime($progress->completed_at)) : 'N/A';
                ?>
                <tr>
                  <td><?php echo htmlspecialchars($studentName); ?></td>
                  <td>
                    <strong><?php echo htmlspecialchars($progress->section_name); ?></strong><br>
                    <small class="text-muted"><?php echo htmlspecialchars($progress->class_code); ?></small>
                  </td>
                  <td><?php echo htmlspecialchars($progress->module_title); ?></td>
                  <td>
                    <div class="progress" style="height: 20px;">
                      <div class="progress-bar <?php echo $progressColor; ?>" style="width: <?php echo $percentage; ?>%;">
                        <?php echo $percentage; ?>%
                      </div>
                    </div>
                  </td>
                  <td><span class="badge <?php echo $statusBadge; ?>"><?php echo $statusText; ?></span></td>
                  <td><?php echo $lastActivity; ?></td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php else: ?>
          <div class="alert alert-info text-center py-5">
            <i class="bi bi-clipboard-data fs-1 d-block mb-3"></i>
            <h5>No Module Progress Data</h5>
            <p class="mb-0">No students have started working on modules yet, or no modules have been assigned.</p>
          </div>
          
          <!-- Class Statistics -->
          <?php if($classStats->count() > 0): ?>
            <h5 class="fw-bold mb-3 mt-4">My Classes</h5>
            <div class="row g-3">
              <?php foreach($classStats as $class): ?>
                <div class="col-md-4">
                  <div class="card border-0 shadow-sm">
                    <div class="card-body">
                      <h6 class="fw-bold"><?php echo htmlspecialchars($class->section_name); ?></h6>
                      <p class="text-muted small mb-2">
                        <code><?php echo htmlspecialchars($class->class_code); ?></code>
                      </p>
                      <div class="d-flex align-items-center">
                        <i class="bi bi-people-fill text-primary me-2"></i>
                        <span><?php echo $class->total_students; ?> Students Enrolled</span>
                      </div>
                    </div>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
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