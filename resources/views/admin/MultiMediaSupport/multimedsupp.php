<?php
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

// Get authenticated user
$user = Auth::user();

// Fetch multimedia files from lesson_materials
$mediaFiles = DB::table('lesson_materials')
    ->join('users as teachers', 'lesson_materials.teacher_id', '=', 'teachers.user_id')
    ->select(
        'lesson_materials.*',
        'teachers.first_name as teacher_first',
        'teachers.last_name as teacher_last'
    )
    ->orderBy('lesson_materials.created_at', 'desc')
    ->get();

// Count by media type
$videoCount = $mediaFiles->filter(function($m) {
    $ext = strtolower(pathinfo($m->file_path, PATHINFO_EXTENSION));
    return in_array($ext, ['mp4', 'avi', 'mov', 'wmv', 'webm']);
})->count();

$audioCount = $mediaFiles->filter(function($m) {
    $ext = strtolower(pathinfo($m->file_path, PATHINFO_EXTENSION));
    return in_array($ext, ['mp3', 'wav', 'ogg', 'm4a']);
})->count();

$imageCount = $mediaFiles->filter(function($m) {
    $ext = strtolower(pathinfo($m->file_path, PATHINFO_EXTENSION));
    return in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
})->count();

$documentCount = $mediaFiles->filter(function($m) {
    $ext = strtolower(pathinfo($m->file_path, PATHINFO_EXTENSION));
    return in_array($ext, ['pdf', 'doc', 'docx', 'ppt', 'pptx']);
})->count();
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

  <!-- navbar -->

  <?php include resource_path('views/includes/sidenav_admin.php'); ?>
  <div class="main-content flex-grow-1">
    <div class="container">
      <div class="row">
        <div class="col">
          <div class="container my-5">
            <div class="card shadow-lg border-0 rounded-4">
              <div class="card-body p-4">
              >
      <!-- Header -->
      <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="d-flex align-items-center">
          <i class="bi bi-collection-play-fill text-purple fs-1 me-3"></i>
          <h3 class="mb-0 fw-bold">Multi-Media Support</h3>
        </div>
        <div class="btn-group">
          <button class="btn btn-purple text-white" style="background-color:#6f42c1;" data-bs-toggle="modal" data-bs-target="#uploadMediaModal">
            <i class="bi bi-upload me-2"></i> Upload Media
          </button>
          <button class="btn btn-success text-white" onclick="exportCurrentTable('mediaTable', 'multimedia')">
            <i class="bi bi-file-earmark-excel me-2"></i> Export
          </button>
        </div>
      </div>

      <!-- Stats Overview -->
      <div class="row g-3 text-center mb-4">
        <div class="col-md-3">
          <div class="border rounded-3 p-3 bg-light">
            <i class="bi bi-camera-video-fill fs-2 text-danger"></i>
            <h5 class="fw-bold mt-2"><?php echo number_format($videoCount); ?></h5>
            <p class="small text-muted mb-0">Videos Uploaded</p>
          </div>
        </div>
        <div class="col-md-3">
          <div class="border rounded-3 p-3 bg-light">
            <i class="bi bi-file-earmark-music-fill fs-2 text-success"></i>
            <h5 class="fw-bold mt-2"><?php echo number_format($audioCount); ?></h5>
            <p class="small text-muted mb-0">Audio Files</p>
          </div>
        </div>
        <div class="col-md-3">
          <div class="border rounded-3 p-3 bg-light">
            <i class="bi bi-image-fill fs-2 text-warning"></i>
            <h5 class="fw-bold mt-2"><?php echo number_format($imageCount); ?></h5>
            <p class="small text-muted mb-0">Images</p>
          </div>
        </div>
        <div class="col-md-3">
          <div class="border rounded-3 p-3 bg-light">
            <i class="bi bi-file-earmark-pdf-fill fs-2 text-primary"></i>
            <h5 class="fw-bold mt-2"><?php echo number_format($documentCount); ?></h5>
            <p class="small text-muted mb-0">Documents</p>
          </div>
        </div>
      </div>

      <!-- Media Table -->
      <h5 class="fw-bold mb-3">Recent Media Uploads</h5>
      <div class="table-responsive">
        <table id="mediaTable" class="table table-hover align-middle">
          <thead class="table-secondary">
            <tr>
              <th>Upload Date</th>
              <th>Title</th>
              <th>Type</th>
              <th>Uploaded By</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <?php if($mediaFiles->count() > 0): ?>
              <?php foreach($mediaFiles as $media): 
                $teacherName = $media->teacher_first . ' ' . $media->teacher_last;
                $ext = strtolower(pathinfo($media->file_path, PATHINFO_EXTENSION));
                
                // Determine media type and badge color
                if(in_array($ext, ['mp4', 'avi', 'mov', 'wmv', 'webm'])) {
                  $mediaType = 'Video';
                  $badgeColor = 'bg-danger';
                } elseif(in_array($ext, ['mp3', 'wav', 'ogg', 'm4a'])) {
                  $mediaType = 'Audio';
                  $badgeColor = 'bg-success';
                } elseif(in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                  $mediaType = 'Image';
                  $badgeColor = 'bg-warning text-dark';
                } elseif(in_array($ext, ['pdf', 'doc', 'docx', 'ppt', 'pptx'])) {
                  $mediaType = 'Document';
                  $badgeColor = 'bg-primary';
                } else {
                  $mediaType = 'File';
                  $badgeColor = 'bg-secondary';
                }
              ?>
              <tr>
                <td><?php echo date('M d, Y', strtotime($media->created_at)); ?></td>
                <td><strong><?php echo htmlspecialchars($media->material_title ?? 'Untitled'); ?></strong></td>
                <td><span class="badge <?php echo $badgeColor; ?>"><?php echo $mediaType; ?></span></td>
                <td><?php echo htmlspecialchars($teacherName); ?></td>
                <td>
                  <button class="btn btn-sm btn-outline-info me-1" title="View">
                    <i class="bi bi-eye"></i>
                  </button>
                  <button class="btn btn-sm btn-outline-primary me-1" title="Download">
                    <i class="bi bi-download"></i>
                  </button>
                  <button class="btn btn-sm btn-outline-warning me-1" title="Edit">
                    <i class="bi bi-pencil"></i>
                  </button>
                  <button class="btn btn-sm btn-outline-danger" title="Delete">
                    <i class="bi bi-trash"></i>
                  </button>
                </td>
              </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="5" class="text-center text-muted">No media files uploaded yet</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>

    
    </div>
  </div>
</div>
             


 


  </style>

  <!-- Upload Media Modal -->
  <div class="modal fade" id="uploadMediaModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header" style="background-color:#6f42c1; color: white;">
          <h5 class="modal-title"><i class="bi bi-cloud-upload me-2"></i>Upload New Media</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <form id="uploadMediaForm">
            <div class="mb-3">
              <label for="mediaTitle" class="form-label fw-bold">Media Title <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="mediaTitle" placeholder="e.g., Algebra Tutorial Video" required>
            </div>

            <div class="row mb-3">
              <div class="col-md-6">
                <label for="mediaType" class="form-label fw-bold">Media Type <span class="text-danger">*</span></label>
                <select class="form-select" id="mediaType" required>
                  <option value="">Select Type</option>
                  <option value="video">Video</option>
                  <option value="audio">Audio</option>
                  <option value="image">Image</option>
                  <option value="document">Document</option>
                  <option value="presentation">Presentation</option>
                </select>
              </div>
              <div class="col-md-6">
                <label for="mediaSubject" class="form-label fw-bold">Subject/Class</label>
                <select class="form-select" id="mediaSubject">
                  <option value="math">Mathematics</option>
                  <option value="science">Science</option>
                  <option value="english">English</option>
                  <option value="history">History</option>
                  <option value="general">General</option>
                </select>
              </div>
            </div>

            <div class="mb-3">
              <label for="mediaDescription" class="form-label fw-bold">Description</label>
              <textarea class="form-control" id="mediaDescription" rows="3" placeholder="Brief description of the media content"></textarea>
            </div>

            <div class="mb-3">
              <label class="form-label fw-bold">Upload Method</label>
              <div class="btn-group w-100 mb-2" role="group">
                <input type="radio" class="btn-check" name="uploadMethod" id="uploadFile" value="file" checked>
                <label class="btn btn-outline-primary" for="uploadFile">Upload File</label>
                
                <input type="radio" class="btn-check" name="uploadMethod" id="uploadURL" value="url">
                <label class="btn btn-outline-primary" for="uploadURL">Use URL (YouTube, Vimeo, etc.)</label>
              </div>
            </div>

            <div id="fileUploadSection">
              <div class="mb-3">
                <label for="mediaFile" class="form-label fw-bold">Select File <span class="text-danger">*</span></label>
                <input type="file" class="form-control" id="mediaFile" accept="video/*,audio/*,image/*,.pdf,.ppt,.pptx">
                <small class="text-muted">Max size: 100MB. Supported: MP4, AVI, MP3, JPG, PNG, PDF, PPT</small>
              </div>
            </div>

            <div id="urlUploadSection" style="display: none;">
              <div class="mb-3">
                <label for="mediaURL" class="form-label fw-bold">Media URL <span class="text-danger">*</span></label>
                <input type="url" class="form-control" id="mediaURL" placeholder="https://youtube.com/watch?v=...">
                <small class="text-muted">Supports: YouTube, Vimeo, Google Drive, Direct links</small>
              </div>
            </div>

            <div class="form-check">
              <input class="form-check-input" type="checkbox" id="makePublic" checked>
              <label class="form-check-label" for="makePublic">
                Make this media available to all students
              </label>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
            <i class="bi bi-x-lg me-2"></i>Cancel
          </button>
          <button type="button" class="btn" style="background-color:#6f42c1; color: white;" onclick="handleUploadMedia()">
            <i class="bi bi-cloud-upload me-2"></i>Upload
          </button>
        </div>
      </div>
    </div>
  </div>

</body>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
  // Toggle between file upload and URL upload
  document.querySelectorAll('input[name="uploadMethod"]').forEach(radio => {
    radio.addEventListener('change', function() {
      if (this.value === 'file') {
        document.getElementById('fileUploadSection').style.display = 'block';
        document.getElementById('urlUploadSection').style.display = 'none';
      } else {
        document.getElementById('fileUploadSection').style.display = 'none';
        document.getElementById('urlUploadSection').style.display = 'block';
      }
    });
  });

  // Handle media upload
  function handleUploadMedia() {
    const title = document.getElementById('mediaTitle').value;
    const type = document.getElementById('mediaType').value;
    const uploadMethod = document.querySelector('input[name="uploadMethod"]:checked').value;
    
    if (!title || !type) {
      alert('Please fill in all required fields');
      return;
    }
    
    if (uploadMethod === 'file') {
      const file = document.getElementById('mediaFile').files[0];
      if (!file) {
        alert('Please select a file to upload');
        return;
      }
    } else {
      const url = document.getElementById('mediaURL').value;
      if (!url) {
        alert('Please enter a valid URL');
        return;
      }
    }
    
    Swal.fire({ title: 'Uploading Media...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
    
    fetch('/admin/materials/upload', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
      },
      body: JSON.stringify({ title, material_type: 'other' })
    })
    .then(r => r.json())
    .then(d => {
      if (d.success) {
        Swal.fire('Success!', 'Media uploaded successfully!', 'success').then(() => {
          bootstrap.Modal.getInstance(document.getElementById('uploadMediaModal')).hide();
          document.getElementById('uploadMediaForm').reset();
          location.reload();
        });
      } else {
        Swal.fire('Error', d.message, 'error');
      }
    })
    .catch(() => Swal.fire('Error', 'Failed to upload media', 'error'));
  }

  // Export media list to Excel
  function exportMediaList() {
    const wb = XLSX.utils.book_new();
    
    const data = [
      ['Multi-Media List Report'],
      [''],
      ['Upload Date', 'Title', 'Type', 'Uploaded By'],
      ['2025-08-09', 'Algebra Basics Lesson', 'Video', 'Mr. Santos'],
      ['2025-08-08', 'Science Quiz Review Audio', 'Audio', 'Ms. Garcia'],
      ['2025-08-07', 'Biology Infographic', 'Image', 'Mr. Cruz']
    ];
    
    const ws = XLSX.utils.aoa_to_sheet(data);
    ws['!cols'] = [{ wch: 15 }, { wch: 30 }, { wch: 12 }, { wch: 20 }];
    
    XLSX.utils.book_append_sheet(wb, ws, 'Media List');
    XLSX.writeFile(wb, 'multimedia-list.xlsx');
    
    alert('Media list exported successfully!');
  }
</script>

</html>