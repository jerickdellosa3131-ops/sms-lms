<?php
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

$user = Auth::user();
$teacher_id = $user ? $user->user_id : null;

// Fetch teacher's teacher_id from teachers table
$teacher = null;
$virtualClasses = collect();

if ($teacher_id) {
    $teacher = DB::table('teachers')->where('user_id', $teacher_id)->first();
    
    if ($teacher) {
        // Fetch virtual classes for this teacher
        $virtualClasses = DB::table('virtual_class_links')
            ->leftJoin('classes', 'virtual_class_links.class_id', '=', 'classes.class_id')
            ->where('virtual_class_links.teacher_id', $teacher->teacher_id)
            ->select(
                'virtual_class_links.*',
                'classes.section_name',
                'classes.class_code'
            )
            ->orderBy('virtual_class_links.scheduled_date', 'desc')
            ->get();
    }
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
  
  <!-- SweetAlert2 for Alerts -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  
  <!-- Modal Handlers Library -->
  <script src="<?php echo asset('js/modal-handlers.js'); ?>"></script>
  
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
            <i class="bi bi-camera-video-fill text-primary fs-1 me-3"></i>
            <h3 class="mb-0 fw-bold">Virtual Class Link Integration</h3>
          </div>
          <a href="#" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i> Back to Class Portal
          </a>
        </div>

        <!-- Create Virtual Class -->
        <h5 class="fw-bold mb-3">Create Virtual Class</h5>
        <form id="virtualClassForm" onsubmit="event.preventDefault(); handleCreateVirtualClass();" class="mb-5">
          <div class="row g-3">
            <!-- Class Title -->
            <div class="col-md-6">
              <label for="classTitle" class="form-label fw-bold">Class Title</label>
              <input type="text" id="classTitle" class="form-control" placeholder="Ex: Algebra Lecture" required>
            </div>

            <!-- Platform -->
            <div class="col-md-6">
              <label for="platform" class="form-label fw-bold">Platform</label>
              <select id="platform" class="form-select" required>
                <option value="" disabled selected>-- Select Platform --</option>
                <option>Zoom</option>
                <option>Google Meet</option>
                <option>Microsoft Teams</option>
                <option>Other</option>
              </select>
            </div>

            <!-- Link -->
            <div class="col-md-8">
              <label for="classLink" class="form-label fw-bold">Class Link</label>
              <input type="url" id="classLink" class="form-control" placeholder="https://example.com/class" required>
            </div>

            <!-- Schedule -->
            <div class="col-md-4">
              <label for="classSchedule" class="form-label fw-bold">Schedule</label>
              <input type="datetime-local" id="classSchedule" class="form-control" required>
            </div>
          </div>

          <!-- Submit -->
          <div class="mt-4 text-end">
            <button type="reset" class="btn btn-outline-secondary me-2">
              <i class="bi bi-x-circle me-1"></i> Clear
            </button>
            <button type="submit" class="btn btn-primary">
              <i class="bi bi-plus-circle me-1"></i> Add Virtual Class
            </button>
          </div>
        </form>

        <!-- Scheduled Classes -->
        <h5 class="fw-bold mb-3">Upcoming Virtual Classes</h5>
        <div class="table-responsive">
          <table class="table table-hover align-middle">
            <thead class="table-primary">
              <tr>
                <th>Class Title</th>
                <th>Platform</th>
                <th>Schedule</th>
                <th>Link</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php if($virtualClasses->count() > 0): ?>
                <?php foreach($virtualClasses as $vc): ?>
                  <?php
                    $platformDisplay = ucwords(str_replace('_', ' ', $vc->meeting_platform));
                    $statusBadge = match($vc->status) {
                      'scheduled' => 'bg-primary',
                      'ongoing' => 'bg-success',
                      'completed' => 'bg-secondary',
                      'cancelled' => 'bg-danger',
                      default => 'bg-info'
                    };
                  ?>
                  <tr>
                    <td>
                      <strong><?php echo htmlspecialchars($vc->meeting_title); ?></strong><br>
                      <small class="text-muted"><?php echo htmlspecialchars($vc->section_name ?? 'N/A'); ?></small>
                    </td>
                    <td><?php echo htmlspecialchars($platformDisplay); ?></td>
                    <td><?php echo date('Y-m-d h:i A', strtotime($vc->scheduled_date)); ?></td>
                    <td>
                      <a href="<?php echo htmlspecialchars($vc->meeting_link); ?>" target="_blank" class="btn btn-sm btn-outline-info">
                        <i class="bi bi-box-arrow-up-right me-1"></i>Join
                      </a>
                    </td>
                    <td>
                      <span class="badge <?php echo $statusBadge; ?>">
                        <?php echo ucfirst($vc->status); ?>
                      </span>
                    </td>
                    <td>
                      <button class="btn btn-sm btn-outline-warning" title="Edit"
                        onclick="editRecord(<?php echo $vc->virtual_class_id; ?>, 'VirtualClass', {
                          'title': '<?php echo addslashes($vc->meeting_title); ?>',
                          'platform': '<?php echo $vc->meeting_platform; ?>',
                          'meeting_link': '<?php echo addslashes($vc->meeting_link); ?>'
                        })">
                        <i class="bi bi-pencil"></i>
                      </button>
                      <button class="btn btn-sm btn-outline-danger" title="Delete"
                        onclick="deleteRecord(<?php echo $vc->virtual_class_id; ?>, 'VirtualClass', '<?php echo addslashes($vc->meeting_title); ?>')">
                        <i class="bi bi-trash"></i>
                      </button>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr>
                  <td colspan="6" class="text-center text-muted py-4">
                    <i class="bi bi-camera-video fs-1 d-block mb-2"></i>
                    No virtual classes scheduled yet. Create one above!
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
             


  <footer class="mt-auto bg-light">
    <div class="container text-center">
      <p class="text-muted">© 2023 Your Company. All rights reserved.</p>
    </div>
  </footer>


  </style>
</body>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>

</html>