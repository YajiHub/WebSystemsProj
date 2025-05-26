<?php
// File: admin/document-access-logs.php
session_start();

// // Check if user is logged in and is admin
// if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
//     header("Location: ../public/login.php");
//     exit;
// }

include 'include/header.php';
include 'include/admin-sidebar.php';

// Get document ID from URL parameter
$doc_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// In a real application, you would fetch document details from a database
// This is a placeholder for demonstration purposes
$document = [
  'id' => $doc_id,
  'title' => 'Project Proposal',
  'type' => 'PDF',
  'size' => '4.2 MB',
  'owner' => 'John Doe',
  'owner_id' => 1,
  'access_level' => 'Level 3',
  'uploaded_date' => '2023-06-01',
  'status' => 'Active'
];

// Access logs - would be fetched from database
$access_logs = [
  ['John Doe', 'Upload', '2023-06-01 14:32:45'],
  ['Maria Garcia', 'View', '2023-06-01 15:18:22'],
  ['John Doe', 'Edit Metadata', '2023-06-02 09:45:07'],
  ['Ahmed Khan', 'View', '2023-06-03 11:10:15'],
  ['Lisa Wong', 'Download', '2023-06-04 16:55:33'],
  ['Robert Smith', 'View', '2023-06-05 10:22:18'],
  ['Emma Johnson', 'View', '2023-06-06 14:05:49'],
  ['Michael Brown', 'Download', '2023-06-07 09:30:11'],
  ['Sophia Martinez', 'View', '2023-06-08 15:45:23'],
  ['John Doe', 'Edit Metadata', '2023-06-09 11:17:36']
];
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
                <li class="breadcrumb-item"><a href="view-document.php?id=<?php echo $doc_id; ?>"><?php echo htmlspecialchars($document['title']); ?></a></li>
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
                <div><?php echo htmlspecialchars($document['title']); ?></div>
              </div>
              <div class="col-md-3">
                <div class="font-weight-bold mb-2">Type:</div>
                <div><span class="badge badge-info"><?php echo htmlspecialchars($document['type']); ?></span></div>
              </div>
              <div class="col-md-3">
                <div class="font-weight-bold mb-2">Owner:</div>
                <div><a href="view-user.php?id=<?php echo $document['owner_id']; ?>"><?php echo htmlspecialchars($document['owner']); ?></a></div>
              </div>
              <div class="col-md-3">
                <div class="font-weight-bold mb-2">Status:</div>
                <div>
                  <span class="badge <?php echo $document['status'] == 'Active' ? 'badge-success' : ($document['status'] == 'Flagged' ? 'badge-warning' : 'badge-danger'); ?>">
                    <?php echo htmlspecialchars($document['status']); ?>
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
                  <option value="view">View</option>
                  <option value="download">Download</option>
                  <option value="upload">Upload</option>
                  <option value="edit">Edit</option>
                </select>
              </div>
              <div class="col-md-3">
                <select class="form-control" id="user-filter">
                  <option value="">All Users</option>
                  <option value="1">John Doe</option>
                  <option value="2">Maria Garcia</option>
                  <option value="3">Ahmed Khan</option>
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
              <div class="col-md-3">
                <button type="button" class="btn btn-primary" id="export-log">
                  <i class="ti-download mr-1"></i> Export Log
                </button>
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
                  <?php
                  foreach ($access_logs as $log) {
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
                      case 'Edit Metadata':
                        $actionIcon = 'ti-pencil';
                        break;
                      default:
                        $actionIcon = 'ti-file';
                    }
                    
                    echo '<tr>';
                    echo '<td>' . $log[0] . '</td>';
                    echo '<td><i class="' . $actionIcon . ' mr-1"></i> ' . $log[1] . '</td>';
                    echo '<td>' . $log[2] . '</td>';
                    echo '<td>';
                    
                    // Details button - would show more info in a real application
                    echo '<button type="button" class="btn btn-info btn-sm view-details" data-toggle="modal" data-target="#detailsModal" data-user="' . $log[0] . '" data-action="' . $log[1] . '" data-time="' . $log[2] . '">
                            <i class="ti-info-alt"></i> Details
                          </button>';
                    
                    echo '</td>';
                    echo '</tr>';
                  }
                  ?>
                </tbody>
              </table>
            </div>
            
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
              <p class="form-control-static" id="modal-user">John Doe</p>
            </div>
          </div>
          <div class="form-group row">
            <label class="col-sm-4 col-form-label">Action:</label>
            <div class="col-sm-8">
              <p class="form-control-static" id="modal-action">View</p>
            </div>
          </div>
          <div class="form-group row">
            <label class="col-sm-4 col-form-label">Timestamp:</label>
            <div class="col-sm-8">
              <p class="form-control-static" id="modal-time">2023-06-01 15:18:22</p>
            </div>
          </div>
          <div class="form-group row">
            <label class="col-sm-4 col-form-label">User Agent:</label>
            <div class="col-sm-8">
              <p class="form-control-static">Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36</p>
            </div>
          </div>
          <div class="form-group row">
            <label class="col-sm-4 col-form-label">Session ID:</label>
            <div class="col-sm-8">
              <p class="form-control-static">s9d87f6g5h4j3k2l1</p>
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
    $('#access-logs-table').DataTable({
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
    
    // Export logs
    $('#export-log').on('click', function() {
      // In a real application, you would trigger a download of the logs
      alert('Exporting access logs as CSV...');
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