<?php
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

$user = Auth::user();
$student_id = $user ? $user->user_id : null;

if ($student_id) {
    // Fetch assignments for enrolled classes
    $assignments = DB::table('assignments')
        ->join('classes', 'assignments.class_id', '=', 'classes.class_id')
        ->join('class_enrollments', 'classes.class_id', '=', 'class_enrollments.class_id')
        ->leftJoin('assignment_submissions', function($join) use ($student_id) {
            $join->on('assignments.assignment_id', '=', 'assignment_submissions.assignment_id')
                 ->where('assignment_submissions.student_id', '=', $student_id);
        })
        ->where('class_enrollments.student_id', $student_id)
        ->where('assignments.status', 'published')
        ->select(
            'assignments.*',
            'classes.section_name',
            'classes.class_code',
            'assignment_submissions.status as submission_status',
            'assignment_submissions.submitted_at',
            'assignment_submissions.score'
        )
        ->orderBy('assignments.due_date', 'asc')
        ->get();
} else {
    $assignments = collect();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?php echo asset('style.css'); ?>">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
  
  <!-- Student Actions Library -->
  <script src="<?php echo asset('student-actions.js'); ?>"></script>
  
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

    <?php include resource_path('views/includes/sidenav_student.php'); ?>


    <!-- Header -->
    <div class="main-content flex-grow-1">
        <div class="container my-5">
            <div class="card shadow-lg border-0 rounded-4">
                <div class="card-body p-4">

                    <!-- Header -->
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-pencil-square text-success fs-1 me-3"></i>
                            <div>
                                <h3 class="mb-0 fw-bold">Assignment Submission</h3>
                                <p class="text-muted small mb-0">Submit your assignment files below</p>
                            </div>
                        </div>
                        <a href="<?php echo route('student.class-portal'); ?>" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-2"></i> Back to Class Portal
                        </a>
                    </div>

                    <!-- Assignments List -->
                    <?php if($assignments->count() > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-success">
                                    <tr>
                                        <th>Class</th>
                                        <th>Assignment</th>
                                        <th>Due Date</th>
                                        <th>Points</th>
                                        <th>Status</th>
                                        <th>Score</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($assignments as $assignment): 
                                        $isOverdue = strtotime($assignment->due_date) < time();
                                        $statusBadge = 'bg-warning';
                                        $statusText = 'Pending';
                                        
                                        if ($assignment->submission_status == 'graded') {
                                            $statusBadge = 'bg-success';
                                            $statusText = 'Graded';
                                        } elseif ($assignment->submission_status == 'submitted') {
                                            $statusBadge = 'bg-info';
                                            $statusText = 'Submitted';
                                        } elseif ($isOverdue && !$assignment->submission_status) {
                                            $statusBadge = 'bg-danger';
                                            $statusText = 'Overdue';
                                        }
                                    ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($assignment->section_name); ?></td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($assignment->title); ?></strong>
                                            <?php if($assignment->description): ?>
                                            <br><small class="text-muted"><?php echo htmlspecialchars(substr($assignment->description, 0, 50)); ?>...</small>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo date('M d, Y g:i A', strtotime($assignment->due_date)); ?></td>
                                        <td><?php echo $assignment->total_points; ?></td>
                                        <td><span class="badge <?php echo $statusBadge; ?>"><?php echo $statusText; ?></span></td>
                                        <td>
                                            <?php if($assignment->score): ?>
                                                <strong class="<?php echo $assignment->score >= 75 ? 'text-success' : 'text-warning'; ?>"><?php echo $assignment->score; ?></strong>
                                            <?php else: ?>
                                                <span class="text-muted">N/A</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if(!$assignment->submission_status): ?>
                                                <button class="btn btn-sm btn-primary" onclick="submitAssignment(<?php echo $assignment->assignment_id; ?>, '<?php echo addslashes($assignment->title); ?>')">
                                                    <i class="bi bi-upload me-1"></i>Submit
                                                </button>
                                            <?php else: ?>
                                                <button class="btn btn-sm btn-success" onclick="viewRecord(<?php echo $assignment->assignment_id; ?>, 'Submission', {assignment: '<?php echo addslashes($assignment->title); ?>', submitted: '<?php echo date('M d, Y g:i A', strtotime($assignment->submitted_at)); ?>', score: '<?php echo $assignment->score ?? 'Not graded'; ?>'})">
                                                    <i class="bi bi-check-circle me-1"></i>Submitted
                                                </button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i>No assignments available yet.
                        </div>
                    <?php endif; ?>

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