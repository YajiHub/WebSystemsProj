<?php
require_once '../public/include/session.php';

// Require admin access
requireAdmin();

// Get all users
$users = getAllUsers($conn);

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
            <h3 class="font-weight-bold">Manage Users</h3>
            <h6 class="font-weight-normal mb-0">View and manage user accounts</h6>
          </div>
          <div class="col-12 col-xl-4">
            <div class="justify-content-end d-flex">
              <a href="add-user.php" class="btn btn-primary">
                <i class="ti-user mr-1"></i> Add New User
              </a>
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
    
    <!-- Users Table -->
    <div class="row">
      <div class="col-md-12 grid-margin stretch-card">
        <div class="card">
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-striped table-hover" id="users-table">
                <thead>
                  <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Username</th>
                    <th>Role</th>
                    <th>Access Level</th>
                    <th>Actions</th>
                  </tr>  
                </thead>
                <tbody>
                  <?php if (empty($users)): ?>
                    <tr>
                      <td colspan="6" class="text-center">No users found</td>
                    </tr>
                  <?php else: ?>
                    <?php foreach ($users as $user): ?>
                      <tr>
                        <td><?php echo htmlspecialchars($user['FirstName'] . ' ' . $user['LastName']); ?></td>
                        <td><?php echo htmlspecialchars($user['EmailAddress']); ?></td>
                        <td><?php echo htmlspecialchars($user['Username']); ?></td>
                        <td>
                          <span class="badge <?php echo ($user['UserRole'] == 'admin') ? 'badge-primary' : 'badge-info'; ?>">
                            <?php echo ucfirst($user['UserRole']); ?>
                          </span>
                        </td>
                        <td><?php echo htmlspecialchars($user['LevelName']); ?></td>
                        <td>
                          <a href="edit-user.php?id=<?php echo $user['UserID']; ?>" class="btn btn-primary btn-sm">
                            <i class="ti-pencil"></i> Edit
                          </a>
                          <?php if ($user['UserID'] != $_SESSION['user_id']): // Prevent deleting yourself ?>
                            <a href="delete-user.php?id=<?php echo $user['UserID']; ?>" class="btn btn-danger btn-sm delete-user">
                              <i class="ti-trash"></i> Delete
                            </a>
                          <?php endif; ?>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Delete Confirmation Modal -->
  <div class="modal fade" id="deleteUserModal" tabindex="-1" role="dialog" aria-labelledby="deleteUserModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="deleteUserModalLabel">Confirm Delete</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          Are you sure you want to delete this user? This will also delete all documents owned by this user.
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
          <a href="#" id="confirm-delete-user" class="btn btn-danger">Delete</a>
        </div>
      </div>
    </div>
  </div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  // Delete confirmation
  const deleteButtons = document.querySelectorAll('.delete-user');
  const confirmDeleteButton = document.getElementById('confirm-delete-user');
  
  deleteButtons.forEach(button => {
    button.addEventListener('click', function(e) {
      e.preventDefault();
      const deleteUrl = this.getAttribute('href');
      confirmDeleteButton.setAttribute('href', deleteUrl);
      $('#deleteUserModal').modal('show');
    });
  });
});
</script>

<?php
include '../public/include/footer.php';
?>