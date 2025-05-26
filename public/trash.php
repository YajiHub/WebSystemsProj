<?php
// File: public/trash.php
// Include session management
require_once 'include/session.php';

// Require login
requireLogin();

// Get user's trashed documents
$trashedDocuments = getTrashedDocuments($conn, $_SESSION['user_id']);

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

include 'include/header.php';
include 'include/sidebar.php';
?>

<!-- Main Panel -->
<div class="main-panel">
  <div class="content-wrapper">
    <!-- Page Title -->
    <div class="row">
      <div class="col-md-12 grid-margin">
        <div class="row">
          <div class="col-12 col-xl-8 mb-4 mb-xl-0">
            <h3 class="font-weight-bold">Trash</h3>
            <h6 class="font-weight-normal mb-0">View and restore deleted documents</h6>
          </div>
          <?php if (!empty($trashedDocuments)): ?>
          <div class="col-12 col-xl-4">
            <div class="justify-content-end d-flex">
              <button class="btn btn-danger empty-trash">
                <i class="ti-trash mr-1"></i> Empty Trash
              </button>
            </div>
          </div>
          <?php endif; ?>
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
    
    <!-- Trash Table -->
    <div class="row">
      <div class="col-md-12 grid-margin stretch-card">
        <div class="card">
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-striped table-hover" id="trash-table">
                <thead>
                  <tr>
                    <th>Document Name</th>
                    <th>Type</th>
                    <th>Size</th>
                    <th>Deleted Date</th>
                    <th>Actions</th>
                  </tr>  
                </thead>
                <tbody>
                  <?php if (empty($trashedDocuments)): ?>
                    <tr>
                      <td colspan="5" class="text-center">Your trash is empty</td>
                    </tr>
                  <?php else: ?>
                    <?php foreach ($trashedDocuments as $doc): ?>
                      <tr>
                        <td><?php echo htmlspecialchars($doc['Title']); ?></td>
                        <td><span class="badge badge-info"><?php echo strtoupper($doc['FileType']); ?></span></td>
                        <td><?php echo formatFileSize(file_exists($doc['FileLocation']) ? filesize($doc['FileLocation']) : 0); ?></td>
                        <td><?php echo date('Y-m-d H:i', strtotime($doc['UploadDate'])); ?></td>
                        <td>
                          <a href="restore.php?id=<?php echo $doc['DocumentID']; ?>" class="btn btn-success btn-icon-text btn-sm">
                            <i class="ti-reload"></i> Restore
                          </a>
                          <a href="permanent-delete.php?id=<?php echo $doc['DocumentID']; ?>" class="btn btn-danger btn-icon-text btn-sm permanent-delete">
                            <i class="ti-trash"></i> Delete Permanently
                          </a>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
            <?php if (!empty($trashedDocuments)): ?>
            <!-- Trash Info -->
            <div class="mt-4 alert alert-info">
              <i class="ti-info-alt mr-2"></i>
              Documents in trash will be automatically deleted after 30 days.
            </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Permanent Delete Confirmation Modal -->
  <div class="modal fade" id="permanentDeleteModal" tabindex="-1" role="dialog" aria-labelledby="permanentDeleteModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="permanentDeleteModalLabel">Confirm Permanent Deletion</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          Are you sure you want to permanently delete this document? This action cannot be undone.
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
          <a href="#" id="confirm-permanent-delete" class="btn btn-danger">Delete Permanently</a>
        </div>
      </div>
    </div>
  </div>

  <!-- Empty Trash Confirmation Modal -->
  <div class="modal fade" id="emptyTrashModal" tabindex="-1" role="dialog" aria-labelledby="emptyTrashModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="emptyTrashModalLabel">Empty Trash</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          Are you sure you want to empty the trash? All documents in trash will be permanently deleted. This action cannot be undone.
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
          <a href="empty-trash.php" class="btn btn-danger">Empty Trash</a>
        </div>
      </div>
    </div>
  </div>

<script>
  // Script for delete confirmation
  document.addEventListener('DOMContentLoaded', function() {
    // Permanent delete confirmation
    const deleteButtons = document.querySelectorAll('.permanent-delete');
    const confirmDeleteButton = document.getElementById('confirm-permanent-delete');
    
    deleteButtons.forEach(button => {
      button.addEventListener('click', function(e) {
        e.preventDefault();
        const deleteUrl = this.getAttribute('href');
        confirmDeleteButton.setAttribute('href', deleteUrl);
        $('#permanentDeleteModal').modal('show');
      });
    });
    
    // Empty trash confirmation
    const emptyTrashButton = document.querySelector('.empty-trash');
    if (emptyTrashButton) {
      emptyTrashButton.addEventListener('click', function(e) {
        e.preventDefault();
        $('#emptyTrashModal').modal('show');
      });
    }
  });
</script>

<?php
include 'include/footer.php';
?>