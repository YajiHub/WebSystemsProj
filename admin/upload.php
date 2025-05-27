<?php
// File: admin/upload.php
// Include session management
require_once '../public/include/session.php';

// Require admin login
requireAdmin();

// Get categories for the form
$categories = getAllCategories($conn);

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
            <h3 class="font-weight-bold">Upload Document</h3>
            <h6 class="font-weight-normal mb-0">Upload new documents to your personal archive</h6>
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
    
    <!-- Upload Form -->
    <div class="row">
      <div class="col-md-12 grid-margin stretch-card">
        <div class="card">
          <div class="card-body">
            <h4 class="card-title">Upload New Document</h4>
            <p class="card-description">
              Supported file types: PDF, JPG, PNG | Max file size: 10MB
            </p>
            
            <form class="forms-sample" action="process-admin-upload.php" method="post" enctype="multipart/form-data">
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
                    <option value="<?php echo $level['AccessLevelID']; ?>" <?php echo ($_SESSION['user_access_level'] == $level['AccessLevelID']) ? 'selected' : ''; ?>>
                      <?php echo htmlspecialchars($level['LevelName']); ?>
                    </option>
                  <?php endforeach; ?>
                </select>
                <small class="form-text text-muted">Default is your access level</small>
              </div>
              
              <button type="submit" class="btn btn-primary mr-2">Upload Document</button>
              <a href="my-documents.php" class="btn btn-light">Cancel</a>
            </form>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Upload Tips -->
    <div class="row">
      <div class="col-md-12 grid-margin stretch-card">
        <div class="card">
          <div class="card-body">
            <h4 class="card-title">Upload Tips</h4>
            <ul class="list-arrow">
              <li>Use descriptive file names for better organization</li>
              <li>Add relevant tags to improve searchability</li>
              <li>Make sure your PDF files are searchable for better text extraction</li>
              <li>Compress large image files before uploading for faster viewing</li>
              <li>For scanned documents, ensure good quality for better readability</li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </div>

<script>
// File validation script
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