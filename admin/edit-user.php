<?php
require_once '../public/include/session.php';

// Require admin login
requireAdmin();

// Get user ID from URL parameter
$user_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($user_id <= 0) {
    $_SESSION['error'] = "Invalid user ID.";
    header("Location: manage-users.php");
    exit;
}

// Get user details from database
$sql = "SELECT 
    u.*,
    a.LevelName,
    a.AccessLevelID
FROM user u
LEFT JOIN accesslevel a ON u.AccessLevel = a.AccessLevelID
WHERE u.UserID = ?";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

if (!$user) {
    $_SESSION['error'] = "User not found.";
    header("Location: manage-users.php");
    exit;
}

// Get access levels for the form
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
  <div class="content-wrapper">
    <!-- Page Title -->
    <div class="row">
      <div class="col-md-12 grid-margin">
        <div class="row">
          <div class="col-12 col-xl-8 mb-4 mb-xl-0">
            <h3 class="font-weight-bold">Edit User: <?php echo htmlspecialchars($user['FirstName'] . ' ' . $user['LastName']); ?></h3>
            <nav aria-label="breadcrumb">
              <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="manage-users.php">Users</a></li>
                <li class="breadcrumb-item"><a href="view-user.php?id=<?php echo $user_id; ?>">View User</a></li>
                <li class="breadcrumb-item active" aria-current="page">Edit User</li>
              </ol>
            </nav>
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
            <h4 class="card-title">Edit User Information</h4>
            <p class="card-description">
              Update user details and permissions
            </p>
            
            <form id="editUserForm" action="process-edit-user.php" method="post">
              <input type="hidden" name="userId" value="<?php echo $user_id; ?>">
              
              <div class="row">
                <div class="col-md-4">
                  <div class="form-group">
                    <label for="firstName">First Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="firstName" name="firstName" value="<?php echo htmlspecialchars($user['FirstName']); ?>" required>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <label for="middleName">Middle Name</label>
                    <input type="text" class="form-control" id="middleName" name="middleName" value="<?php echo htmlspecialchars($user['MiddleName'] ?? ''); ?>">
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <label for="lastName">Last Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="lastName" name="lastName" value="<?php echo htmlspecialchars($user['LastName']); ?>" required>
                  </div>
                </div>
              </div>
              
              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label for="email">Email <span class="text-danger">*</span></label>
                    <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['EmailAddress']); ?>" required>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label for="username">Username <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($user['Username']); ?>" required>
                  </div>
                </div>
              </div>
              
              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label for="newPassword">New Password</label>
                    <input type="password" class="form-control" id="newPassword" name="newPassword" placeholder="Leave blank to keep current password">
                    <small class="form-text text-muted">Minimum 6 characters</small>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label for="confirmPassword">Confirm New Password</label>
                    <input type="password" class="form-control" id="confirmPassword" name="confirmPassword" placeholder="Leave blank to keep current password">
                  </div>
                </div>
              </div>
              
              <div class="row">
                <div class="col-md-4">
                  <div class="form-group">
                    <label for="role">Role <span class="text-danger">*</span></label>
                    <select class="form-control" id="role" name="role" required>
                      <option value="user" <?php echo $user['UserRole'] == 'user' ? 'selected' : ''; ?>>Regular User</option>
                      <option value="admin" <?php echo $user['UserRole'] == 'admin' ? 'selected' : ''; ?>>Administrator</option>
                    </select>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <label for="accessLevel">Access Level <span class="text-danger">*</span></label>
                    <select class="form-control" id="accessLevel" name="accessLevel" required>
                      <?php foreach ($accessLevels as $level): ?>
                        <option value="<?php echo $level['AccessLevelID']; ?>" <?php echo $user['AccessLevel'] == $level['AccessLevelID'] ? 'selected' : ''; ?>>
                          <?php echo htmlspecialchars($level['LevelName']); ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <label for="extension">Extension</label>
                    <input type="text" class="form-control" id="extension" name="extension" value="<?php echo htmlspecialchars($user['Extension'] ?? ''); ?>">
                  </div>
                </div>
              </div>
              
              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label for="extension">Extension</label>
                    <input type="text" class="form-control" id="extension" name="extension" value="<?php echo htmlspecialchars($user['Extension'] ?? ''); ?>">
                  </div>
                </div>
              </div>
              
              <div class="form-group">
                <label for="notes">Admin Notes</label>
                <textarea class="form-control" id="notes" name="notes" rows="3"><?php echo htmlspecialchars($user['Notes'] ?? ''); ?></textarea>
              </div>
              
              <div class="mt-4">
                <button type="submit" class="btn btn-primary mr-2">Save Changes</button>
                <a href="view-user.php?id=<?php echo $user_id; ?>" class="btn btn-light">Cancel</a>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>

<script>
$(document).ready(function() {
    // Form validation
    $('#editUserForm').on('submit', function(e) {
        const newPassword = $('#newPassword').val();
        const confirmPassword = $('#confirmPassword').val();
        
        // Check if passwords match (only if new password is being set)
        if (newPassword !== '' && newPassword !== confirmPassword) {
            e.preventDefault();
            alert('New passwords do not match!');
            return false;
        }
        
        // Check password length (only if new password is being set)
        if (newPassword !== '' && newPassword.length < 6) {
            e.preventDefault();
            alert('New password must be at least 6 characters long!');
            return false;
        }
        
        return true;
    });
});
</script>

<?php
include 'include/footer.php';
?>