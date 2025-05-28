<?php
require_once '../public/include/session.php';

// Require admin access
requireAdmin();

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
            <h3 class="font-weight-bold">Add New User</h3>
            <h6 class="font-weight-normal mb-0">Create a new user account</h6>
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
    
    <!-- Add User Form -->
    <div class="row">
      <div class="col-md-12 grid-margin stretch-card">
        <div class="card">
          <div class="card-body">
            <h4 class="card-title">User Information</h4>
            
            <form action="process-add-user.php" method="post" class="forms-sample">
              <div class="row">
                <div class="col-md-4">
                  <div class="form-group">
                    <label for="firstName">First Name</label>
                    <input type="text" class="form-control" id="firstName" name="firstName" required>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <label for="middleName">Middle Name (Optional)</label>
                    <input type="text" class="form-control" id="middleName" name="middleName">
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <label for="lastName">Last Name</label>
                    <input type="text" class="form-control" id="lastName" name="lastName" required>
                  </div>
                </div>
              </div>
              
              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label for="extension">Extension (Optional)</label>
                    <input type="text" class="form-control" id="extension" name="extension">
                  </div>
                </div>
              </div>
              
              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" class="form-control" id="username" name="username" required>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                  </div>
                </div>
              </div>
              
              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label for="userRole">User Role</label>
                    <select class="form-control" id="userRole" name="userRole" required>
                      <option value="user">User</option>
                      <option value="admin">Administrator</option>
                    </select>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label for="accessLevel">Access Level</label>
                    <select class="form-control" id="accessLevel" name="accessLevel" required>
                      <?php foreach ($accessLevels as $level): ?>
                        <option value="<?php echo $level['AccessLevelID']; ?>">
                          <?php echo htmlspecialchars($level['LevelName']); ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                </div>
              </div>
              
              <button type="submit" class="btn btn-primary mr-2">Add User</button>
              <a href="manage-users.php" class="btn btn-light">Cancel</a>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>

<?php
include '../public/include/footer.php';
?>