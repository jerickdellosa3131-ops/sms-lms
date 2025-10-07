<?php
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

// Get authenticated user
$user = Auth::user();

// Fetch assignment grades
$assignmentGrades = DB::table('assignment_submissions')
    ->join('assignments', 'assignment_submissions.assignment_id', '=', 'assignments.assignment_id')
    ->join('students', 'assignment_submissions.student_id', '=', 'students.student_id')
    ->join('users', 'students.user_id', '=', 'users.user_id')
    ->join('classes', 'assignments.class_id', '=', 'classes.class_id')
    ->leftJoin('teachers', 'assignments.teacher_id', '=', 'teachers.teacher_id')
    ->leftJoin('users as teacher_users', 'teachers.user_id', '=', 'teacher_users.user_id')
    ->where('assignment_submissions.status', 'graded')
    ->select(
        'assignment_submissions.submission_id',
        'assignment_submissions.score',
        'assignment_submissions.graded_at',
        'assignments.title as item_title',
        'assignments.total_points',
        'users.first_name as student_first_name',
        'users.last_name as student_last_name',
        'students.student_number',
        'classes.section_name',
        'classes.class_code',
        'teacher_users.first_name as teacher_first_name',
        'teacher_users.last_name as teacher_last_name',
        DB::raw("'assignment' as grade_type")
    )
    ->get();

// Fetch quiz grades
$quizGrades = DB::table('quiz_attempts')
    ->join('quizzes', 'quiz_attempts.quiz_id', '=', 'quizzes.quiz_id')
    ->join('students', 'quiz_attempts.student_id', '=', 'students.student_id')
    ->join('users', 'students.user_id', '=', 'users.user_id')
    ->join('classes', 'quizzes.class_id', '=', 'classes.class_id')
    ->leftJoin('teachers', 'quizzes.teacher_id', '=', 'teachers.teacher_id')
    ->leftJoin('users as teacher_users', 'teachers.user_id', '=', 'teacher_users.user_id')
    ->whereIn('quiz_attempts.status', ['submitted', 'graded'])
    ->whereNotNull('quiz_attempts.score')
    ->select(
        'quiz_attempts.attempt_id as submission_id',
        'quiz_attempts.score',
        'quiz_attempts.end_time as graded_at',
        'quizzes.quiz_title as item_title',
        'quizzes.total_points',
        'users.first_name as student_first_name',
        'users.last_name as student_last_name',
        'students.student_number',
        'classes.section_name',
        'classes.class_code',
        'teacher_users.first_name as teacher_first_name',
        'teacher_users.last_name as teacher_last_name',
        DB::raw("'quiz' as grade_type")
    )
    ->get();

// Merge and sort all grades
$allGrades = $assignmentGrades->merge($quizGrades)
    ->sortByDesc('graded_at')
    ->values();

// Calculate statistics
$totalGradedItems = $allGrades->count();
$assignmentCount = $assignmentGrades->count();
$quizCount = $quizGrades->count();

$overallAverage = $allGrades->map(function($grade) {
    return ($grade->score / $grade->total_points) * 100;
})->avg() ?? 0;

$topPerformers = $allGrades->filter(function($grade) {
    return (($grade->score / $grade->total_points) * 100) >= 90;
})->unique('student_number')->count();

// Get grade distribution
$gradeDistribution = $allGrades->map(function($grade) {
    $percentage = ($grade->score / $grade->total_points) * 100;
    if ($percentage >= 90) return 'A';
    if ($percentage >= 80) return 'B';
    if ($percentage >= 70) return 'C';
    if ($percentage >= 60) return 'D';
    return 'F';
})->groupBy(function($item) { return $item; })->map(function($items) {
    return $items->count();
});
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
      <div class="row g-3 mb-4">
        <div class="col-md-3">
          <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center">
              <i class="bi bi-clipboard-check fs-1 text-primary mb-2"></i>
              <h4 class="fw-bold mb-1"><?php echo number_format($totalGradedItems); ?></h4>
              <p class="text-muted small mb-0">Total Graded Items</p>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center">
              <i class="bi bi-file-earmark-text fs-1 text-success mb-2"></i>
              <h4 class="fw-bold mb-1"><?php echo number_format($assignmentCount); ?></h4>
              <p class="text-muted small mb-0">Assignments Graded</p>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center">
              <i class="bi bi-question-circle fs-1 text-info mb-2"></i>
              <h4 class="fw-bold mb-1"><?php echo number_format($quizCount); ?></h4>
              <p class="text-muted small mb-0">Quizzes Completed</p>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center">
              <i class="bi bi-graph-up fs-1 text-warning mb-2"></i>
              <h4 class="fw-bold mb-1"><?php echo round($overallAverage, 1); ?>%</h4>
              <p class="text-muted small mb-0">Overall Average</p>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Grade Distribution -->
      <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
          <h5 class="fw-bold mb-3"><i class="bi bi-bar-chart me-2"></i>Grade Distribution</h5>
          <div class="row text-center">
            <div class="col">
              <div class="p-2">
                <h3 class="text-success mb-0"><?php echo $gradeDistribution['A'] ?? 0; ?></h3>
                <small class="text-muted">A (90-100%)</small>
              </div>
            </div>
            <div class="col">
              <div class="p-2">
                <h3 class="text-info mb-0"><?php echo $gradeDistribution['B'] ?? 0; ?></h3>
                <small class="text-muted">B (80-89%)</small>
              </div>
            </div>
            <div class="col">
              <div class="p-2">
                <h3 class="text-warning mb-0"><?php echo $gradeDistribution['C'] ?? 0; ?></h3>
                <small class="text-muted">C (70-79%)</small>
              </div>
            </div>
            <div class="col">
              <div class="p-2">
                <h3 class="text-orange mb-0"><?php echo $gradeDistribution['D'] ?? 0; ?></h3>
                <small class="text-muted">D (60-69%)</small>
              </div>
            </div>
            <div class="col">
              <div class="p-2">
                <h3 class="text-danger mb-0"><?php echo $gradeDistribution['F'] ?? 0; ?></h3>
                <small class="text-muted">F (Below 60%)</small>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Grades Table -->
      <h5 class="fw-bold mb-3"><i class="bi bi-table me-2"></i>All Grades (Assignments & Quizzes)</h5>
      <div class="table-responsive">
        <table id="gradesTable" class="table table-hover align-middle">
          <thead class="table-success">
            <tr>
              <th>Student</th>
              <th>Type</th>
              <th>Item</th>
              <th>Class</th>
              <th>Score</th>
              <th>Percentage</th>
              <th>Teacher</th>
              <th>Graded Date</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php if($allGrades->count() > 0): ?>
              <?php foreach($allGrades as $grade): 
                $studentName = $grade->student_first_name . ' ' . $grade->student_last_name;
                $teacherName = ($grade->teacher_first_name ?? 'N/A') . ' ' . ($grade->teacher_last_name ?? '');
                $percentage = ($grade->score / $grade->total_points) * 100;
                
                // Determine grade letter and badge color
                if ($percentage >= 90) {
                  $gradeLetter = 'A';
                  $badgeColor = 'bg-success';
                } elseif ($percentage >= 80) {
                  $gradeLetter = 'B';
                  $badgeColor = 'bg-info';
                } elseif ($percentage >= 70) {
                  $gradeLetter = 'C';
                  $badgeColor = 'bg-warning';
                } elseif ($percentage >= 60) {
                  $gradeLetter = 'D';
                  $badgeColor = 'bg-warning text-dark';
                } else {
                  $gradeLetter = 'F';
                  $badgeColor = 'bg-danger';
                }
                
                $typeIcon = $grade->grade_type === 'assignment' ? 'bi-file-earmark-text' : 'bi-question-circle';
                $typeBadge = $grade->grade_type === 'assignment' ? 'bg-primary' : 'bg-info';
              ?>
              <tr>
                <td>
                  <strong><?php echo htmlspecialchars($studentName); ?></strong><br>
                  <small class="text-muted"><?php echo htmlspecialchars($grade->student_number); ?></small>
                </td>
                <td>
                  <span class="badge <?php echo $typeBadge; ?>">
                    <i class="bi <?php echo $typeIcon; ?> me-1"></i><?php echo ucfirst($grade->grade_type); ?>
                  </span>
                </td>
                <td>
                  <strong><?php echo htmlspecialchars($grade->item_title); ?></strong>
                </td>
                <td>
                  <small><?php echo htmlspecialchars($grade->section_name); ?></small>
                </td>
                <td>
                  <strong><?php echo round($grade->score, 1); ?></strong> / <?php echo round($grade->total_points, 1); ?>
                </td>
                <td>
                  <span class="badge <?php echo $badgeColor; ?>">
                    <?php echo round($percentage, 1); ?>% (<?php echo $gradeLetter; ?>)
                  </span>
                </td>
                <td>
                  <small><?php echo htmlspecialchars(trim($teacherName)); ?></small>
                </td>
                <td>
                  <small><?php echo date('M d, Y', strtotime($grade->graded_at)); ?></small>
                </td>
                <td>
                  <button class="btn btn-sm btn-outline-info" title="View Details"
                    onclick="viewRecord(<?php echo $grade->submission_id; ?>, '<?php echo ucfirst($grade->grade_type); ?> Grade', {
                      'Student': '<?php echo addslashes($studentName); ?>',
                      'Student Number': '<?php echo addslashes($grade->student_number); ?>',
                      'Type': '<?php echo ucfirst($grade->grade_type); ?>',
                      'Item': '<?php echo addslashes($grade->item_title); ?>',
                      'Class': '<?php echo addslashes($grade->section_name); ?>',
                      'Score': '<?php echo round($grade->score, 1); ?> / <?php echo round($grade->total_points, 1); ?>',
                      'Percentage': '<?php echo round($percentage, 1); ?>%',
                      'Grade': '<?php echo $gradeLetter; ?>',
                      'Teacher': '<?php echo addslashes(trim($teacherName)); ?>',
                      'Graded Date': '<?php echo date('M d, Y g:i A', strtotime($grade->graded_at)); ?>'
                    })">
                    <i class="bi bi-eye"></i>
                  </button>
                </td>
              </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="9" class="text-center text-muted py-4">
                  <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                  No grades available yet
                </td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>

      <!-- Filter Options -->
      <div class="card border-0 shadow-sm mt-4">
        <div class="card-body">
          <h6 class="fw-bold mb-3"><i class="bi bi-funnel me-2"></i>Filter Grades</h6>
          <div class="row g-3">
            <div class="col-md-4">
              <label class="form-label small">Filter by Type</label>
              <select class="form-select" id="filterType" onchange="filterGrades()">
                <option value="">All Types</option>
                <option value="assignment">Assignments Only</option>
                <option value="quiz">Quizzes Only</option>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label small">Filter by Grade</label>
              <select class="form-select" id="filterGrade" onchange="filterGrades()">
                <option value="">All Grades</option>
                <option value="A">A (90-100%)</option>
                <option value="B">B (80-89%)</option>
                <option value="C">C (70-79%)</option>
                <option value="D">D (60-69%)</option>
                <option value="F">F (Below 60%)</option>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label small">Search Student</label>
              <input type="text" class="form-control" id="searchStudent" placeholder="Enter student name..." onkeyup="filterGrades()">
            </div>
          </div>
        </div>
      </div>

      <!-- Insights -->
      <div class="card border-0 shadow-sm mt-4">
        <div class="card-body">
          <h6 class="fw-bold mb-3"><i class="bi bi-lightbulb me-2"></i>Analytics Insights</h6>
          <div class="row">
            <div class="col-md-4">
              <div class="d-flex align-items-center mb-2">
                <i class="bi bi-check-circle-fill text-success fs-4 me-2"></i>
                <small>Overall average is <strong><?php echo round($overallAverage, 1); ?>%</strong></small>
              </div>
            </div>
            <div class="col-md-4">
              <div class="d-flex align-items-center mb-2">
                <i class="bi bi-trophy-fill text-warning fs-4 me-2"></i>
                <small><strong><?php echo $topPerformers; ?></strong> student(s) achieved 90% or higher</small>
              </div>
            </div>
            <div class="col-md-4">
              <div class="d-flex align-items-center mb-2">
                <i class="bi bi-clipboard-data text-info fs-4 me-2"></i>
                <small><strong><?php echo $totalGradedItems; ?></strong> items graded across all classes</small>
              </div>
            </div>
          </div>
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
  // Filter grades function
  function filterGrades() {
    const typeFilter = document.getElementById('filterType').value.toLowerCase();
    const gradeFilter = document.getElementById('filterGrade').value;
    const searchText = document.getElementById('searchStudent').value.toLowerCase();
    
    const table = document.getElementById('gradesTable');
    const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
    
    for (let i = 0; i < rows.length; i++) {
      const row = rows[i];
      const cells = row.getElementsByTagName('td');
      
      if (cells.length === 0) continue;
      
      const studentName = cells[0].textContent.toLowerCase();
      const typeBadge = cells[1].textContent.toLowerCase();
      const percentageBadge = cells[5].textContent;
      
      // Check filters
      let showRow = true;
      
      // Type filter
      if (typeFilter && !typeBadge.includes(typeFilter)) {
        showRow = false;
      }
      
      // Grade filter
      if (gradeFilter && !percentageBadge.includes('(' + gradeFilter + ')')) {
        showRow = false;
      }
      
      // Search filter
      if (searchText && !studentName.includes(searchText)) {
        showRow = false;
      }
      
      row.style.display = showRow ? '' : 'none';
    }
  }
</script>

</html>