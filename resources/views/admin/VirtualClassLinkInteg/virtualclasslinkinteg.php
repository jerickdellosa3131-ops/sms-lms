<?php
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

// Get authenticated user
$user = Auth::user();

// Fetch virtual classes with teacher and class information
try {
    $virtualClasses = DB::table('virtual_class_links')
        ->join('teachers', 'virtual_class_links.teacher_id', '=', 'teachers.teacher_id')
        ->join('users', 'teachers.user_id', '=', 'users.user_id')
        ->join('classes', 'virtual_class_links.class_id', '=', 'classes.class_id')
        ->select(
            'virtual_class_links.*',
            'users.first_name as teacher_first',
            'users.last_name as teacher_last',
            'classes.section_name',
            'classes.class_code'
        )
        ->orderBy('virtual_class_links.scheduled_date', 'desc')
        ->get();
} catch (\Exception $e) {
    // If table doesn't exist, create empty collection
    $virtualClasses = collect([]);
}

// Calculate statistics
$totalScheduled = $virtualClasses->count();
$activeClasses = $virtualClasses->whereIn('status', ['scheduled', 'ongoing'])->count();
$cancelledClasses = $virtualClasses->where('status', 'cancelled')->count();
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

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
  
    <!-- SheetJS for Excel Export -->
    <script src="https://cdn.sheetjs.com/xlsx-0.20.0/package/dist/xlsx.full.min.js"></script>
    
    <!-- SweetAlert2 for Alerts -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  
  <!-- SheetJS for Excel Export -->
  <script src="https://cdn.sheetjs.com/xlsx-0.20.0/package/dist/xlsx.full.min.js"></script>
  
  <!-- Modal Handlers Library -->
  <script src="<?php echo asset('js/modal-handlers.js'); ?>"></script>
  
  <!-- Admin Actions Library -->
  <script src="<?php echo asset('js/admin-actions.js'); ?>"></script>
  
  <style>
    @import url("../../style.css");
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
                            <i class="bi bi-camera-video-fill text-danger fs-1 me-3"></i>
                            <h3 class="mb-0 fw-bold">Virtual Class Link Management</h3>
                        </div>
                        <button class="btn btn-danger text-white" onclick="exportCurrentTable('virtualClassTable', 'virtual_classes')">
                            <i class="bi bi-file-earmark-excel me-2"></i> Export Sessions Report
                        </button>
                    </div>

                    <!-- Overview Stats -->
                    <div class="row g-3 text-center mb-4">
                        <div class="col-md-3">
                            <div class="border rounded-3 p-3 bg-light">
                                <i class="bi bi-calendar-event fs-2 text-primary"></i>
                                <h5 class="fw-bold mt-2"><?php echo number_format($totalScheduled); ?></h5>
                                <p class="small text-muted mb-0">Total Scheduled Classes</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="border rounded-3 p-3 bg-light">
                                <i class="bi bi-camera-video fs-2 text-success"></i>
                                <h5 class="fw-bold mt-2"><?php echo number_format($activeClasses); ?></h5>
                                <p class="small text-muted mb-0">Active Classes</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="border rounded-3 p-3 bg-light">
                                <i class="bi bi-link-45deg fs-2 text-info"></i>
                                <h5 class="fw-bold mt-2"><?php echo $virtualClasses->where('platform', '!=', '')->count(); ?></h5>
                                <p class="small text-muted mb-0">Platforms Used</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="border rounded-3 p-3 bg-light">
                                <i class="bi bi-x-circle fs-2 text-danger"></i>
                                <h5 class="fw-bold mt-2"><?php echo number_format($cancelledClasses); ?></h5>
                                <p class="small text-muted mb-0">Cancelled Sessions</p>
                            </div>
                        </div>
                    </div>

                    <!-- Sessions Table -->
                    <h5 class="fw-bold mb-3">All Scheduled Virtual Classes</h5>
                    <div class="table-responsive">
                        <table id="virtualClassTable" class="table table-hover align-middle">
                            <thead class="table-danger">
                                <tr>
                                    <th>Class Title</th>
                                    <th>Professor</th>
                                    <th>Platform</th>
                                    <th>Schedule</th>
                                    <th>Link</th>
                                    <th>Attendance</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if($virtualClasses->count() > 0): ?>
                                    <?php foreach($virtualClasses as $vc): 
                                        $teacherName = $vc->teacher_first . ' ' . $vc->teacher_last;
                                        
                                        // Determine status badge color
                                        switch($vc->status) {
                                            case 'ongoing':
                                                $statusBadge = 'bg-success';
                                                break;
                                            case 'scheduled':
                                                $statusBadge = 'bg-primary';
                                                break;
                                            case 'cancelled':
                                                $statusBadge = 'bg-danger';
                                                break;
                                            case 'completed':
                                                $statusBadge = 'bg-secondary';
                                                break;
                                            default:
                                                $statusBadge = 'bg-info';
                                        }
                                    ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($vc->meeting_title ?? $vc->section_name); ?></strong><br>
                                            <small class="text-muted"><?php echo htmlspecialchars($vc->class_code); ?></small>
                                        </td>
                                        <td><?php echo htmlspecialchars($teacherName); ?></td>
                                        <td><?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $vc->meeting_platform ?? 'N/A'))); ?></td>
                                        <td><?php echo date('M d, Y g:i A', strtotime($vc->scheduled_date)); ?></td>
                                        <td>
                                            <?php if($vc->meeting_link): ?>
                                                <a href="<?php echo htmlspecialchars($vc->meeting_link); ?>" target="_blank" class="btn btn-sm btn-outline-info">
                                                    <i class="bi bi-box-arrow-up-right"></i> Join
                                                </a>
                                            <?php else: ?>
                                                <span class="text-muted">No link</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary"><?php echo $vc->attendance ?? '0'; ?></span>
                                        </td>
                                        <td><span class="badge <?php echo $statusBadge; ?>"><?php echo ucfirst($vc->status); ?></span></td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-info me-1" title="View"
                                                onclick="viewRecord(<?php echo $vc->virtual_class_id; ?>, 'Virtual Class', {
                                                    'Title': '<?php echo addslashes($vc->meeting_title ?? $vc->section_name); ?>',
                                                    'Teacher': '<?php echo addslashes($teacherName); ?>',
                                                    'Platform': '<?php echo addslashes($vc->meeting_platform ?? 'N/A'); ?>',
                                                    'Schedule': '<?php echo date('M d, Y g:i A', strtotime($vc->scheduled_date)); ?>',
                                                    'Link': '<?php echo addslashes($vc->meeting_link ?? 'N/A'); ?>',
                                                    'Status': '<?php echo ucfirst($vc->status); ?>'
                                                })">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-warning me-1" title="Edit"
                                                onclick="editRecord(<?php echo $vc->virtual_class_id; ?>, 'Virtual Class', {
                                                    'title': '<?php echo addslashes($vc->meeting_title ?? ''); ?>',
                                                    'platform': '<?php echo addslashes($vc->meeting_platform ?? ''); ?>',
                                                    'link': '<?php echo addslashes($vc->meeting_link ?? ''); ?>',
                                                    'schedule': '<?php echo $vc->scheduled_date; ?>'
                                                })">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger" title="Delete"
                                                onclick="deleteRecord(<?php echo $vc->virtual_class_id; ?>, 'Virtual Class', '<?php echo addslashes($vc->meeting_title ?? $vc->section_name); ?>')">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="8" class="text-center text-muted">No virtual classes scheduled</td>
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
      </div>
    </div>
  </div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
  // Export Virtual Class Sessions to Excel
  function exportVirtualClassSessions() {
    const wb = XLSX.utils.book_new();
    
    // Main sessions data
    const sessionsData = [
      ['Virtual Class Sessions Report'],
      [''],
      ['Class Title', 'Professor', 'Platform', 'Schedule', 'Link', 'Attendance', 'Status'],
      ['Algebra Lecture', 'Prof. Santos', 'Zoom', '2025-08-25 09:00 AM', 'https://zoom.us/j/123456789', '45/50', 'Active'],
      ['Science Review', 'Prof. Reyes', 'Google Meet', '2025-08-26 01:00 PM', 'https://meet.google.com/xyz-abc', '60/65', 'Pending'],
      ['English Grammar', 'Prof. Cruz', 'MS Teams', '2025-08-27 02:00 PM', 'https://teams.microsoft.com/12345', '30/40', 'Cancelled']
    ];
    
    const ws1 = XLSX.utils.aoa_to_sheet(sessionsData);
    ws1['!cols'] = [{ wch: 20 }, { wch: 15 }, { wch: 15 }, { wch: 20 }, { wch: 35 }, { wch: 12 }, { wch: 12 }];
    XLSX.utils.book_append_sheet(wb, ws1, 'Sessions');
    
    // Summary sheet
    const summaryData = [
      ['Virtual Class Summary'],
      [''],
      ['Metric', 'Value'],
      ['Total Scheduled Classes', '120'],
      ['Students Attended', '2,350'],
      ['Attendance Rate', '85%'],
      ['Cancelled Sessions', '15']
    ];
    
    const ws2 = XLSX.utils.aoa_to_sheet(summaryData);
    ws2['!cols'] = [{ wch: 25 }, { wch: 15 }];
    XLSX.utils.book_append_sheet(wb, ws2, 'Summary');
    
    // Generate and download
    XLSX.writeFile(wb, 'virtual-class-sessions-report.xlsx');
    
    alert('Virtual class sessions report exported successfully!');
  }
</script>

</body>
</html>