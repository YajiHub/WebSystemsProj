<?php
// File: admin/view-user.php
session_start();

// // Check if user is logged in and is admin
// if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
//     header("Location: ../public/login.php");
//     exit;
// }

include 'include/header.php';
include 'include/admin-sidebar.php';

// Get user ID from URL parameter
$user_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// In a real application, you would fetch user details from a database
// This is a placeholder for demonstration purposes
$user = [
  'id' => $user_id,
  'first_name' => 'John',
  'last_name' => 'Doe',
  'email' => 'john.doe@example.com',
  'role' => 'User',
  'access_level' => 'Level 3',
  'status' => 'Active',
  'department' => 'Marketing',
  'phone' => '(123) 456-7890',
  'registered_date' => '2023-04-15',
  'last_login' => '2023-06-01 14:32:45',
  'document_count' => 12,
  'storage_used' => '28.6 MB',
  'storage_limit' => '100 MB'
];

// Recent activity - would be fetched from database
$user_activity = [
  ['Upload', 'Financial Report Q2.pdf', '2023-06-01 14:32:45', '192.168.1.45'],
  ['View', 'Marketing Plan.docx', '2023-06-01 13:15:22', '192.168.1.45'],
  ['Download', 'Product Mockups.jpg', '2023-06-01 11:45:07', '192.168.1.45'],
  ['Login', '', '2023-06-01 09:30:15', '192.168.1.45'],
  ['Delete', 'Old Contract.pdf', '2023-05-31 16:55:33', '192.168.1.45']
];

// User documents - would be fetched from database
$user_documents = [
  [1, 'Project Proposal', 'PDF', '4.2 MB', '2023-06-01', 'Active'],
  [2, 'Financial Analysis', 'PDF', '1.8 MB', '2023-05-28', 'Active'],
  [3, 'Meeting Notes', 'PDF', '0.3 MB', '2023-05-20', 'Active'],
  [4, 'Product Image', 'JPG', '1.2 MB', '2023-05-15', 'Flagged'],
  [5, 'Logo Design', 'PNG', '0.5 MB', '2023-05-10', 'Active']
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
            <h3 class="font-weight-bold">User Profile: <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></h3>
            <nav aria-label="breadcrumb">
              <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="manage-users.php">Users</a></li>
                <li class="breadcrumb-item active" aria-current="page">View User</li>
              </ol>
            </nav>
          </div>
          <div class="col-12 col-xl-4">
            <div class="justify-content-end d-flex">
              <a href="edit-user.php?id=<?php echo $user_id; ?>" class="btn btn-primary mr-2">
                <i class="ti-pencil mr-1"></i> Edit User
              </a>
              <?php if ($user['status'] == 'Active'): ?>
              <button class="btn btn-warning suspend-user" data-id="<?php echo $user_id; ?>">
                <i class="ti-control-pause mr-1"></i> Suspend User
              </button>
              <?php else: ?>
              <button class="btn btn-success activate-user" data-id="<?php echo $user_id; ?>">
                <i class="ti-control-play mr-1"></i> Activate User
              </button>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <!-- User Info -->
    <div class="row">
      <div class="col-md-4 grid-margin stretch-card">
        <div class="card">
          <div class="card-body">
            <div class="d-flex flex-column align-items-center text-center">
              <img src="../images/faces/face3.jpg" alt="Profile" class="rounded-circle" width="150">
              <div class="mt-3">
                <h4><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></h4>
                <p class="text-muted font-weight-bold"><?php echo htmlspecialchars($user['department']); ?></p>
                <span class="badge <?php echo $user['status'] == 'Active' ? 'badge-success' : ($user['status'] == 'Suspended' ? 'badge-warning' : 'badge-danger'); ?>">
                  <?php echo htmlspecialchars($user['status']); ?>
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <div class="col-md-8 grid-margin stretch-card">
        <div class="card">
          <div class="card-body">
            <h4 class="card-title">User Information</h4>
            <div class="row">
              <div class="col-md-6">
                <div class="list-group list-group-flush">
                  <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                    <span class="font-weight-bold">User ID:</span>
                    <span><?php echo htmlspecialchars($user['id']); ?></span>
                  </div>
                  <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                    <span class="font-weight-bold">Email:</span>
                    <span><?php echo htmlspecialchars($user['email']); ?></span>
                  </div>
                  <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                    <span class="font-weight-bold">Role:</span>
                    <span><?php echo htmlspecialchars($user['role']); ?></span>
                  </div>
                  <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                    <span class="font-weight-bold">Access Level:</span>
                    <span><?php echo htmlspecialchars($user['access_level']); ?></span>
                  </div>
                  <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                    <span class="font-weight-bold">Department:</span>
                    <span><?php echo htmlspecialchars($user['department']); ?></span>
                  </div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="list-group list-group-flush">
                  <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                    <span class="font-weight-bold">Phone:</span>
                    <span><?php echo htmlspecialchars($user['phone']); ?></span>
                  </div>
                  <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                    <span class="font-weight-bold">Registered:</span>
                    <span><?php echo htmlspecialchars($user['registered_date']); ?></span>
                  </div>
                  <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                    <span class="font-weight-bold">Last Login:</span>
                    <span><?php echo htmlspecialchars($user['last_login']); ?></span>
                  </div>
                  <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                    <span class="font-weight-bold">Documents:</span>
                    <span><?php echo htmlspecialchars($user['document_count']); ?></span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Recent Activity & User Documents -->
    <div class="row">
      <div class="col-md-6 grid-margin stretch-card">
        <div class="card">
          <div class="card-body">
            <h4 class="card-title">Recent Activity</h4>
            <div class="table-responsive">
              <table class="table table-hover">
                <thead>
                  <tr>
                    <th>Action</th>
                    <th>Resource</th>
                    <th>Time</th>
                    <th>IP</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                  foreach ($user_activity as $activity) {
                    // Set icon based on action
                    $actionIcon = '';
                    switch ($activity[0]) {
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
                      case 'Login':
                        $actionIcon = 'ti-user';
                        break;
                      case 'Logout':
                        $actionIcon = 'ti-power-off';
                        break;
                      default:
                        $actionIcon = 'ti-file';
                    }
                    
                    echo '<tr>';
                    echo '<td><i class="' . $actionIcon . ' mr-1"></i> ' . $activity[0] . '</td>';
                    echo '<td>' . $activity[1] . '</td>';
                    echo '<td>' . $activity[2] . '</td>';
                    echo '<td>' . $activity[3] . '</td>';
                    echo '</tr>';
                  }
                  ?>
                </tbody>
              </table>
            </div>
            <div class="text-center mt-4">
              <a href="access-logs.php?user_id=<?php echo $user_id; ?>" class="btn btn-primary">View Full Log</a>
            </div>
          </div>
        </div>
      </div>
      
      <div class="col-md-6 grid-margin stretch-card">
        <div class="card">
          <div class="card-body">
            <h4 class="card-title">User Documents</h4>
            <div class="table-responsive">
              <table class="table table-hover">
                <thead>
                  <tr>
                    <th>Document</th>
                    <th>Type</th>
                    <th>Size</th>
                    <th>Status</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                  foreach ($user_documents as $doc) {
                    $statusClass = '';
                    switch ($doc[5]) {
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
                    
                    echo '<tr>';
                    echo '<td>' . $doc[1] . '</td>';
                    echo '<td>' . $doc[2] . '</td>';
                    echo '<td>' . $doc[3] . '</td>';
                    echo '<td><span class="badge ' . $statusClass . '">' . $doc[5] . '</span></td>';
                    echo '<td>
                            <a href="view-document.php?id=' . $doc[0] . '" class="btn btn-primary btn-sm">
                              <i class="ti-eye"></i>
                            </a>
                          </td>';
                    echo '</tr>';
                  }
                  ?>
                </tbody>
              </table>
            </div>
            <div class="text-center mt-4">
              <a href="manage-documents.php?user_id=<?php echo $user_id; ?>" class="btn btn-primary">View All Documents</a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Suspend User Modal -->
  <div class="modal fade" id="suspendUserModal" tabindex="-1" role="dialog" aria-labelledby="suspendUserModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="suspendUserModalLabel">Suspend User</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <form id="suspendUserForm" action="process-suspend-user.php" method="post">
            <input type="hidden" id="suspendUserId" name="userId" value="<?php echo $user_id; ?>">
            <div class="form-group">
              <label for="suspendReason">Reason for Suspension</label>
              <select class="form-control" id="suspendReason" name="suspendReason" required>
                <option value="">-- Select Reason --</option>
                <option value="violation">Policy Violation</option>
                <option value="inactive">Inactivity</option>
                <option value="security">Security Concern</option>
                <option value="request">User Request</option>
                <option value="other">Other</option>
              </select>
            </div>
            <div class="form-group">
              <label for="suspendDuration">Suspension Duration</label>
              <select class="form-control" id="suspendDuration" name="suspendDuration" required>
                <option value="1">1 Day</option>
                <option value="3">3 Days</option>
                <option value="7" selected>7 Days</option>
                <option value="14">14 Days</option>
                <option value="30">30 Days</option>
                <option value="0">Indefinite (Until Manual Reactivation)</option>
              </select>
            </div>
            <div class="form-group">
              <label for="suspendComments">Additional Comments</label>
              <textarea class="form-control" id="suspendComments" name="suspendComments" rows="3"></textarea>
              <small class="form-text text-muted">Will be sent to the user.</small>
            </div>
            <div class="form-group">
              <div class="form-check form-check-flat form-check-primary">
                <label class="form-check-label">
                  <input type="checkbox" class="form-check-input" name="notifyUser" checked>
                  Notify user via email
                </label>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
              <button type="submit" class="btn btn-warning">Suspend User</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

<script>
  $(document).ready(function() {
    // Suspend user
    $('.suspend-user').on('click', function() {
      $('#suspendUserModal').modal('show');
    });
    
    // Activate user
    $('.activate-user').on('click', function() {
      if (confirm('Are you sure you want to reactivate this user?')) {
        var userId = $(this).data('id');
        // In a real application, you would make an AJAX call to reactivate the user
        console.log('Activating user ' + userId);
        // Reload page or update UI
      }
    });
  });
</script>

<?php
include 'include/footer.php';
?>