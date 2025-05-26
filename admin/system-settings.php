<?php
// File: admin/system-settings.php
session_start();

// // Check if user is logged in and is admin
// if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
//     header("Location: ../public/login.php");
//     exit;
// }

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
            <h3 class="font-weight-bold">System Settings</h3>
            <h6 class="font-weight-normal mb-0">Configure system parameters and manage categories</h6>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Settings Tabs -->
    <div class="row">
      <div class="col-md-12 grid-margin stretch-card">
        <div class="card">
          <div class="card-body">
            <ul class="nav nav-tabs" id="settingsTabs" role="tablist">
              <li class="nav-item">
                <a class="nav-link active" id="general-tab" data-toggle="tab" href="#general" role="tab" aria-controls="general" aria-selected="true">General</a>
              </li>
              <li class="nav-item">
                <a class="nav-link" id="category-tab" data-toggle="tab" href="#category" role="tab" aria-controls="category" aria-selected="false">Document Categories</a>
              </li>
              <li class="nav-item">
                <a class="nav-link" id="access-tab" data-toggle="tab" href="#access" role="tab" aria-controls="access" aria-selected="false">Access Levels</a>
              </li>
              <li class="nav-item">
                <a class="nav-link" id="backup-tab" data-toggle="tab" href="#backup" role="tab" aria-controls="backup" aria-selected="false">Backup & Restore</a>
              </li>
            </ul>
            
            <div class="tab-content mt-4" id="settingsTabContent">
              <!-- General Settings -->
              <div class="tab-pane fade show active" id="general" role="tabpanel" aria-labelledby="general-tab">
                <form class="forms-sample" action="process-update-settings.php" method="post">
                  <input type="hidden" name="setting_type" value="general">
                  
                  <div class="form-group row">
                    <label for="systemName" class="col-sm-3 col-form-label">System Name</label>
                    <div class="col-sm-9">
                      <input type="text" class="form-control" id="systemName" name="systemName" value="Document Archive Management System" required>
                    </div>
                  </div>
                  
                  <div class="form-group row">
                    <label for="organizationName" class="col-sm-3 col-form-label">Organization Name</label>
                    <div class="col-sm-9">
                      <input type="text" class="form-control" id="organizationName" name="organizationName" value="Your Organization" required>
                    </div>
                  </div>
                  
                  <div class="form-group row">
                    <label for="adminEmail" class="col-sm-3 col-form-label">Admin Email</label>
                    <div class="col-sm-9">
                      <input type="email" class="form-control" id="adminEmail" name="adminEmail" value="admin@example.com" required>
                      <small class="form-text text-muted">Used for system notifications and alerts.</small>
                    </div>
                  </div>
                  
                  <div class="form-group row">
                    <label for="userRegistration" class="col-sm-3 col-form-label">User Registration</label>
                    <div class="col-sm-9">
                      <select class="form-control" id="userRegistration" name="userRegistration">
                        <option value="open">Open to All</option>
                        <option value="approval" selected>Requires Admin Approval</option>
                        <option value="closed">Closed (Admin Creates Accounts)</option>
                      </select>
                    </div>
                  </div>
                  
                  <button type="submit" class="btn btn-primary mr-2">Save Changes</button>
                </form>
              </div>
              
              <!-- Document Categories -->
              <div class="tab-pane fade" id="category" role="tabpanel" aria-labelledby="category-tab">
                <div class="row mb-4">
                  <div class="col-md-6">
                    <h4>Current Categories</h4>
                  </div>
                  <div class="col-md-6 text-right">
                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addCategoryModal">
                      <i class="ti-plus mr-1"></i> Add New Category
                    </button>
                  </div>
                </div>
                
                <div class="table-responsive">
                  <table class="table table-striped">
                    <thead>
                      <tr>
                        <th>ID</th>
                        <th>Category Name</th>
                        <th>Description</th>
                        <th>Documents</th>
                        <th>Actions</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php
                      // Sample data - Would be replaced with actual data from database
                      $categories = [
                        [1, 'Reports', 'Financial and operational reports', 35],
                        [2, 'Contracts', 'Legal contracts and agreements', 25],
                        [3, 'Presentations', 'Presentations and slideshows', 20],
                        [4, 'Invoices', 'Bills and invoices', 15],
                        [5, 'Marketing', 'Marketing and promotional materials', 22]
                      ];
                      
                      foreach ($categories as $category) {
                        echo '<tr>';
                        echo '<td>' . $category[0] . '</td>';
                        echo '<td>' . $category[1] . '</td>';
                        echo '<td>' . $category[2] . '</td>';
                        echo '<td>' . $category[3] . '</td>';
                        echo '<td>
                                <button type="button" class="btn btn-primary btn-sm edit-category" data-id="' . $category[0] . '" data-name="' . $category[1] . '" data-desc="' . $category[2] . '">
                                  <i class="ti-pencil"></i>
                                </button>
                                <button type="button" class="btn btn-danger btn-sm delete-category" data-id="' . $category[0] . '">
                                  <i class="ti-trash"></i>
                                </button>
                              </td>';
                        echo '</tr>';
                      }
                      ?>
                    </tbody>
                  </table>
                </div>
              </div>
              
              <!-- Access Levels -->
              <div class="tab-pane fade" id="access" role="tabpanel" aria-labelledby="access-tab">
                <div class="row mb-4">
                  <div class="col-md-6">
                    <h4>Access Levels</h4>
                    <p class="text-muted">Define access levels and their permissions</p>
                  </div>
                  <div class="col-md-6 text-right">
                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addAccessLevelModal">
                      <i class="ti-plus mr-1"></i> Add New Level
                    </button>
                  </div>
                </div>
                
                <div class="table-responsive">
                  <table class="table table-striped">
                    <thead>
                      <tr>
                        <th>Level</th>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Permissions</th>
                        <th>Actions</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php
                      // Sample data - Would be replaced with actual data from database
                      $access_levels = [
                        [1, 'Level 1', 'Basic access - View only', 'View'],
                        [2, 'Level 2', 'Standard access - View and download', 'View, Download'],
                        [3, 'Level 3', 'Enhanced access - View, download, and upload', 'View, Download, Upload'],
                        [4, 'Level 4', 'Advanced access - All standard features and editing', 'View, Download, Upload, Edit'],
                        [5, 'Level 5', 'Complete access - All features including admin tools', 'View, Download, Upload, Edit, Delete, Flag']
                      ];
                      
                      foreach ($access_levels as $level) {
                        echo '<tr>';
                        echo '<td>' . $level[0] . '</td>';
                        echo '<td>' . $level[1] . '</td>';
                        echo '<td>' . $level[2] . '</td>';
                        echo '<td>' . $level[3] . '</td>';
                        echo '<td>
                                <button type="button" class="btn btn-primary btn-sm edit-access-level" data-id="' . $level[0] . '">
                                  <i class="ti-pencil"></i>
                                </button>
                              </td>';
                        echo '</tr>';
                      }
                      ?>
                    </tbody>
                  </table>
                </div>
              </div>
              
              <!-- Backup & Restore -->
              <div class="tab-pane fade" id="backup" role="tabpanel" aria-labelledby="backup-tab">
                <div class="row">
                  <div class="col-md-6">
                    <div class="card">
                      <div class="card-body">
                        <h4 class="card-title">Backup</h4>
                        <p class="card-description">Create a backup of the system</p>
                        
                        <form action="process-backup.php" method="post">
                          <div class="form-group">
                            <div class="form-check form-check-flat form-check-primary">
                              <label class="form-check-label">
                                <input type="checkbox" class="form-check-input" name="backup_database" checked>
                                Include Database
                              </label>
                            </div>
                          </div>
                          
                          <div class="form-group">
                            <div class="form-check form-check-flat form-check-primary">
                              <label class="form-check-label">
                                <input type="checkbox" class="form-check-input" name="backup_files" checked>
                                Include Document Files
                              </label>
                            </div>
                          </div>
                          
                          <div class="form-group">
                            <div class="form-check form-check-flat form-check-primary">
                              <label class="form-check-label">
                                <input type="checkbox" class="form-check-input" name="backup_settings" checked>
                                Include System Settings
                              </label>
                            </div>
                          </div>
                          
                          <button type="submit" class="btn btn-primary">Create Backup</button>
                        </form>
                      </div>
                    </div>
                  </div>
                  
                  <div class="col-md-6">
                    <div class="card">
                      <div class="card-body">
                        <h4 class="card-title">Restore</h4>
                        <p class="card-description">Restore from a backup file</p>
                        
                        <form action="process-restore.php" method="post" enctype="multipart/form-data">
                          <div class="form-group">
                            <label>Backup File</label>
                            <input type="file" name="backupFile" class="file-upload-default" required>
                            <div class="input-group col-xs-12">
                              <input type="text" class="form-control file-upload-info" disabled placeholder="Upload Backup File">
                              <span class="input-group-append">
                                <button class="file-upload-browse btn btn-primary" type="button">Browse</button>
                              </span>
                            </div>
                            <small class="form-text text-muted">Select a backup file to restore from.</small>
                          </div>
                          
                          <div class="alert alert-warning">
                            <i class="ti-alert mr-2"></i> Warning: Restoring will overwrite current data. Make sure to backup before proceeding.
                          </div>
                          
                          <button type="submit" class="btn btn-warning">Restore System</button>
                        </form>
                      </div>
                    </div>
                  </div>
                </div>
                
                <div class="row mt-4">
                  <div class="col-md-12">
                    <div class="card">
                      <div class="card-body">
                        <h4 class="card-title">Backup History</h4>
                        <div class="table-responsive">
                          <table class="table table-striped">
                            <thead>
                              <tr>
                                <th>Backup Date</th>
                                <th>Size</th>
                                <th>Type</th>
                                <th>Created By</th>
                                <th>Actions</th>
                              </tr>
                            </thead>
                            <tbody>
                              <?php
                              // Sample data - Would be replaced with actual data from database
                              $backups = [
                                ['2023-06-01 12:45:32', '125 MB', 'Full Backup', 'admin'],
                                ['2023-05-25 09:30:15', '92 MB', 'Database Only', 'system'],
                                ['2023-05-18 17:20:44', '118 MB', 'Full Backup', 'admin']
                              ];
                              
                              foreach ($backups as $backup) {
                                echo '<tr>';
                                echo '<td>' . $backup[0] . '</td>';
                                echo '<td>' . $backup[1] . '</td>';
                                echo '<td>' . $backup[2] . '</td>';
                                echo '<td>' . $backup[3] . '</td>';
                                echo '<td>
                                        <a href="download-backup.php?date=' . urlencode($backup[0]) . '" class="btn btn-primary btn-sm">
                                          <i class="ti-download"></i> Download
                                        </a>
                                        <button type="button" class="btn btn-danger btn-sm delete-backup" data-date="' . $backup[0] . '">
                                          <i class="ti-trash"></i> Delete
                                        </button>
                                      </td>';
                                echo '</tr>';
                              }
                              ?>
                            </tbody>
                          </table>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Add Category Modal -->
  <div class="modal fade" id="addCategoryModal" tabindex="-1" role="dialog" aria-labelledby="addCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="addCategoryModalLabel">Add New Category</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <form id="addCategoryForm" action="process-add-category.php" method="post">
            <div class="form-group">
              <label for="categoryName">Category Name</label>
              <input type="text" class="form-control" id="categoryName" name="categoryName" required>
            </div>
            <div class="form-group">
              <label for="categoryDescription">Description</label>
              <textarea class="form-control" id="categoryDescription" name="categoryDescription" rows="3"></textarea>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
              <button type="submit" class="btn btn-primary">Add Category</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <!-- Edit Category Modal -->
  <div class="modal fade" id="editCategoryModal" tabindex="-1" role="dialog" aria-labelledby="editCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="editCategoryModalLabel">Edit Category</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <form id="editCategoryForm" action="process-edit-category.php" method="post">
            <input type="hidden" id="editCategoryId" name="categoryId" value="">
            <div class="form-group">
              <label for="editCategoryName">Category Name</label>
              <input type="text" class="form-control" id="editCategoryName" name="categoryName" required>
            </div>
            <div class="form-group">
              <label for="editCategoryDescription">Description</label>
              <textarea class="form-control" id="editCategoryDescription" name="categoryDescription" rows="3"></textarea>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
              <button type="submit" class="btn btn-primary">Save Changes</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

<script>
  $(document).ready(function() {
    // File upload script
    $('.file-upload-browse').on('click', function() {
      var file = $(this).parent().parent().parent().find('.file-upload-default');
      file.trigger('click');
    });
    
    $('.file-upload-default').on('change', function() {
      var fileName = $(this).val().split('\\').pop();
      $(this).parent().find('.form-control').val(fileName);
    });
    
    // Edit category
    $('.edit-category').on('click', function() {
      var categoryId = $(this).data('id');
      var categoryName = $(this).data('name');
      var categoryDesc = $(this).data('desc');
      
      $('#editCategoryId').val(categoryId);
      $('#editCategoryName').val(categoryName);
      $('#editCategoryDescription').val(categoryDesc);
      
      $('#editCategoryModal').modal('show');
    });
    
    // Delete category
    $('.delete-category').on('click', function() {
      if (confirm('Are you sure you want to delete this category? Documents in this category will need to be reassigned.')) {
        var categoryId = $(this).data('id');
        // In a real application, you would make an AJAX call to delete the category
        console.log('Deleting category ' + categoryId);
        // Reload page or update UI
      }
    });
    
    // Delete backup
    $('.delete-backup').on('click', function() {
      if (confirm('Are you sure you want to delete this backup? This action cannot be undone.')) {
        var backupDate = $(this).data('date');
        // In a real application, you would make an AJAX call to delete the backup
        console.log('Deleting backup from ' + backupDate);
        // Reload page or update UI
      }
    });
  });
</script>

<?php
include 'include/footer.php';
?>