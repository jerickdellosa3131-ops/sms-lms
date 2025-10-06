<?php
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

$user = Auth::user();
$teacher_id = $user ? $user->user_id : null;

if ($teacher_id) {
    // Fetch lesson materials uploaded by this teacher
    $materials = DB::table('lesson_materials')
        ->leftJoin('modules', 'lesson_materials.module_id', '=', 'modules.module_id')
        ->leftJoin('classes', 'modules.class_id', '=', 'classes.class_id')
        ->where('lesson_materials.teacher_id', $teacher_id)
        ->select(
            'lesson_materials.*',
            'modules.module_title',
            'classes.section_name',
            'classes.class_code'
        )
        ->orderBy('lesson_materials.created_at', 'desc')
        ->get();
} else {
    $materials = collect();
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
            <i class="bi bi-upload text-primary fs-1 me-3"></i>
            <h3 class="mb-0 fw-bold">Upload Lesson Materials</h3>
          </div>
          <a href="#" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i> Back to Class Portal
          </a>
        </div>

        <!-- Upload Form -->
        <form action="#" method="post" enctype="multipart/form-data">
          <div class="row g-3">
            
            <!-- Select Class -->
            <div class="col-md-6">
              <label for="classSelect" class="form-label fw-bold">Select Class</label>
              <select class="form-select" id="classSelect" required>
                <option value="" selected disabled>-- Choose Class --</option>
                <option value="math101">Math 101</option>
                <option value="sci202">Science 202</option>
                <option value="eng303">English 303</option>
              </select>
            </div>

            <!-- Lesson Title -->
            <div class="col-md-6">
              <label for="lessonTitle" class="form-label fw-bold">Lesson Title</label>
              <input type="text" class="form-control" id="lessonTitle" placeholder="Enter lesson title" required>
            </div>

            <!-- Description -->
            <div class="col-12">
              <label for="lessonDesc" class="form-label fw-bold">Description</label>
              <textarea class="form-control" id="lessonDesc" rows="3" placeholder="Short description of the lesson"></textarea>
            </div>

            <!-- Upload File -->
            <div class="col-md-6">
              <label for="uploadFile" class="form-label fw-bold">Upload File</label>
              <input class="form-control" type="file" id="uploadFile" multiple required>
              <small class="text-muted">Accepted: PDF, PPT, DOCX, MP4</small>
            </div>

            <!-- Tags / Category -->
            <div class="col-md-6">
              <label for="materialType" class="form-label fw-bold">Material Type</label>
              <select class="form-select" id="materialType" required>
                <option value="" selected disabled>-- Choose Type --</option>
                <option value="lecture">Lecture Notes</option>
                <option value="slides">Slides</option>
                <option value="video">Video</option>
                <option value="other">Other</option>
              </select>
            </div>

          </div>

          <!-- Submit -->
          <div class="mt-4 text-end">
            <button type="reset" class="btn btn-outline-secondary me-2">
              <i class="bi bi-x-circle me-1"></i> Clear
            </button>
            <button type="submit" class="btn btn-primary">
              <i class="bi bi-cloud-arrow-up me-1"></i> Upload Material
            </button>
          </div>
        </form>

        <!-- Uploaded Materials List -->
        <div class="mt-5">
          <h5 class="fw-bold mb-3">Recently Uploaded Materials (<?php echo $materials->count(); ?>)</h5>
          <div class="table-responsive">
            <table class="table table-hover align-middle">
              <thead class="table-primary">
                <tr>
                  <th>Material Title</th>
                  <th>Module/Class</th>
                  <th>Type</th>
                  <th>File Size</th>
                  <th>Date Uploaded</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php if($materials->count() > 0): ?>
                  <?php foreach($materials as $material): ?>
                  <tr>
                    <td>
                      <strong><?php echo htmlspecialchars($material->material_title); ?></strong>
                      <?php if($material->material_description): ?>
                        <br><small class="text-muted"><?php echo htmlspecialchars(substr($material->material_description, 0, 40)); ?>...</small>
                      <?php endif; ?>
                    </td>
                    <td>
                      <?php echo htmlspecialchars($material->module_title ?? 'N/A'); ?>
                      <?php if($material->section_name): ?>
                        <br><small class="text-muted"><?php echo htmlspecialchars($material->section_name); ?></small>
                      <?php endif; ?>
                    </td>
                    <td><span class="badge bg-info"><?php echo strtoupper($material->material_type); ?></span></td>
                    <td><?php echo $material->file_size ? round($material->file_size / 1024, 2) . ' MB' : 'N/A'; ?></td>
                    <td><?php echo date('M d, Y', strtotime($material->created_at)); ?></td>
                    <td>
                      <button class="btn btn-sm btn-outline-success" onclick="viewRecord(<?php echo $material->material_id; ?>, 'Material', {title: '<?php echo addslashes($material->material_title); ?>', type: '<?php echo $material->material_type; ?>', file: '<?php echo addslashes($material->file_path ?? $material->external_link); ?>'})">
                        <i class="bi bi-eye"></i>
                      </button>
                      <button class="btn btn-sm btn-outline-warning" onclick="editRecord(<?php echo $material->material_id; ?>, 'Material', {title: '<?php echo addslashes($material->material_title); ?>', description: '<?php echo addslashes($material->material_description ?? ''); ?>'})">
                        <i class="bi bi-pencil"></i>
                      </button>
                      <button class="btn btn-sm btn-outline-danger" onclick="deleteRecord(<?php echo $material->material_id; ?>, 'Material', '<?php echo addslashes($material->material_title); ?>')">
                        <i class="bi bi-trash"></i>
                      </button>
                    </td>
                  </tr>
                  <?php endforeach; ?>
                <?php else: ?>
                  <tr>
                    <td colspan="6" class="text-center text-muted py-4">
                      <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                      No lesson materials uploaded yet. Upload your first material above!
                    </td>
                  </tr>
                <?php endif; ?>
              </tbody>
            </table>
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

</html>