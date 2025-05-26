<?php
// File: admin/manage-users.php
require_once '../public/include/session.php';

// Require admin login
requireAdmin();

// Get all users with access level information
$sql = "SELECT 
    u.UserID,
    u.FirstName,
    u.LastName,
    u.EmailAddress,
    u.UserRole,
    u.Extension,
    a.LevelName,
    a.AccessLevelID
FROM user u
LEFT JOIN accesslevel a ON u.AccessLevel = a.AccessLevelID
ORDER BY u.UserID DESC";

$result = mysqli_query($conn, $sql);
$users = [];
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $users[] = $row;
    }
}

// Get access levels for filtering
$accessLevels = [];
$sql = "SELECT * FROM accesslevel ORDER BY AccessLevelID";
$result = mysqli_query($conn, $sql);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $accessLevels[] = $row;
    }
}

include 'include/header.php';
include 'include/admin-sidebar.php';
?>

<!-- Main Panel -->
<div class="main-panel">
<style>
.no-results {
    font-style: italic;
}
.btn:disabled {
    opacity: 0.6;
}
.user-count-update {
    transition: background-color 0.5s ease;
}
.flash-highlight {
    background-color: #e8f4fe;
}
</style>

<!-- Debug info (remove in production) -->
<?php if (isset($_GET['debug'])): ?>
<div class="alert alert-info">
    <strong>Debug Info:</strong><br>
    Total users loaded: <?php echo count($users); ?><br>
    Access levels available: <?php echo count($accessLevels); ?><br>
    Current user ID: <?php echo $_SESSION['user_id']; ?>
</div>
<?php endif; ?>
  <div class="content-wrapper">
    <!-- Page Title -->
    <div class="row">
      <div class="col-md-12 grid-margin">
        <div class="row">
          <div class="col-12 col-xl-8 mb-4 mb-xl-0">
            <h3 class="font-weight-bold">User Management</h3>
            <h6 class="font-weight-normal mb-0 user-count-update">Manage all registered users (<span id="user-count"><?php echo count($users); ?></span> total)</h6>
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
    // Display messages
    if (isset($_SESSION['error'])) {
        echo '<div class="alert alert-danger" role="alert">' . htmlspecialchars($_SESSION['error']) . '</div>';
        unset($_SESSION['error']);
    }
    
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
                    <option value="<?php echo htmlspecialchars($level['LevelName']); ?>"><?php echo htmlspecialchars($level['LevelName']); ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="col-md-6">
                <div class="input-group">
                  <input type="text" class="form-control" id="search-input" placeholder="Search users...">
                  <div class="input-group-append">
                    <button class="btn btn-primary" id="search-btn" type="button">Search</button>
                    <button class="btn btn-secondary" id="clear-filters" type="button">Clear</button>
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
                    <tr data-user-id="<?php echo $user['UserID']; ?>">
                      <td><?php echo $user['UserID']; ?></td>
                      <td><?php echo htmlspecialchars($user['FirstName'] . ' ' . $user['LastName']); ?></td>
                      <td><?php echo htmlspecialchars($user['EmailAddress']); ?></td>
                      <td>
                        <span class="badge <?php echo $user['UserRole'] == 'admin' ? 'badge-danger' : 'badge-primary'; ?>">
                          <?php echo ucfirst($user['UserRole']); ?>
                        </span>
                      </td>
                      <td><?php echo htmlspecialchars($user['LevelName'] ?? 'Not Set'); ?></td>
                      <td>
                        <a href="view-user.php?id=<?php echo $user['UserID']; ?>" class="btn btn-info btn-sm" title="View User">
                          <i class="ti-eye"></i>
                        </a>
                        <a href="edit-user.php?id=<?php echo $user['UserID']; ?>" class="btn btn-primary btn-sm" title="Edit User">
                          <i class="ti-pencil"></i>
                        </a>
                        <?php if ($user['UserID'] != $_SESSION['user_id']): ?>
                          <button type="button" class="btn btn-danger btn-sm delete-user" 
                                  data-id="<?php echo $user['UserID']; ?>" 
                                  data-name="<?php echo htmlspecialchars($user['FirstName'] . ' ' . $user['LastName']); ?>"
                                  title="Delete User">
                            <i class="ti-trash"></i>
                          </button>
                        <?php else: ?>
                          <button type="button" class="btn btn-secondary btn-sm" disabled title="You cannot delete your own account">
                            <i class="ti-trash"></i>
                          </button>
                        <?php endif; ?>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
            
            <!-- Pagination for user table -->
            <div class="mt-4 d-flex justify-content-between align-items-center">
              <div class="dataTables_info" id="users-table_info" role="status" aria-live="polite">
                Showing <span id="showing-start">1</span> to <span id="showing-end"><?php echo min(10, count($users)); ?></span> of <span id="total-entries"><?php echo count($users); ?></span> entries
              </div>
              <div class="dataTables_paginate paging_simple_numbers" id="users-table_paginate">
                <ul class="pagination">
                  <li class="paginate_button page-item previous disabled" id="users-table_previous">
                    <a href="#" aria-controls="users-table" data-dt-idx="0" tabindex="0" class="page-link">Previous</a>
                  </li>
                  <li class="paginate_button page-item active">
                    <a href="#" aria-controls="users-table" data-dt-idx="1" tabindex="0" class="page-link">1</a>
                  </li>
                  <li class="paginate_button page-item next disabled" id="users-table_next">
                    <a href="#" aria-controls="users-table" data-dt-idx="2" tabindex="0" class="page-link">Next</a>
                  </li>
                </ul>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Delete User Confirmation Modal -->
<div class="modal fade" id="deleteUserModal" tabindex="-1" role="dialog" aria-labelledby="deleteUserModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="deleteUserModalLabel">Delete User</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <p>Are you sure you want to delete the user <strong id="delete-user-name"></strong>?</p>
        <p class="text-danger">This action cannot be undone!</p>
        
        <div class="form-group mt-4">
          <label for="document-action">What would you like to do with this user's documents?</label>
          <select class="form-control" id="document-action" name="document-action">
            <option value="reassign">Reassign to admin (recommended)</option>
            <option value="orphan">Remove user association</option>
            <option value="delete">Delete all documents</option>
          </select>
          <small class="form-text text-muted">
            <strong>Reassign:</strong> Transfer ownership to admin account<br>
            <strong>Remove association:</strong> Keep documents but remove ownership<br>
            <strong>Delete:</strong> Permanently delete all user documents
          </small>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-danger" id="confirm-delete">Delete User</button>
      </div>
    </div>
  </div>
</div>


  <!-- Add User Modal -->
  <div class="modal fade" id="addUserModal" tabindex="-1" role="dialog" aria-labelledby="addUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="addUserModalLabel">Add New User</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <form id="addUserForm" action="process-add-user.php" method="post">
            <div class="row">
              <div class="col-md-4">
                <div class="form-group">
                  <label for="firstName">First Name <span class="text-danger">*</span></label>
                  <input type="text" class="form-control" id="firstName" name="firstName" required>
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label for="middleName">Middle Name</label>
                  <input type="text" class="form-control" id="middleName" name="middleName">
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label for="lastName">Last Name <span class="text-danger">*</span></label>
                  <input type="text" class="form-control" id="lastName" name="lastName" required>
                </div>
              </div>
            </div>
            
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label for="email">Email <span class="text-danger">*</span></label>
                  <input type="email" class="form-control" id="email" name="email" required>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label for="username">Username <span class="text-danger">*</span></label>
                  <input type="text" class="form-control" id="username" name="username" required>
                </div>
              </div>
            </div>
            
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label for="password">Password <span class="text-danger">*</span></label>
                  <input type="password" class="form-control" id="password" name="password" required>
                  <small class="form-text text-muted">Minimum 6 characters</small>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label for="confirmPassword">Confirm Password <span class="text-danger">*</span></label>
                  <input type="password" class="form-control" id="confirmPassword" name="confirmPassword" required>
                </div>
              </div>
            </div>
            
            <div class="row">
              <div class="col-md-4">
                <div class="form-group">
                  <label for="role">Role <span class="text-danger">*</span></label>
                  <select class="form-control" id="role" name="role" required>
                    <option value="user">Regular User</option>
                    <option value="admin">Administrator</option>
                  </select>
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label for="accessLevel">Access Level <span class="text-danger">*</span></label>
                  <select class="form-control" id="accessLevel" name="accessLevel" required>
                    <?php foreach ($accessLevels as $level): ?>
                      <option value="<?php echo $level['AccessLevelID']; ?>">
                        <?php echo htmlspecialchars($level['LevelName']); ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label for="extension">Extension</label>
                  <input type="text" class="form-control" id="extension" name="extension">
                </div>
              </div>
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
document.addEventListener('DOMContentLoaded', function() {
    // Form validation for add user
    $('#addUserForm').on('submit', function(e) {
        const password = $('#password').val();
        const confirmPassword = $('#confirmPassword').val();
        
        if (password !== confirmPassword) {
            e.preventDefault();
            alert('Passwords do not match!');
            return false;
        }
        
        if (password.length < 6) {
            e.preventDefault();
            alert('Password must be at least 6 characters long!');
            return false;
        }
        
        return true;
    });
    
    // Direct binding for delete user button click
    $(document).on('click', '.delete-user', function(e) {
        e.preventDefault();
        const userId = $(this).data('id');
        const userName = $(this).data('name');
        
        // Set values in the modal
        $('#delete-user-name').text(userName);
        // Store the user ID to a global variable for later use
        window.userIdToDelete = userId;
        
        // Reset dropdown to default option
        $('#document-action').val('reassign');
        
        // Show the modal
        $('#deleteUserModal').modal('show');
    });
    
    // Direct binding for confirm delete button click
    $(document).on('click', '#confirm-delete', function() {
        // Get the user ID from the global variable
        const userId = window.userIdToDelete;
        const documentAction = $('#document-action').val();
        
        if (!userId) {
            alert('Error: Could not determine which user to delete.');
            return;
        }
        
        console.log("Deleting user ID: " + userId + " with action: " + documentAction);
        
        // Show loading state
        $(this).prop('disabled', true).html('<i class="ti-reload fa-spin"></i> Deleting...');
        
        // Perform the AJAX request
        $.ajax({
            url: 'process-delete-user.php',
            type: 'POST',
            data: { 
                userId: userId,
                documentAction: documentAction
            },
            success: function(response) {
                console.log("Response received: " + response);
                
                if (response.trim() === 'success') {
                    // Add animation to show deletion
                    $('tr[data-user-id="' + userId + '"]').fadeOut(500, function() {
                        $(this).remove();
                        
                        // Update user count with animation
                        const currentCount = parseInt($('#user-count').text());
                        $('#user-count').text(currentCount - 1);
                        $('.user-count-update').addClass('flash-highlight');
                        setTimeout(function() {
                            $('.user-count-update').removeClass('flash-highlight');
                        }, 1500);
                        
                        // Show success notification
                        $('<div class="alert alert-success alert-dismissible fade show" role="alert">' +
                            'User deleted successfully!' +
                            '<button type="button" class="close" data-dismiss="alert" aria-label="Close">' +
                            '<span aria-hidden="true">&times;</span></button></div>')
                            .insertAfter('.grid-margin').delay(3000).fadeOut(function() {
                                $(this).remove();
                            });
                        
                        // Check if table is now empty
                        if ($('#users-table tbody tr').length === 0) {
                            $('#users-table tbody').html('<tr><td colspan="6" class="text-center">No users found.</td></tr>');
                        }
                    });
                    
                    // Close modal
                    $('#deleteUserModal').modal('hide');
                } else {
                    // Show error with details
                    $('#deleteUserModal').modal('hide');
                    $('<div class="alert alert-danger alert-dismissible fade show" role="alert">' +
                        'Error deleting user: ' + response +
                        '<button type="button" class="close" data-dismiss="alert" aria-label="Close">' +
                        '<span aria-hidden="true">&times;</span></button></div>')
                        .insertAfter('.grid-margin');
                }
            },
            error: function(xhr, status, error) {
                console.error("AJAX Error: " + status + " - " + error);
                // Show detailed error message
                $('#deleteUserModal').modal('hide');
                $('<div class="alert alert-danger alert-dismissible fade show" role="alert">' +
                    'Error: ' + status + ' - ' + error +
                    '<button type="button" class="close" data-dismiss="alert" aria-label="Close">' +
                    '<span aria-hidden="true">&times;</span></button></div>')
                    .insertAfter('.grid-margin');
            },
            complete: function() {
                // Reset button state
                $('#confirm-delete').prop('disabled', false).html('Delete User');
            }
        });
    });
});
</script>

<?php
include 'include/footer.php';
?>