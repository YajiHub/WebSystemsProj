<?php
require_once '../public/include/session.php';

// Require admin access
requireAdmin();

// Check if user ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error'] = "No user specified.";
    header("Location: manage-users.php");
    exit;
}

$userId = (int)$_GET['id'];

// Get user information
$user = getUserById($conn, $userId);

// Check if user exists
if (!$user) {
    $_SESSION['error'] = "User not found.";
    header("Location: manage-users.php");
    exit;
}

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
            <h3 class="font-weight-bold">Edit User</h3>
            <h6 class="font-weight-normal mb-0">Edit user account information</h6>
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
    
    <!-- Edit User Form -->
    <div class="row">
      <div class="col-md-12 grid-margin stretch-card">
        <div class="card">
          <div class="card-body">
            <h4 class="card-title">User Information</h4>
            
            <form action="process-edit-user.php" method="post" class="forms-sample">
              <input type="hidden" name="userId" value="<?php echo $user['UserID']; ?>">
              
              <div class="form-group row">
                <label for="firstName" class="col-sm-3 col-form-label">First Name</label>
                <div class="col-sm-9">
                  <input type="text" class="form-control" id="firstName" name="firstName" 
                         value="<?php echo htmlspecialchars($user['FirstName']); ?>" required>
                </div>
              </div>
              
              <div class="form-group row">
                <label for="middleName" class="col-sm-3 col-form-label">Middle Name</label>
                <div class="col-sm-9">
                  <input type="text" class="form-control" id="middleName" name="middleName" 
                         value="<?php echo htmlspecialchars($user['MiddleName'] ?? ''); ?>">
                </div>
              </div>
              
              <div class="form-group row">
                <label for="lastName" class="col-sm-3 col-form-label">Last Name</label>
                <div class="col-sm-9">
                  <input type="text" class="form-control" id="lastName" name="lastName" 
                         value="<?php echo htmlspecialchars($user['LastName']); ?>" required>
                </div>
              </div>
              
              <div class="form-group row">
                <label for="email" class="col-sm-3 col-form-label">Email</label>
                <div class="col-sm-9">
                  <input type="email" class="form-control" id="email" name="email" 
                         value="<?php echo htmlspecialchars($user['EmailAddress']); ?>" required>
                </div>
              </div>
              
              <div class="form-group row">
                <label for="extension" class="col-sm-3 col-form-label">Extension</label>
                <div class="col-sm-9">
                  <input type="text" class="form-control" id="extension" name="extension" 
                         value="<?php echo htmlspecialchars($user['Extension'] ?? ''); ?>">
                </div>
              </div>
              
              <div class="form-group row">
                <label for="userRole" class="col-sm-3 col-form-label">User Role</label>
                <div class="col-sm-9">
                  <select class="form-control" id="userRole" name="userRole" required>
                    <option value="user" <?php echo ($user['UserRole'] == 'user') ? 'selected' : ''; ?>>User</option>
                    <option value="admin" <?php echo ($user['UserRole'] == 'admin') ? 'selected' : ''; ?>>Administrator</option>
                  </select>
                </div>
              </div>
              
              <div class="form-group row">
                <label for="accessLevel" class="col-sm-3 col-form-label">Access Level</label>
                <div class="col-sm-9">
                  <select class="form-control" id="accessLevel" name="accessLevel" required>
                    <?php foreach ($accessLevels as $level): ?>
                      <option value="<?php echo $level['AccessLevelID']; ?>" 
                              <?php echo ($user['AccessLevel'] == $level['AccessLevelID']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($level['LevelName']); ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>
              </div>
              
              <div class="form-group row">
                <label for="resetPassword" class="col-sm-3 col-form-label">Reset Password</label>
                <div class="col-sm-9">
                  <div class="form-check form-check-flat form-check-primary">
                    <label class="form-check-label">
                      <input type="checkbox" class="form-check-input" id="resetPassword" name="resetPassword" value="1" onchange="togglePasswordField()">
                      Reset user's password
                    </label>
                  </div>
                </div>
              </div>
              
              <div class="form-group row" id="newPasswordRow" style="display: none;">
                <label for="newPassword" class="col-sm-3 col-form-label">New Password</label>
                <div class="col-sm-9">
                  <input type="password" class="form-control" id="newPassword" name="newPassword">
                </div>
              </div>
              
              <div class="form-group row">
                <label class="col-sm-3 col-form-label">Username</label>
                <div class="col-sm-9">
                  <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['Username']); ?>" readonly>
                  <small class="form-text text-muted">Username cannot be changed.</small>
                </div>
              </div>
              
              <button type="submit" class="btn btn-primary mr-2">Save Changes</button>
              <a href="manage-users.php" class="btn btn-light">Cancel</a>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>

<script>
function togglePasswordField() {
  var resetCheckbox = document.getElementById('resetPassword');
  var passwordRow = document.getElementById('newPasswordRow');
  var passwordField = document.getElementById('newPassword');
  
  if (resetCheckbox.checked) {
    passwordRow.style.display = 'flex';
    passwordField.required = true;
  } else {
    passwordRow.style.display = 'none';
    passwordField.required = false;
  }
}
</script>

<?php
include '../public/include/footer.php';
?>