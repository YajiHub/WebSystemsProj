<?php
// File: admin/manage-users.php
// Include session management
require_once '../public/include/session.php';

// Require admin
requireAdmin();

// Get all users
$users = getAllUsers($conn);

// Get access levels for the form
$accessLevels = getAllAccessLevels($conn);

include 'include/header.php';
include 'include/admin-sidebar.php';
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
    
    <?php
    // Display error message if any
    if (isset($_SESSION['error'])) {
        echo '<div class="alert alert-danger" role="alert">' . htmlspecialchars($_SESSION['error']) . '</div>';
        unset($_SESSION['error']);
    }
    
    // Display success message if any
    if (isset($_SESSION['success'])) {
        echo '<div class="alert alert-success" role="alert">' . htmlspecialchars($_SESSION['success']) . '</div>';
        unset($_SESSION['success']);
    }
    ?>
    
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
                <select class="form-control" id="access-level-filter">
                  <option value="">All Access Levels</option>
                  <?php foreach ($accessLevels as $level): ?>
                    <option value="<?php echo $level['LevelName']; ?>"><?php echo htmlspecialchars($level['LevelName']); ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="col-md-6">
                <div class="input-group">
                  <input type="text" class="form-control" id="search-input" placeholder="Search users...">
                  <div class="input-group-append">
                    <button class="btn btn-primary" id="search-btn" type="button">Search</button>
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
                    <th>Access Level</th>
                    <th>Actions</th>
                  </tr>  
                </thead>
                <tbody>
                  <?php if (empty($users)): ?>
                    <tr>
                      <td colspan="6" class="text-center">No users found.</td>
                    </tr>
                  <?php else: ?>
                    <?php foreach ($users as $user): ?>
                      <tr>
                        <td><?php echo $user['UserID']; ?></td>
                        <td><?php echo htmlspecialchars($user['FirstName'] . ' ' . $user['LastName']); ?></td>
                        <td><?php echo htmlspecialchars($user['EmailAddress']); ?></td>
                        <td><?php echo ucfirst($user['UserRole']); ?></td>
                        <td><?php echo htmlspecialchars($user['LevelName']); ?></td>
                        <td>
                          <a href="view-user.php?id=<?php echo $user['UserID']; ?>" class="btn btn-info btn-sm" title="View User">
                            <i class="ti-eye"></i>
                          </a>
                          <a href="edit-user.php?id=<?php echo $user['UserID']; ?>" class="btn btn-primary btn-sm" title="Edit User">
                            <i class="ti-pencil"></i>
                          </a>
                          <?php if ($user['UserID'] != $_SESSION['user_id']): // Don't allow deactivating self ?>
                          <button type="button" class="btn btn-warning btn-sm toggle-status" data-id="<?php echo $user['UserID']; ?>" title="Deactivate User">
                            <i class="ti-control-pause"></i>
                          </button>
                          <?php endif; ?>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
            
            <?php if (!empty($users) && count($users) > 10): ?>
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
            <?php endif; ?>
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
              <label for="middleName">Middle Name (Optional)</label>
              <input type="text" class="form-control" id="middleName" name="middleName">
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
              <label for="username">Username</label>
              <input type="text" class="form-control" id="username" name="username" required>
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
              <label for="accessLevel">Access Level</label>
              <select class="form-control" id="accessLevel" name="accessLevel" required>
                <?php foreach ($accessLevels as $level): ?>
                  <option value="<?php echo $level['AccessLevelID']; ?>"><?php echo htmlspecialchars($level['LevelName']); ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="form-group">
              <label for="extension">Extension (Optional)</label>
              <input type="text" class="form-control" id="extension" name="extension">
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
      
      if (confirm('Are you sure you want to deactivate this user?')) {
        // In a real application, you would make an AJAX call to update the status
        window.location.href = 'process-toggle-user.php?id=' + userId;
      }
    });
    
    // Filter functionality
    $('#role-filter, #access-level-filter').on('change', function() {
      filterTable();
    });
    
    $('#search-btn').on('click', function() {
      filterTable();
    });
    
    $('#search-input').on('keyup', function(e) {
      if (e.keyCode === 13) { // Enter key
        filterTable();
      }
    });
    
    function filterTable() {
      var roleFilter = $('#role-filter').val().toLowerCase();
      var levelFilter = $('#access-level-filter').val();
      var searchText = $('#search-input').val().toLowerCase();
      
      $('#users-table tbody tr').each(function() {
        var row = $(this);
        var role = row.find('td:nth-child(4)').text().toLowerCase();
        var level = row.find('td:nth-child(5)').text();
        var name = row.find('td:nth-child(2)').text().toLowerCase();
        var email = row.find('td:nth-child(3)').text().toLowerCase();
        
        var roleMatch = roleFilter === '' || role.includes(roleFilter);
        var levelMatch = levelFilter === '' || level === levelFilter;
        var searchMatch = searchText === '' || name.includes(searchText) || email.includes(searchText);
        
        if (roleMatch && levelMatch && searchMatch) {
          row.show();
        } else {
          row.hide();
        }
      });
    }
  });
</script>

<?php
include 'include/footer.php';
?>