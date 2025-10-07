<?php
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

// Get authenticated user
$user = Auth::user();

// Fetch feedback from students (handle missing table)
try {
    $feedbacks = DB::table('student_feedback')
        ->join('students', 'student_feedback.student_id', '=', 'students.student_id')
        ->join('users as student_users', 'students.user_id', '=', 'student_users.user_id')
        ->leftJoin('classes', 'student_feedback.class_id', '=', 'classes.class_id')
        ->select(
            'student_feedback.*',
            'student_users.first_name',
            'student_users.last_name',
            'students.student_number',
            'classes.section_name',
            'classes.class_code'
        )
        ->orderBy('student_feedback.created_at', 'desc')
        ->get();
} catch (\Exception $e) {
    // If student_feedback table doesn't exist, create empty collection
    $feedbacks = collect([]);
}

$totalFeedback = $feedbacks->count();

// Get feedback statistics
$feedbackByType = $feedbacks->groupBy('feedback_type')->map(function($items) {
    return $items->count();
});

$averageRating = $feedbacks->whereNotNull('rating')->avg('rating') ?? 0;
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
          
          <!-- Statistics Cards -->
          <div class="row g-3 mb-4">
            <div class="col-md-3">
              <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                  <i class="bi bi-chat-dots text-primary fs-1"></i>
                  <h3 class="mt-2 mb-0 fw-bold"><?php echo $totalFeedback; ?></h3>
                  <p class="text-muted small mb-0">Total Feedback</p>
                </div>
              </div>
            </div>
            <div class="col-md-3">
              <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                  <i class="bi bi-star-fill text-warning fs-1"></i>
                  <h3 class="mt-2 mb-0 fw-bold"><?php echo number_format($averageRating, 1); ?></h3>
                  <p class="text-muted small mb-0">Average Rating</p>
                </div>
              </div>
            </div>
            <div class="col-md-3">
              <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                  <i class="bi bi-person-check text-success fs-1"></i>
                  <h3 class="mt-2 mb-0 fw-bold"><?php echo $feedbacks->where('is_anonymous', 0)->count(); ?></h3>
                  <p class="text-muted small mb-0">Named Feedback</p>
                </div>
              </div>
            </div>
            <div class="col-md-3">
              <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                  <i class="bi bi-incognito text-secondary fs-1"></i>
                  <h3 class="mt-2 mb-0 fw-bold"><?php echo $feedbacks->where('is_anonymous', 1)->count(); ?></h3>
                  <p class="text-muted small mb-0">Anonymous</p>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Filters -->
          <div class="row g-2 mb-4">
            <div class="col-md-4">
              <input type="search" class="form-control" id="searchInput" placeholder="Search by student or keyword..." onkeyup="filterFeedback()">
            </div>
            <div class="col-md-3">
              <select class="form-select" id="typeFilter" onchange="filterFeedback()">
                <option value="">All Types</option>
                <option value="general">General Feedback</option>
                <option value="teaching">Teaching Quality</option>
                <option value="content">Course Content</option>
                <option value="facilities">Facilities & Resources</option>
                <option value="suggestion">Suggestion</option>
              </select>
            </div>
            <div class="col-md-3">
              <select class="form-select" id="classFilter" onchange="filterFeedback()">
                <option value="">All Classes</option>
                <?php foreach($feedbacks->pluck('section_name')->unique()->filter() as $className): ?>
                  <option value="<?php echo htmlspecialchars($className); ?>"><?php echo htmlspecialchars($className); ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-2">
              <select class="form-select" id="ratingFilter" onchange="filterFeedback()">
                <option value="">All Ratings</option>
                <option value="5">5 Stars</option>
                <option value="4">4 Stars</option>
                <option value="3">3 Stars</option>
                <option value="2">2 Stars</option>
                <option value="1">1 Star</option>
              </select>
            </div>
          </div>

          <div class="list-group" id="feedbackList">
            <?php if($feedbacks->count() > 0): ?>
              <?php foreach($feedbacks as $fb): 
                $studentName = $fb->is_anonymous ? 'Anonymous Student' : $fb->first_name . ' ' . $fb->last_name;
                $initials = $fb->is_anonymous ? 'AN' : strtoupper(substr($fb->first_name, 0, 1) . substr($fb->last_name, 0, 1));
                $feedbackTypeColor = match($fb->feedback_type) {
                  'teaching' => 'primary',
                  'content' => 'success',
                  'facilities' => 'warning',
                  'suggestion' => 'info',
                  default => 'secondary'
                };
              ?>
              <div class="list-group-item p-3 mb-3 rounded-3 shadow-sm feedback-card" 
                   data-student="<?php echo htmlspecialchars($studentName); ?>"
                   data-class="<?php echo htmlspecialchars($fb->section_name ?? ''); ?>"
                   data-type="<?php echo htmlspecialchars($fb->feedback_type); ?>"
                   data-rating="<?php echo $fb->rating ?? 0; ?>"
                   data-comment="<?php echo htmlspecialchars($fb->comment); ?>">
                <div class="d-flex w-100 align-items-start">
                  <div class="student-avatar me-3">
                    <?php if($fb->is_anonymous): ?>
                      <i class="bi bi-incognito"></i>
                    <?php else: ?>
                      <?php echo $initials; ?>
                    <?php endif; ?>
                  </div>
                  <div class="flex-grow-1">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                      <div>
                        <h6 class="mb-1 fw-bold">
                          <?php echo htmlspecialchars($studentName); ?>
                          <?php if(!$fb->is_anonymous && $fb->student_number): ?>
                            <small class="text-muted fw-normal">(<?php echo htmlspecialchars($fb->student_number); ?>)</small>
                          <?php endif; ?>
                        </h6>
                        <div>
                          <span class="badge bg-<?php echo $feedbackTypeColor; ?> me-2">
                            <?php echo ucfirst(str_replace('_', ' ', $fb->feedback_type)); ?>
                          </span>
                          <?php if($fb->section_name): ?>
                            <span class="badge bg-light text-dark">
                              <i class="bi bi-book me-1"></i><?php echo htmlspecialchars($fb->section_name); ?>
                            </span>
                          <?php endif; ?>
                        </div>
                      </div>
                      <small class="text-muted">
                        <i class="bi bi-calendar-event me-1"></i>
                        <?php echo date('M d, Y g:i A', strtotime($fb->created_at)); ?>
                      </small>
                    </div>
                    
                    <div class="bg-light p-3 rounded mt-2">
                      <p class="mb-0"><?php echo nl2br(htmlspecialchars($fb->comment)); ?></p>
                    </div>
                    
                    <?php if($fb->rating): ?>
                      <div class="mt-2">
                        <strong class="me-2">Rating:</strong>
                        <?php for($i = 1; $i <= 5; $i++): ?>
                          <i class="bi bi-star<?php echo $i <= $fb->rating ? '-fill' : ''; ?> text-warning"></i>
                        <?php endfor; ?>
                        <span class="text-muted ms-2">(<?php echo $fb->rating; ?>/5)</span>
                      </div>
                    <?php endif; ?>
                  </div>
                </div>
              </div>
              <?php endforeach; ?>
            <?php else: ?>
              <div class="alert alert-info text-center py-5">
                <i class="bi bi-inbox fs-1 d-block mb-3"></i>
                <h5>No Feedback Received Yet</h5>
                <p class="mb-0">Student feedback will appear here once submitted.</p>
              </div>
            <?php endif; ?>
          </div>

        </div>
      </div>
    </div>
  </div>



  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Filter Feedback
    function filterFeedback() {
      const searchTerm = document.getElementById('searchInput').value.toLowerCase();
      const typeFilter = document.getElementById('typeFilter').value;
      const classFilter = document.getElementById('classFilter').value;
      const ratingFilter = document.getElementById('ratingFilter').value;
      
      const feedbackCards = document.querySelectorAll('.feedback-card');
      let visibleCount = 0;
      
      feedbackCards.forEach(card => {
        const studentName = card.getAttribute('data-student').toLowerCase();
        const comment = card.getAttribute('data-comment').toLowerCase();
        const cardType = card.getAttribute('data-type');
        const cardClass = card.getAttribute('data-class');
        const cardRating = card.getAttribute('data-rating');
        
        let show = true;
        
        // Search filter
        if (searchTerm && !studentName.includes(searchTerm) && !comment.includes(searchTerm)) {
          show = false;
        }
        
        // Type filter
        if (typeFilter && cardType !== typeFilter) {
          show = false;
        }
        
        // Class filter
        if (classFilter && cardClass !== classFilter) {
          show = false;
        }
        
        // Rating filter
        if (ratingFilter && cardRating !== ratingFilter) {
          show = false;
        }
        
        card.style.display = show ? 'block' : 'none';
        if (show) visibleCount++;
      });
      
      // Show message if no results
      const existingMsg = document.getElementById('noResultsMessage');
      if (existingMsg) existingMsg.remove();
      
      if (visibleCount === 0 && feedbackCards.length > 0) {
        const msg = document.createElement('div');
        msg.id = 'noResultsMessage';
        msg.className = 'alert alert-warning text-center';
        msg.innerHTML = '<i class="bi bi-search me-2"></i>No feedback found matching your filters.';
        document.getElementById('feedbackList').appendChild(msg);
      }
    }
    
    // Export Feedback to Excel
    function exportFeedbackExcel() {
      const wb = XLSX.utils.book_new();
      
      const data = [
        ['Student Feedback Report'],
        ['Generated: ' + new Date().toLocaleString()],
        [''],
        ['Student Name', 'Student Number', 'Class', 'Feedback Type', 'Rating', 'Comment', 'Date', 'Anonymous']
      ];
      
      // Get all visible feedback cards
      const feedbackCards = document.querySelectorAll('.feedback-card');
      feedbackCards.forEach(card => {
        if (card.style.display !== 'none') {
          const student = card.getAttribute('data-student');
          const studentNum = card.querySelector('.text-muted.fw-normal')?.textContent.replace(/[()]/g, '') || '';
          const className = card.getAttribute('data-class') || 'N/A';
          const type = card.getAttribute('data-type');
          const rating = card.getAttribute('data-rating') + '/5';
          const comment = card.getAttribute('data-comment');
          const dateText = card.querySelector('.bi-calendar-event')?.parentElement?.textContent.trim() || '';
          const isAnonymous = student.includes('Anonymous') ? 'Yes' : 'No';
          
          data.push([student, studentNum, className, type, rating, comment, dateText, isAnonymous]);
        }
      });
      
      const ws = XLSX.utils.aoa_to_sheet(data);
      ws['!cols'] = [
        { wch: 20 }, // Student Name
        { wch: 15 }, // Student Number
        { wch: 15 }, // Class
        { wch: 15 }, // Feedback Type
        { wch: 10 }, // Rating
        { wch: 50 }, // Comment
        { wch: 20 }, // Date
        { wch: 10 }  // Anonymous
      ];
      
      XLSX.utils.book_append_sheet(wb, ws, 'Feedback');
      XLSX.writeFile(wb, 'student-feedback-' + new Date().toISOString().split('T')[0] + '.xlsx');
      
      alert('Feedback exported successfully!');
    }
  </script>
</body>
</html>