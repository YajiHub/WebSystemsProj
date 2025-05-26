<?php
// File: public/my-documents.php
// Include session management
require_once 'include/session.php';

// Require login
requireLogin();

// Get user's documents
$documents = getDocumentsByUserId($conn, $_SESSION['user_id']);

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
?>

<!-- Main Panel -->
<div class="main-panel">
  <div class="content-wrapper">
    <!-- Page Title -->
    <div class="row">
      <div class="col-md-12 grid-margin">
        <div class="row">
          <div class="col-12 col-xl-8 mb-4 mb-xl-0">
            <h3 class="font-weight-bold">My Documents</h3>
            <h6 class="font-weight-normal mb-0">View all your uploaded documents</h6>
          </div>
          <div class="col-12 col-xl-4">
            <div class="justify-content-end d-flex">
              <a href="upload.php" class="btn btn-primary">
                <i class="ti-upload mr-1"></i> Upload New Document
              </a>
            </div>
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
                <select class="form-control" id="type-filter">
                  <option value="">All Types</option>
                  <option value="pdf">PDF</option>
                  <option value="jpg">JPG</option>
                  <option value="png">PNG</option>
                </select>
              </div>
              <div class="col-md-3">
                <select class="form-control" id="date-filter">
                  <option value="">Any Date</option>
                  <option value="today">Today</option>
                  <option value="week">This Week</option>
                  <option value="month">This Month</option>
                  <option value="year">This Year</option>
                </select>
              </div>
              <div class="col-md-6">
                <div class="input-group">
                  <input type="text" class="form-control" id="search-input" placeholder="Search documents...">
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
    
    <!-- Documents Table -->
    <div class="row">
      <div class="col-md-12 grid-margin stretch-card">
        <div class="card">
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-striped table-hover" id="documents-table">
                <thead>
                  <tr>
                    <th>Document Name</th>
                    <th>Type</th>
                    <th>Size</th>
                    <th>Uploaded Date</th>
                    <th>Actions</th>
                  </tr>  
                </thead>
                <tbody>
                  <?php if (empty($documents)): ?>
                    <tr>
                      <td colspan="5" class="text-center">No documents found. <a href="upload.php">Upload your first document</a>.</td>
                    </tr>
                  <?php else: ?>
                    <?php foreach ($documents as $doc): ?>
                      <tr>
                        <td><?php echo htmlspecialchars($doc['Title']); ?></td>
                        <td><span class="badge badge-info"><?php echo strtoupper($doc['FileType']); ?></span></td>
                        <td><?php echo formatFileSize(filesize($doc['FileLocation'])); ?></td>
                        <td><?php echo date('Y-m-d H:i', strtotime($doc['UploadDate'])); ?></td>
                        <td>
                          <a href="view.php?id=<?php echo $doc['DocumentID']; ?>" class="btn btn-dark btn-icon-text btn-sm">
                            <i class="ti-eye"></i> View
                          </a>
                          <a href="download.php?id=<?php echo $doc['DocumentID']; ?>" class="btn btn-primary btn-icon-text btn-sm">
                            <i class="ti-download"></i> Download
                          </a>
                          <a href="delete.php?id=<?php echo $doc['DocumentID']; ?>" class="btn btn-danger btn-icon-text btn-sm delete-doc">
                            <i class="ti-trash"></i> Delete
                          </a>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
            
            <?php if (!empty($documents) && count($documents) > 10): ?>
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
  // Script for delete confirmation
  $(document).ready(function() {
    $('.delete-doc').on('click', function(e) {
      e.preventDefault();
      var deleteUrl = $(this).attr('href');
      $('#confirm-delete').attr('href', deleteUrl);
      $('#deleteModal').modal('show');
    });
    
    // Filter functionality
    $('#type-filter, #date-filter').on('change', function() {
      filterTable();
    });
    
    $('#search-btn').on('click', function() {
      filterTable();
    });
    
    $('#search-input').on('keyup', function(e) {
      if (e  {
      filterTable();
    });
    
    $('#search-input').on('keyup', function(e) {
      if (e.keyCode === 13) { // Enter key
        filterTable();
      }
    });
    
    function filterTable() {
      var typeFilter = $('#type-filter').val().toLowerCase();
      var dateFilter = $('#date-filter').val();
      var searchText = $('#search-input').val().toLowerCase();
      
      $('#documents-table tbody tr').each(function() {
        var row = $(this);
        var type = row.find('td:nth-child(2)').text().toLowerCase();
        var date = row.find('td:nth-child(4)').text();
        var title = row.find('td:nth-child(1)').text().toLowerCase();
        
        var typeMatch = typeFilter === '' || type.includes(typeFilter);
        var dateMatch = true;
        
        // Date filtering logic
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
        
        var searchMatch = searchText === '' || title.includes(searchText);
        
        if (typeMatch && dateMatch && searchMatch) {
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