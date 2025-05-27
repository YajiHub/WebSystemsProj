<?php
// File: public/include/header.php
// Include session management to get current user info
require_once 'include/session.php';

// Get current user information
$currentUser = getCurrentUser($conn);
$userInitials = $currentUser ? strtoupper(substr($currentUser['FirstName'], 0, 1) . substr($currentUser['LastName'], 0, 1)) : 'GU';

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
  <title>DAMS - Document Archive Management System</title>
  <link rel="stylesheet" href="../vendors/feather/feather.css">
  <link rel="stylesheet" href="../vendors/ti-icons/css/themify-icons.css">
  <link rel="stylesheet" href="../vendors/css/vendor.bundle.base.css">
  <link rel="stylesheet" href="../css/vertical-layout-light/style.css">
  <link rel="shortcut icon" href="../images/favicon.png" />

  <link rel="stylesheet" href="vendors/datatables.net-bs4/dataTables.bootstrap4.css">
  <link rel="stylesheet" type="text/css" href="js/select.dataTables.min.css">
  
  <style>
  /* Enhanced Default profile picture styling */
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
    margin: 0 auto; /* Center the profile pic */
  }
  
  .profile-pic-large {
    width: 150px;
    height: 150px;
    font-size: 48px;
    border: 3px solid #dee2e6;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
  }
  
  /* Profile page specific styling */
  .profile-picture-container {
    display: flex;
    justify-content: center;
    align-items: center;
    width: 100%;
  }
  
  /* Ensure the card body centers everything */
  .card-body.d-flex.flex-column.justify-content-center.align-items-center {
    text-align: center;
  }
  
  /* Form styling improvements */
  .forms-sample .form-group.row {
    margin-bottom: 1.5rem;
  }
  
  .forms-sample .col-form-label {
    font-weight: 600;
    color: #495057;
  }
  
  /* Success and error message styling */
  .alert {
    border-radius: 8px;
    border: none;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
  }
  </style>
</head>
<body>
  <div class="container-scroller">
    <!-- Navigation Bar -->
    <nav class="navbar col-lg-12 col-12 p-0 fixed-top d-flex flex-row">
      <div class="text-center navbar-brand-wrapper d-flex align-items-center justify-content-center">
        <a class="navbar-brand brand-logo mr-5" href="index.php"><img src="../images/updocs.png" class="mr-2" alt="logo"  width="140" height="210"  /></a>
        <a class="navbar-brand brand-logo-mini" href="index.php"><img src="../images/logo-mini.svg" alt="logo"/></a>
      </div>
      <div class="navbar-menu-wrapper d-flex align-items-center justify-content-end">
        <button class="navbar-toggler navbar-toggler align-self-center" type="button" data-toggle="minimize">
          <span class="icon-menu"></span>
        </button>
        <ul class="navbar-nav navbar-nav-right">
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
              <a class="dropdown-item" href="profile.php">
                <i class="ti-settings text-primary"></i>
                Profile
              </a>
              <a class="dropdown-item" href="logout.php">
                <i class="ti-power-off text-primary"></i>
                Logout
              </a>
            </div>
          </li>
        </ul>
      </div>
    </nav>