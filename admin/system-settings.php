<?php
// File: admin/system-settings.php
require_once '../public/include/session.php';

// Require admin login
requireAdmin();

// Get access levels from database
$accessLevels = [];
$sql = "SELECT * FROM accesslevel ORDER BY AccessLevelID";
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
            <h3 class="font-weight-bold">System Settings</h3>
            <h6 class="font-weight-normal mb-0">Manage access levels and permissions</h6>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Access Levels Section -->
    <div class="row">
      <div class="col-md-12 grid-margin stretch-card">
        <div class="card">
          <div class="card-body">
            <div class="row mb-4">
              <div class="col-md-6">
                <h4>Access Levels (<?php echo count($accessLevels); ?>)</h4>
                <p class="text-muted">Define access levels and their permissions</p>
              </div>
              <div class="col-md-6 text-right">
                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addAccessLevelModal">
                  <i class="ti-plus mr-1"></i> Add New Level
                </button>
              </div>
            </div>
            
            <div class="table-responsive">
              <table class="table table-striped">
                <thead>
                  <tr>
                    <th>Level ID</th>
                    <th>Level Name</th>
                    <th>Users Count</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (!empty($accessLevels)): ?>
                    <?php foreach ($accessLevels as $level): ?>
                      <?php
                      // Get user count for this access level
                      $userCountSql = "SELECT COUNT(*) as count FROM user WHERE AccessLevel = " . $level['AccessLevelID'];
                      $userCountResult = mysqli_query($conn, $userCountSql);
                      $userCount = mysqli_fetch_assoc($userCountResult)['count'];
                      ?>
                      <tr>
                        <td><?php echo $level['AccessLevelID']; ?></td>
                        <td><?php echo htmlspecialchars($level['LevelName']); ?></td>
                        <td>
                          <span class="badge badge-info"><?php echo $userCount; ?> users</span>
                        </td>
                        <td>
                          <button type="button" class="btn btn-primary btn-sm edit-access-level" 
                                  data-id="<?php echo $level['AccessLevelID']; ?>"
                                  data-name="<?php echo htmlspecialchars($level['LevelName']); ?>"
                                  title="Edit Access Level">
                            <i class="ti-pencil"></i>
                          </button>
                          <?php if ($userCount == 0): ?>
                            <button type="button" class="btn btn-danger btn-sm delete-access-level" 
                                    data-id="<?php echo $level['AccessLevelID']; ?>"
                                    title="Delete Access Level">
                              <i class="ti-trash"></i>
                            </button>
                          <?php else: ?>
                            <button type="button" class="btn btn-secondary btn-sm" disabled 
                                    title="Cannot delete level with users">
                              <i class="ti-trash"></i>
                            </button>
                          <?php endif; ?>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  <?php else: ?>
                    <tr>
                      <td colspan="4" class="text-center text-muted">No access levels found</td>
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

  <!-- Add Access Level Modal -->
  <div class="modal fade" id="addAccessLevelModal" tabindex="-1" role="dialog" aria-labelledby="addAccessLevelModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="addAccessLevelModalLabel">Add New Access Level</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <form id="addAccessLevelForm" action="process-add-access-level.php" method="post">
            <div class="form-group">
              <label for="accessLevelName">Level Name</label>
              <input type="text" class="form-control" id="accessLevelName" name="levelName" required>
              <small class="form-text text-muted">Enter a descriptive name for this access level</small>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
              <button type="submit" class="btn btn-primary">Add Access Level</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <!-- Edit Access Level Modal -->
  <div class="modal fade" id="editAccessLevelModal" tabindex="-1" role="dialog" aria-labelledby="editAccessLevelModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="editAccessLevelModalLabel">Edit Access Level</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <form id="editAccessLevelForm" action="process-edit-access-level.php" method="post">
            <input type="hidden" id="editAccessLevelId" name="accessLevelId" value="">
            <div class="form-group">
              <label for="editAccessLevelName">Level Name</label>
              <input type="text" class="form-control" id="editAccessLevelName" name="levelName" required>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
              <button type="submit" class="btn btn-primary">Save Changes</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

<script>
  $(document).ready(function() {
    // Edit access level
    $('.edit-access-level').on('click', function() {
      var levelId = $(this).data('id');
      var levelName = $(this).data('name');
      
      $('#editAccessLevelId').val(levelId);
      $('#editAccessLevelName').val(levelName);
      
      $('#editAccessLevelModal').modal('show');
    });

    // Delete access level
    $('.delete-access-level').on('click', function() {
      if (confirm('Are you sure you want to delete this access level?')) {
        var levelId = $(this).data('id');
        
        $.ajax({
          url: 'process-delete-access-level.php',
          type: 'POST',
          data: { accessLevelId: levelId },
          success: function(response) {
            if (response.trim() === 'success') {
              location.reload();
            } else {
              alert('Error deleting access level: ' + response);
            }
          },
          error: function() {
            alert('Error deleting access level. Please try again.');
          }
        });
      }
    });
  });
</script>

<?php
include 'include/footer.php';
?>