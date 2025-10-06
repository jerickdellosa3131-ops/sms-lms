<?php
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

// Get authenticated user
$user = Auth::user();

// Fetch all users with their details
$users = DB::table('users')
    ->leftJoin('students', 'users.user_id', '=', 'students.user_id')
    ->leftJoin('teachers', 'users.user_id', '=', 'teachers.user_id')
    ->select(
        'users.*',
        'students.student_number'
    )
    ->orderBy('users.created_at', 'desc')
    ->get();

// Count by user type
$totalAdmins = $users->where('user_type', 'admin')->count();
$totalTeachers = $users->where('user_type', 'teacher')->count();
$totalStudents = $users->where('user_type', 'student')->count();
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="<?php echo csrf_token(); ?>">
  <title>SMS3 - Manage Users</title>

  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="<?php echo asset('style.css'); ?>">

  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
  
  <!-- SheetJS for Excel Export -->
  <script src="https://cdn.sheetjs.com/xlsx-0.20.0/package/dist/xlsx.full.min.js"></script>
  
  <!-- SweetAlert2 for Alerts -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  
  <!-- Admin Actions Library -->
  <script src="<?php echo asset('js/admin-actions.js'); ?>"></script>
  
  <style>
    @import url("../../style.css");

    /* Make sidebar scrollable in landscape mode on mobile devices */
    @media (max-width: 768px) and (orientation: landscape) {
      .sidebar {
        max-height: 100vh;
        overflow-y: auto;
      }
    }
    
    .user-table {
      margin-top: 2rem;
    }
    
    .user-avatar {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      display: flex;
      align-items: center;
      justify-content: center;
      color: white;
      font-weight: 600;
    }
    
    .status-badge {
      padding: 0.25rem 0.75rem;
      border-radius: 20px;
      font-size: 0.875rem;
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
      <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="d-flex align-items-center">
          <i class="bi bi-people-fill text-primary fs-1 me-3"></i>
          <h3 class="mb-0 fw-bold">Manage Users</h3>
        </div>
        <div class="btn-group">
          <button class="btn btn-success text-white" data-bs-toggle="modal" data-bs-target="#addUserModal">
            <i class="bi bi-plus-lg me-2"></i> Add New User
          </button>
          <button class="btn btn-primary text-white" onclick="exportCurrentTable('usersTable', 'users')">
            <i class="bi bi-file-earmark-excel me-2"></i> Export
          </button>
        </div>
      </div>

      <!-- Search and Filter -->
      <div class="row mb-4">
        <div class="col-md-6">
          <div class="input-group">
            <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
            <input type="text" class="form-control" placeholder="Search users...">
          </div>
        </div>
        <div class="col-md-6 text-end">
          <div class="btn-group">
            <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
              <i class="bi bi-filter me-2"></i>Filter by Role
            </button>
            <ul class="dropdown-menu">
              <li><a class="dropdown-item" href="#">All Users</a></li>
              <li><a class="dropdown-item" href="#">Admins</a></li>
              <li><a class="dropdown-item" href="#">Teachers</a></li>
              <li><a class="dropdown-item" href="#">Students</a></li>
            </ul>
          </div>
          <button class="btn btn-outline-secondary ms-2">
            <i class="bi bi-funnel"></i>
          </button>
        </div>
      </div>

      <!-- Users Table -->
      <div class="table-responsive user-table">
        <table id="usersTable" class="table table-hover align-middle">
          <thead class="table-light">
            <tr>
              <th>User</th>
              <th>Email</th>
              <th>Role</th>
              <th>Status</th>
              <th>Joined</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php if($users->count() > 0): ?>
              <?php foreach($users as $u): 
                $fullName = $u->first_name . ' ' . $u->last_name;
                $userIdDisplay = $u->student_number ?? $u->user_id;
                $roleBadgeClass = match($u->user_type) {
                  'admin' => 'bg-danger',
                  'teacher' => 'bg-info',
                  'student' => 'bg-primary',
                  default => 'bg-secondary'
                };
                $statusClass = $u->status == 'active' ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger';
              ?>
            <tr>
              <td>
                <div class="d-flex align-items-center">
                  <div>
                    <div class="fw-semibold"><?php echo htmlspecialchars($fullName); ?></div>
                    <small class="text-muted">ID: <?php echo htmlspecialchars($userIdDisplay); ?></small>
                  </div>
                </div>
              </td>
              <td><?php echo htmlspecialchars($u->email); ?></td>
              <td><span class="badge <?php echo $roleBadgeClass; ?>"><?php echo ucfirst($u->user_type); ?></span></td>
              <td><span class="status-badge <?php echo $statusClass; ?>"><?php echo ucfirst($u->status); ?></span></td>
              <td><?php echo date('M d, Y', strtotime($u->created_at)); ?></td>
              <td>
                <button class="btn btn-sm btn-outline-info me-1" title="View" onclick="viewRecord(<?php echo $u->user_id; ?>, 'User', {name: '<?php echo addslashes($fullName); ?>', email: '<?php echo addslashes($u->email); ?>', type: '<?php echo ucfirst($u->user_type); ?>', status: '<?php echo ucfirst($u->status); ?>', joined: '<?php echo date('M d, Y', strtotime($u->created_at)); ?>', id: '<?php echo htmlspecialchars($userIdDisplay); ?>'})">
                  <i class="bi bi-eye"></i>
                </button>
                <button class="btn btn-sm btn-outline-warning me-1" title="Edit" onclick="editRecord(<?php echo $u->user_id; ?>, 'User', {name: '<?php echo addslashes($fullName); ?>', email: '<?php echo addslashes($u->email); ?>', type: '<?php echo ucfirst($u->user_type); ?>', status: '<?php echo ucfirst($u->status); ?>'})">
                  <i class="bi bi-pencil"></i>
                </button>
                <button class="btn btn-sm btn-outline-danger" title="Delete" onclick="deleteRecord(<?php echo $u->user_id; ?>, 'User', '<?php echo addslashes($fullName); ?>')">
                  <i class="bi bi-trash"></i>
                </button>
              </td>
            </tr>
            <?php endforeach; ?>
            <?php else: ?>
              <tr><td colspan="6" class="text-center text-muted">No users found</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <nav aria-label="Page navigation" class="mt-4">
        <ul class="pagination justify-content-center">
          <li class="page-item disabled">
            <a class="page-link" href="#" tabindex="-1">Previous</a>
          </li>
          <li class="page-item active"><a class="page-link" href="#">1</a></li>
          <li class="page-item"><a class="page-link" href="#">2</a></li>
          <li class="page-item"><a class="page-link" href="#">3</a></li>
          <li class="page-item">
            <a class="page-link" href="#">Next</a>
          </li>
        </ul>
      </nav>

              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Add New User Modal -->
  <div class="modal fade" id="addUserModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title"><i class="bi bi-person-plus-fill me-2"></i>Add New User</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <form id="addUserForm">
            <div class="row">
              <div class="col-md-6 mb-3">
                <label for="firstName" class="form-label">First Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="firstName" required>
              </div>
              <div class="col-md-6 mb-3">
                <label for="lastName" class="form-label">Last Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="lastName" required>
              </div>
            </div>
            <div class="mb-3">
              <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
              <input type="email" class="form-control" id="email" required>
            </div>
            <div class="row">
              <div class="col-md-6 mb-3">
                <label for="userType" class="form-label">User Type <span class="text-danger">*</span></label>
                <select class="form-select" id="userType" required>
                  <option value="">Select Type</option>
                  <option value="student">Student</option>
                  <option value="teacher">Teacher</option>
                  <option value="admin">Admin</option>
                </select>
              </div>
              <div class="col-md-6 mb-3">
                <label for="userId" class="form-label">User ID/Number</label>
                <input type="text" class="form-control" id="userId" placeholder="e.g., 2024001">
              </div>
            </div>
            <div class="row">
              <div class="col-md-6 mb-3">
                <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                <input type="password" class="form-control" id="password" required>
              </div>
              <div class="col-md-6 mb-3">
                <label for="confirmPassword" class="form-label">Confirm Password <span class="text-danger">*</span></label>
                <input type="password" class="form-control" id="confirmPassword" required>
              </div>
            </div>
            <div class="mb-3">
              <label for="phone" class="form-label">Phone Number</label>
              <input type="tel" class="form-control" id="phone">
            </div>
            <div class="form-check mb-3">
              <input class="form-check-input" type="checkbox" id="sendEmail" checked>
              <label class="form-check-label" for="sendEmail">
                Send welcome email with login credentials
              </label>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-success" onclick="handleAddUser()">
            <i class="bi bi-check-lg me-2"></i>Add User
          </button>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    function handleAddUser() {
      const firstName = document.getElementById('firstName').value;
      const lastName = document.getElementById('lastName').value;
      const email = document.getElementById('email').value;
      const userType = document.getElementById('userType').value;
      const userId = document.getElementById('userId').value;
      const password = document.getElementById('password').value;
      const confirmPassword = document.getElementById('confirmPassword').value;
      const phone = document.getElementById('phone').value;

      // Validation
      if (!firstName || !lastName || !email || !userType || !password) {
        Swal.fire({
          icon: 'error',
          title: 'Missing Fields',
          text: 'Please fill in all required fields marked with *'
        });
        return;
      }

      if (password !== confirmPassword) {
        Swal.fire({
          icon: 'error',
          title: 'Password Mismatch',
          text: 'Passwords do not match. Please try again.'
        });
        return;
      }

      if (password.length < 6) {
        Swal.fire({
          icon: 'error',
          title: 'Weak Password',
          text: 'Password must be at least 6 characters long'
        });
        return;
      }

      // Show loading
      Swal.fire({
        title: 'Creating User...',
        text: 'Please wait while we create the new user',
        allowOutsideClick: false,
        didOpen: () => {
          Swal.showLoading();
        }
      });

      // Get CSRF token
      const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

      // Send AJAX request to save user
      fetch('/admin/users/store', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrfToken,
          'Accept': 'application/json'
        },
        body: JSON.stringify({
          first_name: firstName,
          last_name: lastName,
          email: email,
          user_type: userType,
          user_number: userId,
          password: password,
          phone: phone
        })
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          Swal.fire({
            icon: 'success',
            title: 'User Created!',
            html: `
              <strong>${firstName} ${lastName}</strong> (${userType})<br>
              <small class="text-muted">${email}</small><br><br>
              <div class="alert alert-success mt-2">
                <i class="bi bi-check-circle me-2"></i>
                User has been saved to the database successfully!
              </div>
            `,
            showConfirmButton: true,
            confirmButtonText: 'Done'
          }).then(() => {
            bootstrap.Modal.getInstance(document.getElementById('addUserModal')).hide();
            document.getElementById('addUserForm').reset();
            // Reload page to show new user
            location.reload();
          });
        } else {
          Swal.fire({
            icon: 'error',
            title: 'Error',
            html: data.message || 'Failed to create user'
          });
        }
      })
      .catch(error => {
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: 'An error occurred. Please try again.'
        });
        console.error('Error:', error);
      });
    }

    // Export Users to Excel
    function exportUsersExcel() {
      const wb = XLSX.utils.book_new();
      
      const data = [
        ['User Management Report'],
        [''],
        ['Name', 'Email', 'User Type', 'Status', 'Date Joined'],
        ['John Doe', 'john@example.com', 'Student', 'Active', '2025-01-15'],
        ['Jane Smith', 'jane@example.com', 'Teacher', 'Active', '2025-01-10'],
        ['Mark Johnson', 'mark@example.com', 'Admin', 'Active', '2025-01-05'],
        ['Sarah Williams', 'sarah@example.com', 'Student', 'Active', '2025-02-01']
      ];
      
      const ws = XLSX.utils.aoa_to_sheet(data);
      ws['!cols'] = [{ wch: 20 }, { wch: 25 }, { wch: 12 }, { wch: 10 }, { wch: 15 }];
      
      XLSX.utils.book_append_sheet(wb, ws, 'Users');
      XLSX.writeFile(wb, 'users-list.xlsx');
      
      alert('User list exported successfully!');
    }
  </script>
</body>

</html>
