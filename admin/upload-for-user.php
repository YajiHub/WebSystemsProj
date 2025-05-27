<?php
// File: admin/upload-for-user.php
// Include session management
require_once '../public/include/session.php';

// Include database functions
require_once '../public/include/db_functions.php';

// Require admin login
requireAdmin();

// Get all users for selection
$users = getAllUsers($conn);

// Get categories for the form
$categories = getAllCategories($conn);

// Get user ID from URL parameter if provided
$selected_user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;

// Get selected user details if user_id is provided
$selectedUser = null;
if ($selected_user_id > 0) {
    $selectedUser = getUserById($conn, $selected_user_id);
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
            <h3 class="font-weight-bold">Upload Document for User</h3>
            <h6 class="font-weight-normal mb-0">Upload documents directly to a user's account</h6>
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
    
    <!-- User Selection Form -->
    <div class="row mb-4">
      <div class="col-md-12 grid-margin stretch-card">
        <div class="card">
          <div class="card-body">
            <h4 class="card-title">Select User</h4>
            <p class="card-description">
              Choose a user to upload documents for
            </p>
            
            <form action="upload-for-user.php" method="get">
              <div class="form-group">
                <label for="user_id">Select User</label>
                <select class="form-control" id="user_id" name="user_id" required onchange="this.form.submit()">
                  <option value="">-- Select User --</option>
                  <?php foreach ($users as $user): ?>
                    <option value="<?php echo $user['UserID']; ?>" <?php echo ($selected_user_id == $user['UserID']) ? 'selected' : ''; ?>>
                      <?php echo htmlspecialchars($user['FirstName'] . ' ' . $user['LastName'] . ' (' . $user['EmailAddress'] . ')'); ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
    
    <?php if ($selectedUser): ?>
    <!-- Upload Form -->
    <div class="row">
      <div class="col-md-12 grid-margin stretch-card">
        <div class="card">
          <div class="card-body">
            <h4 class="card-title">Upload Document for <?php echo htmlspecialchars($selectedUser['FirstName'] . ' ' . $selectedUser['LastName']); ?></h4>
            <p class="card-description">
              Supported file types: PDF, JPG, PNG | Max file size: 10MB
            </p>
            
            <form class="forms-sample" action="process-upload-for-user.php" method="post" enctype="multipart/form-data">
              <input type="hidden" name="userId" value="<?php echo $selectedUser['UserID']; ?>">
              
              <div class="form-group">
                <label for="documentTitle">Document Title</label>
                <input type="text" class="form-control" id="documentTitle" name="documentTitle" placeholder="Enter document title" required>
              </div>
              
              <div class="form-group">
                <label for="documentDescription">Description (Optional)</label>
                <textarea class="form-control" id="documentDescription" name="documentDescription" rows="4" placeholder="Enter a brief description of the document"></textarea>
              </div>
              
              <div class="form-group">
                <label for="categoryId">Category (Optional)</label>
                <select class="form-control" id="categoryId" name="categoryId">
                  <option value="">-- Select Category --</option>
                  <?php foreach ($categories as $category): ?>
                    <option value="<?php echo $category['CategoryID']; ?>"><?php echo htmlspecialchars($category['CategoryName']); ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              
              <div class="form-group">
                <label for="documentFile">Document File (PDF, JPG, PNG only, max 10MB)</label>
                <input type="file" class="form-control" id="documentFile" name="documentFile" required accept=".pdf,.jpg,.jpeg,.png">
                <div id="fileInfo" class="mt-2"></div>
              </div>
              
              <div class="form-group">
                <label for="documentTags">Tags (Optional)</label>
                <input type="text" class="form-control" id="documentTags" name="documentTags" placeholder="Enter tags separated by commas">
                <small class="form-text text-muted">Example: report, 2023, financial</small>
              </div>
              
              <div class="form-group">
                <label for="accessLevel">Access Level</label>
                <select class="form-control" id="accessLevel" name="accessLevel" required>
                  <?php
                  // Get all access levels
                  $accessLevels = getAllAccessLevels($conn);
                  foreach ($accessLevels as $level):
                  ?>
                    <option value="<?php echo $level['AccessLevelID']; ?>" <?php echo ($selectedUser['AccessLevel'] == $level['AccessLevelID']) ? 'selected' : ''; ?>>
                      <?php echo htmlspecialchars($level['LevelName']); ?>
                    </option>
                  <?php endforeach; ?>
                </select>
                <small class="form-text text-muted">Default is user's access level</small>
              </div>
              
              <div class="form-group">
                <div class="form-check">
                  <label class="form-check-label">
                    <input type="checkbox" class="form-check-input" name="notifyUser" checked>
                    Notify user about this upload
                  </label>
                </div>
              </div>
              
              <button type="submit" class="btn btn-primary mr-2">Upload Document for User</button>
              <a href="manage-users.php" class="btn btn-light">Cancel</a>
            </form>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Upload Notes -->
    <div class="row">
      <div class="col-md-12 grid-margin stretch-card">
        <div class="card">
          <div class="card-body">
            <h4 class="card-title">Notes</h4>
            <ul class="list-arrow">
              <li>The document will appear in the user's document list</li>
              <li>The upload will be recorded as performed by you (admin) but will be associated with the selected user</li>
              <li>The user will be able to download, view, and delete this document like any other document in their account</li>
              <li>Make sure the document is appropriate for the user's access level</li>
            </ul>
          </div>
        </div>
      </div>
    </div>
    <?php endif; ?>
  </div>

<script>
// Simple file validation script
document.addEventListener('DOMContentLoaded', function() {
  const fileInput = document.getElementById('documentFile');
  const fileInfo = document.getElementById('fileInfo');
  
  if (fileInput && fileInfo) {
    fileInput.addEventListener('change', function() {
      if (fileInput.files && fileInput.files[0]) {
        const file = fileInput.files[0];
        
        // Format file size
        const fileSize = file.size;
        let formattedSize = '';
        if (fileSize > 1024 * 1024) {
          formattedSize = (fileSize / (1024 * 1024)).toFixed(2) + ' MB';
        } else if (fileSize > 1024) {
          formattedSize = (fileSize / 1024).toFixed(2) + ' KB';
        } else {
          formattedSize = fileSize + ' bytes';
        }
        
        // Display file info
        fileInfo.innerHTML = `
          <div class="alert alert-info">
            <strong>File selected:</strong> ${file.name}<br>
            <strong>Size:</strong> ${formattedSize}<br>
            <strong>Type:</strong> ${file.type}
          </div>
        `;
        
        // Check file size
        const maxSize = 10 * 1024 * 1024; // 10MB
        if (fileSize > maxSize) {
          fileInfo.innerHTML += `
            <div class="alert alert-danger">
              <strong>Warning:</strong> File exceeds the maximum size of 10MB.
            </div>
          `;
        }
      } else {
        fileInfo.innerHTML = '';
      }
    });
  }
});
</script>

<?php
include 'include/footer.php';
?>