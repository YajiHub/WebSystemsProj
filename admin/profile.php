<?php
require_once '../public/include/session.php';

// Require admin access
requireAdmin();

// Get current user information
$currentUser = getCurrentUser($conn);

if (!$currentUser) {
    $_SESSION['error'] = "Unable to load profile information.";
    header("Location: dashboard.php");
    exit;
}

$userInitials = strtoupper(substr($currentUser['FirstName'], 0, 1) . substr($currentUser['LastName'], 0, 1));

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
            <h3 class="font-weight-bold">Administrator Profile</h3>
            <h6 class="font-weight-normal mb-0">View and update your administrator profile information</h6>
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
    
    <!-- Profile Content -->
    <div class="row">
      <!-- Profile Picture Section - CENTERED -->
      <div class="col-md-4 grid-margin stretch-card">
        <div class="card h-100">
          <div class="card-body d-flex flex-column justify-content-center align-items-center text-center" style="min-height: 400px;">
            <!-- Centered Profile Picture Container -->
            <div class="profile-picture-container mb-4">
              <div class="default-profile-pic profile-pic-large mx-auto">
                <?php echo $userInitials; ?>
              </div>
            </div>
            
            <!-- User Info -->
            <h4 class="card-title mb-2"><?php echo htmlspecialchars($currentUser['FirstName'] . ' ' . $currentUser['LastName']); ?></h4>
            <p class="text-muted mb-3"><?php echo ucfirst($currentUser['UserRole']); ?></p>
            
            <!-- Access Level Badge -->
            <div class="text-center">
              <span class="badge badge-info mb-2">Access Level <?php echo $currentUser['AccessLevel']; ?></span>
              <?php if (!empty($currentUser['Department'])): ?>
                <br><small class="text-muted"><?php echo htmlspecialchars($currentUser['Department']); ?></small>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Profile Information Section -->
      <div class="col-md-8 grid-margin stretch-card">
        <div class="card">
          <div class="card-body">
            <h4 class="card-title">Profile Information</h4>
            
            <form action="process-update-profile.php" method="post" class="forms-sample">
              <div class="form-group row">
                <label for="firstName" class="col-sm-3 col-form-label">First Name</label>
                <div class="col-sm-9">
                  <input type="text" class="form-control" id="firstName" name="firstName" 
                         value="<?php echo htmlspecialchars($currentUser['FirstName']); ?>" required>
                </div>
              </div>
              
              <div class="form-group row">
                <label for="lastName" class="col-sm-3 col-form-label">Last Name</label>
                <div class="col-sm-9">
                  <input type="text" class="form-control" id="lastName" name="lastName" 
                         value="<?php echo htmlspecialchars($currentUser['LastName']); ?>" required>
                </div>
              </div>
              
              <div class="form-group row">
                <label for="middleName" class="col-sm-3 col-form-label">Middle Name</label>
                <div class="col-sm-9">
                  <input type="text" class="form-control" id="middleName" name="middleName" 
                         value="<?php echo htmlspecialchars($currentUser['MiddleName'] ?? ''); ?>">
                </div>
              </div>
              
              <div class="form-group row">
                <label for="email" class="col-sm-3 col-form-label">Email</label>
                <div class="col-sm-9">
                  <input type="email" class="form-control" id="email" name="email" 
                         value="<?php echo htmlspecialchars($currentUser['EmailAddress']); ?>" required>
                </div>
              </div>
              
              <div class="form-group row">
                <label for="username" class="col-sm-3 col-form-label">Username</label>
                <div class="col-sm-9">
                  <input type="text" class="form-control" id="username" name="username" 
                         value="<?php echo htmlspecialchars($currentUser['Username']); ?>" required>
                </div>
              </div>
              
              <div class="form-group row">
                <label for="extension" class="col-sm-3 col-form-label">Extension</label>
                <div class="col-sm-9">
                  <input type="text" class="form-control" id="extension" name="extension" 
                         value="<?php echo htmlspecialchars($currentUser['Extension'] ?? ''); ?>">
                </div>
              </div>
              
              <?php if (!empty($currentUser['Department'])): ?>
              <div class="form-group row">
                <label for="department" class="col-sm-3 col-form-label">Department</label>
                <div class="col-sm-9">
                  <input type="text" class="form-control" id="department" 
                         value="<?php echo htmlspecialchars($currentUser['Department']); ?>" readonly>
                </div>
              </div>
              <?php endif; ?>
              
              <div class="form-group row">
                <label for="accessLevel" class="col-sm-3 col-form-label">Access Level</label>
                <div class="col-sm-9">
                  <input type="text" class="form-control" id="accessLevel" 
                         value="Level <?php echo $currentUser['AccessLevel']; ?>" readonly>
                </div>
              </div>
              
              <div class="form-group row">
                <label for="userRole" class="col-sm-3 col-form-label">Role</label>
                <div class="col-sm-9">
                  <input type="text" class="form-control" id="userRole" 
                         value="<?php echo ucfirst($currentUser['UserRole']); ?>" readonly>
                </div>
              </div>
              
              <button type="submit" class="btn btn-primary mr-2">Update Profile</button>
              <button type="button" class="btn btn-light" onclick="window.location.href='dashboard.php'">Cancel</button>
            </form>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Change Password Section -->
    <div class="row">
      <div class="col-md-12 grid-margin stretch-card">
        <div class="card">
          <div class="card-body">
            <h4 class="card-title">Change Password</h4>
            
            <form action="process-change-password.php" method="post" class="forms-sample">
              <div class="form-group row">
                <label for="currentPassword" class="col-sm-3 col-form-label">Current Password</label>
                <div class="col-sm-9">
                  <input type="password" class="form-control" id="currentPassword" name="currentPassword" required>
                </div>
              </div>
              
              <div class="form-group row">
                <label for="newPassword" class="col-sm-3 col-form-label">New Password</label>
                <div class="col-sm-9">
                  <input type="password" class="form-control" id="newPassword" name="newPassword" required>
                </div>
              </div>
              
              <div class="form-group row">
                <label for="confirmPassword" class="col-sm-3 col-form-label">Confirm New Password</label>
                <div class="col-sm-9">
                  <input type="password" class="form-control" id="confirmPassword" name="confirmPassword" required>
                </div>
              </div>
              
              <button type="submit" class="btn btn-primary mr-2">Change Password</button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>

<?php
include 'include/footer.php';
?>
