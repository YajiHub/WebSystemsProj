<?php
// File: public/index.php
// Include session management
require_once 'include/session.php';

// Require login
requireLogin();

// Get document counts for the current user
$documentCounts = countDocumentsByType($conn, $_SESSION['user_id']);

// Get recent documents for the current user
$recentDocuments = getDocumentsByUserId($conn, $_SESSION['user_id']);
// Limit to 5 most recent documents
$recentDocuments = array_slice($recentDocuments, 0, 5);

include 'include/header.php';
include 'include/sidebar.php';

// Function to format file size
function formatFileSize($bytes) {
    if ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return number_format($bytes / 1024, 2) . ' KB';
    } else {
        return $bytes . ' bytes';
    }
}

// Function to get time ago
function timeAgo($datetime) {
    $now = new DateTime();
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);
    
    if ($diff->y > 0) {
        return $diff->y . ' year' . ($diff->y > 1 ? 's' : '') . ' ago';
    } elseif ($diff->m > 0) {
        return $diff->m . ' month' . ($diff->m > 1 ? 's' : '') . ' ago';
    } elseif ($diff->d > 0) {
        return $diff->d . ' day' . ($diff->d > 1 ? 's' : '') . ' ago';
    } elseif ($diff->h > 0) {
        return $diff->h . ' hour' . ($diff->h > 1 ? 's' : '') . ' ago';
    } elseif ($diff->i > 0) {
        return $diff->i . ' minute' . ($diff->i > 1 ? 's' : '') . ' ago';
    } else {
        return 'Just now';
    }
}
?>

<!-- Main Panel -->
<div class="main-panel">
  <div class="content-wrapper">
    <!-- Welcome Message -->
    <div class="row">
      <div class="col-md-12 grid-margin">
        <div class="row">
          <div class="col-12 col-xl-8 mb-4 mb-xl-0">
            <h3 class="font-weight-bold">Document Management</h3>
            <h6 class="font-weight-normal mb-0">Welcome to your document archive directory</h6>
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
    
    <!-- Quick Stats -->
    <div class="row">
      <div class="col-md-4 grid-margin stretch-card">
        <div class="card card-tale">
          <div class="card-body">
            <p class="mb-4">My Documents</p>
            <p class="fs-30 mb-2"><?php echo $documentCounts['total']; ?></p>
            <p>
              <?php 
              if (!empty($recentDocuments)) {
                  echo 'Last uploaded: ' . timeAgo($recentDocuments[0]['UploadDate']);
              } else {
                  echo 'No documents uploaded yet';
              }
              ?>
            </p>
          </div>
        </div>
      </div>
      <div class="col-md-4 grid-margin stretch-card">
        <div class="card card-dark-blue">
          <div class="card-body">
            <p class="mb-4">PDF Documents</p>
            <p class="fs-30 mb-2"><?php echo $documentCounts['pdf']; ?></p>
          </div>
        </div>
      </div>
      <div class="col-md-4 grid-margin stretch-card">
        <div class="card card-light-blue">
          <div class="card-body">
            <p class="mb-4">Image Files</p>
            <p class="fs-30 mb-2"><?php echo $documentCounts['jpg'] + $documentCounts['png']; ?></p>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Recent Documents -->
    <div class="row">
      <div class="col-md-12 grid-margin stretch-card">
        <div class="card">
          <div class="card-body">
            <p class="card-title">Recent Documents</p>
            <div class="table-responsive">
              <table class="table table-striped table-hover">
                <thead>
                  <tr>
                    <th>Document Name</th>
                    <th>Type</th>
                    <th>Size</th>
                    <th>Uploaded</th>
                    <th>Actions</th>
                  </tr>  
                </thead>
                <tbody>
                  <?php if (empty($recentDocuments)): ?>
                    <tr>
                      <td colspan="5" class="text-center">No documents found. <a href="upload.php">Upload your first document</a>.</td>
                    </tr>
                  <?php else: ?>
                    <?php foreach ($recentDocuments as $doc): ?>
                      <tr>
                        <td><?php echo htmlspecialchars($doc['Title']); ?></td>
                        <td><span class="badge badge-info"><?php echo strtoupper($doc['FileType']); ?></span></td>
                        <td><?php echo formatFileSize(filesize($doc['FileLocation'])); ?></td>
                        <td><?php echo timeAgo($doc['UploadDate']); ?></td>
                        <td>
                          <a href="view.php?id=<?php echo $doc['DocumentID']; ?>" class="btn btn-dark btn-icon-text btn-sm">
                            View
                            <i class="ti-file btn-icon-append"></i>
                          </a>
                          <a href="download.php?id=<?php echo $doc['DocumentID']; ?>" class="btn btn-primary btn-icon-text btn-sm">
                            <i class="ti-download btn-icon-prepend"></i>
                            Download
                          </a>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
            <div class="text-center mt-4">
              <a href="my-documents.php" class="btn btn-primary">View All Documents</a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

<?php
include 'include/footer.php';
?>