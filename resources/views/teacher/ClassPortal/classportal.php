<?php
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

$user = Auth::user();
$teacher_id = $user ? $user->user_id : null;

if ($teacher_id) {
    // Fetch classes taught by this teacher with student counts
    $classes = DB::table('classes')
        ->leftJoin('subjects', 'classes.subject_id', '=', 'subjects.subject_id')
        ->leftJoin('class_enrollments', 'classes.class_id', '=', 'class_enrollments.class_id')
        ->where('classes.teacher_id', $teacher_id)
        ->select(
            'classes.*',
            'subjects.subject_name',
            DB::raw('COUNT(DISTINCT class_enrollments.enrollment_id) as student_count')
        )
        ->groupBy('classes.class_id')
        ->get();
} else {
    $classes = collect();
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
  
  <!-- Teacher Actions Library -->
  <script src="<?php echo asset('teacher-actions.js'); ?>"></script>
  
  <style>
    @import url("../style.css");

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
            <i class="bi bi-journal-bookmark-fill text-primary fs-1 me-3"></i>
            <h3 class="mb-0 fw-bold">Professor Class Portal</h3>
          </div>
          <button class="btn btn-primary" onclick="quickCreateQuiz()">
            <i class="bi bi-plus-circle me-2"></i> Create New Class
          </button>
        </div>

        <!-- Action Cards -->
        <div class="row g-4">
          <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100 text-center">
              <div class="card-body">
                <i class="bi bi-upload text-info fs-1 mb-2"></i>
                <h6 class="fw-bold">Upload Materials</h6>
                <p class="text-muted small">Upload lecture files, slides, and videos.</p>
                <a href="<?php echo route('teacher.lesson-materials'); ?>" class="btn btn-outline-info btn-sm">Go</a>
              </div>
            </div>
          </div>

          <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100 text-center">
              <div class="card-body">
                <i class="bi bi-pencil-square text-success fs-1 mb-2"></i>
                <h6 class="fw-bold">Create Quizzes</h6>
                <p class="text-muted small">Add quizzes and assignments for students.</p>
                <button onclick="quickCreateQuiz()" class="btn btn-outline-success btn-sm">Go</button>
              </div>
            </div>
          </div>

          <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100 text-center">
              <div class="card-body">
                <i class="bi bi-people-fill text-warning fs-1 mb-2"></i>
                <h6 class="fw-bold">Manage Students</h6>
                <p class="text-muted small">View enrolled students and track progress.</p>
                <a href="<?php echo route('teacher.analytics'); ?>" class="btn btn-outline-warning btn-sm">Go</a>
              </div>
            </div>
          </div>

          <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100 text-center">
              <div class="card-body">
                <i class="bi bi-clipboard-check-fill text-danger fs-1 mb-2"></i>
                <h6 class="fw-bold">Grading</h6>
                <p class="text-muted small">Review, grade, and release results.</p>
                <a href="<?php echo route('teacher.grading'); ?>" class="btn btn-outline-danger btn-sm">Go</a>
              </div>
            </div>
          </div>
        </div>

        <!-- My Classes Section -->
        <div class="mt-5">
          <h5 class="fw-bold mb-3">My Classes (<?php echo $classes->count(); ?>)</h5>
          <div class="row g-3">
            <?php if($classes->count() > 0): ?>
              <?php foreach($classes as $class): ?>
              <div class="col-md-6">
                <div class="card border-0 shadow-sm">
                  <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                      <div>
                        <h6 class="fw-bold text-primary"><?php echo htmlspecialchars($class->section_name); ?></h6>
                        <p class="text-muted mb-1"><?php echo htmlspecialchars($class->subject_name ?? $class->class_code); ?></p>
                        <small class="text-muted">
                          <i class="bi bi-calendar3 me-1"></i><?php echo htmlspecialchars($class->schedule_days); ?>
                          <?php echo date('g:i A', strtotime($class->schedule_time_start)); ?>
                        </small>
                        <br>
                        <small class="text-muted">
                          <i class="bi bi-geo-alt me-1"></i><?php echo htmlspecialchars($class->room); ?>
                        </small>
                      </div>
                      <div class="text-end">
                        <span class="badge bg-info"><?php echo $class->student_count; ?> Students</span>
                      </div>
                    </div>
                    <div class="mt-3 d-flex gap-2">
                      <button class="btn btn-sm btn-outline-primary" onclick="viewClassDetails(<?php echo $class->class_id; ?>, '<?php echo addslashes($class->section_name); ?>')">
                        <i class="bi bi-eye me-1"></i>View
                      </button>
                      <button class="btn btn-sm btn-outline-warning" onclick="editRecord(<?php echo $class->class_id; ?>, 'Class', {name: '<?php echo addslashes($class->section_name); ?>', code: '<?php echo addslashes($class->class_code); ?>'})">
                        <i class="bi bi-pencil me-1"></i>Edit
                      </button>
                    </div>
                  </div>
                </div>
              </div>
              <?php endforeach; ?>
            <?php else: ?>
              <div class="col-12">
                <div class="alert alert-info">
                  <i class="bi bi-info-circle me-2"></i>No classes assigned yet. Click "Create New Class" to get started.
                </div>
              </div>
            <?php endif; ?>
          </div>
        </div>

      </div>
    </div>
  </div>
</div>

            </div> <!-- card-body -->
          </div> <!-- card -->
        </div> <!-- container my-5 -->
      </div> <!-- col -->
    </div> <!-- row -->
  </div> <!-- container -->
</div> <!-- main-content -->


  <footer class="mt-auto bg-light">
    <div class="container text-center">
      <p class="text-muted">© 2023 Your Company. All rights reserved.</p>
    </div>
  </footer>


  </style>
</body>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>

</html>