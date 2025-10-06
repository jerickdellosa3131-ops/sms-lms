<?php
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

// Get authenticated user
$user = Auth::user();

// Fetch analytics data
$activeStudents = DB::table('users')->where('user_type', 'student')->where('status', 'active')->count();
$totalMaterials = DB::table('lesson_materials')->count();

// Try to get average quiz score, handle if table doesn't exist
try {
    $averageQuizScore = DB::table('quiz_submissions')
        ->where('status', 'graded')
        ->avg('score') ?? 0;
} catch (\Exception $e) {
    $averageQuizScore = 0;
}

$totalAssignments = DB::table('assignments')->count();
$submittedAssignments = DB::table('assignment_submissions')->count();
$assignmentSubmissionRate = $totalAssignments > 0 ? round(($submittedAssignments / $totalAssignments) * 100) : 0;

// Recent activity
$recentSubmissions = DB::table('assignment_submissions')
    ->join('users', 'assignment_submissions.student_id', '=', 'users.user_id')
    ->join('assignments', 'assignment_submissions.assignment_id', '=', 'assignments.assignment_id')
    ->select(
        'users.first_name',
        'users.last_name',
        'assignments.title',
        'assignment_submissions.submitted_at',
        'assignment_submissions.score'
    )
    ->orderBy('assignment_submissions.submitted_at', 'desc')
    ->limit(5)
    ->get();
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
                <!-- Header -->
                <div class="container my-4">
  <div class="card shadow-lg border-0 rounded-4">
    <div class="card-body p-4">
      <!-- Header -->
      <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="d-flex align-items-center">
          <i class="bi bi-bar-chart-fill text-danger fs-1 me-3"></i>
          <h3 class="mb-0 fw-bold">View Analytics</h3>
        </div>
        <div class="btn-group">
          <button class="btn btn-danger text-white" onclick="downloadAnalyticsReport()">
            <i class="bi bi-download me-2"></i> Download Report
          </button>
          <button type="button" class="btn btn-danger dropdown-toggle dropdown-toggle-split text-white" data-bs-toggle="dropdown">
            <span class="visually-hidden">Toggle Dropdown</span>
          </button>
          <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="#" onclick="downloadCSV(); return false;"><i class="bi bi-filetype-csv me-2"></i>Download as CSV</a></li>
            <li><a class="dropdown-item" href="#" onclick="downloadPDF(); return false;"><i class="bi bi-filetype-pdf me-2"></i>Download as PDF</a></li>
            <li><a class="dropdown-item" href="#" onclick="downloadExcel(); return false;"><i class="bi bi-filetype-xlsx me-2"></i>Download as Excel</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item" href="#" onclick="printReport(); return false;"><i class="bi bi-printer me-2"></i>Print Report</a></li>
          </ul>
        </div>
      </div>

      <!-- Overview Stats -->
      <div class="row g-3 text-center mb-4">
        <div class="col-md-3">
          <div class="border rounded-3 p-3 bg-light">
            <i class="bi bi-people-fill fs-2 text-primary"></i>
            <h5 class="fw-bold mt-2"><?php echo number_format($activeStudents); ?></h5>
            <p class="small text-muted mb-0">Active Students</p>
          </div>
        </div>
        <div class="col-md-3">
          <div class="border rounded-3 p-3 bg-light">
            <i class="bi bi-file-earmark-text-fill fs-2 text-success"></i>
            <h5 class="fw-bold mt-2"><?php echo number_format($totalMaterials); ?></h5>
            <p class="small text-muted mb-0">Total Materials</p>
          </div>
        </div>
        <div class="col-md-3">
          <div class="border rounded-3 p-3 bg-light">
            <i class="bi bi-pencil-square fs-2 text-warning"></i>
            <h5 class="fw-bold mt-2"><?php echo round($averageQuizScore, 1); ?>%</h5>
            <p class="small text-muted mb-0">Average Quiz Score</p>
          </div>
        </div>
        <div class="col-md-3">
          <div class="border rounded-3 p-3 bg-light">
            <i class="bi bi-file-check fs-2 text-info"></i>
            <h5 class="fw-bold mt-2"><?php echo $assignmentSubmissionRate; ?>%</h5>
            <p class="small text-muted mb-0">Submission Rate</p>
          </div>
        </div>
      </div>

      <!-- Data Table -->
      <h5 class="fw-bold mb-3">Recent Assignment Submissions</h5>
      <div class="table-responsive">
        <table class="table table-hover align-middle">
          <thead class="table-danger">
            <tr>
              <th>Date</th>
              <th>Student</th>
              <th>Assignment</th>
              <th>Score</th>
            </tr>
          </thead>
          <tbody>
            <?php if($recentSubmissions->count() > 0): ?>
              <?php foreach($recentSubmissions as $submission): ?>
              <tr>
                <td><?php echo date('M d, Y', strtotime($submission->submitted_at)); ?></td>
                <td><?php echo htmlspecialchars($submission->first_name . ' ' . $submission->last_name); ?></td>
                <td><i class="bi bi-file-earmark-text me-2 text-info"></i><?php echo htmlspecialchars($submission->title); ?></td>
                <td>
                  <?php if($submission->score !== null): ?>
                    <span class="badge bg-<?php echo $submission->score >= 75 ? 'success' : 'warning'; ?>">
                      <?php echo round($submission->score, 1); ?>%
                    </span>
                  <?php else: ?>
                    <span class="badge bg-secondary">Not Graded</span>
                  <?php endif; ?>
                </td>
              </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="4" class="text-center text-muted">No recent submissions</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>

      <!-- Summary Insights -->
      <div class="mt-4">
        <h6 class="fw-bold">Insights</h6>
        <ul class="list-unstyled text-muted">
          <li><i class="bi bi-check-circle-fill text-success me-2"></i> Student engagement is high this week.</li>
          <li><i class="bi bi-exclamation-circle-fill text-warning me-2"></i> Average quiz scores have slightly dropped compared to last month.</li>
          <li><i class="bi bi-lightbulb-fill text-info me-2"></i> Consider uploading more practice quizzes for Math and Science.</li>
        </ul>
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
<script>
  // Analytics data
  const analyticsData = {
    overview: {
      activeStudents: 150,
      materialsAccessed: 320,
      avgQuizScore: '85%',
      avgTimeSpent: '12h'
    },
    activities: [
      { date: '2025-08-08', activity: 'Material Uploaded', details: 'Physics Module 2', users: 'Mr. Reyes' },
      { date: '2025-08-08', activity: 'Quiz Submitted', details: 'Math Quiz 3', users: '35 Students' },
      { date: '2025-08-07', activity: 'New Students Joined', details: '5 Enrollments', users: 'Admin' }
    ]
  };

  // Download as CSV
  function downloadCSV() {
    let csv = 'Analytics Report\n\n';
    csv += 'Overview Statistics\n';
    csv += 'Metric,Value\n';
    csv += `Active Students,${analyticsData.overview.activeStudents}\n`;
    csv += `Materials Accessed,${analyticsData.overview.materialsAccessed}\n`;
    csv += `Average Quiz Score,${analyticsData.overview.avgQuizScore}\n`;
    csv += `Average Time Spent,${analyticsData.overview.avgTimeSpent}\n\n`;
    
    csv += 'Recent Activities\n';
    csv += 'Date,Activity,Details,Users Involved\n';
    analyticsData.activities.forEach(activity => {
      csv += `${activity.date},${activity.activity},${activity.details},${activity.users}\n`;
    });

    downloadFile(csv, 'analytics-report.csv', 'text/csv');
    showNotification('CSV report downloaded successfully!');
  }

  // Download as PDF (simulated - in production use a library like jsPDF)
  function downloadPDF() {
    alert('PDF Download:\n\nIn production, this would generate a PDF report using libraries like jsPDF or server-side PDF generation.\n\nFor now, downloading as text file...');
    
    let content = '=== ANALYTICS REPORT ===\n\n';
    content += 'OVERVIEW STATISTICS\n';
    content += '-------------------\n';
    content += `Active Students: ${analyticsData.overview.activeStudents}\n`;
    content += `Materials Accessed: ${analyticsData.overview.materialsAccessed}\n`;
    content += `Average Quiz Score: ${analyticsData.overview.avgQuizScore}\n`;
    content += `Average Time Spent: ${analyticsData.overview.avgTimeSpent}\n\n`;
    
    content += 'RECENT ACTIVITIES\n';
    content += '-----------------\n';
    analyticsData.activities.forEach(activity => {
      content += `${activity.date} - ${activity.activity}\n`;
      content += `  Details: ${activity.details}\n`;
      content += `  Users: ${activity.users}\n\n`;
    });

    downloadFile(content, 'analytics-report.txt', 'text/plain');
    showNotification('Report downloaded! (PDF generation requires jsPDF library)');
  }

  // Download as Excel (Real XLSX file)
  function downloadExcel() {
    // Create a new workbook
    const wb = XLSX.utils.book_new();
    
    // Overview Sheet
    const overviewData = [
      ['Analytics Report - Overview'],
      [''],
      ['Metric', 'Value'],
      ['Active Students', analyticsData.overview.activeStudents],
      ['Materials Accessed', analyticsData.overview.materialsAccessed],
      ['Average Quiz Score', analyticsData.overview.avgQuizScore],
      ['Average Time Spent', analyticsData.overview.avgTimeSpent]
    ];
    const ws1 = XLSX.utils.aoa_to_sheet(overviewData);
    
    // Set column widths
    ws1['!cols'] = [{ wch: 25 }, { wch: 15 }];
    
    XLSX.utils.book_append_sheet(wb, ws1, 'Overview');
    
    // Activities Sheet
    const activitiesData = [
      ['Recent Activities'],
      [''],
      ['Date', 'Activity', 'Details', 'Users Involved']
    ];
    
    analyticsData.activities.forEach(activity => {
      activitiesData.push([activity.date, activity.activity, activity.details, activity.users]);
    });
    
    const ws2 = XLSX.utils.aoa_to_sheet(activitiesData);
    ws2['!cols'] = [{ wch: 12 }, { wch: 20 }, { wch: 20 }, { wch: 15 }];
    
    XLSX.utils.book_append_sheet(wb, ws2, 'Recent Activities');
    
    // Generate and download
    XLSX.writeFile(wb, 'analytics-report.xlsx');
    showNotification('Excel report downloaded successfully!');
  }

  // Default download (CSV)
  function downloadAnalyticsReport() {
    downloadCSV();
  }

  // Print Report
  function printReport() {
    window.print();
  }

  // Helper function to download file
  function downloadFile(content, filename, mimeType) {
    const blob = new Blob([content], { type: mimeType });
    const url = URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    link.download = filename;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    URL.revokeObjectURL(url);
  }

  // Show notification
  function showNotification(message) {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = 'alert alert-success alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3';
    notification.style.zIndex = '9999';
    notification.innerHTML = `
      <i class="bi bi-check-circle-fill me-2"></i>${message}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(notification);
    
    // Auto remove after 3 seconds
    setTimeout(() => {
      notification.remove();
    }, 3000);
  }
</script>

</html>