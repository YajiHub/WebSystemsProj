<!-- Main Content Area -->
<div class="container-fluid page-body-wrapper">
  <!-- Sidebar -->
  <nav class="sidebar sidebar-offcanvas" id="sidebar">
    <ul class="nav">
      <li class="nav-item">
        <a class="nav-link" href="dashboard.php">
          <i class="icon-grid menu-icon"></i>
          <span class="menu-title">Dashboard</span>
        </a>
      </li>
      <!-- Personal Document Management Section -->
      <li class="nav-item">
        <a class="nav-link" data-toggle="collapse" href="#personal-docs" aria-expanded="false" aria-controls="personal-docs">
          <i class="icon-doc menu-icon"></i>
          <span class="menu-title">My Documents</span>
          <i class="menu-arrow"></i>
        </a>
        <div class="collapse" id="personal-docs">
          <ul class="nav flex-column sub-menu">
            <li class="nav-item"> <a class="nav-link" href="my-documents.php">View Documents</a></li>
            <li class="nav-item"> <a class="nav-link" href="upload.php">Upload Document</a></li>
            <li class="nav-item"> <a class="nav-link" href="admin-trash.php">Trash</a></li>
          </ul>
        </div>
      </li>
      <!-- System Management Section -->
      <li class="nav-item">
        <a class="nav-link" data-toggle="collapse" href="#system-mgmt" aria-expanded="false" aria-controls="system-mgmt">
          <i class="icon-layers menu-icon"></i>
          <span class="menu-title">System Management</span>
          <i class="menu-arrow"></i>
        </a>
        <div class="collapse" id="system-mgmt">
          <ul class="nav flex-column sub-menu">
            <li class="nav-item"> <a class="nav-link" href="manage-documents.php">All Documents</a></li>
            <li class="nav-item"> <a class="nav-link" href="manage-users.php">Users</a></li>
            <li class="nav-item"> <a class="nav-link" href="upload-for-user.php">Upload for User</a></li>
            <li class="nav-item"> <a class="nav-link" href="flagged-documents.php">Flagged Documents</a></li>
          </ul>
        </div>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="system-settings.php">
          <i class="icon-cog menu-icon"></i>
          <span class="menu-title">System Settings</span>
        </a>
      </li>
    </ul>
  </nav>