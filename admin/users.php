<?php
// // File: admin/users.php
// session_start();

// // Check if user is logged in and is admin
// if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
//     header("Location: ../public/login.php");
//     exit;
// }

include '../public/include/header.php';
include '../public/include/sidebar.php';
?>

<!-- Main Panel -->
<div class="main-panel">
  <div class="content-wrapper">
    <!-- Page Title -->
    <div class="row">
      <div class="col-md-12 grid-margin">
        <div class="row">
          <div class="col-12 col-xl-8 mb-4 mb-xl-0">
            <h3 class="font-weight-bold">User Management</h3>
            <h6 class="font-weight-normal mb-0">Manage all registered users</h6>
          </div>
          <div class="col-12 col-xl-4">
            <div class="justify-content-end d-flex">
              <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addUserModal">
                <i class="ti-user mr-1"></i> Add New User
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Filters -->
    <div class="row mb-4">
      <div class="col-md-12">
        <div class="card">
          <div class="card-body py-3">
            <div class="row">
              <div class="col-md-3">
                <select class="form-control" id="role-filter">
                  <option value="">All Roles</option>
                  <option value="admin">Administrators</option>
                  <option value="user">Regular Users</option>
                </select>
              </div>
              <div class="col-md-3">
                <select class="form-control" id="status-filter">
                  <option value="">All Status</option>
                  <option value="active">Active</option>
                  <option value="inactive">Inactive</option>
                  <option value="suspended">Suspended</option>
                </select>
              </div>
              <div class="col-md-6">
                <div class="input-group">
                  <input type="text" class="form-control" placeholder="Search users...">
                  <div class="input-group-append">
                    <button class="btn btn-primary" type="button">Search</button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Users Table -->
    <div class="row">
      <div class="col-md-12 grid-margin stretch-card">
        <div class="card">
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-striped table-hover" id="users-table">
                <thead>
                  <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Registered</th>
                    <th>Documents</th>
                    <th>Actions</th>
                  </tr>  
                </thead>
                <tbody>
                  <?php
                  // Sample data - Would be replaced with actual data from database
                  $users = [
                    [1, 'John Doe', 'john.doe@example.com', 'User', 'Active', '2023-05-15', 12],
                    [2, 'Maria Garcia', 'maria.garcia@example.com', 'User', 'Active', '2023-05-20', 8],
                    [3, 'Ahmed Khan', 'ahmed.khan@example.com', 'Admin', 'Active', '2023-04-10', 5],
                    [4, 'Lisa Wong', 'lisa.wong@example.com', 'User', 'Inactive', '2023-06-01', 0],
                    [5, 'Robert Smith', 'robert.smith@example.com', 'User', 'Suspended', '2023-03-22', 3],
                    [6, 'Emma Johnson', 'emma.johnson@example.com', 'User', 'Active', '2023-06-10', 7],
                    [7, 'Michael Brown', 'michael.brown@example.com', 'Admin', 'Active', '2023-02-15', 9],
                    [8, 'Sophia Martinez', 'sophia.martinez@example.com', 'User', 'Active', '2023-05-05', 4]
                  ];
                  
                  foreach ($users as $user) {
                    $statusClass = '';
                    switch ($user[4]) {
                      case 'Active':
                        $statusClass = 'badge-success';
                        break;
                      case 'Inactive':
                        $statusClass = 'badge-warning';
                        break;
                      case 'Suspended':
                        $statusClass = 'badge-danger';
                        break;
                    }
                    
                    echo '<tr>';
                    echo '<td>' . $user[0] . '</td>';
                    echo '<td>' . $user[1] . '</td>';
                    echo '<td>' . $user[2] . '</td>';
                    echo '<td>' . $user[3] . '</td>';
                    echo '<td><span class="badge ' . $statusClass . '">' . $user[4] . '</span></td>';
                    echo '<td>' . $user[5] . '</td>';
                    echo '<td>' . $user[6] . '</td>';
                    echo '<td>
                            <a href="view-user.php?id=' . $user[0] . '" class="btn btn-info btn-sm">
                              <i class="ti-eye"></i>
                            </a>
                            <a href="edit-user.php?id=' . $user[0] . '" class="btn btn-primary btn-sm">
                              <i class="ti-pencil"></i>
                            </a>
                            <button type="button" class="btn ' . ($user[4] == 'Active' ? 'btn-warning' : 'btn-success') . ' btn-sm toggle-status" data-id="' . $user[0] . '" data-status="' . $user[4] . '">
                              <i class="ti-' . ($user[4] == 'Active' ? 'control-pause' : 'control-play') . '"></i>
                            </button>
                          </td>';
                    echo '</tr>';
                  }
                  ?>
                </tbody>
              </table>
            </div>
            <!-- Pagination -->
            <div class="mt-4">
              <nav>
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

  <!-- Add User Modal -->
  <div class="modal fade" id="addUserModal" tabindex="-1" role="dialog" aria-labelledby="addUserModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="addUserModalLabel">Add New User</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <form id="addUserForm" action="process-add-user.php" method="post">
            <div class="form-group">
              <label for="firstName">First Name</label>
              <input type="text" class="form-control" id="firstName" name="firstName" required>
            </div>
            <div class="form-group">
              <label for="lastName">Last Name</label>
              <input type="text" class="form-control" id="lastName" name="lastName" required>
            </div>
            <div class="form-group">
              <label for="email">Email</label>
              <input type="email" class="form-control" id="email" name="email" required>
            </div>
            <div class="form-group">
              <label for="password">Password</label>
              <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <div class="form-group">
              <label for="role">Role</label>
              <select class="form-control" id="role" name="role" required>
                <option value="user">Regular User</option>
                <option value="admin">Administrator</option>
              </select>
            </div>
            <div class="form-group">
              <label for="department">Department</label>
              <input type="text" class="form-control" id="department" name="department">
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
              <button type="submit" class="btn btn-primary">Add User</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

<script>
  $(document).ready(function() {
    // Toggle user status
    $('.toggle-status').on('click', function() {
      var userId = $(this).data('id');
      var currentStatus = $(this).data('status');
      var newStatus = currentStatus == 'Active' ? 'Inactive' : 'Active';
      
      // In a real application, you would make an AJAX call to update the status
      if (confirm('Are you sure you want to change this user\'s status to ' + newStatus + '?')) {
        console.log('Changing user ' + userId + ' status from ' + currentStatus + ' to ' + newStatus);
        // Reload page or update UI
      }
    });
    
    // Initialize DataTable with search, sorting, and pagination
    $('#users-table').DataTable({
      "pageLength": 10,
      "lengthMenu": [10, 25, 50, 100],
      "language": {
        "search": "Search:",
        "lengthMenu": "Show _MENU_ entries per page",
        "info": "Showing _START_ to _END_ of _TOTAL_ entries",
        "infoEmpty": "Showing 0 to 0 of 0 entries",
        "infoFiltered": "(filtered from _MAX_ total entries)"
      }
    });
  });
</script>

<?php
include '../public/include/footer.php';
?>