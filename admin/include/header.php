<?php
// File: admin/include/header.php
// Include session management to get current user info
require_once '../public/include/session.php';

// Get current user information
$currentUser = getCurrentUser($conn);
$userInitials = $currentUser ? strtoupper(substr($currentUser['FirstName'], 0, 1) . substr($currentUser['LastName'], 0, 1)) : 'AD';

// Check if user has a custom profile picture
$hasCustomPicture = false;
$profilePicture = '';
if ($currentUser && !empty($currentUser['ProfilePicture']) && file_exists($currentUser['ProfilePicture'])) {
    $hasCustomPicture = true;
    $profilePicture = $currentUser['ProfilePicture'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title>DAMS - Admin Panel</title>
  <link rel="stylesheet" href="../vendors/feather/feather.css">
  <link rel="stylesheet" href="../vendors/ti-icons/css/themify-icons.css">
  <link rel="stylesheet" href="../vendors/css/vendor.bundle.base.css">
  <link rel="stylesheet" href="../css/vertical-layout-light/style.css">
  <link rel="shortcut icon" href="../images/favicon.png" />

  <link rel="stylesheet" href="../vendors/datatables.net-bs4/dataTables.bootstrap4.css">
  <link rel="stylesheet" type="text/css" href="../js/select.dataTables.min.css">

  <style>
  /* Default profile picture styling */
  .default-profile-pic {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background-color: #e9ecef;
    border: 2px solid #dee2e6;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    font-weight: 600;
    color: #6c757d;
  }
  
  .profile-pic-large {
    width: 150px;
    height: 150px;
    font-size: 48px;
  }
  </style>

</head>
<body>
  <div class="container-scroller">
    <!-- Navigation Bar -->
    <nav class="navbar col-lg-12 col-12 p-0 fixed-top d-flex flex-row">
      <div class="text-center navbar-brand-wrapper d-flex align-items-center justify-content-center">
        <a class="navbar-brand brand-logo mr-5" href="dashboard.php"><img src="../images/updocs.png" class="mr-2" alt="logo"   width="120" height="200" /></a>
        <a class="navbar-brand brand-logo-mini" href="dashboard.php"><img src="../images/updocssmall.png" alt="logo"/></a>
      </div>
      <div class="navbar-menu-wrapper d-flex align-items-center justify-content-end">
        <button class="navbar-toggler navbar-toggler align-self-center" type="button" data-toggle="minimize">
          <span class="icon-menu"></span>
        </button>
        <ul class="navbar-nav navbar-nav-right">
          <li class="nav-item dropdown">
            <a class="nav-link count-indicator dropdown-toggle" id="notificationDropdown" href="#" data-toggle="dropdown">
              <i class="icon-bell mx-0"></i>
              <span class="count"></span>
            </a>
            <div class="dropdown-menu dropdown-menu-right navbar-dropdown preview-list" aria-labelledby="notificationDropdown">
              <p class="mb-0 font-weight-normal float-left dropdown-header">Notifications</p>
              <a class="dropdown-item preview-item">
                <div class="preview-thumbnail">
                  <div class="preview-icon bg-warning">
                    <i class="ti-flag-alt mx-0"></i>
                  </div>
                </div>
                <div class="preview-item-content">
                  <h6 class="preview-subject font-weight-normal">Document Flagged</h6>
                  <p class="font-weight-light small-text mb-0 text-muted">
                    Just now
                  </p>
                </div>
              </a>
              <a class="dropdown-item preview-item">
                <div class="preview-thumbnail">
                  <div class="preview-icon bg-info">
                    <i class="ti-user mx-0"></i>
                  </div>
                </div>
                <div class="preview-item-content">
                  <h6 class="preview-subject font-weight-normal">New user registration</h6>
                  <p class="font-weight-light small-text mb-0 text-muted">
                    2 days ago
                  </p>
                </div>
              </a>
            </div>
          </li>
          <li class="nav-item nav-profile dropdown">
            <a class="nav-link dropdown-toggle" href="#" data-toggle="dropdown" id="profileDropdown">
              <?php if ($hasCustomPicture): ?>
                <img src="<?php echo $profilePicture; ?>" alt="profile"/>
              <?php else: ?>
                <div class="default-profile-pic">
                  <?php echo $userInitials; ?>
                </div>
              <?php endif; ?>
            </a>
            <div class="dropdown-menu dropdown-menu-right navbar-dropdown" aria-labelledby="profileDropdown">
              <a class="dropdown-item" href="../admin/profile.php">
                <i class="ti-settings text-primary"></i>
                Profile
              </a>
              <a class="dropdown-item" href="../public/logout.php">
                <i class="ti-power-off text-primary"></i>
                Logout
              </a>
            </div>
          </li>
        </ul>
      </div>
    </nav>
