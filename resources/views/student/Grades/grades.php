<?php
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

$user = Auth::user();
$user_id = $user ? $user->user_id : null;

// Get the actual student_id from students table
$student = null;
if ($user_id) {
    $student = DB::table('students')->where('user_id', $user_id)->first();
}

if ($student) {
    $student_id = $student->student_id;
    
    // Fetch assignment grades
    $assignmentGrades = DB::table('assignment_submissions')
        ->join('assignments', 'assignment_submissions.assignment_id', '=', 'assignments.assignment_id')
        ->join('classes', 'assignments.class_id', '=', 'classes.class_id')
        ->where('assignment_submissions.student_id', $student_id)
        ->whereNotNull('assignment_submissions.score')
        ->select(
            'classes.class_id',
            'classes.section_name',
            'classes.class_code',
            'assignments.title as item_name',
            'assignment_submissions.score',
            'assignments.total_points as max_score',
            DB::raw('(assignment_submissions.score / assignments.total_points * 100) as percentage'),
            'assignment_submissions.graded_at',
            DB::raw('"Assignment" as type')
        )
        ->get();
    
    // Fetch quiz grades (show all submitted or graded quizzes)
    $quizGrades = DB::table('quiz_attempts')
        ->join('quizzes', 'quiz_attempts.quiz_id', '=', 'quizzes.quiz_id')
        ->join('classes', 'quizzes.class_id', '=', 'classes.class_id')
        ->where('quiz_attempts.student_id', $student_id)
        ->whereIn('quiz_attempts.status', ['submitted', 'graded'])
        ->whereNotNull('quiz_attempts.score')
        ->select(
            'classes.class_id',
            'classes.section_name',
            'classes.class_code',
            'quizzes.quiz_title as item_name',
            'quiz_attempts.score',
            'quizzes.total_points as max_score',
            DB::raw('(quiz_attempts.score / quizzes.total_points * 100) as percentage'),
            'quiz_attempts.end_time as graded_at',
            DB::raw('"Quiz" as type')
        )
        ->get();
    
    // Fetch other grades from grades table
    $otherGrades = DB::table('grades')
        ->join('classes', 'grades.class_id', '=', 'classes.class_id')
        ->leftJoin('grade_components', 'grades.component_id', '=', 'grade_components.component_id')
        ->where('grades.student_id', $student_id)
        ->select(
            'classes.class_id',
            'classes.section_name',
            'classes.class_code',
            'grade_components.component_name as item_name',
            'grades.score',
            'grades.max_score',
            'grades.percentage',
            'grades.graded_at',
            DB::raw('"Other" as type')
        )
        ->get();
    
    // Merge all grades
    $allGrades = $assignmentGrades->concat($quizGrades)->concat($otherGrades)
        ->sortByDesc('graded_at');
    
    // Calculate overall average
    $overallAvg = $allGrades->where('percentage', '>', 0)->avg('percentage');
    
    // Calculate totals by type
    $assignmentCount = $assignmentGrades->count();
    $quizCount = $quizGrades->count();
    $assignmentAvg = $assignmentGrades->avg('percentage') ?? 0;
    $quizAvg = $quizGrades->avg('percentage') ?? 0;
} else {
    $allGrades = collect();
    $overallAvg = 0;
    $assignmentCount = 0;
    $quizCount = 0;
    $assignmentAvg = 0;
    $quizAvg = 0;
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
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">

  <style>
    @import url("../../style.css");

    /* Make sidebar scrollable in landscape mode on mobile devices */
    @media (max-width: 768px) and (orientation: landscape) {
      .sidebar {
        max-height: 100vh;
        overflow-y: auto;
      }
    }

     /* 1.  CUSTOM COLOR PALETTE & STYLING
        -   Professional Teal & Amber Theme
        -   Enhanced fonts and shadows for a modern look
    */
    :root {
     /* Colors from the provided image */
      --secondary-50: #D2E2F9;
      --secondary-100: #77A9EE;
      --secondary-200: #1C6FE3;
      --secondary-300: #1450A3; /* Base color for primary elements */
      --secondary-400: #114388;

      /* Colors from the provided image */
      --secondary-50: #D2E2F9;
      --secondary-100: #77A9EE;
      --secondary-200: #1C6FE3;
      --secondary-300: #1450A3;
      --secondary-400: #114388;

      /* Mapping to theme variables */
      --theme-primary: var(--secondary-300);
      --theme-success: #46d693ff;
      --theme-danger: #dc3545;
      --theme-warning: #eccc6aff;
      --theme-pending: #6c757d;

      --bg-main: #f8f9fa;
      --card-bg: #ffffff;
      --text-dark: #212529;
      --text-muted: #6c757d;
      --card-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.07);
      --card-border-radius: 0.75rem;
      --font-family-sans-serif: 'Poppins', sans-serif;
    }

    body {
      background-color: var(--bg-main);
      font-family: var(--font-family-sans-serif);
    }
    
    .main-content {
        padding: 2rem;
    }

    .card {
      border: none;
      border-radius: var(--card-border-radius);
      box-shadow: var(--card-shadow);
    }
    
    .card-header.theme-bg {
        background-color: var(--theme-primary);
        color: white;
        border-top-left-radius: var(--card-border-radius);
        border-top-right-radius: var(--card-border-radius);
    }

    /* Page Header */
    .page-header h2 {
      color: var(--theme-primary);
      font-weight: 700;
    }

    /* Table Styling */
    .table thead th {
        background-color: var(--bg-main);
        color: var(--theme-primary);
        font-weight: 600;
        border: none;
        letter-spacing: 0.5px;
        text-transform: uppercase;
        font-size: 0.85rem;
    }
    .table tbody td {
        vertical-align: middle;
    }
    .table-hover tbody tr:hover {
        background-color: var(--secondary-50);
        color: var(--theme-primary);
    }
    .score {
        font-weight: 600;
        font-size: 1.05rem;
    }
    .text-not-submitted {
        color: var(--theme-danger);
        font-style: italic;
    }

    /* Status Badges */
    .badge.status-graded {
        background-color: var(--theme-success);
    }
    .badge.status-pending {
        background-color: var(--theme-warning);
        color: #000;
    }
    .badge.status-missing {
        background-color: var(--theme-danger);
    }
     .sidebar {
      background-color: #084d92ff;
      color: #fff;
    }
    .sidebar .nav-link {
      color: #fff;
      transition: background-color 0.2s ease;
    }
    .sidebar .nav-link:hover,
    .sidebar .nav-link.active {
      background-color: #ffffffff;
    }

    
    /* Responsive Sidebar Handling (from original code) */
     @media (max-width: 992px) {
        body {
            padding-left: 0;
        }
    }


  </style>
</head>

<body>

  <!-- navbar -->

  <?php include resource_path('views/includes/sidenav_student.php'); ?>
  <div class="main-content flex-grow-1">
    <div class="container">
      <div class="row">
        <div class="col">
          <div class="container my-5">
            <div class="card shadow-lg border-0 rounded-4">
              <div class="card-body p-4">
 
        
      

     <div class="main-content">
    <div class="container-fluid">
      
      <div class="page-header mb-4 d-flex justify-content-between align-items-center">
          <div>
            <h2 class="display-6"><i class="bi bi-bar-chart-fill me-3"></i>My Grades</h2>
            <p class="text-muted mb-0">Overall Average: <strong class="text-primary"><?php echo round($overallAvg, 2); ?>%</strong></p>
          </div>
          <a href="<?php echo route('student.performance'); ?>" class="btn btn-outline-primary">
            <i class="bi bi-arrow-left me-2"></i>Back to Performance
          </a>
      </div>

      <!-- Statistics Cards -->
      <div class="row g-3 mb-4">
        <div class="col-md-4">
          <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
              <i class="bi bi-clipboard-check text-primary" style="font-size: 2.5rem;"></i>
              <h3 class="mt-2 mb-0 fw-bold"><?php echo $assignmentCount; ?></h3>
              <p class="text-muted small mb-0">Graded Assignments</p>
              <p class="mb-0 fw-bold text-success"><?php echo round($assignmentAvg, 1); ?>% Avg</p>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
              <i class="bi bi-question-circle text-info" style="font-size: 2.5rem;"></i>
              <h3 class="mt-2 mb-0 fw-bold"><?php echo $quizCount; ?></h3>
              <p class="text-muted small mb-0">Completed Quizzes</p>
              <p class="mb-0 fw-bold text-success"><?php echo round($quizAvg, 1); ?>% Avg</p>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
              <i class="bi bi-trophy text-warning" style="font-size: 2.5rem;"></i>
              <h3 class="mt-2 mb-0 fw-bold"><?php echo round($overallAvg, 1); ?>%</h3>
              <p class="text-muted small mb-0">Overall Average</p>
              <p class="mb-0 fw-bold <?php echo $overallAvg >= 75 ? 'text-success' : 'text-warning'; ?>">
                <?php echo $overallAvg >= 90 ? 'Excellent' : ($overallAvg >= 75 ? 'Good' : 'Needs Improvement'); ?>
              </p>
            </div>
          </div>
        </div>
      </div>

      <?php if($allGrades->count() > 0): ?>
        <?php 
        // Group grades by class
        $gradesByClass = $allGrades->groupBy('class_id');
        foreach($gradesByClass as $classId => $classGrades): 
            $firstGrade = $classGrades->first();
            $classAvg = $classGrades->avg('percentage');
        ?>
        <div class="card mb-4">
            <div class="card-header theme-bg fw-bold d-flex justify-content-between align-items-center">
                <span><i class="bi bi-book me-2"></i> <?php echo htmlspecialchars($firstGrade->section_name); ?></span>
                <span class="badge bg-light text-dark">Average: <?php echo round($classAvg, 1); ?>%</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="text-center">
                            <tr>
                                <th class="text-start ps-3">Type</th>
                                <th class="text-start">Item Name</th>
                                <th>Score</th>
                                <th>Max Score</th>
                                <th>Percentage</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody class="text-center">
                            <?php foreach($classGrades as $grade): 
                                $typeColor = match($grade->type) {
                                    'Assignment' => 'primary',
                                    'Quiz' => 'info',
                                    default => 'secondary'
                                };
                                $statusClass = $grade->percentage >= 90 ? 'success' : ($grade->percentage >= 75 ? 'warning' : 'danger');
                                $statusText = $grade->percentage >= 90 ? 'Excellent' : ($grade->percentage >= 75 ? 'Good' : 'Needs Work');
                            ?>
                            <tr>
                                <td class="text-start ps-3">
                                    <span class="badge bg-<?php echo $typeColor; ?>">
                                        <?php echo htmlspecialchars($grade->type); ?>
                                    </span>
                                </td>
                                <td class="text-start fw-bold"><?php echo htmlspecialchars($grade->item_name ?? 'N/A'); ?></td>
                                <td class="score <?php echo $grade->percentage >= 75 ? 'text-success' : 'text-warning'; ?>">
                                    <?php echo $grade->score; ?>
                                </td>
                                <td class="score"><?php echo $grade->max_score; ?></td>
                                <td class="score <?php echo $grade->percentage >= 75 ? 'text-success' : 'text-warning'; ?>">
                                    <strong><?php echo round($grade->percentage, 1); ?>%</strong>
                                </td>
                                <td>
                                    <span class="badge rounded-pill bg-<?php echo $statusClass; ?> px-3">
                                        <?php echo $statusText; ?>
                                    </span>
                                </td>
                                <td class="text-muted small">
                                    <?php echo $grade->graded_at ? date('M d, Y', strtotime($grade->graded_at)) : 'N/A'; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="alert alert-info text-center py-5">
            <i class="bi bi-inbox fs-1 d-block mb-3"></i>
            <h5>No Grades Available Yet</h5>
            <p class="mb-0">Your grades will appear here once assignments and quizzes are graded.</p>
        </div>
      <?php endif; ?>
      
    </div>
  </div>
  </div>
  </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>