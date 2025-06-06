<?php
require_once '../public/include/session.php';

// Require admin
requireAdmin();

// Get document ID from URL parameter
$doc_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Get document information
$document = getDocumentById($conn, $doc_id);

// Check if document exists
if (!$document) {
    $_SESSION['error'] = "Document not found.";
    header("Location: manage-documents.php");
    exit;
}

// Get access history for the document
$access_history = [];
$sql = "SELECT u.FirstName, u.LastName, a.AccessName, f.Timestamp 
        FROM fileaccesslog f 
        JOIN user u ON f.UserID = u.UserID 
        JOIN accesstype a ON f.AccessType = a.AccessTypeID 
        WHERE f.DocumentID = $doc_id 
        ORDER BY f.Timestamp DESC 
        LIMIT 5";
$result = mysqli_query($conn, $sql);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $access_history[] = [
            $row['FirstName'] . ' ' . $row['LastName'],
            $row['AccessName'],
            $row['Timestamp']
        ];
    }
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
            <h3 class="font-weight-bold"><?php echo htmlspecialchars($document['Title']); ?></h3>
            <nav aria-label="breadcrumb">
              <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="manage-documents.php">Documents</a></li>
                <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($document['Title']); ?></li>
              </ol>
            </nav>
          </div>
          <div class="col-12 col-xl-4">
            <div class="justify-content-end d-flex">
              <a href="../public/download.php?id=<?php echo $doc_id; ?>" class="btn btn-primary mr-2">
                <i class="ti-download mr-1"></i> Download
              </a>
              <a href="document-access-logs.php?id=<?php echo $doc_id; ?>" class="btn btn-info mr-2">
                <i class="ti-list mr-1"></i> Access Logs
              </a>
              <?php if ($document['IsDeleted'] == 0): ?>
              <button class="btn btn-warning mr-2 flag-document" data-id="<?php echo $doc_id; ?>">
                <i class="ti-flag-alt mr-1"></i> Flag
              </button>
              <?php endif; ?>
              <button class="btn btn-danger delete-document" data-id="<?php echo $doc_id; ?>">
                <i class="ti-trash mr-1"></i> Delete
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Document View -->
    <div class="row">
      <div class="col-md-8 grid-margin stretch-card">
        <div class="card">
          <div class="card-body">
            <div class="document-viewer">
              <?php 
              // Based on document type, display appropriate viewer
              $fileType = strtolower($document['FileType']);
              $filePath = $document['FileLocation'];
              
              if ($fileType == 'pdf') {
                echo '<iframe src="' . $filePath . '" width="100%" height="600px" style="border: none;"></iframe>';
              } elseif ($fileType == 'jpg' || $fileType == 'jpeg' || $fileType == 'png') {
                echo '<img src="' . $filePath . '" class="img-fluid" alt="' . htmlspecialchars($document['Title']) . '">';
              } else {
                echo '<div class="alert alert-warning">Preview not available for this file type.</div>';
              }
              ?>
            </div>
          </div>
        </div>
      </div>
      
      <div class="col-md-4">
        <!-- Document Details -->
        <div class="card mb-4">
          <div class="card-body">
            <h4 class="card-title">Document Details</h4>
            <div class="list-group list-group-flush">
              <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                <span class="font-weight-bold">File Type:</span>
                <span class="badge badge-info"><?php echo htmlspecialchars(strtoupper($document['FileType'])); ?></span>
              </div>
              <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                <span class="font-weight-bold">Size:</span>
                <span><?php echo file_exists($filePath) ? formatFileSize(filesize($filePath)) : 'Unknown'; ?></span>
              </div>
              <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                <span class="font-weight-bold">Owner:</span>
                <a href="view-user.php?id=<?php echo $document['UserID']; ?>"><?php echo htmlspecialchars($document['FirstName'] . ' ' . $document['LastName']); ?></a>
              </div>
              <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                <span class="font-weight-bold">Access Level:</span>
                <span><?php echo htmlspecialchars($document['LevelName']); ?></span>
              </div>
              <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                <span class="font-weight-bold">Uploaded:</span>
                <span><?php echo date('Y-m-d', strtotime($document['UploadDate'])); ?></span>
              </div>
              <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                <span class="font-weight-bold">Status:</span>
                <span class="badge <?php echo $document['IsDeleted'] == 0 ? 'badge-success' : ($document['FlagReason'] ? 'badge-warning' : 'badge-danger'); ?>">
                  <?php echo $document['IsDeleted'] == 0 ? 'Active' : ($document['FlagReason'] ? 'Flagged' : 'Deleted'); ?>
                </span>
              </div>
              <?php if ($document['FlagReason']): ?>
              <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                <span class="font-weight-bold">Flag Reason:</span>
                <span><?php echo htmlspecialchars($document['FlagReason']); ?></span>
              </div>
              <?php endif; ?>
              <div class="list-group-item px-0">
                <span class="font-weight-bold">Description:</span>
                <p class="mt-2"><?php echo htmlspecialchars($document['FileTypeDescription'] ?? 'No description available.'); ?></p>
              </div>
              <?php if (isset($document['CategoryName'])): ?>
              <div class="list-group-item px-0">
                <span class="font-weight-bold">Category:</span>
                <p class="mt-2"><?php echo htmlspecialchars($document['CategoryName']); ?></p>
              </div>
              <?php endif; ?>
            </div>
          </div>
        </div>
        
        <!-- Recent Access History -->
        <div class="card">
          <div class="card-body">
            <h4 class="card-title">Recent Access</h4>
            <div class="table-responsive">
              <table class="table table-hover">
                <thead>
                  <tr>
                    <th>User</th>
                    <th>Action</th>
                    <th>Timestamp</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (empty($access_history)): ?>
                    <tr>
                      <td colspan="3" class="text-center">No access history found.</td>
                    </tr>
                  <?php else: ?>
                    <?php foreach ($access_history as $access): ?>
                      <tr>
                        <td><?php echo htmlspecialchars($access[0]); ?></td>
                        <td><?php echo htmlspecialchars($access[1]); ?></td>
                        <td><?php echo htmlspecialchars($access[2]); ?></td>
                      </tr>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
            <div class="text-center mt-3">
              <a href="document-access-logs.php?id=<?php echo $doc_id; ?>" class="btn btn-outline-primary btn-sm">
                View Full Access History
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Flag Document Modal -->
  <div class="modal fade" id="flagDocumentModal" tabindex="-1" role="dialog" aria-labelledby="flagDocumentModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="flagDocumentModalLabel">Flag Document</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <form id="flagDocumentForm" action="process-flag-document.php" method="post">
            <input type="hidden" id="documentId" name="documentId" value="<?php echo $doc_id; ?>">
            <div class="form-group">
              <label for="flagReason">Reason for Flagging</label>
              <select class="form-control" id="flagReason" name="flagReason" required>
                <option value="">-- Select Reason --</option>
                <option value="inappropriate">Inappropriate Content</option>
                <option value="copyright">Copyright Violation</option>
                <option value="confidential">Confidential Information</option>
                <option value="irrelevant">Irrelevant Content</option>
                <option value="other">Other</option>
              </select>
            </div>
            <div class="form-group">
              <label for="flagComments">Additional Comments</label>
              <textarea class="form-control" id="flagComments" name="flagComments" rows="3"></textarea>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
              <button type="submit" class="btn btn-warning">Flag Document</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <!-- Delete Document Modal -->
  <div class="modal fade" id="deleteDocumentModal" tabindex="-1" role="dialog" aria-labelledby="deleteDocumentModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="deleteDocumentModalLabel">Delete Document</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <p>Are you sure you want to delete this document?</p>
          <form id="deleteDocumentForm" action="process-delete-document.php" method="post">
            <input type="hidden" id="deleteDocumentId" name="documentId" value="<?php echo $doc_id; ?>">
            <div class="form-group">
              <label for="deleteReason">Reason for Deletion</label>
              <textarea class="form-control" id="deleteReason" name="deleteReason" rows="3" required></textarea>
              <small class="form-text text-muted">This will be sent to the document owner.</small>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
              <button type="submit" class="btn btn-danger">Delete Document</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

<script>
  $(document).ready(function() {
    // Flag document
    $('.flag-document').on('click', function() {
      $('#flagDocumentModal').modal('show');
    });
    
    // Delete document
    $('.delete-document').on('click', function() {
      $('#deleteDocumentModal').modal('show');
    });
  });
</script>

<?php
// Helper function to format file size
function formatFileSize($bytes) {
    if ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return number_format($bytes / 1024, 2) . ' KB';
    } else {
        return $bytes . ' bytes';
    }
}

include 'include/footer.php';
?>