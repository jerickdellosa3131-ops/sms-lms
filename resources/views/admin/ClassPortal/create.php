<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SMS3 - Create New Class</title>

  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="<?php echo asset('style.css'); ?>">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
  
  <style>
    @import url("../../style.css");

    .form-section {
      background: white;
      border-radius: 12px;
      padding: 2rem;
      margin-bottom: 1.5rem;
      box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }

    .form-section h5 {
      font-weight: 600;
      margin-bottom: 1.5rem;
      color: #1a237e;
      border-bottom: 2px solid #f0f0f0;
      padding-bottom: 0.75rem;
    }

    .form-label {
      font-weight: 500;
      color: #2c3e50;
      margin-bottom: 0.5rem;
    }

    .form-control, .form-select {
      border-radius: 8px;
      border: 1px solid #dee2e6;
      padding: 0.75rem 1rem;
    }

    .form-control:focus, .form-select:focus {
      border-color: #1a237e;
      box-shadow: 0 0 0 0.2rem rgba(26, 35, 126, 0.15);
    }

    .btn-create {
      background: linear-gradient(45deg, #1a237e, #3949ab);
      border: none;
      padding: 0.875rem 2.5rem;
      font-weight: 600;
      border-radius: 8px;
      color: white;
      transition: transform 0.2s;
    }

    .btn-create:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(26, 35, 126, 0.3);
    }

    .btn-cancel {
      padding: 0.875rem 2.5rem;
      font-weight: 600;
      border-radius: 8px;
    }
  </style>
</head>

<body>

  <!-- Navigation -->
  <?php include resource_path('views/includes/sidenav_admin.php'); ?>
  
  <!-- Main Content -->
  <div class="main-content flex-grow-1">
    <div class="container">
      <div class="row">
        <div class="col">
          <div class="container my-5">
            
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
              <div>
                <h2 class="fw-bold mb-2">
                  <i class="bi bi-plus-circle text-primary me-2"></i>
                  Create New Class
                </h2>
                <p class="text-muted mb-0">Fill in the details below to create a new class</p>
              </div>
              <a href="<?php echo route('admin.class-portal'); ?>" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-2"></i> Back to Class Portal
              </a>
            </div>

            <!-- Success/Error Messages -->
            <?php if (session('success')): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
              <i class="bi bi-check-circle me-2"></i>
              <?php echo session('success'); ?>
              <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>

            <?php if (session('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
              <i class="bi bi-exclamation-circle me-2"></i>
              <?php echo session('error'); ?>
              <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>

            <!-- Create Class Form -->
            <form action="<?php echo route('admin.class-portal.store'); ?>" method="POST">
              <?php echo csrf_field(); ?>

              <!-- Basic Information -->
              <div class="form-section">
                <h5><i class="bi bi-info-circle me-2"></i>Basic Information</h5>
                
                <div class="row">
                  <div class="col-md-8 mb-3">
                    <label for="class_name" class="form-label">Class Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="class_name" name="class_name" required
                           placeholder="e.g., Introduction to Programming">
                  </div>

                  <div class="col-md-4 mb-3">
                    <label for="class_code" class="form-label">Class Code <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="class_code" name="class_code" required
                           placeholder="e.g., CS101">
                  </div>
                </div>

                <div class="mb-3">
                  <label for="description" class="form-label">Description</label>
                  <textarea class="form-control" id="description" name="description" rows="4"
                            placeholder="Enter a brief description of the class"></textarea>
                </div>

                <div class="row">
                  <div class="col-md-4 mb-3">
                    <label for="subject" class="form-label">Subject <span class="text-danger">*</span></label>
                    <select class="form-select" id="subject" name="subject" required>
                      <option value="">Select Subject</option>
                      <option value="Computer Science">Computer Science</option>
                      <option value="Mathematics">Mathematics</option>
                      <option value="English">English</option>
                      <option value="Science">Science</option>
                      <option value="Social Studies">Social Studies</option>
                      <option value="Physical Education">Physical Education</option>
                      <option value="Arts">Arts</option>
                      <option value="Other">Other</option>
                    </select>
                  </div>

                  <div class="col-md-4 mb-3">
                    <label for="grade_level" class="form-label">Grade Level</label>
                    <select class="form-select" id="grade_level" name="grade_level">
                      <option value="">Select Grade</option>
                      <option value="7">Grade 7</option>
                      <option value="8">Grade 8</option>
                      <option value="9">Grade 9</option>
                      <option value="10">Grade 10</option>
                      <option value="11">Grade 11</option>
                      <option value="12">Grade 12</option>
                    </select>
                  </div>

                  <div class="col-md-4 mb-3">
                    <label for="school_year" class="form-label">School Year <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="school_year" name="school_year" required
                           value="2024-2025" placeholder="e.g., 2024-2025">
                  </div>
                </div>
              </div>

              <!-- Schedule Information -->
              <div class="form-section">
                <h5><i class="bi bi-calendar-event me-2"></i>Schedule Information</h5>
                
                <div class="row">
                  <div class="col-md-4 mb-3">
                    <label for="start_date" class="form-label">Start Date</label>
                    <input type="date" class="form-control" id="start_date" name="start_date">
                  </div>

                  <div class="col-md-4 mb-3">
                    <label for="end_date" class="form-label">End Date</label>
                    <input type="date" class="form-control" id="end_date" name="end_date">
                  </div>

                  <div class="col-md-4 mb-3">
                    <label for="class_time" class="form-label">Class Time</label>
                    <input type="text" class="form-control" id="class_time" name="class_time"
                           placeholder="e.g., Mon/Wed/Fri 10:00 AM - 11:30 AM">
                  </div>
                </div>

                <div class="row">
                  <div class="col-md-6 mb-3">
                    <label for="room" class="form-label">Room/Location</label>
                    <input type="text" class="form-control" id="room" name="room"
                           placeholder="e.g., Room 301 or Virtual">
                  </div>

                  <div class="col-md-6 mb-3">
                    <label for="max_students" class="form-label">Maximum Students</label>
                    <input type="number" class="form-control" id="max_students" name="max_students"
                           min="1" value="30" placeholder="30">
                  </div>
                </div>
              </div>

              <!-- Additional Settings -->
              <div class="form-section">
                <h5><i class="bi bi-gear me-2"></i>Additional Settings</h5>
                
                <div class="row">
                  <div class="col-md-6 mb-3">
                    <label for="status" class="form-label">Class Status</label>
                    <select class="form-select" id="status" name="status">
                      <option value="active" selected>Active</option>
                      <option value="inactive">Inactive</option>
                      <option value="archived">Archived</option>
                    </select>
                  </div>

                  <div class="col-md-6 mb-3">
                    <label for="visibility" class="form-label">Visibility</label>
                    <select class="form-select" id="visibility" name="visibility">
                      <option value="public">Public (Visible to all students)</option>
                      <option value="private" selected>Private (Enrollment required)</option>
                    </select>
                  </div>
                </div>

                <div class="form-check mb-3">
                  <input class="form-check-input" type="checkbox" id="allow_self_enrollment" name="allow_self_enrollment" value="1">
                  <label class="form-check-label" for="allow_self_enrollment">
                    Allow students to self-enroll in this class
                  </label>
                </div>

                <div class="form-check mb-3">
                  <input class="form-check-input" type="checkbox" id="enable_forum" name="enable_forum" value="1" checked>
                  <label class="form-check-label" for="enable_forum">
                    Enable discussion forum for this class
                  </label>
                </div>
              </div>

              <!-- Form Actions -->
              <div class="d-flex justify-content-end gap-3">
                <a href="<?php echo route('admin.class-portal'); ?>" class="btn btn-outline-secondary btn-cancel">
                  <i class="bi bi-x-lg me-2"></i>Cancel
                </a>
                <button type="submit" class="btn btn-create">
                  <i class="bi bi-check-lg me-2"></i>Create Class
                </button>
              </div>

            </form>

          </div>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
