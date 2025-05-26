<?php
// File: admin/view-user.php
require_once '../public/include/session.php';

// Require admin login
requireAdmin();

// Get user ID from URL parameter
$user_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($user_id <= 0) {
    $_SESSION['error'] = "Invalid user ID.";
    header("Location: manage-users.php");
    exit;
}

// Get user details from database
$sql = "SELECT 
    u.*,
    a.LevelName,
    a.AccessLevelID
FROM user u
LEFT JOIN accesslevel a ON u.AccessLevel = a.AccessLevelID
WHERE u.UserID = ?";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

if (!$user) {
    $_SESSION['error'] = "User not found.";
    header("Location: manage-users.php");
    exit;
}

// Get user's documents with pagination support
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;
$offset = ($page - 1) * $limit;

// Apply filters if provided
$whereClause = "WHERE UserID = ?";
$filterParams = [$user_id];
$filterTypes = "i";

if (isset($_GET['status']) && $_GET['status'] != '') {
    $status = $_GET['status'];
    switch ($status) {
        case 'active':
            $whereClause .= " AND IsDeleted = 0 AND FlagReason IS NULL";
            break;
        case 'flagged':
            $whereClause .= " AND IsDeleted = 1 AND FlagReason IS NOT NULL";
            break;
        case 'deleted':
            $whereClause .= " AND IsDeleted = 1";
            break;
    }
}

if (isset($_GET['type']) && $_GET['type'] != '') {
    $fileType = $_GET['type'];
    $whereClause .= " AND FileType = ?";
    $filterParams[] = $fileType;
    $filterTypes .= "s";
}

// Get total document count for pagination
$countSql = "SELECT COUNT(*) as total FROM document $whereClause";
$countStmt = mysqli_prepare($conn, $countSql);
mysqli_stmt_bind_param($countStmt, $filterTypes, ...$filterParams);
mysqli_stmt_execute($countStmt);
$countResult = mysqli_stmt_get_result($countStmt);
$totalDocs = mysqli_fetch_assoc($countResult)['total'];
$totalPages = ceil($totalDocs / $limit);

// Get paginated documents
$docSql = "SELECT 
    DocumentID,
    Title,
    FileType,
    UploadDate,
    CASE 
        WHEN IsDeleted = 1 THEN 'Deleted'
        WHEN FlagReason IS NOT NULL AND FlagReason != '' THEN 'Flagged'
        ELSE 'Active'
    END as Status,
    FileLocation,
    IsDeleted,
    FlagReason
FROM document 
$whereClause 
ORDER BY UploadDate DESC 
LIMIT ?, ?";

// Add limit and offset to params
$filterParams[] = $offset;
$filterParams[] = $limit;
$filterTypes .= "ii";

$docStmt = mysqli_prepare($conn, $docSql);
mysqli_stmt_bind_param($docStmt, $filterTypes, ...$filterParams);
mysqli_stmt_execute($docStmt);
$docResult = mysqli_stmt_get_result($docStmt);
$userDocuments = [];
while ($row = mysqli_fetch_assoc($docResult)) {
    // Get file size if file exists
    $fileSize = 'Unknown';
    if (file_exists($row['FileLocation'])) {
        $bytes = filesize($row['FileLocation']);
        $fileSize = formatBytes($bytes);
    }
    $row['FileSize'] = $fileSize;
    $userDocuments[] = $row;
}

// Get document count and total storage
$statsSql = "SELECT 
    COUNT(*) as DocumentCount,
    SUM(CASE WHEN IsDeleted = 0 THEN 1 ELSE 0 END) as ActiveDocuments,
    SUM(CASE WHEN IsDeleted = 1 THEN 1 ELSE 0 END) as DeletedDocuments,
    SUM(CASE WHEN FlagReason IS NOT NULL AND FlagReason != '' THEN 1 ELSE 0 END) as FlaggedDocuments
FROM document 
WHERE UserID = ?";

$statsStmt = mysqli_prepare($conn, $statsSql);
mysqli_stmt_bind_param($statsStmt, "i", $user_id);
mysqli_stmt_execute($statsStmt);
$statsResult = mysqli_stmt_get_result($statsStmt);
$userStats = mysqli_fetch_assoc($statsResult);

// Function to format bytes
function formatBytes($bytes, $precision = 2) {
    $units = array('B', 'KB', 'MB', 'GB', 'TB');
    
    for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
        $bytes /= 1024;
    }
    
    return round($bytes, $precision) . ' ' . $units[$i];
}

// Function to calculate how long ago a timestamp was
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
            <h3 class="font-weight-bold">User Profile: <?php echo htmlspecialchars($user['FirstName'] . ' ' . $user['LastName']); ?></h3>
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
                <?php if ($user_id != $_SESSION['user_id']): ?>
                <button class="btn btn-danger delete-user-btn" 
                        data-id="<?php echo $user_id; ?>" 
                        data-name="<?php echo htmlspecialchars($user['FirstName'] . ' ' . $user['LastName']); ?>">
                    <i class="ti-trash mr-1"></i> Delete User
                </button>
                <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <?php
    // Display messages
    if (isset($_SESSION['error'])) {
        echo '<div class="alert alert-danger" role="alert">' . htmlspecialchars($_SESSION['error']) . '</div>';
        unset($_SESSION['error']);
    }
    
    if (isset($_SESSION['success'])) {
        echo '<div class="alert alert-success" role="alert">' . htmlspecialchars($_SESSION['success']) . '</div>';
        unset($_SESSION['success']);
    }
    ?>
    
    <!-- User Info -->
    <div class="row">
      <div class="col-md-4 grid-margin stretch-card">
        <div class="card">
          <div class="card-body">
            <div class="d-flex flex-column align-items-center text-center">
              <?php if (!empty($user['ProfilePicture']) && file_exists($user['ProfilePicture'])): ?>
                <img src="<?php echo $user['ProfilePicture']; ?>" alt="Profile" class="rounded-circle" width="150">
              <?php else: ?>
                <div class="default-profile-pic profile-pic-large">
                  <?php echo strtoupper(substr($user['FirstName'], 0, 1) . substr($user['LastName'], 0, 1)); ?>
                </div>
              <?php endif; ?>
              <div class="mt-3">
                <h4><?php echo htmlspecialchars($user['FirstName'] . ' ' . $user['LastName']); ?></h4>
                <p class="text-muted font-weight-bold">User Profile</p>
                <span class="badge <?php echo $user['UserRole'] == 'admin' ? 'badge-danger' : 'badge-primary'; ?> mb-2">
                  <?php echo ucfirst($user['UserRole']); ?>
                </span>
              </div>
            </div>
            
            <!-- User Stats -->
            <div class="mt-4">
              <h6 class="card-title mb-3">Document Statistics</h6>
              <div class="template-demo">
                <div class="d-flex justify-content-between mt-2">
                  <small class="text-muted">Total Documents</small>
                  <span class="badge badge-info"><?php echo $userStats['DocumentCount']; ?></span>
                </div>
                <div class="d-flex justify-content-between mt-2">
                  <small class="text-muted">Active Documents</small>
                  <span class="badge badge-success"><?php echo $userStats['ActiveDocuments']; ?></span>
                </div>
                <div class="d-flex justify-content-between mt-2">
                  <small class="text-muted">Flagged Documents</small>
                  <span class="badge badge-warning"><?php echo $userStats['FlaggedDocuments']; ?></span>
                </div>
                <div class="d-flex justify-content-between mt-2">
                  <small class="text-muted">Deleted Documents</small>
                  <span class="badge badge-danger"><?php echo $userStats['DeletedDocuments']; ?></span>
                </div>
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
                    <span><?php echo $user['UserID']; ?></span>
                  </div>
                  <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                    <span class="font-weight-bold">Username:</span>
                    <span><?php echo htmlspecialchars($user['Username']); ?></span>
                  </div>
                  <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                    <span class="font-weight-bold">Email:</span>
                    <span><?php echo htmlspecialchars($user['EmailAddress']); ?></span>
                  </div>
                  <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                    <span class="font-weight-bold">Role:</span>
                    <span class="badge <?php echo $user['UserRole'] == 'admin' ? 'badge-danger' : 'badge-primary'; ?>">
                      <?php echo ucfirst($user['UserRole']); ?>
                    </span>
                  </div>
                  <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                    <span class="font-weight-bold">Access Level:</span>
                    <span><?php echo htmlspecialchars($user['LevelName'] ?? 'Not Set'); ?></span>
                  </div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="list-group list-group-flush">
                  <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                    <span class="font-weight-bold">Extension:</span>
                    <span><?php echo htmlspecialchars($user['Extension'] ?? 'Not Set'); ?></span>
                  </div>
                  <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                    <span class="font-weight-bold">Middle Name:</span>
                    <span><?php echo htmlspecialchars($user['MiddleName'] ?? 'Not Set'); ?></span>
                  </div>
                  <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                    <span class="font-weight-bold">Status:</span>
                    <span class="badge badge-success">Active</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <!-- User Documents -->
    <div class="row">
      <div class="col-md-12 grid-margin stretch-card">
        <div class="card">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-4">
              <h4 class="card-title mb-0">User Documents (<?php echo $totalDocs; ?> total)</h4>
              
              <!-- Document filters -->
              <div class="d-flex">
                <div class="mr-2">
                  <select class="form-control form-control-sm" id="document-type-filter">
                    <option value="">All Types</option>
                    <option value="pdf" <?php echo isset($_GET['type']) && $_GET['type'] == 'pdf' ? 'selected' : ''; ?>>PDF</option>
                    <option value="jpg" <?php echo isset($_GET['type']) && $_GET['type'] == 'jpg' ? 'selected' : ''; ?>>JPG</option>
                    <option value="png" <?php echo isset($_GET['type']) && $_GET['type'] == 'png' ? 'selected' : ''; ?>>PNG</option>
                  </select>
                </div>
                <div>
                  <select class="form-control form-control-sm" id="document-status-filter">
                    <option value="">All Status</option>
                    <option value="active" <?php echo isset($_GET['status']) && $_GET['status'] == 'active' ? 'selected' : ''; ?>>Active</option>
                    <option value="flagged" <?php echo isset($_GET['status']) && $_GET['status'] == 'flagged' ? 'selected' : ''; ?>>Flagged</option>
                    <option value="deleted" <?php echo isset($_GET['status']) && $_GET['status'] == 'deleted' ? 'selected' : ''; ?>>Deleted</option>
                  </select>
                </div>
              </div>
            </div>
            
            <div class="table-responsive">
              <table class="table table-hover">
                <thead>
                  <tr>
                    <th>Document</th>
                    <th>Type</th>
                    <th>Size</th>
                    <th>Upload Date</th>
                    <th>Status</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (empty($userDocuments)): ?>
                    <tr>
                      <td colspan="6" class="text-center text-muted">No documents found</td>
                    </tr>
                  <?php else: ?>
                    <?php foreach ($userDocuments as $doc): ?>
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
                      <tr>
                        <td><?php echo htmlspecialchars($doc['Title']); ?></td>
                        <td><span class="badge badge-info"><?php echo strtoupper($doc['FileType']); ?></span></td>
                        <td><?php echo $doc['FileSize']; ?></td>
                        <td><?php echo date('M d, Y', strtotime($doc['UploadDate'])); ?></td>
                        <td><span class="badge <?php echo $statusClass; ?>"><?php echo $doc['Status']; ?></span></td>
                        <td>
                          <a href="view-document.php?id=<?php echo $doc['DocumentID']; ?>" class="btn btn-primary btn-sm" title="View Document">
                            <i class="ti-eye"></i>
                          </a>
                          <?php if ($doc['Status'] == 'Active'): ?>
                            <a href="../public/download.php?id=<?php echo $doc['DocumentID']; ?>" class="btn btn-success btn-sm" title="Download">
                              <i class="ti-download"></i>
                            </a>
                          <?php elseif ($doc['Status'] == 'Deleted' || $doc['Status'] == 'Flagged'): ?>
                            <a href="process-unflag-document.php?id=<?php echo $doc['DocumentID']; ?>" class="btn btn-warning btn-sm" title="Restore">
                              <i class="ti-reload"></i>
                            </a>
                          <?php endif; ?>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
            
            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
            <div class="mt-4 d-flex justify-content-between align-items-center">
              <div>
                Showing <?php echo $offset + 1; ?> to <?php echo min($offset + $limit, $totalDocs); ?> of <?php echo $totalDocs; ?> documents
              </div>
              <ul class="pagination">
                <?php if ($page > 1): ?>
                <li class="page-item">
                  <a class="page-link" href="?id=<?php echo $user_id; ?>&page=<?php echo $page - 1; ?>&limit=<?php echo $limit; ?><?php echo isset($_GET['type']) ? '&type=' . $_GET['type'] : ''; ?><?php echo isset($_GET['status']) ? '&status=' . $_GET['status'] : ''; ?>">Previous</a>
                </li>
                <?php else: ?>
                <li class="page-item disabled">
                  <a class="page-link" href="#">Previous</a>
                </li>
                <?php endif; ?>
                
                <?php
                // Display pagination links
                $startPage = max(1, $page - 2);
                $endPage = min($totalPages, $page + 2);
                
                if ($startPage > 1) {
                    echo '<li class="page-item"><a class="page-link" href="?id=' . $user_id . '&page=1&limit=' . $limit . (isset($_GET['type']) ? '&type=' . $_GET['type'] : '') . (isset($_GET['status']) ? '&status=' . $_GET['status'] : '') . '">1</a></li>';
                    if ($startPage > 2) {
                        echo '<li class="page-item disabled"><a class="page-link" href="#">...</a></li>';
                    }
                }
                
                for ($i = $startPage; $i <= $endPage; $i++) {
                    $activeClass = $i == $page ? 'active' : '';
                    echo '<li class="page-item ' . $activeClass . '"><a class="page-link" href="?id=' . $user_id . '&page=' . $i . '&limit=' . $limit . (isset($_GET['type']) ? '&type=' . $_GET['type'] : '') . (isset($_GET['status']) ? '&status=' . $_GET['status'] : '') . '">' . $i . '</a></li>';
                }
                
                if ($endPage < $totalPages) {
                    if ($endPage < $totalPages - 1) {
                        echo '<li class="page-item disabled"><a class="page-link" href="#">...</a></li>';
                    }
                    echo '<li class="page-item"><a class="page-link" href="?id=' . $user_id . '&page=' . $totalPages . '&limit=' . $limit . (isset($_GET['type']) ? '&type=' . $_GET['type'] : '') . (isset($_GET['status']) ? '&status=' . $_GET['status'] : '') . '">' . $totalPages . '</a></li>';
                }
                ?>
                
                <?php if ($page < $totalPages): ?>
                <li class="page-item">
                  <a class="page-link" href="?id=<?php echo $user_id; ?>&page=<?php echo $page + 1; ?>&limit=<?php echo $limit; ?><?php echo isset($_GET['type']) ? '&type=' . $_GET['type'] : ''; ?><?php echo isset($_GET['status']) ? '&status=' . $_GET['status'] : ''; ?>">Next</a>
                </li>
                <?php else: ?>
                <li class="page-item disabled">
                  <a class="page-link" href="#">Next</a>
                </li>
                <?php endif; ?>
              </ul>
            </div>
            <?php endif; ?>
            
            <?php if (count($userDocuments) > 0): ?>
              <div class="text-center mt-4">
                <a href="manage-documents.php?user_id=<?php echo $user_id; ?>" class="btn btn-primary">Manage All Documents</a>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Delete User Confirmation Modal -->
  <div class="modal fade" id="deleteUserModal" tabindex="-1" role="dialog" aria-labelledby="deleteUserModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="deleteUserModalLabel">Delete User</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <p>Are you sure you want to delete the user <strong id="delete-user-name"></strong>?</p>
          <p class="text-danger">This action cannot be undone!</p>
          
          <div class="form-group mt-4">
            <label for="document-action">What would you like to do with this user's documents?</label>
            <select class="form-control" id="document-action" name="document-action">
              <option value="reassign">Reassign to admin (recommended)</option>
              <option value="orphan">Remove user association</option>
              <option value="delete">Delete all documents</option>
            </select>
            <small class="form-text text-muted">
              <strong>Reassign:</strong> Transfer ownership to admin account<br>
              <strong>Remove association:</strong> Keep documents but remove ownership<br>
              <strong>Delete:</strong> Permanently delete all user documents
            </small>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-danger" id="confirm-delete">Delete User</button>
        </div>
      </div>
    </div>
  </div>

<script>
$(document).ready(function() {
    console.log('DOM content loaded - setting up view user page event handlers');
    
    // Check jQuery version to ensure it's loaded
    console.log('jQuery version: ' + $.fn.jquery);
    
    // Apply filters when changed
    $('#document-type-filter, #document-status-filter').on('change', function() {
        const type = $('#document-type-filter').val();
        const status = $('#document-status-filter').val();
        
        // Construct URL with filters
        let url = 'view-user.php?id=<?php echo $user_id; ?>';
        if (type) url += '&type=' + type;
        if (status) url += '&status=' + status;
        
        // Redirect to filtered view
        window.location.href = url;
    });
    
    // Delete user button click
    $('.delete-user-btn').on('click', function() {
        console.log('Delete user button clicked');
        const userId = $(this).data('id');
        const userName = $(this).data('name');
        
        console.log('User ID to delete: ' + userId);
        console.log('User name: ' + userName);
        
        // Set values in the modal
        $('#delete-user-name').text(userName);
        
        // Reset dropdown to default option
        $('#document-action').val('reassign');
        
        // Show the modal
        $('#deleteUserModal').modal('show');
    });
    
    // Delete confirmation
    $('#confirm-delete').on('click', function() {
        // Get user ID from the delete button
        const userId = $('.delete-user-btn').data('id');
        const documentAction = $('#document-action').val();
        
        if (!userId) {
            alert('Error: Could not determine which user to delete.');
            return;
        }
        
        // Validate document action
        if (!documentAction || !['reassign', 'orphan', 'delete'].includes(documentAction)) {
            alert('Please select a valid action for the user\'s documents.');
            return;
        }
        
        console.log("Deleting user ID: " + userId + " with action: " + documentAction);
        
        // Show loading state
        $(this).prop('disabled', true).html('<i class="ti-reload fa-spin"></i> Deleting...');
        
        $.ajax({
            url: 'process-delete-user.php',
            type: 'POST',
            data: { 
                userId: userId,
                documentAction: documentAction
            },
            success: function(response) {
                console.log("Response received: " + response);
                
                if (response.trim() === 'success') {
                    // Redirect to user management page with success message
                    window.location.href = 'manage-users.php?success=User+deleted+successfully';
                } else {
                    // Show error with details
                    $('#deleteUserModal').modal('hide');
                    $('<div class="alert alert-danger alert-dismissible fade show" role="alert">' +
                        'Error deleting user: ' + response +
                        '<button type="button" class="close" data-dismiss="alert" aria-label="Close">' +
                        '<span aria-hidden="true">&times;</span></button></div>')
                        .insertAfter('.grid-margin');
                }
            },
            error: function(xhr, status, error) {
                console.error("AJAX Error Details:");
                console.error("Status: " + status);
                console.error("Error: " + error);
                console.error("Response Text: " + xhr.responseText);
                
                // Show detailed error message
                $('#deleteUserModal').modal('hide');
                $('<div class="alert alert-danger alert-dismissible fade show" role="alert">' +
                    'Error: ' + status + ' - ' + error + '<br>' +
                    'Details: ' + xhr.responseText +
                    '<button type="button" class="close" data-dismiss="alert" aria-label="Close">' +
                    '<span aria-hidden="true">&times;</span></button></div>')
                    .insertAfter('.grid-margin');
            },
            complete: function() {
                // Reset button state
                $('#confirm-delete').prop('disabled', false).html('Delete User');
            }
        });
    });
});
</script>

<?php
include 'include/footer.php';
?>