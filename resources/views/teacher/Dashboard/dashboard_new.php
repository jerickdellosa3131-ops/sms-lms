<?php

require_once __DIR__ . '/../includes/Dashboard.php';




$teacher_id = $_SESSION['teacher_id'];
$dashboard = new Dashboard();
$data = $dashboard->getTeacherDashboard($teacher_id);
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Teacher Dashboard - SMS3</title>

  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="<?php echo asset('style.css'); ?>">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
  
  <style>
    @import url("../style.css");

    @media (max-width: 768px) and (orientation: landscape) {
      .sidebar {
        max-height: 100vh;
        overflow-y: auto;
      }
    }
  </style>
</head>

<body>

  <?php include '../includes/sidenav_teacher.php'; ?>
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
          <i class="bi bi-mortarboard-fill text-success fs-1 me-3"></i>
          <div>
            <h3 class="mb-0 fw-bold">Teacher Dashboard</h3>
            <p class="text-muted mb-0">Welcome back, <?php echo htmlspecialchars($_SESSION['first_name']); ?>!</p>
          </div>
        </div>
        <a href="<?php echo SITE_URL; ?>/Teacher/ClassPortal/classportal.php" class="btn btn-success">
          <i class="bi bi-plus-circle me-2"></i> Manage Classes
        </a>
      </div>

      <!-- Overview Cards -->
      <div class="row g-3 mb-4 text-center">
        <div class="col-md-3">
          <div class="border rounded-3 p-3 bg-light">
            <i class="bi bi-people-fill fs-2 text-primary"></i>
            <h5 class="fw-bold mt-2"><?php echo number_format($data['total_students']); ?></h5>
            <p class="small text-muted mb-0">Active Students</p>
          </div>
        </div>
        <div class="col-md-3">
          <div class="border rounded-3 p-3 bg-light">
            <i class="bi bi-journal-text fs-2 text-info"></i>
            <h5 class="fw-bold mt-2"><?php echo number_format($data['total_classes']); ?></h5>
            <p class="small text-muted mb-0">Active Classes</p>
          </div>
        </div>
        <div class="col-md-3">
          <div class="border rounded-3 p-3 bg-light">
            <i class="bi bi-pencil-square fs-2 text-warning"></i>
            <h5 class="fw-bold mt-2"><?php echo number_format($data['pending_grading']); ?></h5>
            <p class="small text-muted mb-0">Pending Grading</p>
          </div>
        </div>
        <div class="col-md-3">
          <div class="border rounded-3 p-3 bg-light">
            <i class="bi bi-bar-chart-line-fill fs-2 text-danger"></i>
            <h5 class="fw-bold mt-2"><?php echo number_format($data['avg_performance'], 1); ?>%</h5>
            <p class="small text-muted mb-0">Avg. Class Performance</p>
          </div>
        </div>
      </div>

      <!-- Quick Actions -->
      <h5 class="fw-bold mb-3">Quick Actions</h5>
      <div class="d-flex flex-wrap gap-2 mb-4">
        <a href="<?php echo SITE_URL; ?>/Teacher/LessonMaterialUpload/lessonmaterialupload.php" class="btn btn-outline-primary">
          <i class="bi bi-upload me-2"></i> Upload Materials
        </a>
        <a href="<?php echo SITE_URL; ?>/Teacher/AssignmentSubmission/assignsubmission.php" class="btn btn-outline-success">
          <i class="bi bi-pencil-square me-2"></i> Create Assignment
        </a>
        <a href="<?php echo SITE_URL; ?>/Teacher/GradingIntegration/gradinginteg.php" class="btn btn-outline-warning">
          <i class="bi bi-clipboard-check me-2"></i> Grade Submissions
        </a>
        <a href="<?php echo SITE_URL; ?>/Teacher/LMSAnalytics/LMSAnalytics.php" class="btn btn-outline-info">
          <i class="bi bi-graph-up me-2"></i> View Analytics
        </a>
      </div>

      <!-- My Classes -->
      <?php if (!empty($data['classes'])): ?>
      <h5 class="fw-bold mb-3">My Classes</h5>
      <div class="row g-3 mb-4">
        <?php foreach ($data['classes'] as $class): ?>
        <div class="col-md-6 col-lg-4">
          <div class="card h-100">
            <div class="card-body">
              <h6 class="fw-bold"><?php echo htmlspecialchars($class['subject_code']); ?></h6>
              <p class="text-muted small mb-2"><?php echo htmlspecialchars($class['subject_name']); ?></p>
              <p class="small mb-2">
                <i class="bi bi-tag me-1"></i> Section: <?php echo htmlspecialchars($class['section_name']); ?>
              </p>
              <p class="small mb-0">
                <i class="bi bi-people me-1"></i> <?php echo $class['enrolled_students']; ?> Students
              </p>
            </div>
            <div class="card-footer bg-white">
              <a href="<?php echo SITE_URL; ?>/Teacher/ClassPortal/classportal.php?class_id=<?php echo $class['class_id']; ?>" class="btn btn-sm btn-primary w-100">
                View Class
              </a>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>

      <!-- Recent Activity -->
      <?php if (!empty($data['recent_activity'])): ?>
      <h5 class="fw-bold mb-3">Recent Activity</h5>
      <div class="table-responsive">
        <table class="table table-hover align-middle">
          <thead class="table-success">
            <tr>
              <th>Date</th>
              <th>Activity</th>
              <th>Class</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach (array_slice($data['recent_activity'], 0, 10) as $activity): ?>
            <tr>
              <td><?php echo format_date($activity['date']); ?></td>
              <td>
                <?php
                  $icon_map = [
                    'material' => 'bi bi-upload text-primary',
                    'assignment' => 'bi bi-pencil-fill text-warning',
                    'quiz' => 'bi bi-question-circle text-info'
                  ];
                  $icon = $icon_map[$activity['type']] ?? 'bi bi-circle';
                ?>
                <i class="<?php echo $icon; ?> me-2"></i>
                <?php echo htmlspecialchars($activity['activity']); ?>
              </td>
              <td><?php echo htmlspecialchars($activity['subject_name']); ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <?php endif; ?>

      <!-- Insights -->
      <div class="mt-4">
        <h6 class="fw-bold">Insights</h6>
        <ul class="list-unstyled text-muted">
          <li><i class="bi bi-lightbulb-fill text-info me-2"></i> 
            You have <?php echo $data['pending_grading']; ?> submissions waiting for grading.
          </li>
          <li><i class="bi bi-check-circle-fill text-success me-2"></i> 
            Your students have an average performance of <?php echo number_format($data['avg_performance'], 1); ?>%.
          </li>
          <li><i class="bi bi-info-circle-fill text-primary me-2"></i> 
            You have uploaded <?php echo $data['uploaded_materials']; ?> learning materials total.
          </li>
        </ul>
      </div>

              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
