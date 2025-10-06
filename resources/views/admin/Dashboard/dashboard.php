<?php
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

// Get authenticated user
$user = Auth::user();

// Fetch dashboard statistics
$totalStudents = DB::table('users')->where('user_type', 'student')->where('status', 'active')->count();
$newStudents = DB::table('users')->where('user_type', 'student')->where('status', 'active')->where('created_at', '>=', now()->subDays(30))->count();
$totalCourses = DB::table('classes')->where('status', 'active')->count();
$upcomingDeadlines = DB::table('assignments')->where('due_date', '>=', now())->where('due_date', '<=', now()->addDays(7))->count();

// Fetch recent students
$recentStudents = DB::table('users')
    ->leftJoin('students', 'users.user_id', '=', 'students.user_id')
    ->where('users.user_type', 'student')
    ->where('users.status', 'active')
    ->select('users.user_id', 'users.first_name', 'users.last_name', 'students.student_number', 'users.created_at')
    ->orderBy('users.created_at', 'desc')
    ->limit(3)
    ->get();

// Fetch recent uploads
$recentUploads = DB::table('lesson_materials')
    ->join('users', 'lesson_materials.teacher_id', '=', 'users.user_id')
    ->select('lesson_materials.*', 'users.first_name', 'users.last_name')
    ->orderBy('lesson_materials.created_at', 'desc')
    ->limit(2)
    ->get();

// Fetch announcements (you can create an announcements table or use this placeholder)
$announcements = [
    ['title' => 'Midterm Grades Published', 'date' => 'Oct 1, 2025', 'icon' => 'clipboard-list'],
    ['title' => 'Parent-Teacher Meeting', 'date' => 'Oct 10, 2025', 'icon' => 'bullhorn'],
    ['title' => 'Project Proposals Due', 'date' => 'Oct 15, 2025', 'icon' => 'file-signature']
];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo csrf_token(); ?>">
    <title>SMS3</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../style.css?v=1.1">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- SweetAlert2 for Alerts -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- SheetJS for Excel Export -->
    <script src="https://cdn.sheetjs.com/xlsx-0.20.0/package/dist/xlsx.full.min.js"></script>
    
    <!-- Admin Actions Library -->
    <script src="<?php echo asset('js/admin-actions.js'); ?>"></script>
    
    <style>
        @import url("../../style.css");

        /* --- Enhanced Global Styles --- */
        :root {
            --primary-purple: #7367f0;
            --primary-orange: #ff9f43;
            --primary-cyan: #00cfe8;
            --primary-pink: #ea5455;
            --primary-green: #28c76f;
            --bg-color: #f8f9fe;
            --card-bg-color: #ffffff;
            --text-color-dark: #4b4b4b;
            --text-color-light: #a9a9a9;
            --border-color: #ebeef0;
            --shadow: 0 4px 24px 0 rgba(34, 41, 47, 0.08); /* Softer shadow */
            --border-radius: 0.75rem;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--bg-color);
            color: var(--text-color-dark);
            margin: 0;
        }

        .dashboard-main {
            padding: 2rem;
            display: grid;
            gap: 1.75rem; /* Increased gap for better spacing */
            grid-template-columns: repeat(4, 1fr);
        }

        .card {
            background-color: var(--card-bg-color);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            border: 1px solid var(--border-color); /* Subtle border */
            padding: 1.5rem;
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem; /* Increased margin */
            padding-bottom: 0.75rem;
            border-bottom: 1px solid var(--border-color);
        }

        .card-header h3 {
            margin: 0;
            font-size: 1.1rem;
            font-weight: 600;
        }

        /* --- Enhanced Summary Cards --- */
        .summary-card {
            border: none; /* Summary cards don't need the extra border */
        }
        .summary-card h4 {
            margin: 0 0 0.5rem 0;
            font-size: 0.9rem;
            color: var(--text-color-light);
            font-weight: 500;
        }

        .summary-card .value {
            font-size: 1.75rem;
            font-weight: 600;
            margin: 0;
        }

        .summary-card .increase {
            color: var(--text-color-light);
            font-size: 0.85rem;
            margin: 0.25rem 0 0 0;
        }

        .summary-card-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        /* ENHANCED: Icons are now in rounded squares */
        .summary-icon {
            font-size: 1.75rem;
            width: 60px;
            height: 60px;
            border-radius: 0.5rem; /* Rounded square */
            color: #fff;
            display: grid;
            place-items: center;
        }
        .icon-new-students { background-color: var(--primary-purple); }
        .icon-total-students { background-color: var(--primary-orange); }
        .icon-total-courses { background-color: var(--primary-pink); }
        .icon-deadlines { background-color: var(--primary-green); }

        /* --- Student Activist Card --- */
        .activist-card {
            grid-column: span 3;
        }
        .legend { display: flex; gap: 1rem; }
        .legend span { display: flex; align-items: center; font-size: 0.9rem; }
        .legend span::before { content: ''; width: 12px; height: 12px; border-radius: 3px; margin-right: 0.5rem; }
        .legend .assignments::before { background-color: var(--primary-purple); }
        .legend .quizzes::before { background-color: var(--primary-cyan); }
        .chart-container { position: relative; height: 280px; width: 100%; }

        /* --- Announcements Card (Formerly Deadlines/Exams) --- */
        .announcements-card {
            grid-column: span 1;
        }
        .announcements-list {
            list-style: none;
            padding: 0;
            margin: 0;
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }
        .announcements-list li {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        .announcement-icon {
            font-size: 1.25rem;
            width: 35px;
            height: 35px;
            border-radius: 8px;
            display: grid;
            place-items: center;
            background-color: var(--bg-color);
        }
        .icon-purple { color: var(--primary-purple); }
        .icon-pink { color: var(--primary-pink); }
        .announcement-info .task { font-weight: 500; font-size: 0.9rem; line-height: 1.3; }
        .announcement-info .date { color: var(--text-color-light); font-size: 0.8rem; }

        /* --- Students List Card --- */
        .students-card { grid-column: span 2; }
        .students-table { width: 100%; border-collapse: collapse; }
        .students-table th, .students-table td { text-align: left; padding: 1rem 0.5rem; font-size: 0.9rem; border-bottom: 1px solid var(--border-color); }
        .students-table th {
            color: var(--text-color-light);
            font-weight: 500;
            text-transform: uppercase;
            font-size: 0.75rem;
        }
        .students-table tbody tr:last-child td { border-bottom: none; }
        
        /* --- Teachers Uploads Card --- */
        .uploads-card { grid-column: span 2; }
        .uploads-list { list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 1.5rem; }
        .uploads-list li { display: flex; justify-content: space-between; align-items: center; }
        .upload-info { display: flex; align-items: center; gap: 1rem; }
        /* REMOVED: Avatar styling */
        .file-details .teacher-name { font-weight: 600; font-size: 0.9rem; }
        .file-details .file-name { font-size: 0.8rem; color: var(--text-color-light); }
        .file-type { text-align: right; }
        .file-type .icon { font-size: 1.75rem; margin-bottom: 0.25rem; }
        .file-type .date { font-size: 0.8rem; color: var(--text-color-light); }

        /* Responsive adjustments */
        @media (max-width: 1200px) {
            .dashboard-main { grid-template-columns: repeat(2, 1fr); }
            .activist-card, .announcements-card, .students-card, .uploads-card { grid-column: span 2; }
        }
        @media (max-width: 768px) {
            .dashboard-main { grid-template-columns: 1fr; }
            .summary-card, .activist-card, .announcements-card, .students-card, .uploads-card { grid-column: span 1; }
        }
    </style>
</head>

<body>

    <?php include resource_path('views/includes/sidenav_admin.php'); ?>
    <div class="main-content flex-grow-1">
        <div class="container">
            <div class="row">
                <div class="col">
                    <div class="container my-5">
                        <div class="card shadow-lg border-0 rounded-4">
                            <div class="card-body p-4">
    
    <main class="dashboard-main">

        <div class="card summary-card">
            <div class="summary-card-content">
                <div>
                    <h4>New Students</h4>
                    <h2 class="value"><?php echo number_format($newStudents); ?></h2>
                    <p class="increase">Last 30 days</p>
                </div>
                <div class="summary-icon icon-new-students"><i class="fa-solid fa-user-plus"></i></div>
            </div>
        </div>

        <div class="card summary-card">
            <div class="summary-card-content">
                <div>
                    <h4>Total Students</h4>
                    <h2 class="value"><?php echo number_format($totalStudents); ?></h2>
                    <p class="increase">Active students</p>
                </div>
                <div class="summary-icon icon-total-students"><i class="fa-solid fa-users"></i></div>
            </div>
        </div>

        <div class="card summary-card">
            <div class="summary-card-content">
                <div>
                    <h4>Total Courses</h4>
                    <h2 class="value"><?php echo number_format($totalCourses); ?></h2>
                    <p class="increase">Active courses</p>
                </div>
                <div class="summary-icon icon-total-courses"><i class="fa-solid fa-book"></i></div>
            </div>
        </div>

        <div class="card summary-card">
            <div class="summary-card-content">
                <div>
                    <h4>Deadlines</h4>
                    <h2 class="value"><?php echo number_format($upcomingDeadlines); ?></h2>
                    <p class="increase">Next 7 days</p>
                </div>
                <div class="summary-icon icon-deadlines"><i class="fa-solid fa-calendar-check"></i></div>
            </div>
        </div>

        <div class="card activist-card">
            <div class="card-header">
                <h3>Student Performance</h3>
                <div class="legend">
                    <span class="assignments">Assignments</span>
                    <span class="quizzes">Quizzes</span>
                </div>
            </div>
            <div class="chart-container">
                <canvas id="studentActivistChart"></canvas>
            </div>
        </div>
        
        <div class="card announcements-card">
            <div class="card-header">
                <h3>Announcements</h3>
            </div>
            <ul class="announcements-list">
                <?php foreach($announcements as $announcement): ?>
                <li>
                    <div class="announcement-icon icon-<?php echo $announcement['icon'] == 'bullhorn' ? 'pink' : 'purple'; ?>">
                        <i class="fa-solid fa-<?php echo $announcement['icon']; ?>"></i>
                    </div>
                    <div class="announcement-info">
                        <div class="task"><?php echo htmlspecialchars($announcement['title']); ?></div>
                        <div class="date"><?php echo htmlspecialchars($announcement['date']); ?></div>
                    </div>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <div class="card students-card">
            <div class="card-header">
                <h3>Students Lists</h3>
            </div>
            <table class="students-table">
                <thead>
                    <tr><th>ID</th><th>NAME</th><th>STUDENT #</th><th>JOINED</th></tr>
                </thead>
                <tbody>
                    <?php if($recentStudents->count() > 0): ?>
                        <?php foreach($recentStudents as $student): ?>
                        <tr>
                            <td>#<?php echo $student->user_id; ?></td>
                            <td><?php echo htmlspecialchars($student->first_name . ' ' . $student->last_name); ?></td>
                            <td><?php echo htmlspecialchars($student->student_number ?? 'N/A'); ?></td>
                            <td><?php echo date('M d, Y', strtotime($student->created_at)); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="4" class="text-center text-muted">No recent students</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="card uploads-card">
            <div class="card-header">
                <h3>Teacher Uploads</h3>
            </div>
            <ul class="uploads-list">
                <?php if($recentUploads->count() > 0): ?>
                    <?php foreach($recentUploads as $upload): 
                        $extension = strtolower(pathinfo($upload->file_path, PATHINFO_EXTENSION));
                        $iconColor = '#7367f0';
                        $iconClass = 'file';
                        
                        if($extension == 'pdf') {
                            $iconColor = '#ea5455';
                            $iconClass = 'file-pdf';
                        } elseif(in_array($extension, ['doc', 'docx'])) {
                            $iconColor = '#7367f0';
                            $iconClass = 'file-word';
                        } elseif(in_array($extension, ['xls', 'xlsx'])) {
                            $iconColor = '#28c76f';
                            $iconClass = 'file-excel';
                        } elseif(in_array($extension, ['ppt', 'pptx'])) {
                            $iconColor = '#ff9f43';
                            $iconClass = 'file-powerpoint';
                        }
                    ?>
                    <li>
                        <div class="upload-info">
                            <div class="file-details">
                                <div class="teacher-name"><?php echo htmlspecialchars($upload->first_name . ' ' . $upload->last_name); ?></div>
                                <div class="file-name"><?php echo htmlspecialchars($upload->material_title ?? basename($upload->file_path)); ?></div>
                            </div>
                        </div>
                        <div class="file-type">
                            <div class="icon" style="color: <?php echo $iconColor; ?>;"><i class="fa-solid fa-<?php echo $iconClass; ?>"></i></div>
                            <div class="date"><?php echo date('M d, Y', strtotime($upload->created_at)); ?></div>
                        </div>
                    </li>
                    <?php endforeach; ?>
                <?php else: ?>
                    <li class="text-center text-muted">No recent uploads</li>
                <?php endif; ?>
            </ul>
        </div>
    </main>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('studentActivistChart').getContext('2d');
            
            const labels = ['Week 1', 'Week 2', 'Week 3', 'Week 4', 'Week 5', 'Week 6'];
            const assignmentsData = [65, 59, 80, 81, 56, 55];
            const quizzesData = [75, 69, 90, 88, 66, 70];

            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Assignments',
                        data: assignmentsData,
                        backgroundColor: 'rgba(115, 103, 240, 0.8)',
                        borderWidth: 0,
                        borderRadius: 5,
                        barPercentage: 0.5
                    }, {
                        label: 'Quizzes',
                        data: quizzesData,
                        backgroundColor: 'rgba(0, 207, 232, 0.8)',
                        borderWidth: 0,
                        borderRadius: 5,
                        barPercentage: 0.5
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: { color: '#ebeef0' },
                            ticks: { callback: function(value) { return value + '%'; } }
                        },
                        x: { grid: { display: false } }
                    }
                }
            });
        });
    </script>
    
                            </main>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>