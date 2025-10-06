<?php
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

// Get authenticated user
$user = Auth::user();

// Fetch all classes with teacher and subject information
$classes = DB::table('classes')
    ->join('users as teachers', 'classes.teacher_id', '=', 'teachers.user_id')
    ->leftJoin('subjects', 'classes.subject_id', '=', 'subjects.subject_id')
    ->select(
        'classes.*',
        'teachers.first_name as teacher_first',
        'teachers.last_name as teacher_last',
        'subjects.subject_name',
        DB::raw('(SELECT COUNT(*) FROM class_enrollments WHERE class_enrollments.class_id = classes.class_id AND class_enrollments.status = "enrolled") as enrolled_count')
    )
    ->orderBy('classes.created_at', 'desc')
    ->get();

// Count statistics
$totalClasses = $classes->count();
$activeClasses = $classes->where('status', 'active')->count();
$totalEnrollments = $classes->sum('enrolled_count');
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
     <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- SweetAlert2 for Alerts -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- SheetJS for Excel Export -->
    <script src="https://cdn.sheetjs.com/xlsx-0.20.0/package/dist/xlsx.full.min.js"></script>
    
    <!-- Admin Actions Library -->
    <script src="<?php echo asset('js/admin-actions.js'); ?>"></script>
    
    <style>
      @import url("../style.css");

      /* Make sidebar scrollable in landscape mode on mobile devices */
      @media (max-width: 768px) and (orientation: landscape) {
        .sidebar {
          max-height: 100vh;
          overflow-y: auto;
        }
      }

        /*
        =======================================
        Enhanced Global Styles & Colors
        =======================================
        */
        :root {
            /* Main Palette */
            --secondary-color: #6c757d;
            --background-color: #f8f9fc;
            --card-bg: #e9f5fdff;
            --text-dark: #222b45;
            --text-light: #ffffffff;
            --border-color: #eef2f7;
            --shadow: 0 5px 25px rgba(0, 0, 0, 0.04);
            
            /* Theme Colors - Chosen for beauty and accessibility */
            --c-upload-main: #005f69;
            --c-upload-soft: #ddfff9ff;
            
            --c-virtual-main: #1e88e5;
            --c-virtual-soft: #d4ebfcff;

            --c-quizzes-main: #5e35b1;
            --c-quizzes-soft: #ede7f6;
            
            --c-assignments-main: #4a74ffff;
            --c-assignments-soft: #d1dff8ff;

            --c-multimedia-main: #4169E1;
            --c-multimedia-soft: #dfdbfcff;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--background-color);
            color: var(--text-dark);
        }

        /*
        =======================================
        Enhanced Main Layout & Header
        =======================================
        */
        .main-content-wrapper {
            padding: 2.5rem;
        }

        .dashboard-header {
            margin-bottom: 2.5rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid var(--border-color);
        }

        .dashboard-header h1 {
            font-weight: 700;
            font-size: 2.25rem;
        }
        
        .dashboard-header p {
            color: var(--secondary-color);
            font-size: 1.1rem;
        }

        .create-class-btn {
            background-image: linear-gradient(45deg, #0d6efd, #0dcaf0);
            border: none;
            padding: 0.75rem 1.75rem;
            border-radius: 50px;
            font-weight: 600;
            color: var(--text-light);
            box-shadow: 0 4px 15px rgba(0, 123, 255, 0.2);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .create-class-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(0, 123, 255, 0.3);
        }

        /*
        =======================================
        Enhanced Dashboard Cards
        =======================================
        */
        .card-tile {
            background-color: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 1rem;
            box-shadow: var(--shadow);
            transition: transform 0.3s ease, box-shadow 0.3s ease, border-top-color 0.3s ease;
            height: 100%;
            display: flex;
            flex-direction: column;
            border-top: 4px solid var(--border-color); /* Subtle top border */
        }
        
        /* Colored border appears on hover */
        .card-tile:hover {
            transform: translateY(-5px);
            box-shadow: 0 1rem 2rem rgba(0, 0, 0, 0.07);
        }
        .card-upload:hover { border-top-color: var(--c-upload-main); }
        .card-virtual:hover { border-top-color: var(--c-virtual-main); }
        .card-quizzes:hover { border-top-color: var(--c-quizzes-main); }
        .card-assignments:hover { border-top-color: var(--c-assignments-main); }
        .card-multimedia:hover { border-top-color: var(--c-multimedia-main); }


        .card-tile .card-body {
            padding: 1.75rem;
            display: flex;
            flex-direction: column;
            flex-grow: 1;
        }

        .icon-wrapper {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: grid;
            place-items: center;
            margin-bottom: 1.25rem;
        }

        .icon-wrapper i {
            font-size: 1.75rem;
        }

        .card-tile h5 {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .card-tile p {
            font-size: 0.9rem;
            color: var(--secondary-color);
            line-height: 1.6;
            flex-grow: 1;
            margin-bottom: 1.5rem;
        }
        
        .card-tile .btn {
            border-radius: 50px;
            font-weight: 500;
            padding: 0.6rem 1.35rem;
            align-self: flex-start;
            border: none;
            color: var(--text-light);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .card-tile .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        /* Icon Wrapper & Button Color Variants (New Accessible Palette) */
        .icon-upload { background-color: var(--c-upload-soft); color: var(--c-upload-main); }
        .btn-upload { background-color: var(--c-upload-main); }

        .icon-virtual { background-color: var(--c-virtual-soft); color: var(--c-virtual-main); }
        .btn-virtual { background-color: var(--c-virtual-main); }

        .icon-quizzes { background-color: var(--c-quizzes-soft); color: var(--c-quizzes-main); }
        .btn-quizzes { background-color: var(--c-quizzes-main); }

        .icon-assignments { background-color: var(--c-assignments-soft); color: var(--c-assignments-main); }
        .btn-assignments { background-color: var(--c-assignments-main); }
        
        .icon-multimedia { background-color: var(--c-multimedia-soft); color: var(--c-multimedia-main); }
        .btn-multimedia { background-color: var(--c-multimedia-main); }

        @media (max-width: 991px) {
            .main-content-wrapper { padding: 1.5rem; }
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

                  
              <div class="main-content-wrapper">
        <div class="dashboard-header d-flex flex-wrap justify-content-between align-items-center">
            <div>
                <h1>Class Portal</h1>
                <p class="mb-0">Welcome Admin, manage your class resources and activities here.</p>
            </div>
            <div class="mt-3 mt-md-0">
                <a href="<?php echo route('admin.class-portal.create'); ?>" class="btn create-class-btn">
                    <i class="bi bi-plus-lg me-2"></i> Create New Class
                </a>
            </div>
        </div>

        <?php if (session('success')): ?>
        <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
          <i class="bi bi-check-circle me-2"></i>
          <?php echo session('success'); ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <div class="row g-4">
            <div class="col-lg-4 col-md-6">
                <div class="card card-tile card-upload">
                    <div class="card-body">
                        <div class="icon-wrapper icon-upload">
                            <i class="bi bi-cloud-arrow-up-fill"></i>
                        </div>
                        <h5>Upload Materials</h5>
                        <p>Add and organize lesson files, documents, and videos for your students.</p>
                        <button type="button" class="btn btn-upload" data-bs-toggle="modal" data-bs-target="#uploadMaterialsModal">
                          Manage <i class="bi bi-arrow-right-short"></i>
                        </button>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-6">
                <div class="card card-tile card-virtual">
                    <div class="card-body">
                        <div class="icon-wrapper icon-virtual">
                            <i class="bi bi-camera-video-fill"></i>
                        </div>
                        <h5>Virtual Class</h5>
                        <p>Manage links for live virtual classrooms and online sessions.</p>
                        <button type="button" class="btn btn-virtual" data-bs-toggle="modal" data-bs-target="#virtualClassModal">
                          Set Up <i class="bi bi-arrow-right-short"></i>
                        </button>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-6">
                <div class="card card-tile card-quizzes">
                    <div class="card-body">
                        <div class="icon-wrapper icon-quizzes">
                            <i class="bi bi-patch-question-fill"></i>
                        </div>
                        <h5>Quizzes</h5>
                        <p>Create, manage, and automatically grade online quizzes and exams.</p>
                        <button type="button" class="btn btn-quizzes" data-bs-toggle="modal" data-bs-target="#createQuizModal">
                          Create <i class="bi bi-arrow-right-short"></i>
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6">
                <div class="card card-tile card-assignments">
                    <div class="card-body">
                        <div class="icon-wrapper icon-assignments">
                            <i class="bi bi-file-earmark-check-fill"></i>
                        </div>
                        <h5>Assignments</h5>
                        <p>Review and provide feedback on student assignment submissions.</p>
                        <button type="button" class="btn btn-assignments" data-bs-toggle="modal" data-bs-target="#addAssignmentModal">
                          View <i class="bi bi-arrow-right-short"></i>
                        </button>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-6">
                <div class="card card-tile card-multimedia">
                    <div class="card-body">
                        <div class="icon-wrapper icon-multimedia">
                            <i class="bi bi-collection-play-fill"></i>
                        </div>
                        <h5>Multimedia</h5>
                        <p>Upload and manage engaging rich media content for your lessons.</p>
                        <button type="button" class="btn btn-multimedia" data-bs-toggle="modal" data-bs-target="#addMultimediaModal">
                          Add Media <i class="bi bi-arrow-right-short"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Classes List Section -->
        <div class="mt-5">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 class="fw-bold">All Classes (<?php echo $totalClasses; ?>)</h3>
                <div>
                    <button class="btn btn-success me-2" onclick="exportCurrentTable('classesTable', 'classes')">
                        <i class="bi bi-file-earmark-excel me-2"></i>Export
                    </button>
                    <span class="badge bg-success me-2">Active: <?php echo $activeClasses; ?></span>
                    <span class="badge bg-info">Total Enrollments: <?php echo $totalEnrollments; ?></span>
                </div>
            </div>

            <?php if($classes->count() > 0): ?>
            <div class="table-responsive">
                <table id="classesTable" class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Class Code</th>
                            <th>Section</th>
                            <th>Subject</th>
                            <th>Teacher</th>
                            <th>Schedule</th>
                            <th>Enrolled</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($classes as $class): 
                            $statusClass = $class->status == 'active' ? 'success' : 'secondary';
                        ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($class->class_code); ?></strong></td>
                            <td><?php echo htmlspecialchars($class->section_name); ?></td>
                            <td><?php echo htmlspecialchars($class->subject_name ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($class->teacher_first . ' ' . $class->teacher_last); ?></td>
                            <td>
                                <small><?php echo htmlspecialchars($class->schedule_days); ?></small><br>
                                <small class="text-muted"><?php echo date('g:i A', strtotime($class->schedule_time_start)) . ' - ' . date('g:i A', strtotime($class->schedule_time_end)); ?></small>
                            </td>
                            <td><span class="badge bg-primary"><?php echo $class->enrolled_count; ?></span></td>
                            <td><span class="badge bg-<?php echo $statusClass; ?>"><?php echo ucfirst($class->status); ?></span></td>
                            <td>
                                <button class="btn btn-sm btn-outline-info me-1" title="View Details"
                                    onclick="viewRecord(<?php echo $class->class_id; ?>, 'Class', {
                                        'Class Code': '<?php echo addslashes($class->class_code); ?>',
                                        'Section': '<?php echo addslashes($class->section_name); ?>',
                                        'Subject': '<?php echo addslashes($class->subject_name ?? 'N/A'); ?>',
                                        'Teacher': '<?php echo addslashes($class->teacher_first . ' ' . $class->teacher_last); ?>',
                                        'Schedule': '<?php echo addslashes($class->schedule_days); ?>',
                                        'Time': '<?php echo date('g:i A', strtotime($class->schedule_time_start)) . ' - ' . date('g:i A', strtotime($class->schedule_time_end)); ?>',
                                        'Enrolled': '<?php echo $class->enrolled_count; ?>',
                                        'Status': '<?php echo ucfirst($class->status); ?>'
                                    })">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-warning me-1" title="Edit"
                                    onclick="editRecord(<?php echo $class->class_id; ?>, 'Class', {
                                        'class_code': '<?php echo addslashes($class->class_code); ?>',
                                        'section_name': '<?php echo addslashes($class->section_name); ?>',
                                        'schedule_days': '<?php echo addslashes($class->schedule_days); ?>'
                                    })">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger" title="Delete"
                                    onclick="deleteRecord(<?php echo $class->class_id; ?>, 'Class', '<?php echo addslashes($class->section_name); ?>')">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="alert alert-info text-center">
                <i class="bi bi-info-circle me-2"></i>No classes found. Create your first class to get started!
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Upload Materials Modal -->
    <div class="modal fade" id="uploadMaterialsModal" tabindex="-1">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title"><i class="bi bi-cloud-arrow-up-fill me-2"></i>Upload Materials</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <form id="uploadMaterialsForm">
              <div class="mb-3">
                <label for="materialTitle" class="form-label">Material Title <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="materialTitle" required>
              </div>
              <div class="mb-3">
                <label for="materialType" class="form-label">Material Type</label>
                <select class="form-select" id="materialType">
                  <option value="document">Document (PDF, DOC)</option>
                  <option value="presentation">Presentation (PPT)</option>
                  <option value="video">Video</option>
                  <option value="other">Other</option>
                </select>
              </div>
              <div class="mb-3">
                <label for="materialFile" class="form-label">Choose File <span class="text-danger">*</span></label>
                <input type="file" class="form-control" id="materialFile" required>
                <small class="text-muted">Max file size: 50MB</small>
              </div>
              <div class="mb-3">
                <label for="materialDescription" class="form-label">Description</label>
                <textarea class="form-control" id="materialDescription" rows="3"></textarea>
              </div>
            </form>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="button" class="btn btn-primary" onclick="handleUploadMaterial()">
              <i class="bi bi-upload me-2"></i>Upload
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Virtual Class Setup Modal -->
    <div class="modal fade" id="virtualClassModal" tabindex="-1">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title"><i class="bi bi-camera-video-fill me-2"></i>Set Up Virtual Class</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <form id="virtualClassForm">
              <div class="mb-3">
                <label for="meetingTitle" class="form-label">Meeting Title <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="meetingTitle" required>
              </div>
              <div class="mb-3">
                <label for="meetingPlatform" class="form-label">Platform</label>
                <select class="form-select" id="meetingPlatform">
                  <option value="zoom">Zoom</option>
                  <option value="google-meet">Google Meet</option>
                  <option value="microsoft-teams">Microsoft Teams</option>
                  <option value="other">Other</option>
                </select>
              </div>
              <div class="mb-3">
                <label for="meetingLink" class="form-label">Meeting Link <span class="text-danger">*</span></label>
                <input type="url" class="form-control" id="meetingLink" placeholder="https://" required>
              </div>
              <div class="row">
                <div class="col-md-6 mb-3">
                  <label for="meetingDate" class="form-label">Date</label>
                  <input type="date" class="form-control" id="meetingDate">
                </div>
                <div class="col-md-6 mb-3">
                  <label for="meetingTime" class="form-label">Time</label>
                  <input type="time" class="form-control" id="meetingTime">
                </div>
              </div>
              <div class="mb-3">
                <label for="meetingPassword" class="form-label">Meeting Password (Optional)</label>
                <input type="text" class="form-control" id="meetingPassword">
              </div>
            </form>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="button" class="btn btn-primary" onclick="handleVirtualClass()">
              <i class="bi bi-check-lg me-2"></i>Save
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Create Quiz Modal -->
    <div class="modal fade" id="createQuizModal" tabindex="-1">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title"><i class="bi bi-patch-question-fill me-2"></i>Create New Quiz</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <form id="createQuizForm">
              <div class="mb-3">
                <label for="quizTitle" class="form-label">Quiz Title <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="quizTitle" required>
              </div>
              <div class="mb-3">
                <label for="quizDescription" class="form-label">Description</label>
                <textarea class="form-control" id="quizDescription" rows="2"></textarea>
              </div>
              <div class="row">
                <div class="col-md-4 mb-3">
                  <label for="quizDuration" class="form-label">Duration (minutes)</label>
                  <input type="number" class="form-control" id="quizDuration" value="30" min="1">
                </div>
                <div class="col-md-4 mb-3">
                  <label for="quizTotalPoints" class="form-label">Total Points</label>
                  <input type="number" class="form-control" id="quizTotalPoints" value="100" min="1">
                </div>
                <div class="col-md-4 mb-3">
                  <label for="quizPassingScore" class="form-label">Passing Score (%)</label>
                  <input type="number" class="form-control" id="quizPassingScore" value="60" min="0" max="100">
                </div>
              </div>
              <div class="row">
                <div class="col-md-6 mb-3">
                  <label for="quizStartDate" class="form-label">Available From</label>
                  <input type="datetime-local" class="form-control" id="quizStartDate">
                </div>
                <div class="col-md-6 mb-3">
                  <label for="quizEndDate" class="form-label">Available Until</label>
                  <input type="datetime-local" class="form-control" id="quizEndDate">
                </div>
              </div>
              <div class="mb-3">
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" id="quizRandomize">
                  <label class="form-check-label" for="quizRandomize">
                    Randomize question order
                  </label>
                </div>
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" id="quizShowAnswers">
                  <label class="form-check-label" for="quizShowAnswers">
                    Show correct answers after submission
                  </label>
                </div>
              </div>
            </form>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="button" class="btn btn-primary" onclick="handleCreateQuiz()">
              <i class="bi bi-check-lg me-2"></i>Create Quiz
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Add Assignment Modal -->
    <div class="modal fade" id="addAssignmentModal" tabindex="-1">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title"><i class="bi bi-file-earmark-check-fill me-2"></i>Add New Assignment</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <form id="addAssignmentForm">
              <div class="mb-3">
                <label for="assignmentTitle" class="form-label">Assignment Title <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="assignmentTitle" required>
              </div>
              <div class="mb-3">
                <label for="assignmentInstructions" class="form-label">Instructions</label>
                <textarea class="form-control" id="assignmentInstructions" rows="4"></textarea>
              </div>
              <div class="row">
                <div class="col-md-6 mb-3">
                  <label for="assignmentPoints" class="form-label">Total Points</label>
                  <input type="number" class="form-control" id="assignmentPoints" value="100">
                </div>
                <div class="col-md-6 mb-3">
                  <label for="assignmentDueDate" class="form-label">Due Date <span class="text-danger">*</span></label>
                  <input type="datetime-local" class="form-control" id="assignmentDueDate" required>
                </div>
              </div>
              <div class="mb-3">
                <label for="assignmentAttachment" class="form-label">Attachment (Optional)</label>
                <input type="file" class="form-control" id="assignmentAttachment">
              </div>
              <div class="mb-3">
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" id="allowLateSubmission">
                  <label class="form-check-label" for="allowLateSubmission">
                    Allow late submissions
                  </label>
                </div>
              </div>
            </form>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="button" class="btn btn-primary" onclick="handleAddAssignment()">
              <i class="bi bi-check-lg me-2"></i>Create Assignment
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Add Multimedia Modal -->
    <div class="modal fade" id="addMultimediaModal" tabindex="-1">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title"><i class="bi bi-collection-play-fill me-2"></i>Add Multimedia Content</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <form id="addMultimediaForm">
              <div class="mb-3">
                <label for="mediaTitle" class="form-label">Media Title <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="mediaTitle" required>
              </div>
              <div class="mb-3">
                <label for="mediaType" class="form-label">Media Type</label>
                <select class="form-select" id="mediaType">
                  <option value="video">Video</option>
                  <option value="audio">Audio</option>
                  <option value="image">Image</option>
                  <option value="embed">Embed Link (YouTube, Vimeo, etc.)</option>
                </select>
              </div>
              <div class="mb-3">
                <label for="mediaSource" class="form-label">Upload File or Enter URL <span class="text-danger">*</span></label>
                <input type="file" class="form-control mb-2" id="mediaFile">
                <small class="text-muted d-block mb-2">OR</small>
                <input type="url" class="form-control" id="mediaUrl" placeholder="https://youtube.com/...">
              </div>
              <div class="mb-3">
                <label for="mediaDescription" class="form-label">Description</label>
                <textarea class="form-control" id="mediaDescription" rows="3"></textarea>
              </div>
            </form>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="button" class="btn btn-primary" onclick="handleAddMultimedia()">
              <i class="bi bi-check-lg me-2"></i>Add Media
            </button>
          </div>
        </div>
      </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
      // Get CSRF Token
      function getCSRFToken() {
        return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
      }

      // Handle Upload Material
      function handleUploadMaterial() {
        const title = document.getElementById('materialTitle').value;
        if (!title) {
          Swal.fire('Error', 'Please fill in required fields', 'error');
          return;
        }
        
        Swal.fire({ title: 'Uploading...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
        
        fetch('/admin/materials/upload', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': getCSRFToken() },
          body: JSON.stringify({ title })
        })
        .then(r => r.json())
        .then(d => {
          if (d.success) {
            Swal.fire('Success!', d.message, 'success').then(() => location.reload());
          } else {
            Swal.fire('Error', d.message, 'error');
          }
        })
        .catch(() => Swal.fire('Error', 'Failed to upload material', 'error'));
      }

      // Handle Virtual Class Setup
      function handleVirtualClass() {
        const title = document.getElementById('meetingTitle').value;
        const link = document.getElementById('meetingLink').value;
        if (!title || !link) {
          Swal.fire('Error', 'Please fill in required fields', 'error');
          return;
        }
        
        Swal.fire({ title: 'Creating...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
        
        fetch('/admin/virtual-class', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': getCSRFToken() },
          body: JSON.stringify({ title, meeting_link: link, platform: 'other', scheduled_at: new Date().toISOString(), class_id: 1 })
        })
        .then(r => r.json())
        .then(d => {
          if (d.success) {
            Swal.fire('Success!', d.message, 'success').then(() => location.reload());
          } else {
            Swal.fire('Error', d.message, 'error');
          }
        })
        .catch(() => Swal.fire('Error', 'Failed to create virtual class', 'error'));
      }

      // Handle Create Quiz
      function handleCreateQuiz() {
        const title = document.getElementById('quizTitle').value;
        if (!title) {
          Swal.fire('Error', 'Please fill in required fields', 'error');
          return;
        }
        
        Swal.fire({ title: 'Creating Quiz...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
        
        fetch('/admin/quizzes/store', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': getCSRFToken() },
          body: JSON.stringify({ title, duration: 30, total_points: 100 })
        })
        .then(r => r.json())
        .then(d => {
          if (d.success) {
            Swal.fire('Success!', d.message, 'success').then(() => location.reload());
          } else {
            Swal.fire('Error', d.message, 'error');
          }
        })
        .catch(() => Swal.fire('Error', 'Failed to create quiz', 'error'));
      }

      // Handle Add Assignment
      function handleAddAssignment() {
        const title = document.getElementById('assignmentTitle').value;
        const dueDate = document.getElementById('assignmentDueDate').value;
        const classId = document.getElementById('assignmentClassId')?.value || 1; // fallback to 1 if no dropdown
        
        if (!title || !dueDate) {
          Swal.fire('Error', 'Please fill in required fields', 'error');
          return;
        }
        
        Swal.fire({ title: 'Creating Assignment...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
        
        fetch('/admin/assignments/store', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': getCSRFToken() },
          body: JSON.stringify({ title, due_date: dueDate, max_points: 100, class_id: parseInt(classId) })
        })
        .then(r => r.json())
        .then(d => {
          if (d.success) {
            Swal.fire('Success!', d.message, 'success').then(() => location.reload());
          } else {
            Swal.fire('Error', d.message, 'error');
          }
        })
        .catch(() => Swal.fire('Error', 'Failed to create assignment', 'error'));
      }

      // Handle Add Multimedia
      function handleAddMultimedia() {
        const title = document.getElementById('mediaTitle').value;
        if (!title) {
          Swal.fire('Error', 'Please fill in required fields', 'error');
          return;
        }
        
        Swal.fire({ title: 'Adding Media...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
        
        fetch('/admin/materials/upload', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': getCSRFToken() },
          body: JSON.stringify({ title, material_type: 'other' })
        })
        .then(r => r.json())
        .then(d => {
          if (d.success) {
            Swal.fire('Success!', d.message, 'success').then(() => location.reload());
          } else {
            Swal.fire('Error', d.message, 'error');
          }
        })
        .catch(() => Swal.fire('Error', 'Failed to add media', 'error'));
      }
    </script>
</body>
</html>