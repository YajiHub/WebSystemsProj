<?php
// File: admin/my-documents.php
// Include session management
require_once '../public/include/session.php';

// Require admin login
requireAdmin();

// Get admin's personal documents
$documents = getDocumentsByUserId($conn, $_SESSION['user_id']);

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
            <h3 class="font-weight-bold">My Documents</h3>
            <h6 class="font-weight-normal mb-0">View and manage your personal documents</h6>
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
                    <button class="btn btn-secondary" id="clear-btn" type="button">Clear</button>
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
                    <th>Uploaded</th>
                    <th>Actions</th>
                  </tr>  
                </thead>
                <tbody id="documents-tbody">
                  <?php if (empty($documents)): ?>
                    <tr>
                      <td colspan="5" class="text-center">No documents found. <a href="upload.php">Upload your first document</a>.</td>
                    </tr>
                  <?php else: ?>
                    <?php foreach ($documents as $doc): ?>
                      <tr class="document-row">
                        <td><?php echo htmlspecialchars($doc['Title']); ?></td>
                        <td><span class="badge badge-info"><?php echo strtoupper($doc['FileType']); ?></span></td>
                        <td><?php echo formatFileSize(file_exists($doc['FileLocation']) ? filesize($doc['FileLocation']) : 0); ?></td>
                        <td><?php echo date('Y-m-d H:i', strtotime($doc['UploadDate'])); ?></td>
                        <td>
                          <a href="admin-view.php?id=<?php echo $doc['DocumentID']; ?>" class="btn btn-info btn-sm">
                            <i class="ti-eye"></i> View
                          </a>
                          <a href="../public/download.php?id=<?php echo $doc['DocumentID']; ?>" class="btn btn-primary btn-sm">
                            <i class="ti-download"></i> Download
                          </a>
                          <a href="admin-delete.php?id=<?php echo $doc['DocumentID']; ?>" class="btn btn-danger btn-sm delete-doc">
                            <i class="ti-trash"></i> Delete
                          </a>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </tbody>
              </table>
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
// Strict mode to catch common JavaScript errors
'use strict';

// Wait for page to fully load
document.addEventListener('DOMContentLoaded', function() {
  // For debugging
  console.log('Document ready, initializing search functionality');
  
  // Get DOM elements
  var typeFilterElement = document.getElementById('type-filter');
  var dateFilterElement = document.getElementById('date-filter');
  var searchInputElement = document.getElementById('search-input');
  var searchBtnElement = document.getElementById('search-btn');
  var clearBtnElement = document.getElementById('clear-btn');
  var documentRows = document.querySelectorAll('.document-row');
  var tbody = document.getElementById('documents-tbody');
  
  // Handle delete confirmation
  var deleteButtons = document.querySelectorAll('.delete-doc');
  var confirmDeleteButton = document.getElementById('confirm-delete');
  
  deleteButtons.forEach(function(button) {
    button.addEventListener('click', function(e) {
      e.preventDefault();
      var deleteUrl = this.getAttribute('href');
      confirmDeleteButton.setAttribute('href', deleteUrl);
      $('#deleteModal').modal('show');
    });
  });
  
  // Function to filter documents
  function applyFilters() {
    console.log('Applying filters...');
    
    // Get filter values
    var typeFilter = typeFilterElement.value.toLowerCase();
    var dateFilter = dateFilterElement.value;
    var searchText = searchInputElement.value.toLowerCase();
    
    console.log('Type filter:', typeFilter);
    console.log('Date filter:', dateFilter);
    console.log('Search text:', searchText);
    
    // Track if we have any visible rows
    var visibleRowCount = 0;
    
    // Process each document row
    documentRows.forEach(function(row) {
      // Get values from columns
      var title = row.cells[0].textContent.toLowerCase();
      var type = row.cells[1].textContent.toLowerCase();
      var size = row.cells[2].textContent.toLowerCase();
      var dateStr = row.cells[3].textContent;
      
      // Check if type matches filter
      var typeMatch = (typeFilter === '' || type.indexOf(typeFilter) !== -1);
      
      // Check if date matches filter
      var dateMatch = true;
      if (dateFilter !== '') {
        var uploadDate = new Date(dateStr);
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
      
      // Check if any field matches search text (partial matching)
      var searchMatch = (searchText === '' || 
                        title.indexOf(searchText) !== -1 || 
                        type.indexOf(searchText) !== -1 || 
                        size.indexOf(searchText) !== -1 || 
                        dateStr.toLowerCase().indexOf(searchText) !== -1);
      
      // Determine if this row should be visible
      var shouldBeVisible = typeMatch && dateMatch && searchMatch;
      
      // Show or hide row
      row.style.display = shouldBeVisible ? '' : 'none';
      
      // Count visible rows
      if (shouldBeVisible) {
        visibleRowCount++;
      }
    });
    
    // Remove any existing "no results" message
    var noResultsRow = document.getElementById('no-results-message');
    if (noResultsRow) {
      noResultsRow.parentNode.removeChild(noResultsRow);
    }
    
    // Show "no results" message if needed
    if (visibleRowCount === 0 && documentRows.length > 0) {
      var noResultsRow = document.createElement('tr');
      noResultsRow.id = 'no-results-message';
      
      var noResultsCell = document.createElement('td');
      noResultsCell.colSpan = 5;
      noResultsCell.className = 'text-center';
      noResultsCell.innerHTML = 'No documents match your search criteria. <button type="button" id="clear-filters-message" class="btn btn-link p-0">Clear filters</button>';
      
      noResultsRow.appendChild(noResultsCell);
      tbody.appendChild(noResultsRow);
      
      // Add click handler for the "Clear filters" button in message
      document.getElementById('clear-filters-message').addEventListener('click', function() {
        clearFilters();
      });
    }
  }
  
  // Function to clear all filters
  function clearFilters() {
    console.log('Clearing all filters');
    typeFilterElement.value = '';
    dateFilterElement.value = '';
    searchInputElement.value = '';
    applyFilters();
  }
  
  // Add event listeners
  if (typeFilterElement) {
    typeFilterElement.addEventListener('change', applyFilters);
  }
  
  if (dateFilterElement) {
    dateFilterElement.addEventListener('change', applyFilters);
  }
  
  if (searchBtnElement) {
    searchBtnElement.addEventListener('click', applyFilters);
  }
  
  if (searchInputElement) {
    searchInputElement.addEventListener('keyup', function(e) {
      if (e.keyCode === 13) { // Enter key
        applyFilters();
      }
    });
  }
  
  if (clearBtnElement) {
    clearBtnElement.addEventListener('click', clearFilters);
  }
});
</script>

<?php
include 'include/footer.php';
?>