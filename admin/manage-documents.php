<?php
// File: admin/manage-documents.php
require_once '../public/include/session.php';

// Require admin login
requireAdmin();

// Get all documents with user information
$sql = "SELECT 
    d.DocumentID,
    d.Title,
    d.FileType,
    d.UploadDate,
    d.AccessLevel,
    d.IsDeleted,
    d.FlagReason,
    CONCAT(u.FirstName, ' ', u.LastName) as OwnerName,
    u.UserID,
    a.LevelName,
    CASE 
        WHEN d.IsDeleted = 1 THEN 'Deleted'
        WHEN d.FlagReason IS NOT NULL AND d.FlagReason != '' THEN 'Flagged'
        ELSE 'Active'
    END as Status
FROM document d
JOIN user u ON d.UserID = u.UserID
LEFT JOIN accesslevel a ON d.AccessLevel = a.AccessLevelID
ORDER BY d.UploadDate DESC";

$result = mysqli_query($conn, $sql);
$documents = [];
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $documents[] = $row;
    }
}

// Get access levels for filter
$accessLevels = [];
$sql = "SELECT AccessLevelID, LevelName FROM accesslevel ORDER BY AccessLevelID";
$result = mysqli_query($conn, $sql);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $accessLevels[] = $row;
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
            <h3 class="font-weight-bold">Document Management</h3>
            <h6 class="font-weight-normal mb-0">Manage all documents in the system (<?php echo count($documents); ?> total)</h6>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Filters -->
    <div class="row mb-4">
      <div class="col-md-12">
        <div class="card">
          <div class="card-body py-3">
            <div class="row">
              <div class="col-md-2">
                <select class="form-control" id="file-type-filter">
                  <option value="">All Types</option>
                  <option value="pdf">PDF</option>
                  <option value="png">PNG</option>
                  <option value="jpg">JPG</option>
                  <option value="jpeg">JPEG</option>
                  <option value="gif">GIF</option>
                </select>
              </div>
              <div class="col-md-2">
                <select class="form-control" id="access-level-filter">
                  <option value="">All Access Levels</option>
                  <?php foreach ($accessLevels as $level): ?>
                    <option value="<?php echo $level['AccessLevelID']; ?>">
                      Level <?php echo $level['AccessLevelID']; ?> - <?php echo htmlspecialchars($level['LevelName']); ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="col-md-2">
                <select class="form-control" id="status-filter">
                  <option value="">All Status</option>
                  <option value="Active">Active</option>
                  <option value="Flagged">Flagged</option>
                  <option value="Deleted">Deleted</option>
                </select>
              </div>
              <div class="col-md-2">
                <select class="form-control" id="date-filter">
                  <option value="">Any Date</option>
                  <option value="today">Today</option>
                  <option value="week">This Week</option>
                  <option value="month">This Month</option>
                  <option value="year">This Year</option>
                </select>
              </div>
              <div class="col-md-4">
                <div class="input-group">
                  <input type="text" class="form-control" id="search-input" placeholder="Search documents...">
                  <div class="input-group-append">
                    <button class="btn btn-primary" type="button" id="search-btn">Search</button>
                    <button class="btn btn-secondary" type="button" id="clear-filter-btn">Clear</button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Documents Table -->
    <div class="row">
      <div class="col-md-12 grid-margin stretch-card">
        <div class="card">
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-striped table-hover" id="documents-table">
                <thead>
                  <tr>
                    <th>ID</th>
                    <th>Document Name</th>
                    <th>Type</th>
                    <th>Owner</th>
                    <th>Access Level</th>
                    <th>Uploaded Date</th>
                    <th>Status</th>
                    <th>Actions</th>
                  </tr>  
                </thead>
                <tbody>
                  <?php if (!empty($documents)): ?>
                    <?php foreach ($documents as $doc): ?>
                      <?php
                      $statusClass = '';
                      switch ($doc['Status']) {
                        case 'Active':
                          $statusClass = 'badge-success';
                          break;
                        case 'Flagged':
                          $statusClass = 'badge-warning';
                          break;
                        case 'Deleted':
                          $statusClass = 'badge-danger';
                          break;
                      }
                      ?>
                      <tr class="document-row">
                        <td><?php echo $doc['DocumentID']; ?></td>
                        <td><?php echo htmlspecialchars($doc['Title']); ?></td>
                        <td><span class="badge badge-info"><?php echo strtoupper($doc['FileType']); ?></span></td>
                        <td><?php echo htmlspecialchars($doc['OwnerName']); ?></td>
                        <td>Level <?php echo $doc['AccessLevel']; ?> - <?php echo htmlspecialchars($doc['LevelName']); ?></td>
                        <td><?php echo date('M d, Y', strtotime($doc['UploadDate'])); ?></td>
                        <td><span class="badge <?php echo $statusClass; ?>"><?php echo $doc['Status']; ?></span></td>
                        <td>
                          <a href="view-document.php?id=<?php echo $doc['DocumentID']; ?>" class="btn btn-info btn-sm" title="View Document">
                            <i class="ti-eye"></i>
                          </a>
                          <a href="../public/download.php?id=<?php echo $doc['DocumentID']; ?>" class="btn btn-primary btn-sm" title="Download">
                            <i class="ti-download"></i>
                          </a>
                          
                          <?php if ($doc['Status'] == 'Active'): ?>
                            <button type="button" class="btn btn-warning btn-sm flag-document" data-id="<?php echo $doc['DocumentID']; ?>" title="Flag Document">
                              <i class="ti-flag-alt"></i>
                            </button>
                          <?php elseif ($doc['Status'] == 'Flagged'): ?>
                            <button type="button" class="btn btn-success btn-sm unflag-document" data-id="<?php echo $doc['DocumentID']; ?>" title="Unflag Document">
                              <i class="ti-check"></i>
                            </button>
                          <?php endif; ?>
                          
                          <?php if ($doc['Status'] != 'Deleted'): ?>
                            <button type="button" class="btn btn-danger btn-sm delete-document" data-id="<?php echo $doc['DocumentID']; ?>" title="Delete Document">
                              <i class="ti-trash"></i>
                            </button>
                          <?php else: ?>
                            <button type="button" class="btn btn-success btn-sm restore-document" data-id="<?php echo $doc['DocumentID']; ?>" title="Restore Document">
                              <i class="ti-reload"></i>
                            </button>
                          <?php endif; ?>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  <?php else: ?>
                    <tr>
                      <td colspan="8" class="text-center text-muted">No documents found</td>
                    </tr>
                  <?php endif; ?>
                </tbody>
              </table>
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
            <input type="hidden" id="documentId" name="documentId" value="">
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
            <input type="hidden" id="deleteDocumentId" name="documentId" value="">
            <div class="form-group">
              <label for="deleteReason">Reason for Deletion</label>
              <textarea class="form-control" id="deleteReason" name="deleteReason" rows="3" required></textarea>
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

<!-- Add this script to the bottom of the page -->
<script>
// Wait for the DOM to be fully loaded
document.addEventListener('DOMContentLoaded', function() {
  console.log("Document ready - initializing document management");
  
  // Flag document functionality
  $('.flag-document').on('click', function() {
    console.log("Flag document clicked");
    var documentId = $(this).data('id');
    $('#documentId').val(documentId);
    $('#flagDocumentModal').modal('show');
  });
  
  // Delete document functionality
  $('.delete-document').on('click', function() {
    console.log("Delete document clicked");
    var documentId = $(this).data('id');
    $('#deleteDocumentId').val(documentId);
    $('#deleteDocumentModal').modal('show');
  });
  
  // Unflag document functionality
  $('.unflag-document').on('click', function() {
    if (confirm('Are you sure you want to remove the flag from this document?')) {
      console.log("Unflagging document");
      var documentId = $(this).data('id');
      
      $.ajax({
        url: 'process-unflag-document.php',
        type: 'POST',
        data: { documentId: documentId },
        success: function(response) {
          location.reload();
        },
        error: function() {
          alert('Error unflagging document. Please try again.');
        }
      });
    }
  });
  
  // Restore document functionality
  $('.restore-document').on('click', function() {
    if (confirm('Are you sure you want to restore this document?')) {
      console.log("Restoring document");
      var documentId = $(this).data('id');
      
      $.ajax({
        url: 'process-restore-document.php',
        type: 'POST',
        data: { documentId: documentId },
        success: function(response) {
          location.reload();
        },
        error: function() {
          alert('Error restoring document. Please try again.');
        }
      });
    }
  });

  // Search and filtering functionality
  function applyFilters() {
    console.log("Applying filters");
    var fileType = $('#file-type-filter').val().toLowerCase();
    var accessLevel = $('#access-level-filter').val();
    var status = $('#status-filter').val();
    var dateFilter = $('#date-filter').val();
    var searchText = $('#search-input').val().toLowerCase();
    
    console.log("File type: " + fileType);
    console.log("Access level: " + accessLevel);
    console.log("Status: " + status);
    console.log("Date filter: " + dateFilter);
    console.log("Search text: " + searchText);
    
    $('.document-row').each(function() {
      var row = $(this);
      var docId = row.find('td:nth-child(1)').text().toLowerCase();
      var title = row.find('td:nth-child(2)').text().toLowerCase();
      var type = row.find('td:nth-child(3)').text().toLowerCase();
      var owner = row.find('td:nth-child(4)').text().toLowerCase();
      var accessLevelText = row.find('td:nth-child(5)').text();
      var date = row.find('td:nth-child(6)').text();
      var statusText = row.find('td:nth-child(7)').text();
      
      // Check file type filter
      var typeMatch = (fileType === '' || type.indexOf(fileType) > -1);
      
      // Check access level filter
      var accessLevelMatch = (accessLevel === '' || accessLevelText.indexOf('Level ' + accessLevel) > -1);
      
      // Check status filter
      var statusMatch = (status === '' || statusText === status);
      
      // Check date filter
      var dateMatch = true;
      if (dateFilter !== '') {
        var uploadDate = new Date(date);
        var today = new Date();
        
        if (dateFilter === 'today') {
          dateMatch = uploadDate.toDateString() === today.toDateString();
        } else if (dateFilter === 'week') {
          var weekAgo = new Date();
          weekAgo.setDate(today.getDate() - 7);
          dateMatch = uploadDate >= weekAgo;
        } else if (dateFilter === 'month') {
          var monthAgo = new Date();
          monthAgo.setMonth(today.getMonth() - 1);
          dateMatch = uploadDate >= monthAgo;
        } else if (dateFilter === 'year') {
          var yearAgo = new Date();
          yearAgo.setFullYear(today.getFullYear() - 1);
          dateMatch = uploadDate >= yearAgo;
        }
      }
      
      // Check search text (partial matching in multiple columns)
      var searchMatch = (searchText === '' || 
                        docId.indexOf(searchText) > -1 ||
                        title.indexOf(searchText) > -1 || 
                        type.indexOf(searchText) > -1 || 
                        owner.indexOf(searchText) > -1 ||
                        accessLevelText.toLowerCase().indexOf(searchText) > -1 ||
                        date.toLowerCase().indexOf(searchText) > -1 ||
                        statusText.toLowerCase().indexOf(searchText) > -1);
      
      console.log("Row " + title + ": Type match=" + typeMatch + ", Access match=" + accessLevelMatch + 
                 ", Status match=" + statusMatch + ", Date match=" + dateMatch + ", Search match=" + searchMatch);
      
      // Show/hide row based on combined filters
      if (typeMatch && accessLevelMatch && statusMatch && dateMatch && searchMatch) {
        row.show();
      } else {
        row.hide();
      }
    });
    
    // Show message if no rows are visible
    var visibleRows = $('.document-row:visible').length;
    console.log("Visible rows: " + visibleRows);
    
    if (visibleRows === 0 && $('.document-row').length > 0) {
      // If we already have a "no results" message, don't add another one
      if ($('#no-results-message').length === 0) {
        $('#documents-table tbody').append(
          '<tr id="no-results-message"><td colspan="8" class="text-center">No documents match your search criteria. <button type="button" id="clear-filters-msg" class="btn btn-link p-0">Clear filters</button></td></tr>'
        );
        
        // Add click handler for the "Clear filters" button in message
        $('#clear-filters-msg').on('click', function() {
          clearFilters();
        });
      }
    } else {
      // Remove the "no results" message if there are visible rows
      $('#no-results-message').remove();
    }
  }
  
  // Function to clear all filters
  function clearFilters() {
    console.log("Clearing all filters");
    $('#file-type-filter').val('');
    $('#access-level-filter').val('');
    $('#status-filter').val('');
    $('#date-filter').val('');
    $('#search-input').val('');
    applyFilters();
  }
  
  // Add manual event handlers for all filter changes
  document.getElementById('file-type-filter').addEventListener('change', function() {
    console.log("File type filter changed");
    applyFilters();
  });
  
  document.getElementById('access-level-filter').addEventListener('change', function() {
    console.log("Access level filter changed");
    applyFilters();
  });
  
  document.getElementById('status-filter').addEventListener('change', function() {
    console.log("Status filter changed");
    applyFilters();
  });
  
  document.getElementById('date-filter').addEventListener('change', function() {
    console.log("Date filter changed");
    applyFilters();
  });
  
  document.getElementById('search-btn').addEventListener('click', function() {
    console.log("Search button clicked");
    applyFilters();
  });
  
  document.getElementById('search-input').addEventListener('keyup', function(e) {
    if (e.keyCode === 13) { // Enter key
      console.log("Enter key pressed in search input");
      applyFilters();
    }
  });
  
  document.getElementById('clear-filter-btn').addEventListener('click', function() {
    console.log("Clear filters button clicked");
    clearFilters();
  });
  
  console.log("Document management functionality initialized");
});
</script>

<?php
include 'include/footer.php';
?>