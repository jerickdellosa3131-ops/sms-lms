<?php
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

// Get authenticated user
$user = Auth::user();

// Fetch feedback from students (handle missing table)
try {
    $feedbacks = DB::table('feedback')
        ->join('users as students', 'feedback.student_id', '=', 'students.user_id')
        ->leftJoin('classes', 'feedback.class_id', '=', 'classes.class_id')
        ->select(
            'feedback.*',
            'students.first_name',
            'students.last_name',
            'classes.section_name'
        )
        ->orderBy('feedback.created_at', 'desc')
        ->get();
} catch (\Exception $e) {
    // If feedback table doesn't exist, create empty collection
    $feedbacks = collect([]);
}

$totalFeedback = $feedbacks->count();
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Feedback</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="<?php echo asset('style.css'); ?>">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
  
  <!-- SheetJS for Excel Export -->
  <script src="https://cdn.sheetjs.com/xlsx-0.20.0/package/dist/xlsx.full.min.js"></script>
  
  <style>
    @import url("../../style.css");

    body {
      background-color: #f8f9fa; /* Lighter background for a softer look */
    }
    .main-content {
      padding-top: 2rem;
      padding-bottom: 2rem;
    }
    .feedback-card {
      border: none;
      border-left: 4px solid #0d6efd; /* Accent border */
      transition: box-shadow 0.3s ease-in-out, transform 0.3s ease-in-out;
    }
    .feedback-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 .5rem 1rem rgba(0,0,0,.15) !important;
    }
    .student-avatar {
      width: 40px;
      height: 40px;
      background-color: #e9ecef;
      color: #495057;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: bold;
      border-radius: 50%;
    }
  </style>
</head>

<body>

  <?php include resource_path('views/includes/sidenav_admin.php'); ?>

  <div class="main-content flex-grow-1">
    <div class="container">
      <div class="card shadow-sm border-0 rounded-4">
        <div class="card-body p-4 p-md-5">

          <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
            <div class="d-flex align-items-center mb-3 mb-md-0">
              <i class="bi bi-chat-left-dots-fill text-primary" style="font-size: 2.5rem;"></i>
              <div class="ms-3">
                <h2 class="mb-0 fw-bold">Student Feedback</h2>
                <p class="text-muted mb-0">Review comments and suggestions from students.</p>
              </div>
            </div>
            <button class="btn btn-primary" onclick="exportFeedbackExcel()">
              <i class="bi bi-file-earmark-excel me-2"></i> Export All
            </button>
          </div>
          
          <div class="row g-2 mb-4">
            <div class="col-md-8">
              <input type="search" class="form-control" placeholder="Search by student or keyword...">
            </div>
            <div class="col-md-4">
              <select class="form-select">
                <option selected>Filter by Subject...</option>
                <option>Math</option>
                <option>Science</option>
                <option>English</option>
              </select>
            </div>
          </div>

          <div class="list-group">
            <?php if($feedbacks->count() > 0): ?>
              <?php foreach($feedbacks as $fb): 
                $studentName = $fb->first_name . ' ' . $fb->last_name;
                $initials = strtoupper(substr($fb->first_name, 0, 1) . substr($fb->last_name, 0, 1));
              ?>
              <div class="list-group-item p-3 mb-3 rounded-3 shadow-sm feedback-card">
                <div class="d-flex w-100 align-items-center">
                  <div class="student-avatar me-3"><?php echo $initials; ?></div>
                  <div class="flex-grow-1">
                    <div class="d-flex justify-content-between">
                      <h6 class="mb-1 fw-bold">
                        <?php echo htmlspecialchars($studentName); ?>
                        <span class="text-muted fw-normal ms-1">- <?php echo htmlspecialchars($fb->section_name ?? 'General'); ?></span>
                      </h6>
                      <small class="text-muted"><?php echo date('F j, Y', strtotime($fb->created_at)); ?></small>
                    </div>
                    <p class="mb-1 mt-1">"<?php echo htmlspecialchars($fb->comment ?? $fb->feedback_text ?? 'No comment'); ?>"</p>
                    <?php if(isset($fb->rating)): ?>
                      <div class="mt-2">
                        <?php for($i = 1; $i <= 5; $i++): ?>
                          <i class="bi bi-star<?php echo $i <= $fb->rating ? '-fill' : ''; ?> text-warning"></i>
                        <?php endfor; ?>
                      </div>
                    <?php endif; ?>
                  </div>
                  <div class="ms-3">
                    <a href="#" class="btn btn-sm btn-outline-primary"><i class="bi bi-reply-fill"></i> Reply</a>
                  </div>
                </div>
              </div>
              <?php endforeach; ?>
            <?php else: ?>
              <div class="alert alert-info text-center">
                <i class="bi bi-info-circle me-2"></i>No feedback received yet
              </div>
            <?php endif; ?>
          </div>

        </div>
      </div>
    </div>
  </div>



  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Export Feedback to Excel
    function exportFeedbackExcel() {
      const wb = XLSX.utils.book_new();
      
      const data = [
        ['Student Feedback Report'],
        [''],
        ['Student Name', 'Subject', 'Date', 'Feedback', 'Rating'],
        ['John Doe', 'Math', '2025-08-05', 'The lesson materials were very helpful', '5/5'],
        ['Jane Smith', 'Science', '2025-08-06', 'Would appreciate more practice problems', '4/5'],
        ['Mark Johnson', 'English', '2025-08-07', 'Great teaching style and clear explanations', '5/5'],
        ['Sarah Williams', 'History', '2025-08-08', 'More interactive sessions would be nice', '4/5']
      ];
      
      const ws = XLSX.utils.aoa_to_sheet(data);
      ws['!cols'] = [{ wch: 20 }, { wch: 12 }, { wch: 15 }, { wch: 40 }, { wch: 10 }];
      
      XLSX.utils.book_append_sheet(wb, ws, 'Feedback');
      XLSX.writeFile(wb, 'student-feedback.xlsx');
      
      alert('Feedback exported successfully!');
    }
  </script>
</body>
</html>