<?php

require_once __DIR__ . '/../includes/Dashboard.php';




$student_id = $_SESSION['student_id'];
$dashboard = new Dashboard();
$data = $dashboard->getStudentDashboard($student_id);
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Student Dashboard - SMS3</title>

  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="<?php echo asset('style.css'); ?>">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
  
  <style>
    @import url("../style.css");

    @media (max-width: 768px) and (orientation: landscape) {
      .sidebar {
        max-height: 100vh;
        overflow-y: auto;
      }
    }
    
    body {
      font-family: 'Poppins', sans-serif;
      background-color: #f0f2f5;
    }
    
    .main-content {
      padding: 40px;
    }

    .card {
      border: none;
      border-radius: 1rem;
      transition: transform 0.3s ease-in-out, box-shadow 0.3s ease-in-out;
      box-shadow: 0 10px 20px rgba(0, 0, 0, 0.05);
    }
    
    .card:hover {
      transform: translateY(-5px);
      box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
    }

    .card-header {
      border-radius: 1rem 1rem 0 0 !important;
      font-weight: 600;
      font-size: 1.1rem;
      padding: 1rem 1.5rem;
      border-bottom: none;
    }

    .card-body {
      padding: 1.5rem;
    }

    .bg-primary { background-color: #007bff !important; }
    .bg-success { background-color: #28a745 !important; }
    .bg-info { background-color: #17a2b8 !important; }
    .bg-warning { background-color: #ffc107 !important; }

    .progress {
      height: 8px;
      border-radius: 50px;
      background-color: #e9ecef;
    }
    .progress-bar {
      border-radius: 60px;
    }

    .badge {
      font-weight: 500;
      padding: 0.4em 0.8em;
      border-radius: 50px;
    }

    .alert {
      border-radius: 0.75rem;
      border: 1px solid transparent;
    }

    .alert-primary {
      color: #004085;
      background-color: #cce5ff;
      border-color: #b8daff;
    }

    .alert-warning {
      color: #856404;
      background-color: #fff3cd;
      border-color: #ffeeba;
    }

    .alert-success {
      color: #155724;
      background-color: #d4edda;
      border-color: #c3e6cb;
    }
  </style>
</head>

<body>

  <?php include '../includes/sidenav_student.php'; ?>
  <div class="main-content flex-grow-1">
    <div class="container">
      <div class="row">
        <div class="col">
          <div class="container my-5">
            <div class="card shadow-lg border-0 rounded-4">
              <div class="card-body p-4">

  <div class="dashboard-content">
    <div class="container-fluid">
      <div class="row">
        <div class="col-12">
          <div class="mb-5">
            <h1 class="fw-bold text-dark"><i class="bi bi-speedometer2 text-primary me-3"></i>Student Dashboard</h1>
            <p class="text-muted fs-5">Welcome back, <?php echo htmlspecialchars($_SESSION['first_name']); ?>! Quick access to your courses, deadlines, grades, and announcements.</p>
          </div>

          <div class="row g-4">
            <!-- Active Courses Card -->
            <div class="col-lg-6">
              <div class="card h-100">
                <div class="card-header bg-primary text-white">
                  <i class="bi bi-journal-bookmark-fill me-2"></i> My Active Courses
                </div>
                <div class="card-body">
                  <?php if (!empty($data['enrolled_classes'])): ?>
                    <?php foreach (array_slice($data['enrolled_classes'], 0, 3) as $class): ?>
                      <?php
                        $completion_percentage = ($class['total_modules'] > 0) 
                          ? round(($class['completed_modules'] / $class['total_modules']) * 100) 
                          : 0;
                      ?>
                      <div class="d-flex align-items-center mb-3 pb-3 border-bottom">
                        <div class="flex-grow-1">
                          <h6 class="fw-bold"><?php echo htmlspecialchars($class['subject_code']); ?> – <?php echo htmlspecialchars($class['subject_name']); ?></h6>
                          <p class="text-muted small mb-0">Professor: <?php echo htmlspecialchars($class['teacher_name']); ?></p>
                        </div>
                        <div class="text-end">
                          <div class="progress" style="width: 200px;">
                            <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo $completion_percentage; ?>%" aria-valuenow="<?php echo $completion_percentage; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                          </div>
                          <small class="text-muted mt-1 d-block"><?php echo $completion_percentage; ?>% Complete</small>
                        </div>
                      </div>
                    <?php endforeach; ?>
                  <?php else: ?>
                    <p class="text-muted">No active courses enrolled</p>
                  <?php endif; ?>
                </div>
              </div>
            </div>

            <!-- Upcoming Deadlines Card -->
            <div class="col-lg-6">
              <div class="card h-100">
                <div class="card-header bg-warning">
                  <i class="bi bi-calendar-event-fill me-2"></i> Upcoming Deadlines
                </div>
                <div class="card-body">
                  <ul class="list-group list-group-flush">
                    <?php if (!empty($data['upcoming_assignments'])): ?>
                      <?php foreach (array_slice($data['upcoming_assignments'], 0, 3) as $assignment): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                          <div>
                            <h6 class="mb-0"><?php echo htmlspecialchars($assignment['title']); ?> 
                              <small class="text-muted fw-normal">(<?php echo htmlspecialchars($assignment['subject_code']); ?>)</small>
                            </h6>
                            <small class="text-muted">Due <?php echo format_date($assignment['due_date']); ?></small>
                          </div>
                          <span class="badge <?php echo $assignment['submitted'] ? 'bg-success' : 'bg-danger'; ?>">
                            <?php echo $assignment['submitted'] ? 'Submitted' : 'Not Started'; ?>
                          </span>
                        </li>
                      <?php endforeach; ?>
                    <?php else: ?>
                      <li class="list-group-item">No upcoming deadlines</li>
                    <?php endif; ?>
                  </ul>
                </div>
              </div>
            </div>

            <!-- Recent Grades Card -->
            <div class="col-lg-6">
              <div class="card h-100">
                <div class="card-header bg-success text-white">
                  <i class="bi bi-clipboard-check-fill me-2"></i> Recent Grades
                </div>
                <div class="card-body">
                  <ul class="list-group list-group-flush">
                    <?php if (!empty($data['recent_grades'])): ?>
                      <?php foreach (array_slice($data['recent_grades'], 0, 5) as $grade): ?>
                        <?php
                          $grade_class = ($grade['percentage'] >= 75) ? 'bg-success' : 'bg-danger';
                        ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                          <span><?php echo htmlspecialchars($grade['component_name']); ?> 
                            <small class="text-muted fw-normal">(<?php echo htmlspecialchars($grade['subject_code']); ?>)</small>
                          </span>
                          <span class="badge <?php echo $grade_class; ?> fw-bold p-2">
                            <?php echo number_format($grade['percentage'], 1); ?>%
                          </span>
                        </li>
                      <?php endforeach; ?>
                    <?php else: ?>
                      <li class="list-group-item">No grades available yet</li>
                    <?php endif; ?>
                  </ul>
                  <?php if (!empty($data['academic_info']['gpa'])): ?>
                    <div class="mt-3 text-center">
                      <strong>Current GPA:</strong> <span class="text-primary fs-5"><?php echo number_format($data['academic_info']['gpa'], 2); ?></span>
                    </div>
                  <?php endif; ?>
                </div>
              </div>
            </div>

            <!-- Announcements Card -->
            <div class="col-lg-6">
              <div class="card h-100">
                <div class="card-header bg-info text-white">
                  <i class="bi bi-megaphone-fill me-2"></i> Announcements
                </div>
                <div class="card-body">
                  <?php if (!empty($data['announcements'])): ?>
                    <?php foreach (array_slice($data['announcements'], 0, 3) as $announcement): ?>
                      <div class="alert alert-primary" role="alert">
                        <i class="bi bi-info-circle-fill me-2"></i> 
                        <strong><?php echo htmlspecialchars($announcement['title']); ?></strong><br>
                        <small><?php echo format_date($announcement['published_at']); ?> - <?php echo htmlspecialchars($announcement['posted_by_name']); ?></small>
                      </div>
                    <?php endforeach; ?>
                  <?php else: ?>
                    <p class="text-muted">No announcements available</p>
                  <?php endif; ?>
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
          </div>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
