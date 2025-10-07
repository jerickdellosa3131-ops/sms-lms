<?php
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

// Get authenticated user
$user = Auth::user();

// Fetch lesson materials with teacher information
$materials = DB::table('lesson_materials')
    ->leftJoin('teachers', 'lesson_materials.teacher_id', '=', 'teachers.teacher_id')
    ->leftJoin('users', 'teachers.user_id', '=', 'users.user_id')
    ->select(
        'lesson_materials.*',
        'users.first_name as teacher_first',
        'users.last_name as teacher_last'
    )
    ->orderBy('lesson_materials.created_at', 'desc')
    ->get();

// Calculate statistics
$totalMaterials = $materials->count();
$totalPDF = $materials->filter(function($m) {
    return strtolower(pathinfo($m->file_path, PATHINFO_EXTENSION)) == 'pdf';
})->count();
$totalVideo = $materials->filter(function($m) {
    $ext = strtolower(pathinfo($m->file_path, PATHINFO_EXTENSION));
    return in_array($ext, ['mp4', 'avi', 'mov', 'wmv']);
})->count();
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
  
  <!-- SheetJS for Excel Export -->
  <script src="https://cdn.sheetjs.com/xlsx-0.20.0/package/dist/xlsx.full.min.js"></script>
  
  <!-- SweetAlert2 for Alerts -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  
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

  <?php include resource_path('views/includes/sidenav_admin.php'); ?>

  <div class="main-content flex-grow-1">
    <div class="container my-5">
      <div class="card shadow-lg border-0 rounded-4">
        <div class="card-body p-4">

          <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="d-flex align-items-center">
              <i class="bi bi-upload text-info fs-1 me-3"></i>
              <h3 class="mb-0 fw-bold">Upload Materials</h3>
            </div>
            <div class="btn-group">
              <button class="btn btn-info text-white" data-bs-toggle="modal" data-bs-target="#uploadMaterialModal">
                <i class="bi bi-plus-lg me-2"></i> New Upload
              </button>
              <button class="btn btn-success text-white" onclick="exportCurrentTable('materialsTable', 'lesson_materials')">
                <i class="bi bi-file-earmark-excel me-2"></i> Export
              </button>
            </div>
          </div>

          <form hidden>
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label fw-bold">Material Title</label>
                <input type="text" class="form-control" placeholder="Enter title">
              </div>
              <div class="col-md-6">
                <label class="form-label fw-bold">Class / Subject</label>
                <select class="form-select">
                  <option selected>Select class</option>
                  <option>Class 1 - Math</option>
                  <option>Class 2 - Science</option>
                  <option>Class 3 - English</option>
                </select>
              </div>
              <div class="col-12">
                <label class="form-label fw-bold">Description</label>
                <textarea class="form-control" rows="3" placeholder="Brief description of the material"></textarea>
              </div>
              <div class="col-12">
                <label class="form-label fw-bold">Upload File</label>
                <input type="file" class="form-control">
                <small class="text-muted">Accepted formats: PDF, DOCX, PPTX, MP4, JPG, PNG</small>
              </div>
              <div class="col-12 text-end">
                <button type="submit" class="btn btn-success mt-3">
                  <i class="bi bi-cloud-upload me-2"></i> Upload Material
                </button>
              </div>
            </div>
          </form>

          <hr class="my-4">
          <h5 class="fw-bold mb-3">Uploaded Materials</h5>
          <div class="table-responsive">
            <table id="materialsTable" class="table table-hover align-middle">
              <thead class="table-info">
                <tr>
                  <th>Title</th>
                  <th>Class</th>
                  <th>Type</th>
                  <th>Date Uploaded</th>
                  <th class="text-center">Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php if($materials->count() > 0): ?>
                  <?php foreach($materials as $material): 
                    $extension = strtolower(pathinfo($material->file_path, PATHINFO_EXTENSION));
                    $fileIcon = 'file-earmark';
                    $fileColor = 'secondary';
                    
                    if($extension == 'pdf') {
                      $fileIcon = 'file-pdf';
                      $fileColor = 'danger';
                    } elseif(in_array($extension, ['doc', 'docx'])) {
                      $fileIcon = 'file-word';
                      $fileColor = 'primary';
                    } elseif(in_array($extension, ['xls', 'xlsx'])) {
                      $fileIcon = 'file-excel';
                      $fileColor = 'success';
                    } elseif(in_array($extension, ['ppt', 'pptx'])) {
                      $fileIcon = 'file-ppt';
                      $fileColor = 'warning';
                    } elseif(in_array($extension, ['mp4', 'avi', 'mov', 'wmv'])) {
                      $fileIcon = 'camera-video';
                      $fileColor = 'info';
                    } elseif(in_array($extension, ['jpg', 'jpeg', 'png', 'gif'])) {
                      $fileIcon = 'image';
                      $fileColor = 'success';
                    }
                  ?>
                  <tr>
                    <td>
                      <strong><?php echo htmlspecialchars($material->material_title ?? 'Untitled'); ?></strong><br>
                      <small class="text-muted">by <?php echo htmlspecialchars($material->teacher_first . ' ' . $material->teacher_last); ?></small>
                    </td>
                    <td><?php echo htmlspecialchars($material->description ?? 'General'); ?></td>
                    <td>
                      <i class="bi bi-<?php echo $fileIcon; ?> text-<?php echo $fileColor; ?> me-1"></i>
                      <?php echo strtoupper($extension); ?>
                    </td>
                    <td><?php echo date('M d, Y', strtotime($material->created_at)); ?></td>
                    <td class="text-center">
                      <button class="btn btn-sm btn-outline-info me-1" title="View">
                        <i class="bi bi-eye"></i>
                      </button>
                      <button class="btn btn-sm btn-outline-success me-1" title="Download">
                        <i class="bi bi-download"></i>
                      </button>
                      <button class="btn btn-sm btn-outline-danger" title="Delete">
                        <i class="bi bi-trash"></i>
                      </button>
                    </td>
                  </tr>
                  <?php endforeach; ?>
                <?php else: ?>
                  <tr>
                    <td colspan="5" class="text-center text-muted">No materials uploaded yet</td>
                  </tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>

  <footer class="mt-auto bg-light">
    <div class="container text-center py-3">
      <p class="text-muted mb-0">© 2025 Your Company. All rights reserved.</p>
    </div>
  </footer>

  <!-- Upload Material Modal -->
  <div class="modal fade" id="uploadMaterialModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header bg-info text-white">
          <h5 class="modal-title"><i class="bi bi-cloud-upload me-2"></i>Upload New Material</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <form id="uploadMaterialForm">
            <div class="mb-3">
              <label for="materialTitle" class="form-label fw-bold">Material Title <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="materialTitle" placeholder="e.g., Chapter 3 Lesson Plan" required>
            </div>

            <div class="row mb-3">
              <div class="col-md-6">
                <label for="materialClass" class="form-label fw-bold">Class/Subject <span class="text-danger">*</span></label>
                <select class="form-select" id="materialClass" required>
                  <option value="">Select Class</option>
                  <?php
                  $allClasses = DB::table('classes')
                    ->leftJoin('subjects', 'classes.subject_id', '=', 'subjects.subject_id')
                    ->select('classes.class_id', 'classes.section_name', 'subjects.subject_name')
                    ->get();
                  foreach($allClasses as $cls) {
                    echo '<option value="' . $cls->class_id . '">' . 
                         htmlspecialchars($cls->subject_name ?? $cls->section_name) . ' - ' . 
                         htmlspecialchars($cls->section_name) . '</option>';
                  }
                  ?>
                </select>
              </div>
              <div class="col-md-6">
                <label for="materialType" class="form-label fw-bold">Material Type</label>
                <select class="form-select" id="materialType">
                  <option value="document">Document (PDF, DOCX)</option>
                  <option value="presentation">Presentation (PPT, PPTX)</option>
                  <option value="video">Video (MP4, AVI)</option>
                  <option value="image">Image (JPG, PNG)</option>
                  <option value="spreadsheet">Spreadsheet (XLS, XLSX)</option>
                  <option value="other">Other</option>
                </select>
              </div>
            </div>

            <div class="mb-3">
              <label for="materialDescription" class="form-label fw-bold">Description</label>
              <textarea class="form-control" id="materialDescription" rows="3" placeholder="Brief description of the material"></textarea>
            </div>

            <div class="mb-3">
              <label for="materialTags" class="form-label fw-bold">Tags (Optional)</label>
              <input type="text" class="form-control" id="materialTags" placeholder="e.g., algebra, geometry, homework">
              <small class="text-muted">Separate tags with commas</small>
            </div>

            <div class="mb-3">
              <label for="materialFile" class="form-label fw-bold">Upload File <span class="text-danger">*</span></label>
              <input type="file" class="form-control" id="materialFile" required>
              <small class="text-muted">Max file size: 50MB. Accepted: PDF, DOCX, PPTX, MP4, JPG, PNG, XLS</small>
            </div>

            <div class="mb-3">
              <label class="form-label fw-bold">Visibility</label>
              <div class="form-check">
                <input class="form-check-input" type="radio" name="visibility" id="visibilityPublic" value="public" checked>
                <label class="form-check-label" for="visibilityPublic">
                  Public - Visible to all students in class
                </label>
              </div>
              <div class="form-check">
                <input class="form-check-input" type="radio" name="visibility" id="visibilityPrivate" value="private">
                <label class="form-check-label" for="visibilityPrivate">
                  Private - Only visible when shared
                </label>
              </div>
            </div>

            <div class="form-check">
              <input class="form-check-input" type="checkbox" id="notifyStudents" checked>
              <label class="form-check-label" for="notifyStudents">
                Notify students about this new material
              </label>
            </div>

          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
            <i class="bi bi-x-lg me-2"></i>Cancel
          </button>
          <button type="button" class="btn btn-info text-white" onclick="handleUploadMaterial()">
            <i class="bi bi-cloud-upload me-2"></i>Upload
          </button>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    function handleUploadMaterial() {
      const title = document.getElementById('materialTitle').value;
      const classVal = document.getElementById('materialClass').value;
      const file = document.getElementById('materialFile').files[0];
      
      if (!title || !classVal) {
        Swal.fire('Error', 'Please fill in all required fields', 'error');
        return;
      }
      
      Swal.fire({ title: 'Uploading...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
      
      const classId = parseInt(classVal);
      
      fetch('/admin/materials/upload', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
        },
        body: JSON.stringify({ title, class_id: classId, material_type: 'pdf' })
      })
      .then(r => r.json())
      .then(d => {
        if (d.success) {
          Swal.fire('Success!', 'Material uploaded! Students will be notified.', 'success').then(() => location.reload());
        } else {
          Swal.fire('Error', d.message, 'error');
        }
      })
      .catch(() => Swal.fire('Error', 'Failed to upload material', 'error'));
    }

    // Export Materials to Excel
    function exportMaterialsExcel() {
      const wb = XLSX.utils.book_new();
      
      const data = [
        ['Lesson Materials Report'],
        [''],
        ['Title', 'Class', 'Type', 'Date Uploaded', 'Downloads'],
        ['Introduction to Algebra', 'Grade 7 - Math', 'PDF', '2025-08-01', '45'],
        ['Science Lab Manual', 'Grade 8 - Science', 'PDF', '2025-08-03', '38'],
        ['History Chapter 5', 'Grade 9 - History', 'PPTX', '2025-08-05', '42'],
        ['English Grammar Guide', 'Grade 10 - English', 'DOCX', '2025-08-07', '50']
      ];
      
      const ws = XLSX.utils.aoa_to_sheet(data);
      ws['!cols'] = [{ wch: 25 }, { wch: 20 }, { wch: 10 }, { wch: 15 }, { wch: 12 }];
      
      XLSX.utils.book_append_sheet(wb, ws, 'Materials');
      XLSX.writeFile(wb, 'lesson-materials.xlsx');
      
      alert('Lesson materials list exported successfully!');
    }
  </script>

</body>

</html> 