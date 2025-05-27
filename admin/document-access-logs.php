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

// Get access logs for the document
$access_logs = [];
$sql = "SELECT u.FirstName, u.LastName, a.AccessName, f.Timestamp 
        FROM fileaccesslog f 
        JOIN user u ON f.UserID = u.UserID 
        JOIN accesstype a ON f.AccessType = a.AccessTypeID 
        WHERE f.DocumentID = $doc_id 
        ORDER BY f.Timestamp DESC";
$result = mysqli_query($conn, $sql);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $access_logs[] = [
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
            <h3 class="font-weight-bold">Document Access Log</h3>
            <nav aria-label="breadcrumb">
              <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="manage-documents.php">Documents</a></li>
                <li class="breadcrumb-item"><a href="view-document.php?id=<?php echo $doc_id; ?>"><?php echo htmlspecialchars($document['Title']); ?></a></li>
                <li class="breadcrumb-item active" aria-current="page">Access Log</li>
              </ol>
            </nav>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Document Info -->
    <div class="row">
      <div class="col-md-12 grid-margin stretch-card">
        <div class="card">
          <div class="card-body">
            <h4 class="card-title">Document Information</h4>
            <div class="row">
              <div class="col-md-3">
                <div class="font-weight-bold mb-2">Document Title:</div>
                <div><?php echo htmlspecialchars($document['Title']); ?></div>
              </div>
              <div class="col-md-3">
                <div class="font-weight-bold mb-2">Type:</div>
                <div><span class="badge badge-info"><?php echo strtoupper($document['FileType']); ?></span></div>
              </div>
              <div class="col-md-3">
                <div class="font-weight-bold mb-2">Owner:</div>
                <div><a href="view-user.php?id=<?php echo $document['UserID']; ?>"><?php echo htmlspecialchars($document['FirstName'] . ' ' . $document['LastName']); ?></a></div>
              </div>
              <div class="col-md-3">
                <div class="font-weight-bold mb-2">Status:</div>
                <div>
                  <span class="badge <?php echo $document['IsDeleted'] == 0 ? 'badge-success' : ($document['FlagReason'] ? 'badge-warning' : 'badge-danger'); ?>">
                    <?php echo $document['IsDeleted'] == 0 ? 'Active' : ($document['FlagReason'] ? 'Flagged' : 'Deleted'); ?>
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Access Logs -->
    <div class="row">
      <div class="col-md-12 grid-margin stretch-card">
        <div class="card">
          <div class="card-body">
            <h4 class="card-title">Access History</h4>
            
            <!-- Filters -->
            <div class="row mb-4">
              <div class="col-md-3">
                <select class="form-control" id="action-filter">
                  <option value="">All Actions</option>
                  <option value="View">View</option>
                  <option value="Download">Download</option>
                  <option value="Upload">Upload</option>
                  <option value="Delete">Delete</option>
                  <option value="Flag">Flag</option>
                </select>
              </div>
              <div class="col-md-3">
                <select class="form-control" id="date-filter">
                  <option value="">Any Date</option>
                  <option value="today">Today</option>
                  <option value="yesterday">Yesterday</option>
                  <option value="week">This Week</option>
                  <option value="month">This Month</option>
                </select>
              </div>
              <div class="col-md-6">
                <div class="input-group">
                  <input type="text" class="form-control" id="search-input" placeholder="Search user...">
                  <div class="input-group-append">
                    <button class="btn btn-primary" id="search-btn" type="button">Search</button>
                  </div>
                </div>
              </div>
            </div>
            
            <div class="table-responsive">
              <table class="table table-striped table-hover" id="access-logs-table">
                <thead>
                  <tr>
                    <th>User</th>
                    <th>Action</th>
                    <th>Timestamp</th>
                    <th>Details</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (empty($access_logs)): ?>
                    <tr>
                      <td colspan="4" class="text-center">No access logs found for this document.</td>
                    </tr>
                  <?php else: ?>
                    <?php foreach ($access_logs as $log): ?>
                      <?php
                      // Set icon based on action
                      $actionIcon = '';
                      switch ($log[1]) {
                        case 'Upload':
                          $actionIcon = 'ti-upload';
                          break;
                        case 'Download':
                          $actionIcon = 'ti-download';
                          break;
                        case 'View':
                          $actionIcon = 'ti-eye';
                          break;
                        case 'Delete':
                          $actionIcon = 'ti-trash';
                          break;
                        case 'Flag':
                          $actionIcon = 'ti-flag-alt';
                          break;
                        default:
                          $actionIcon = 'ti-file';
                      }
                      ?>
                      <tr>
                        <td><?php echo htmlspecialchars($log[0]); ?></td>
                        <td><i class="<?php echo $actionIcon; ?> mr-1"></i> <?php echo htmlspecialchars($log[1]); ?></td>
                        <td><?php echo htmlspecialchars($log[2]); ?></td>
                        <td>
                          <button type="button" class="btn btn-info btn-sm view-details" data-toggle="modal" data-target="#detailsModal" 
                                  data-user="<?php echo htmlspecialchars($log[0]); ?>" 
                                  data-action="<?php echo htmlspecialchars($log[1]); ?>" 
                                  data-time="<?php echo htmlspecialchars($log[2]); ?>">
                            <i class="ti-info-alt"></i> Details
                          </button>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
            
            <!-- Pagination - only show if more than 10 logs -->
            <?php if (count($access_logs) > 10): ?>
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

  <!-- Log Details Modal -->
  <div class="modal fade" id="detailsModal" tabindex="-1" role="dialog" aria-labelledby="detailsModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="detailsModalLabel">Access Details</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <div class="form-group row">
            <label class="col-sm-4 col-form-label">User:</label>
            <div class="col-sm-8">
              <p class="form-control-static" id="modal-user"></p>
            </div>
          </div>
          <div class="form-group row">
            <label class="col-sm-4 col-form-label">Action:</label>
            <div class="col-sm-8">
              <p class="form-control-static" id="modal-action"></p>
            </div>
          </div>
          <div class="form-group row">
            <label class="col-sm-4 col-form-label">Timestamp:</label>
            <div class="col-sm-8">
              <p class="form-control-static" id="modal-time"></p>
            </div>
          </div>
          <div class="form-group row">
            <label class="col-sm-4 col-form-label">User Agent:</label>
            <div class="col-sm-8">
              <p class="form-control-static">
                <?php echo htmlspecialchars($_SERVER['HTTP_USER_AGENT'] ?? 'Not available'); ?>
              </p>
            </div>
          </div>
          <div class="form-group row">
            <label class="col-sm-4 col-form-label">IP Address:</label>
            <div class="col-sm-8">
              <p class="form-control-static">
                <?php echo htmlspecialchars($_SERVER['REMOTE_ADDR'] ?? 'Not available'); ?>
              </p>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>

<script>
  $(document).ready(function() {
    // Initialize DataTable
    var table = $('#access-logs-table').DataTable({
      "pageLength": 10,
      "lengthMenu": [10, 25, 50, 100],
      "order": [[ 2, "desc" ]],
      "searching": true,
      "language": {
        "search": "Search:",
        "lengthMenu": "Show _MENU_ entries per page",
        "info": "Showing _START_ to _END_ of _TOTAL_ entries",
        "infoEmpty": "Showing 0 to 0 of 0 entries",
        "infoFiltered": "(filtered from _MAX_ total entries)"
      }
    });
    
    // Action filter
    $('#action-filter').on('change', function() {
      var actionFilter = $(this).val();
      table.column(1).search(actionFilter).draw();
    });
    
    // Date filter
    $('#date-filter').on('change', function() {
      var dateFilter = $(this).val();
      var today = new Date();
      var filterDate = '';
      
      switch(dateFilter) {
        case 'today':
          filterDate = today.toISOString().split('T')[0];
          table.column(2).search(filterDate).draw();
          break;
        case 'yesterday':
          var yesterday = new Date(today);
          yesterday.setDate(yesterday.getDate() - 1);
          filterDate = yesterday.toISOString().split('T')[0];
          table.column(2).search(filterDate).draw();
          break;
        case 'week':
          // Search for dates in the last 7 days
          table.column(2).search('').draw();
          break;
        case 'month':
          // Search for dates in the current month
          table.column(2).search('').draw();
          break;
        default:
          table.column(2).search('').draw();
      }
    });
    
    // Search button
    $('#search-btn').on('click', function() {
      var searchText = $('#search-input').val();
      table.column(0).search(searchText).draw();
    });
    
    // Search input - search when Enter key is pressed
    $('#search-input').on('keyup', function(e) {
      if (e.keyCode === 13) {
        var searchText = $(this).val();
        table.column(0).search(searchText).draw();
      }
    });
    
    // Pass data to modal
    $('#detailsModal').on('show.bs.modal', function (event) {
      var button = $(event.relatedTarget);
      var user = button.data('user');
      var action = button.data('action');
      var time = button.data('time');
      
      var modal = $(this);
      modal.find('#modal-user').text(user);
      modal.find('#modal-action').text(action);
      modal.find('#modal-time').text(time);
    });
  });
</script>

<?php
include 'include/footer.php';
?>
                