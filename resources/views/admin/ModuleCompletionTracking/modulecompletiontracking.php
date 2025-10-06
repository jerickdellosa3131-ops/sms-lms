<?php
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

// Get authenticated user
$user = Auth::user();

// Fetch enrollment statistics
$totalStudents = DB::table('users')->where('user_type', 'student')->where('status', 'active')->count();
$totalClasses = DB::table('classes')->where('status', 'active')->count();

// Fetch class enrollment data with completion tracking
$classProgress = DB::table('classes')
    ->leftJoin('class_enrollments', 'classes.class_id', '=', 'class_enrollments.class_id')
    ->select(
        'classes.class_id',
        'classes.section_name',
        'classes.class_code',
        DB::raw('COUNT(CASE WHEN class_enrollments.status = "completed" THEN 1 END) as completed_count'),
        DB::raw('COUNT(CASE WHEN class_enrollments.status = "enrolled" THEN 1 END) as in_progress_count'),
        DB::raw('COUNT(class_enrollments.enrollment_id) as total_enrolled')
    )
    ->groupBy('classes.class_id', 'classes.section_name', 'classes.class_code')
    ->get();

$avgCompletionRate = $classProgress->avg(function($class) {
    return $class->total_enrolled > 0 ? ($class->completed_count / $class->total_enrolled) * 100 : 0;
}) ?? 0;
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
  
  <!-- SweetAlert2 for Alerts -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  
  <!-- SheetJS for Excel Export -->
  <script src="https://cdn.sheetjs.com/xlsx-0.20.0/package/dist/xlsx.full.min.js"></script>
  
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
      <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="d-flex align-items-center">
          <i class="bi bi-journal-check text-info fs-1 me-3"></i>
          <h3 class="mb-0 fw-bold">Module Completion Tracking</h3>
        </div>
        <a href="#" class="btn btn-info text-white">
          <i class="bi bi-download me-2"></i> Export Completion Report
        </a>
      </div>

      <!-- Stats Overview -->
      <div class="row g-3 text-center mb-4">
        <div class="col-md-4">
          <div class="border rounded-3 p-3 bg-light">
            <i class="bi bi-book-fill fs-2 text-primary"></i>
            <h5 class="fw-bold mt-2"><?php echo number_format($totalClasses); ?></h5>
            <p class="small text-muted mb-0">Total Classes</p>
          </div>
        </div>
        <div class="col-md-4">
          <div class="border rounded-3 p-3 bg-light">
            <i class="bi bi-check2-circle fs-2 text-success"></i>
            <h5 class="fw-bold mt-2"><?php echo round($avgCompletionRate, 1); ?>%</h5>
            <p class="small text-muted mb-0">Average Completion Rate</p>
          </div>
        </div>
        <div class="col-md-4">
          <div class="border rounded-3 p-3 bg-light">
            <i class="bi bi-people-fill fs-2 text-warning"></i>
            <h5 class="fw-bold mt-2"><?php echo number_format($totalStudents); ?></h5>
            <p class="small text-muted mb-0">Active Students</p>
          </div>
        </div>
      </div>

      <!-- Progress Table -->
      <h5 class="fw-bold mb-3">Module Progress Overview</h5>
      <div class="table-responsive">
        <table class="table table-hover align-middle">
          <thead class="table-info">
            <tr>
              <th>Module Title</th>
              <th>Completed</th>
              <th>In Progress</th>
              <th>Not Started</th>
              <th>Completion Rate</th>
            </tr>
          </thead>
          <tbody>
            <?php if($classProgress->count() > 0): ?>
              <?php foreach($classProgress as $class): 
                $completionRate = $class->total_enrolled > 0 ? ($class->completed_count / $class->total_enrolled) * 100 : 0;
                $notStarted = $class->total_enrolled - $class->completed_count - $class->in_progress_count;
                $progressColor = $completionRate >= 80 ? 'bg-success' : ($completionRate >= 60 ? 'bg-warning' : 'bg-danger');
              ?>
              <tr>
                <td>
                  <strong><?php echo htmlspecialchars($class->section_name); ?></strong><br>
                  <small class="text-muted"><?php echo htmlspecialchars($class->class_code); ?></small>
                </td>
                <td><span class="badge bg-success"><?php echo $class->completed_count; ?></span></td>
                <td><span class="badge bg-warning"><?php echo $class->in_progress_count; ?></span></td>
                <td><span class="badge bg-secondary"><?php echo $notStarted; ?></span></td>
                <td>
                  <div class="progress" style="height: 20px;">
                    <div class="progress-bar <?php echo $progressColor; ?>" style="width: <?php echo round($completionRate); ?>%;">
                      <?php echo round($completionRate); ?>%
                    </div>
                  </div>
                </td>
              </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="5" class="text-center text-muted">No enrollment data available</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>

      <!-- Insights -->
      <div class="mt-4">
        <h6 class="fw-bold">Insights</h6>
        <ul class="list-unstyled text-muted">
          <li><i class="bi bi-check-circle-fill text-success me-2"></i> Most students have completed the English module.</li>
          <li><i class="bi bi-exclamation-circle-fill text-warning me-2"></i> Physics module needs additional support materials.</li>
          <li><i class="bi bi-lightbulb-fill text-info me-2"></i> Encourage faster completion of Math modules by setting deadlines.</li>
        </ul>
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