<?php
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

$user = Auth::user();
$user_id = $user ? $user->user_id : null;

// Get class_id from request
$class_id = request()->route('class_id');

// Get student info
$student = null;
if ($user_id) {
    $student = DB::table('students')->where('user_id', $user_id)->first();
}

// Fetch class details
$class = DB::table('classes')
    ->join('teachers', 'classes.teacher_id', '=', 'teachers.teacher_id')
    ->join('users as teacher_users', 'teachers.user_id', '=', 'teacher_users.user_id')
    ->leftJoin('subjects', 'classes.subject_id', '=', 'subjects.subject_id')
    ->where('classes.class_id', $class_id)
    ->select(
        'classes.*',
        'teacher_users.first_name as teacher_first',
        'teacher_users.last_name as teacher_last',
        'subjects.subject_name'
    )
    ->first();

// Fetch lesson materials for this class
$lessonMaterials = DB::table('lesson_materials')
    ->join('modules', 'lesson_materials.module_id', '=', 'modules.module_id')
    ->where('modules.class_id', $class_id)
    ->orderBy('lesson_materials.created_at', 'desc')
    ->select('lesson_materials.*')
    ->get();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="<?php echo csrf_token(); ?>">
  <title>SMS3 - Lesson Materials</title>

  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="<?php echo asset('style.css'); ?>">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
  
  <style>
    @import url("../../style.css");

    /* Make sidebar scrollable in landscape mode on mobile devices */
    @media (max-width: 768px) and (orientation: landscape) {
      .sidebar {
        max-height: 100vh;
        overflow-y: auto;
      }
    }
    
    .material-card {
      transition: transform 0.3s ease, box-shadow 0.3s ease;
      cursor: pointer;
      border-left: 4px solid #007bff;
    }
    
    .material-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    }
    
    .file-icon {
      font-size: 3rem;
      color: #007bff;
    }
  </style>
</head>

<body>
  <?php include resource_path('views/includes/sidenav_student.php'); ?>
  <div class="main-content flex-grow-1">
    <div class="container">
      <div class="row">
        <div class="col">
          <div class="container my-5">
            <div class="card shadow-lg border-0 rounded-4">
              <div class="card-body p-4">

                <!-- Header -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                  <div>
                    <h2 class="fw-bold mb-0">
                      <i class="bi bi-book-fill text-primary me-2"></i>
                      <?php echo htmlspecialchars($class->section_name ?? 'Class Materials'); ?>
                    </h2>
                    <p class="text-muted mb-0">
                      <?php echo htmlspecialchars($class->subject_name ?? $class->class_code); ?> - 
                      <?php echo htmlspecialchars($class->teacher_first . ' ' . $class->teacher_last); ?>
                    </p>
                  </div>
                  <a href="<?php echo route('student.class-portal'); ?>" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-2"></i> Back to Classes
                  </a>
                </div>

                <!-- Lesson Materials -->
                <?php if($lessonMaterials->count() > 0): ?>
                  <div class="row g-4">
                    <?php foreach($lessonMaterials as $material): ?>
                    <div class="col-md-6 col-lg-4">
                      <div class="card material-card h-100">
                        <div class="card-body">
                          <div class="text-center mb-3">
                            <i class="bi bi-<?php 
                              echo match($material->material_type) {
                                'pdf' => 'file-pdf',
                                'doc' => 'file-word',
                                'video' => 'camera-video',
                                'link' => 'link-45deg',
                                'image' => 'image',
                                default => 'file-earmark'
                              }; 
                            ?>-fill file-icon"></i>
                          </div>
                          
                          <h5 class="card-title fw-bold"><?php echo htmlspecialchars($material->material_title); ?></h5>
                          
                          <?php if($material->material_description): ?>
                          <p class="card-text text-muted small">
                            <?php echo htmlspecialchars(substr($material->material_description, 0, 100)); ?>...
                          </p>
                          <?php endif; ?>
                          
                          <div class="mb-3">
                            <span class="badge bg-primary"><?php echo strtoupper($material->material_type); ?></span>
                            <small class="text-muted d-block mt-2">
                              <i class="bi bi-calendar3"></i> <?php echo date('M d, Y', strtotime($material->created_at)); ?>
                            </small>
                          </div>
                          
                          <div class="d-grid gap-2">
                            <?php if($material->material_type === 'link' && $material->external_link): ?>
                              <a href="<?php echo htmlspecialchars($material->external_link); ?>" target="_blank" class="btn btn-primary">
                                <i class="bi bi-box-arrow-up-right me-2"></i> Open Link
                              </a>
                            <?php elseif($material->file_path): ?>
                              <a href="<?php echo asset('storage/' . $material->file_path); ?>" target="_blank" class="btn btn-success">
                                <i class="bi bi-download me-2"></i> Download
                              </a>
                              <a href="<?php echo asset('storage/' . $material->file_path); ?>" target="_blank" class="btn btn-outline-primary">
                                <i class="bi bi-eye me-2"></i> View
                              </a>
                            <?php else: ?>
                              <span class="text-muted text-center">No file available</span>
                            <?php endif; ?>
                          </div>
                        </div>
                      </div>
                    </div>
                    <?php endforeach; ?>
                  </div>
                <?php else: ?>
                  <div class="alert alert-info text-center py-5">
                    <i class="bi bi-inbox display-1 d-block mb-3"></i>
                    <h4>No Lesson Materials Yet</h4>
                    <p class="mb-0">Your teacher hasn't uploaded any materials for this class yet.</p>
                  </div>
                <?php endif; ?>

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
