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

// Get user's documents
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
    FileLocation
FROM document 
WHERE UserID = ? 
ORDER BY UploadDate DESC 
LIMIT 10";

$docStmt = mysqli_prepare($conn, $docSql);
mysqli_stmt_bind_param($docStmt, "i", $user_id);
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
        </div>
      </div>
    </div>
    
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
                <p class="text-muted font-weight-bold"><?php echo htmlspecialchars($user['Department'] ?? 'No Department'); ?></p>
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
                    <span class="font-weight-bold">Department:</span>
                    <span><?php echo htmlspecialchars($user['Department'] ?? 'Not Set'); ?></span>
                  </div>
                  <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                    <span class="font-weight-bold">Extension:</span>
                    <span><?php echo htmlspecialchars($user['Extension'] ?? 'Not Set'); ?></span>
                  </div>
                  <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                    <span class="font-weight-bold">Total Documents:</span>
                    <span class="badge badge-info"><?php echo $userStats['DocumentCount']; ?></span>
                  </div>
                  <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                    <span class="font-weight-bold">Active Documents:</span>
                    <span class="badge badge-success"><?php echo $userStats['ActiveDocuments']; ?></span>
                  </div>
                  <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                    <span class="font-weight-bold">Flagged Documents:</span>
                    <span class="badge badge-warning"><?php echo $userStats['FlaggedDocuments']; ?></span>
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
            <h4 class="card-title">User Documents (<?php echo count($userDocuments); ?> recent)</h4>
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
                  <?php if (!empty($userDocuments)): ?>
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
                          <?php endif; ?>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  <?php else: ?>
                    <tr>
                      <td colspan="6" class="text-center text-muted">No documents found</td>
                    </tr>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
            <?php if (count($userDocuments) >= 10): ?>
              <div class="text-center mt-4">
                <a href="manage-documents.php?user_id=<?php echo $user_id; ?>" class="btn btn-primary">View All Documents</a>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </div>

<?php
include 'include/footer.php';
?>
