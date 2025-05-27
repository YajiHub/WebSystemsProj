<?php
// File: admin/admin-view.php
// Include session management
require_once '../public/include/session.php';

// Require admin login
requireAdmin();

// Check if document ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error'] = "No document specified.";
    header("Location: my-documents.php");
    exit;
}

$documentId = (int)$_GET['id'];

// Get document information
$document = getDocumentById($conn, $documentId);

// Check if document exists
if (!$document) {
    $_SESSION['error'] = "Document not found.";
    header("Location: my-documents.php");
    exit;
}

// Check if admin has access to the document (either own document or admin privilege)
if (!hasDocumentAccess($conn, $_SESSION['user_id'], $documentId)) {
    $_SESSION['error'] = "You do not have permission to view this document.";
    header("Location: my-documents.php");
    exit;
}

// Get uploader information from access logs
$uploader = null;
$sql = "SELECT u.UserID, u.FirstName, u.LastName, u.UserRole, f.Timestamp 
        FROM fileaccesslog f 
        JOIN user u ON f.UserID = u.UserID 
        WHERE f.DocumentID = ? AND f.AccessType = 3 
        ORDER BY f.Timestamp ASC 
        LIMIT 1";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $documentId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($result && mysqli_num_rows($result) > 0) {
    $uploader = mysqli_fetch_assoc($result);
}

// Log the view action
$viewAccessTypeId = 1; // Assuming 1 is the ID for 'View' in accesstype table
logFileAccess($conn, $_SESSION['user_id'], $documentId, $viewAccessTypeId);

// Format file size function
function formatFileSize($bytes) {
    if ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return number_format($bytes / 1024, 2) . ' KB';
    } else {
        return $bytes . ' bytes';
    }
}

include 'include/header.php';
include 'include/admin-sidebar.php';

// Get file type and path
$fileType = strtolower($document['FileType']);
$filePath = $document['FileLocation'];
$fileSize = file_exists($filePath) ? filesize($filePath) : 0;
?>

<!-- Main Panel -->
<div class="main-panel">
  <div class="content-wrapper">
    <!-- Document Viewer Header -->
    <div class="row">
      <div class="col-md-12 grid-margin">
        <div class="card">
          <div class="card-body p-3">
            <div class="d-flex flex-wrap justify-content-between align-items-center">
              <div class="d-flex align-items-center">
                <?php 
                // Display appropriate icon based on file type
                if ($fileType == 'pdf') {
                    echo '<i class="ti-file text-danger" style="font-size: 24px;"></i>';
                } else if ($fileType == 'jpg' || $fileType == 'jpeg' || $fileType == 'png') {
                    echo '<i class="ti-image text-primary" style="font-size: 24px;"></i>';
                } else {
                    echo '<i class="ti-file text-secondary" style="font-size: 24px;"></i>';
                }
                ?>
                <div class="ml-3">
                  <h4 class="mb-0"><?php echo htmlspecialchars($document['Title']); ?></h4>
                  <div class="text-muted small">
                    <?php echo strtoupper($fileType); ?> · <?php echo formatFileSize($fileSize); ?> · 
                    <?php 
                    if ($uploader && $uploader['UserID'] != $document['UserID']) {
                        echo 'Uploaded by <span class="text-primary font-weight-bold">' . 
                             htmlspecialchars($uploader['FirstName'] . ' ' . $uploader['LastName']) . 
                             '</span> (' . ($uploader['UserRole'] == 'admin' ? 'Administrator' : 'User') . ') on ';
                    } else {
                        echo 'Uploaded on ';
                    }
                    echo date('M d, Y', strtotime($document['UploadDate'])); 
                    ?>
                  </div>
                </div>
              </div>
              <div class="d-flex document-actions">
                <a href="../public/download.php?id=<?php echo $documentId; ?>" class="btn btn-outline-primary btn-sm mr-2">
                  <i class="ti-download mr-1"></i> Download
                </a>
                <?php if ($_SESSION['user_id'] == $document['UserID']): ?>
                <a href="admin-delete.php?id=<?php echo $documentId; ?>" class="btn btn-outline-danger btn-sm delete-doc">
                  <i class="ti-trash mr-1"></i> Delete
                </a>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Document Content -->
    <div class="row">
      <div class="col-md-9">
        <!-- Document Viewer -->
        <div class="card document-viewer-card">
          <div class="card-body p-0 position-relative">
            <div class="document-viewer-toolbar bg-light p-2 border-bottom">
              <div class="d-flex justify-content-between align-items-center">
                <div>
                  <button type="button" class="btn btn-sm btn-light" id="zoom-out">
                    <i class="ti-zoom-out"></i>
                  </button>
                  <span id="zoom-level" class="mx-2">100%</span>
                  <button type="button" class="btn btn-sm btn-light" id="zoom-in">
                    <i class="ti-zoom-in"></i>
                  </button>
                </div>
                <div>
                  <a href="../public/download.php?id=<?php echo $documentId; ?>" class="btn btn-sm btn-light">
                    <i class="ti-download"></i> Download
                  </a>
                  <button type="button" class="btn btn-sm btn-light ml-1" id="fullscreen-toggle">
                    <i class="ti-fullscreen"></i>
                  </button>
                </div>
              </div>
            </div>
            
            <div class="document-viewer" id="document-viewer">
              <?php
              if ($fileType == 'pdf') {
                // PDF viewer with toolbar
                echo '<div class="pdf-container" style="height: 80vh;">';
                echo '<iframe src="' . $filePath . '" style="width: 100%; height: 100%; border: none;"></iframe>';
                echo '</div>';
              } else if ($fileType == 'jpg' || $fileType == 'jpeg' || $fileType == 'png') {
                // Image viewer with zoom capabilities
                echo '<div class="image-container text-center" style="min-height: 80vh; display: flex; align-items: center; justify-content: center; overflow: auto;">';
                echo '<img src="' . $filePath . '" id="previewImage" class="img-fluid" alt="' . htmlspecialchars($document['Title']) . '" style="max-width: 100%; transition: transform 0.3s ease;">';
                echo '</div>';
              } else {
                // Unsupported file type
                echo '<div class="alert alert-warning m-3">';
                echo '<h4><i class="ti-info-alt mr-2"></i> Preview not available</h4>';
                echo '<p>This file type does not support preview. Please download the file to view its contents.</p>';
                echo '<a href="../public/download.php?id=' . $documentId . '" class="btn btn-primary"><i class="ti-download mr-1"></i> Download File</a>';
                echo '</div>';
              }
              ?>
            </div>
          </div>
        </div>
      </div>
      
      <div class="col-md-3">
        <!-- Document Details Panel -->
        <div class="card">
          <div class="card-body">
            <h5 class="card-title mb-3">Document Information</h5>
            
            <div class="document-info">
              <div class="mb-3">
                <label class="text-muted d-block">File Type</label>
                <span class="badge badge-info"><?php echo strtoupper($document['FileType']); ?></span>
              </div>
              
              <div class="mb-3">
                <label class="text-muted d-block">Category</label>
                <span><?php echo $document['CategoryName'] ? htmlspecialchars($document['CategoryName']) : 'Uncategorized'; ?></span>
              </div>
              
              <div class="mb-3">
                <label class="text-muted d-block">Access Level</label>
                <span><?php echo htmlspecialchars($document['LevelName']); ?></span>
              </div>
              
              <div class="mb-3">
                <label class="text-muted d-block">File Size</label>
                <span><?php echo formatFileSize($fileSize); ?></span>
              </div>
              
              <div class="mb-3">
                <label class="text-muted d-block">Upload Date</label>
                <span><?php echo date('F d, Y', strtotime($document['UploadDate'])); ?></span>
              </div>
              
              <div class="mb-3">
                <label class="text-muted d-block">Document Owner</label>
                <span><?php echo htmlspecialchars($document['FirstName'] . ' ' . $document['LastName']); ?></span>
              </div>
              
              <?php if ($uploader && $uploader['UserID'] != $document['UserID']): ?>
              <div class="mb-3">
                <label class="text-muted d-block">Uploaded By</label>
                <span class="text-primary">
                  <?php echo htmlspecialchars($uploader['FirstName'] . ' ' . $uploader['LastName']); ?> 
                  <span class="badge badge-primary">
                    <?php echo ucfirst($uploader['UserRole']); ?>
                  </span>
                </span>
              </div>
              <?php endif; ?>
            </div>
            
            <?php if (!empty($document['FileTypeDescription'])): ?>
            <div class="mt-4">
              <h5 class="card-title mb-3">Description</h5>
              <p class="text-muted"><?php echo nl2br(htmlspecialchars($document['FileTypeDescription'])); ?></p>
            </div>
            <?php endif; ?>
            
            <div class="mt-4">
              <a href="../public/download.php?id=<?php echo $documentId; ?>" class="btn btn-primary btn-block">
                <i class="ti-download mr-1"></i> Download
              </a>
              <?php if ($_SESSION['user_id'] == $document['UserID']): ?>
              <a href="admin-delete.php?id=<?php echo $documentId; ?>" class="btn btn-outline-danger btn-block mt-2 delete-doc">
                <i class="ti-trash mr-1"></i> Delete
              </a>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Delete Confirmation Modal -->
  <div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          Are you sure you want to move this document to trash?
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
          <a href="#" id="confirm-delete" class="btn btn-danger">Delete</a>
        </div>
      </div>
    </div>
  </div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  // Delete confirmation
  const deleteButtons = document.querySelectorAll('.delete-doc');
  const confirmDeleteButton = document.getElementById('confirm-delete');
  
  deleteButtons.forEach(button => {
    button.addEventListener('click', function(e) {
      e.preventDefault();
      const deleteUrl = this.getAttribute('href');
      confirmDeleteButton.setAttribute('href', deleteUrl);
      $('#deleteModal').modal('show');
    });
  });
  
  // Image zoom functionality
  const zoomIn = document.getElementById('zoom-in');
  const zoomOut = document.getElementById('zoom-out');
  const zoomLevel = document.getElementById('zoom-level');
  const previewImage = document.getElementById('previewImage');
  
  if (previewImage && zoomIn && zoomOut && zoomLevel) {
    let currentZoom = 100;
    
    zoomIn.addEventListener('click', function() {
      if (currentZoom < 200) {
        currentZoom += 25;
        updateZoom();
      }
    });
    
    zoomOut.addEventListener('click', function() {
      if (currentZoom > 50) {
        currentZoom -= 25;
        updateZoom();
      }
    });
    
    function updateZoom() {
      zoomLevel.textContent = currentZoom + '%';
      previewImage.style.transform = `scale(${currentZoom / 100})`;
    }
  }
  
  // Fullscreen toggle
  const fullscreenToggle = document.getElementById('fullscreen-toggle');
  const documentViewer = document.getElementById('document-viewer');
  
  if (fullscreenToggle && documentViewer) {
    fullscreenToggle.addEventListener('click', function() {
      if (!document.fullscreenElement) {
        if (documentViewer.requestFullscreen) {
          documentViewer.requestFullscreen();
        } else if (documentViewer.mozRequestFullScreen) {
          documentViewer.mozRequestFullScreen();
        } else if (documentViewer.webkitRequestFullscreen) {
          documentViewer.webkitRequestFullscreen();
        } else if (documentViewer.msRequestFullscreen) {
          documentViewer.msRequestFullscreen();
        }
      } else {
        if (document.exitFullscreen) {
          document.exitFullscreen();
        } else if (document.mozCancelFullScreen) {
          document.mozCancelFullScreen();
        } else if (document.webkitExitFullscreen) {
          document.webkitExitFullscreen();
        } else if (document.msExitFullscreen) {
          document.msExitFullscreen();
        }
      }
    });
  }
});
</script>

<style>
/* Document viewer styling */
.document-viewer-card {
  height: 85vh;
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

.document-viewer {
  flex: 1;
  overflow: auto;
  background-color: #f8f9fa;
}

.pdf-container {
  display: flex;
  align-items: center;
  justify-content: center;
  height: 100%;
}

.image-container {
  display: flex;
  align-items: center;
  justify-content: center;
  height: 100%;
  overflow: auto;
}

/* Improve styling for fullscreen mode */
#document-viewer:fullscreen {
  background-color: white;
  padding: 20px;
}

#document-viewer:fullscreen .document-viewer-toolbar {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  z-index: 9999;
}
</style>

<?php
include 'include/footer.php';
?>