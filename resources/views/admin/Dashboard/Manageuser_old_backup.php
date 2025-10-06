<!DOCTYPE html>
  <html lang="en">

  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SMS3</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?php echo asset('style.css'); ?>">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
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
        Global Styles & Typography
        =======================================
        */
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap');
        :root {
            --primary-color: #007bff;
            --secondary-color: #6c757d;
            --background-color: #f8f9fa;
            --sidebar-bg: #212529;
            --card-bg: #ffffff;
            --text-dark: #212529;
            --text-light: #f8f9fa;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--background-color);
            color: var(--text-dark);
        }

        h1, h2, h3, h4, h5, h6 {
            font-weight: 600;
        }

        /*
        =======================================
        Layout & Main Containers
        =======================================
        */
        .main-container {
            display: flex;
        }
         .dashboard-header {
            margin-bottom: 2rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid rgba(0, 0, 0, 0.08);
        }

        .header-icon {
            font-size: 2.5rem;
            color: var(--primary-color);
            margin-right: 1rem;
        }
        
        .create-class-btn {
            background-image: linear-gradient(45deg, #007bff, #00c6ff);
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 50px;
            box-shadow: 0 4px 15px rgba(0, 123, 255, 0.3);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .create-class-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 123, 255, 0.4);
        }

        /*
        =======================================
        Dashboard Cards
        =======================================
        */
        .card-tile {
            border: none;
            border-radius: 1rem;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.05);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .card-tile:hover {
            transform: translateY(-8px);
            box-shadow: 0 1rem 2rem rgba(0, 0, 0, 0.1);
        }

        .card-tile .card-body {
            padding: 2rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
        }

        .card-tile::before {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 6px;
            background-color: transparent;
            transition: background-color 0.3s ease;
        }

        /* Specific card colors */
        .card-analytics::before { background-color: #dc3545; }
        .card-upload::before { background-color: #17a2b8; }
        .card-virtual::before { background-color: #007bff; }
        .card-quizzes::before { background-color: #28a745; }
        .card-progress::before { background-color: #ffc107; }
        .card-multimedia::before { background-color: #6f42c1; }
        .card-feedback::before { background-color: #fd7e14; }
        .card-assignments::before { background-color: #e83e8c; }
        .card-grading::before { background-color: #20c997; }

        .card-tile i {
            font-size: 3rem;
            margin-bottom: 1.25rem;
            transition: color 0.3s ease;
        }

        .card-tile h6 {
            font-size: 1.15rem;
            font-weight: 700;
        }

        .card-tile p {
            font-size: 0.9rem;
            color: var(--secondary-color);
        }

        .card-tile .btn {
            border-radius: 50px;
            font-weight: 600;
            letter-spacing: 0.5px;
            padding: 0.6rem 1.25rem;
            margin-top: 1rem;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .main-container {
                flex-direction: column;
            }

            .sidebar {
                width: 100%;
                min-height: auto;
            }

            .main-content {
                padding: 1.5rem;
            }
            .dashboard-header {
                flex-direction: column;
                align-items: flex-start !important;
            }
            .create-class-btn {
                margin-top: 1rem;
            }
        }
        /* 2. Layouts */
    .main-content {
      padding: 40px;
      flex-grow: 1;
    }

    .sidebar {
      background-color: #084d92ff;
      color: #fff;
    }
    .sidebar .nav-link {
      color: #fff;
      transition: background-color 0.2s ease;
    }
    .sidebar .nav-link:hover,
    .sidebar .nav-link.active {
      background-color: #baa9e2ff;
    }

    /*
        =======================================
        Global Styles & Typography
        =======================================
        */
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap');
        :root {
            --primary-color: #007bff;
            --secondary-color: #6c757d;
            --background-color: #f8f9fa;
            --text-dark: #212529;
            --text-light: #f8f9fa;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--background-color);
            color: var(--text-dark);
        }

        h1, h2, h3, h4, h5, h6 {
            font-weight: 600;
        }

        /*
        =======================================
        Layout & Main Containers
        =======================================
        */
        .main-container {
            display: flex;
        }

        /* Sidebar
        --------------------------------------- */
        .sidebar {
            width: 250px;
            min-height: 100vh;
            background-color: #122159; /* Dark blue background */
            padding: 1rem;
            color: var(--text-light);
            transition: width 0.3s ease;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .sidebar-brand {
            display: flex;
            flex-direction: column; /* Stack logo and text vertically */
            align-items: center; /* Center horizontally */
            text-align: center;
            font-weight: 700;
            margin-bottom: 2.5rem;
            padding: 1rem;
        }
        
        /* New logo styling */
        .sidebar-brand-logo {
            width: 80px; /* Adjust size based on image provided */
            height: 80px; /* Adjust size based on image provided */
            background-color: white; /* White square background */
            border-radius: 1rem;
            padding: 12px;
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 1rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .nav-link {
            display: flex;
            align-items: center;
            color: var(--text-light);
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            margin-bottom: 0.5rem;
            transition: background-color 0.2s ease, color 0.2s ease;
        }

        .nav-link:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }
        
        .nav-link.active {
            background-color: #007bff;
            color: #fff;
        }
        
        .nav-link i {
            font-size: 1.25rem;
            margin-right: 1rem;
        }

        /* Main Content Area
        --------------------------------------- */
        .main-content {
            flex-grow: 1;
            padding: 2.5rem;
            background-color: var(--background-color);
        }

        .dashboard-header {
            margin-bottom: 2rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid rgba(0, 0, 0, 0.08);
        }

        .header-icon {
            font-size: 2.5rem;
            color: var(--primary-color);
            margin-right: 1rem;
        }
        
        .create-class-btn {
            background-image: linear-gradient(45deg, #007bff, #00c6ff);
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 50px;
            box-shadow: 0 4px 15px rgba(0, 123, 255, 0.3);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .create-class-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 123, 255, 0.4);
        }

        /*
        =======================================
        Dashboard Cards
        =======================================
        */
        .card-tile {
            border: none;
            border-radius: 1rem;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.05);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .card-tile:hover {
            transform: translateY(-8px);
            box-shadow: 0 1rem 2rem rgba(0, 0, 0, 0.1);
        }

        .card-tile .card-body {
            padding: 2rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
        }

        .card-tile::before {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 6px;
            background-color: transparent;
            transition: background-color 0.3s ease;
        }

        /* Specific card colors */
        .card-analytics::before { background-color: #dc3545; }
        .card-upload::before { background-color: #17a2b8; }
        .card-virtual::before { background-color: #007bff; }
        .card-quizzes::before { background-color: #28a745; }
        .card-progress::before { background-color: #ffc107; }
        .card-multimedia::before { background-color: #6f42c1; }
        .card-feedback::before { background-color: #fd7e14; }
        .card-assignments::before { background-color: #e83e8c; }
        .card-grading::before { background-color: #20c997; }

        .card-tile i {
            font-size: 3rem;
            margin-bottom: 1.25rem;
            transition: color 0.3s ease;
        }

        .card-tile h6 {
            font-size: 1.15rem;
            font-weight: 700;
        }

        .card-tile p {
            font-size: 0.9rem;
            color: var(--secondary-color);
        }

        .card-tile .btn {
            border-radius: 50px;
            font-weight: 600;
            letter-spacing: 0.5px;
            padding: 0.6rem 1.25rem;
            margin-top: 1rem;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        
        /* New styles for User Management section */
        .user-management-section {
            background-color: #fff;
            border-radius: 1rem;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.05);
            padding: 2rem;
        }
        
        .user-search-filter {
            margin-bottom: 1.5rem;
        }
        
        .user-table {
            border-collapse: separate;
            border-spacing: 0 0.5rem;
        }
        
        .user-table th {
            font-weight: 700;
            color: #6c757d;
            text-transform: uppercase;
            font-size: 0.8rem;
            padding: 0.75rem 1rem;
        }
        
        .user-table td {
            background-color: #f8f9fa;
            border: none;
            vertical-align: middle;
            padding: 1rem;
        }
        
        .user-table tr:hover td {
            background-color: #e9ecef;
        }
        
        .user-table .user-name-cell {
            font-weight: 600;
        }
        
        .user-table .user-role-badge {
            font-size: 0.7rem;
            font-weight: 700;
            padding: 0.4rem 0.8rem;
            border-radius: 50px;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .main-container {
                flex-direction: column;
            }

            .sidebar {
                width: 100%;
                min-height: auto;
            }

            .main-content {
                padding: 1.5rem;
            }
            .dashboard-header {
                flex-direction: column;
                align-items: flex-start !important;
            }
            .create-class-btn {
                margin-top: 1rem;
            }
            .user-search-filter .form-control,
            .user-search-filter .btn {
                width: 100%;
                margin-bottom: 0.5rem;
            }
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
                    <!-- User Management Section -->
                    <div class="user-management-section">
                        <!-- Header for Manage Users -->
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-people-fill text-primary fs-1 me-3"></i>
                                <h3 class="mb-0 fw-bold">Manage Users</h3>
                            </div>
                            <button class="btn btn-success create-class-btn">
                                <i class="bi bi-plus-lg me-2"></i> Add New User
                            </button>
                        </div>
                        
                        <!-- Search and Filter -->
                        <div class="d-flex flex-wrap align-items-center user-search-filter">
                            <input type="text" class="form-control flex-grow-1 me-2 rounded-pill" placeholder="Search users...">
                                        <div class="card-body text-center">
                                            <i class="bi bi-upload text-info fs-1 mb-2"></i>
                                            <h6>Upload Materials</h6>
                                            <p class="text-muted small">Add lesson files, videos, and resources for students.</p>
                                            <a href="../viewanalytics/uploadmaterials.php" class="btn btn-info btn-sm mt-auto">Go</a>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Track Progress Card -->
                                <div class="col-lg-3 col-md-6 col-sm-12">
                                    <div class="card card-tile h-100 card-progress">
                                        <div class="card-body text-center">
                                            <i class="bi bi-clipboard-data-fill text-warning fs-1 mb-2"></i>
                                            <h6>Track Progress</h6>
                                            <p class="text-muted small">Monitor class performance and module completion.</p>
                                            <a href="../trackprogress/trackp.php" class="btn btn-warning btn-sm mt-auto">Go</a>
                                        </div>
                                    </div>
                                </div>

                                <!-- View Analytics Card -->
                                <div class="col-lg-3 col-md-6 col-sm-12">
                                    <div class="card card-tile h-100 card-analytics">
                                        <div class="card-body text-center">
                                            <i class="bi bi-bar-chart-fill text-danger fs-1 mb-2"></i>
                                            <h6>View Analytics</h6>
                                            <p class="text-muted small">Check LMS usage, quiz results, and grades with detailed, real-time analytics.</p>
                                            <a href="../viewanalytics/viewanaly.php" class="btn btn-danger btn-sm mt-auto">Go</a>
                                        </div>
                                    </div>
                                </div>

                                <!-- Virtual Class Card -->
                                <div class="col-lg-3 col-md-6 col-sm-12">
                                    <div class="card card-tile h-100 card-virtual">
                                        <div class="card-body text-center">
                                            <i class="bi bi-camera-video-fill text-primary fs-1 mb-2"></i>
                                            <h6>Virtual Class</h6>
                                            <p class="text-muted small">Manage and integrate links for live virtual classrooms.</p>
                                            <a href="../VirtualClassLinkInteg/virtualclasslinkinteg.php" class="btn btn-primary btn-sm mt-auto">Go</a>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Quizzes Card -->
                                <div class="col-lg-3 col-md-6 col-sm-12">
                                    <div class="card card-tile h-100 card-quizzes">
                                        <div class="card-body text-center">
                                            <i class="bi bi-pencil-square text-success fs-1 mb-2"></i>
                                            <h6>Quizzes</h6>
                                            <p class="text-muted small">Create, manage, and grade online quizzes and exams.</p>
                                            <a href="../OnlineQuizzes/onlinequizzes.php" class="btn btn-success btn-sm mt-auto">Go</a>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Assignments Card -->
                                <div class="col-lg-3 col-md-6 col-sm-12">
                                    <div class="card card-tile h-100 card-assignments">
                                        <div class="card-body text-center">
                                            <i class="bi bi-person-fill-check text-warning fs-1 mb-2"></i>
                                            <h6>Assignment Submission</h6>
                                            <p class="text-muted small">Review and manage all student assignment submissions.</p>
                                            <a href="../AssignmentSubmission/assignsubmission.php" class="btn btn-warning btn-sm mt-auto">Go</a>
                                        </div>
                                    </div>
                                </div>

                                <!-- Multimedia Card -->
                                <div class="col-lg-3 col-md-6 col-sm-12">
                                    <div class="card card-tile h-100 card-multimedia">
                                        <div class="card-body text-center">
                                            <i class="bi bi-images text-danger fs-1 mb-2"></i>
                                            <h6>MultiMedia Support</h6>
                                            <p class="text-muted small">Manage and upload various media types for lessons.</p>
                                            <a href="../MultiMediaSupport/multimedsupp.php" class="btn btn-danger btn-sm mt-auto">Go</a>
                                        </div>
                                    </div>
                                </div>

                                <!-- Feedback Card -->
                                <div class="col-lg-3 col-md-6 col-sm-12">
                                    <div class="card card-tile h-100 card-feedback">
                                        <div class="card-body text-center">
                                            <i class="bi bi-chat-dots-fill text-secondary fs-1 mb-2"></i>
                                            <h6>Feedback & Comments</h6>
                                            <p class="text-muted small">Read and respond to feedback from students.</p>
                                            <a href="../FeedbackandComments/feedbackandcomments.php" class="btn btn-secondary btn-sm mt-auto">Go</a>
                                        </div>
                                    </div>
                                </div>

                                <!-- Grading Integration Card -->
                                <div class="col-lg-3 col-md-6 col-sm-12">
                                    <div class="card card-tile h-100 card-grading">
                                        <div class="card-body text-center">
                                            <i class="bi bi-journal-text text-success fs-1 mb-2"></i>
                                            <h6>Grading Integration</h6>
                                            <p class="text-muted small">Streamline the grading process and integrate with other systems.</p>
                                            <a href="../GradingIntegration/gradinginteg.php" class="btn btn-success btn-sm mt-auto">Go</a>
                                        </div>
                                    </div>
                                </div>
                                
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div id="manageUsersContent" class="content-section">
                <div class="container my-5">
                    <div class="user-management-section">
                        <!-- Header for Manage Users -->
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-people-fill text-primary fs-1 me-3"></i>
                                <h3 class="mb-0 fw-bold">Manage Users</h3>
                            </div>
                            <button class="btn btn-success create-class-btn">
                                <i class="bi bi-plus-lg me-2"></i> Add New User
                            </button>
                        </div>
                        
                        <!-- Search and Filter -->
                        <div class="d-flex flex-wrap align-items-center user-search-filter">
                            <input type="text" class="form-control flex-grow-1 me-2 rounded-pill" placeholder="Search users...">
                            <div class="dropdown me-2">
                                <button class="btn btn-outline-secondary dropdown-toggle rounded-pill" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    Role
                                </button>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="#">All</a></li>
                                    <li><a class="dropdown-item" href="#">Student</a></li>
                                    <li><a class="dropdown-item" href="#">Teacher</a></li>
                                    <li><a class="dropdown-item" href="#">Admin</a></li>
                                </ul>
                            </div>
                            <button class="btn btn-outline-secondary rounded-pill">
                                <i class="bi bi-filter-left me-1"></i> Filter
                            </button>
                        </div>

                        <!-- User Table -->
                        <div class="table-responsive">
                            <table class="table user-table">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Role</th>
                                        <th>Status</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Sample User 1 -->
                                    <tr>
                                        <td class="user-name-cell">John Doe</td>
                                        <td>john.doe@example.com</td>
                                        <td><span class="badge bg-primary user-role-badge">Student</span></td>
                                        <td><span class="badge bg-success">Active</span></td>
                                        <td class="text-end">
                                            <div class="btn-group">
                                                <button class="btn btn-sm btn-outline-info rounded-pill me-1"><i class="bi bi-pencil-square"></i></button>
                                                <button class="btn btn-sm btn-outline-danger rounded-pill"><i class="bi bi-trash"></i></button>
                                            </div>
                                        </td>
                                    </tr>
                                    <!-- Sample User 2 -->
                                    <tr>
                                        <td class="user-name-cell">Jane Smith</td>
                                        <td>jane.smith@example.com</td>
                                        <td><span class="badge bg-secondary user-role-badge">Teacher</span></td>
                                        <td><span class="badge bg-success">Active</span></td>
                                        <td class="text-end">
                                            <div class="btn-group">
                                                <button class="btn btn-sm btn-outline-info rounded-pill me-1"><i class="bi bi-pencil-square"></i></button>
                                                <button class="btn btn-sm btn-outline-danger rounded-pill"><i class="bi bi-trash"></i></button>
                                            </div>
                                        </td>
                                    </tr>
                                    <!-- Sample User 3 -->
                                    <tr>
                                        <td class="user-name-cell">Admin User</td>
                                        <td>admin@example.com</td>
                                        <td><span class="badge bg-danger user-role-badge">Admin</span></td>
                                        <td><span class="badge bg-success">Active</span></td>
                                        <td class="text-end">
                                            <div class="btn-group">
                                                <button class="btn btn-sm btn-outline-info rounded-pill me-1"><i class="bi bi-pencil-square"></i></button>
                                                <button class="btn btn-sm btn-outline-danger rounded-pill"><i class="bi bi-trash"></i></button>
                                            </div>
                                        </td>
                                    </tr>
                                    <!-- Sample User 4 -->
                                    <tr>
                                        <td class="user-name-cell">Student C</td>
                                        <td>student.c@example.com</td>
                                        <td><span class="badge bg-primary user-role-badge">Student</span></td>
                                        <td><span class="badge bg-warning">Suspended</span></td>
                                        <td class="text-end">
                                            <div class="btn-group">
                                                <button class="btn btn-sm btn-outline-info rounded-pill me-1"><i class="bi bi-pencil-square"></i></button>
                                                <button class="btn btn-sm btn-outline-danger rounded-pill"><i class="bi bi-trash"></i></button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Function to show the correct content section and highlight the active link
        function showContent(sectionId) {
            // Hide all content sections
            document.querySelectorAll('.content-section').forEach(section => {
                section.style.display = 'none';
            });
            // Remove active class from all nav links
            document.querySelectorAll('.nav-link').forEach(link => {
                link.classList.remove('active');
            });

            // Show the requested content section
            const sectionToShow = document.getElementById(sectionId + 'Content');
            if (sectionToShow) {
                sectionToShow.style.display = 'block';
            }
            
            // Add active class to the clicked nav link
            const activeLink = document.querySelector(`.nav-link[onclick*="${sectionId}"]`);
            if (activeLink) {
                activeLink.classList.add('active');
            }
        }
        
        // Initial load: show the "manageUsers" content by default
        showContent('manageUsers');

    </script>
</body>

</html>
