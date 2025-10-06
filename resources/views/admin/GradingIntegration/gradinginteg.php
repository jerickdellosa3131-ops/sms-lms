<?php
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

// Get authenticated user
$user = Auth::user();

// Fetch grading statistics
$totalGradedStudents = DB::table('grades')
    ->distinct('student_id')
    ->count('student_id');

$classAverage = DB::table('grades')
    ->avg('percentage') ?? 0;

$topPerformers = DB::table('grades')
    ->where('percentage', '>=', 90)
    ->distinct('student_id')
    ->count('student_id');

// Fetch recent grades with student details
$recentGrades = DB::table('grades')
    ->join('users', 'grades.student_id', '=', 'users.user_id')
    ->join('classes', 'grades.class_id', '=', 'classes.class_id')
    ->select(
        'grades.*',
        'users.first_name',
        'users.last_name',
        'classes.section_name',
        'classes.class_code'
    )
    ->orderBy('grades.graded_at', 'desc')
    ->limit(10)
    ->get();
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
          <i class="bi bi-card-checklist text-success fs-1 me-3"></i>
          <h3 class="mb-0 fw-bold">Grading Integration</h3>
        </div>
        <button class="btn btn-success text-white" onclick="exportCurrentTable('gradesTable', 'grades')">
          <i class="bi bi-download me-2"></i> Export Grades
        </button>
      </div>

      <!-- Overview Stats -->
      <div class="row g-3 text-center mb-4">
        <div class="col-md-4">
          <div class="border rounded-3 p-3 bg-light">
            <i class="bi bi-person-check-fill fs-2 text-primary"></i>
            <h5 class="fw-bold mt-2"><?php echo number_format($totalGradedStudents); ?></h5>
            <p class="small text-muted mb-0">Students Graded</p>
          </div>
        </div>
        <div class="col-md-4">
          <div class="border rounded-3 p-3 bg-light">
            <i class="bi bi-graph-up fs-2 text-warning"></i>
            <h5 class="fw-bold mt-2"><?php echo round($classAverage, 1); ?>%</h5>
            <p class="small text-muted mb-0">Class Average</p>
          </div>
        </div>
        <div class="col-md-4">
          <div class="border rounded-3 p-3 bg-light">
            <i class="bi bi-award-fill fs-2 text-danger"></i>
            <h5 class="fw-bold mt-2"><?php echo number_format($topPerformers); ?></h5>
            <p class="small text-muted mb-0">Top Performers (90%+)</p>
          </div>
        </div>
      </div>

      <!-- Grades Table -->
      <h5 class="fw-bold mb-3">Grade Records</h5>
      <div class="table-responsive">
        <table id="gradesTable" class="table table-hover align-middle">
          <thead class="table-success">
            <tr>
              <th>Student Name</th>
              <th>Quiz Average</th>
              <th>Assignment Average</th>
              <th>Exam Score</th>
              <th>Final Grade</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php if($recentGrades->count() > 0): ?>
              <?php foreach($recentGrades as $grade): 
                $studentName = $grade->first_name . ' ' . $grade->last_name;
                $isPassed = $grade->percentage >= 75;
                $statusBadge = $isPassed ? 'bg-success' : 'bg-danger';
                $statusText = $isPassed ? 'Passed' : 'Failed';
              ?>
              <tr>
                <td>
                  <strong><?php echo htmlspecialchars($studentName); ?></strong><br>
                  <small class="text-muted"><?php echo htmlspecialchars($grade->section_name); ?></small>
                </td>
                <td><?php echo round($grade->quiz_score ?? 0, 1); ?>%</td>
                <td><?php echo round($grade->assignment_score ?? 0, 1); ?>%</td>
                <td><?php echo round($grade->exam_score ?? 0, 1); ?>%</td>
                <td><strong><?php echo round($grade->percentage, 1); ?>%</strong></td>
                <td><span class="badge <?php echo $statusBadge; ?>"><?php echo $statusText; ?></span></td>
                <td>
                  <button class="btn btn-sm btn-outline-info me-1" title="View"
                    onclick="viewRecord(<?php echo $grade->grade_id; ?>, 'Grade', {
                      'Student': '<?php echo addslashes($studentName); ?>',
                      'Section': '<?php echo addslashes($grade->section_name); ?>',
                      'Quiz Average': '<?php echo round($grade->quiz_score ?? 0, 1); ?>%',
                      'Assignment Average': '<?php echo round($grade->assignment_score ?? 0, 1); ?>%',
                      'Exam Score': '<?php echo round($grade->exam_score ?? 0, 1); ?>%',
                      'Final Grade': '<?php echo round($grade->percentage, 1); ?>%',
                      'Status': '<?php echo $statusText; ?>'
                    })">
                    <i class="bi bi-eye"></i>
                  </button>
                  <button class="btn btn-sm btn-outline-warning me-1" title="Edit"
                    onclick="editRecord(<?php echo $grade->grade_id; ?>, 'Grade', {
                      'quiz_score': '<?php echo round($grade->quiz_score ?? 0, 1); ?>',
                      'assignment_score': '<?php echo round($grade->assignment_score ?? 0, 1); ?>',
                      'exam_score': '<?php echo round($grade->exam_score ?? 0, 1); ?>'
                    })">
                    <i class="bi bi-pencil"></i>
                  </button>
                  <button class="btn btn-sm btn-outline-danger" title="Delete"
                    onclick="deleteRecord(<?php echo $grade->grade_id; ?>, 'Grade', '<?php echo addslashes($studentName); ?>')">
                    <i class="bi bi-trash"></i>
                  </button>
                </td>
              </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="7" class="text-center text-muted">No grades found</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>

      <!-- Insights -->
      <div class="mt-4">
        <h6 class="fw-bold">Insights</h6>
        <ul class="list-unstyled text-muted">
          <li><i class="bi bi-check-circle-fill text-success me-2"></i> Majority of students passed the final grading period.</li>
          <li><i class="bi bi-exclamation-circle-fill text-warning me-2"></i> Science scores are below average — recommend remedial classes.</li>
          <li><i class="bi bi-lightbulb-fill text-info me-2"></i> Top performers excelled in both assignments and quizzes.</li>
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