<?php
// File: admin/flagged-documents.php
// Include session management
require_once '../public/include/session.php';

// Require admin
requireAdmin();

// Get flagged documents
$flaggedDocuments = getFlaggedDocuments($conn);

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
            <h3 class="font-weight-bold">Flagged Documents</h3>
            <h6 class="font-weight-normal mb-0">Review and manage flagged documents</h6>
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
    
    <!-- Filters -->
    <div class="row mb-4">
      <div class="col-md-12">
        <div class="card">
          <div class="card-body py-3">
            <div class="row">
              <div class="col-md-3">
                <select class="form-control" id="flag-reason-filter">
                  <option value="">All Flag Reasons</option>
                  <option value="Inappropriate">Inappropriate Content</option>
                  <option value="Copyright">Copyright Violation</option>
                  <option value="Confidential">Confidential Information</option>
                  <option value="Irrelevant">Irrelevant Content</option>
                  <option value="Other">Other</option>
                  <option value="Deleted">Deleted by User</option>
                </select>
              </div>
              <div class="col-md-3">
                <select class="form-control" id="file-type-filter">
                  <option value="">All Types</option>
                  <option value="pdf">PDF</option>
                  <option value="png">PNG</option>
                  <option value="jpg">JPG</option>
                </select>
              </div>
              <div class="col-md-6">
                <div class="input-group">
                  <input type="text" class="form-control" id="search-input" placeholder="Search flagged documents...">
                  <div class="input-group-append">
                    <button class="btn btn-primary" id="search-btn" type="button">Search</button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Flagged Documents Table -->
    <div class="row">
      <div class="col-md-12 grid-margin stretch-card">
        <div class="card">
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-striped table-hover" id="flagged-documents-table">
                <thead>
                  <tr>
                    <th>ID</th>
                    <th>Document Name</th>
                    <th>Type</th>
                    <th>Owner</th>
                    <th>Uploaded Date</th>
                    <th>Flag Reason</th>
                    <th>Actions</th>
                  </tr>  
                </thead>
                <tbody>
                  <?php if (empty($flaggedDocuments)): ?>
                    <tr>
                      <td colspan="7" class="text-center">No flagged documents found.</td>
                    </tr>
                  <?php else: ?>
                    <?php foreach ($flaggedDocuments as $doc): ?>
                      <tr>
                        <td><?php echo $doc['DocumentID']; ?></td>
                        <td><?php echo htmlspecialchars($doc['Title']); ?></td>
                        <td><span class="badge badge-info"><?php echo strtoupper($doc['FileType']); ?></span></td>
                        <td><?php echo htmlspecialchars($doc['FirstName'] . ' ' . $doc['LastName']); ?></td>
                        <td><?php echo date('Y-m-d H:i', strtotime($doc['UploadDate'])); ?></td>
                        <td><span class="badge badge-warning"><?php echo htmlspecialchars($doc['FlagReason']); ?></span></td>
                        <td>
                          <a href="view-document.php?id=<?php echo $doc['DocumentID']; ?>" class="btn btn-info btn-sm" title="View Document">
                            <i class="ti-eye"></i>
                          </a>
                          <a href="process-unflag-document.php?id=<?php echo $doc['DocumentID']; ?>" class="btn btn-success btn-sm approve-document" title="Approve Document">
                            <i class="ti-check"></i>
                          </a>
                          <button type="button" class="btn btn-danger btn-sm remove-document" data-id="<?php echo $doc['DocumentID']; ?>" title="Remove Document">
                            <i class="ti-trash"></i>
                          </button>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
            
            <?php if (!empty($flaggedDocuments) && count($flaggedDocuments) > 10): ?>
            <!-- Pagination -->
            <div class="mt-4">
              <nav>
                <ul class="pagination justify-content-center">
                  <li class="page-item disabled">
                    <a class="page-link" href="#" tabindex="-1">Previous</a>
                  </li>
                  <li class="page-item active"><a class="page-link" href="#">1</a></li>
                  <li class="page-item"><a class="page-link" href="#">2</a></li>
                  <li class="page-item"><a class="page-link" href="#">3</a></li>
                  <li class="page-item">
                    <a class="page-link" href="#">Next</a>
                  </li>
                </ul>
              </nav>
            </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Remove Document Modal -->
  <div class="modal fade" id="removeDocumentModal" tabindex="-1" role="dialog" aria-labelledby="removeDocumentModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="removeDocumentModalLabel">Remove Document</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <p>Are you sure you want to permanently remove this document? This action cannot be undone.</p>
          <form id="removeDocumentForm" action="process-delete-document.php" method="post">
            <input type="hidden" id="removeDocumentId" name="documentId" value="">
            <div class="form-group">
              <label for="deleteReason">Reason for Removal</label>
              <textarea class="form-control" id="deleteReason" name="deleteReason" rows="3" required></textarea>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
              <button type="submit" class="btn btn-danger">Remove Document</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <script>
  $(document).ready(function() {
    // Remove document
    $('.remove-document').on('click', function() {
      var documentId = $(this).data('id');
      $('#removeDocumentId').val(documentId);
      $('#removeDocumentModal').modal('show');
    });
    
    // Filter functionality
    $('#flag-reason-filter, #file-type-filter').on('change', function() {
      filterTable();
    });
    
    $('#search-btn').on('click', function() {
      filterTable();
    });
    
    $('#search-input').on('keyup', function(e) {
      if (e.keyCode === 13) { // Enter key
        filterTable();
      }
    });
    
    function filterTable() {
      var reasonFilter = $('#flag-reason-filter').val().toLowerCase();
      var typeFilter = $('#file-type-filter').val().toLowerCase();
      var searchText = $('#search-input').val().toLowerCase();
      
      $('#flagged-documents-table tbody tr').each(function() {
        var row = $(this);
        var reason = row.find('td:nth-child(6)').text().toLowerCase();
        var type = row.find('td:nth-child(3)').text().toLowerCase();
        var title = row.find('td:nth-child(2)').text().toLowerCase();
        var owner = row.find('td:nth-child(4)').text().toLowerCase();
        
        var reasonMatch = reasonFilter === '' || reason.includes(reasonFilter);
        var typeMatch = typeFilter === '' || type.includes(typeFilter);
        var searchMatch = searchText === '' || title.includes(searchText) || owner.includes(searchText);
        
        if (reasonMatch && typeMatch && searchMatch) {
          row.show();
        } else {
          row.hide();
        }
      });
    }
  });
</script>

<?php
include 'include/footer.php';
?>