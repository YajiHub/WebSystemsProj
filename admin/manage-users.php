<?php
// File: admin/manage-users.php
require_once '../public/include/session.php';

// Require admin login
requireAdmin();

// Get all users with access level information
$sql = "SELECT 
    u.UserID,
    u.FirstName,
    u.LastName,
    u.EmailAddress,
    u.UserRole,
    u.Status,
    u.Department,
    u.Extension,
    a.LevelName,
    a.AccessLevelID
FROM user u
LEFT JOIN accesslevel a ON u.AccessLevel = a.AccessLevelID
ORDER BY u.UserID DESC";

$result = mysqli_query($conn, $sql);
$users = [];
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $users[] = $row;
    }
}

// Get access levels for filtering
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
<style>
.no-results {
    font-style: italic;
}
.btn:disabled {
    opacity: 0.6;
}
</style>

<!-- Debug info (remove in production) -->
<?php if (isset($_GET['debug'])): ?>
<div class="alert alert-info">
    <strong>Debug Info:</strong><br>
    Total users loaded: <?php echo count($users); ?><br>
    Access levels available: <?php echo count($accessLevels); ?><br>
    Current user ID: <?php echo $_SESSION['user_id']; ?>
</div>
<?php endif; ?>
  <div class="content-wrapper">
    <!-- Page Title -->
    <div class="row">
      <div class="col-md-12 grid-margin">
        <div class="row">
          <div class="col-12 col-xl-8 mb-4 mb-xl-0">
            <h3 class="font-weight-bold">User Management</h3>
            <h6 class="font-weight-normal mb-0">Manage all registered users (<?php echo count($users); ?> total)</h6>
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
    
    <!-- Filters -->
    <div class="row mb-4">
      <div class="col-md-12">
        <div class="card">
          <div class="card-body py-3">
            <div class="row">
              <div class="col-md-3">
                <select class="form-control" id="role-filter">
                  <option value="">All Roles</option>
                  <option value="admin">Administrators</option>
                  <option value="user">Regular Users</option>
                </select>
              </div>
              <div class="col-md-3">
                <select class="form-control" id="access-level-filter">
                  <option value="">All Access Levels</option>
                  <?php foreach ($accessLevels as $level): ?>
                    <option value="<?php echo htmlspecialchars($level['LevelName']); ?>"><?php echo htmlspecialchars($level['LevelName']); ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="col-md-6">
                <div class="input-group">
                  <input type="text" class="form-control" id="search-input" placeholder="Search users...">
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
    
    <!-- Users Table -->
    <div class="row">
      <div class="col-md-12 grid-margin stretch-card">
        <div class="card">
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-striped table-hover" id="users-table">
                <thead>
                  <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Access Level</th>
                    <th>Actions</th>
                  </tr>  
                </thead>
                <tbody>
                  <?php if (empty($users)): ?>
                    <tr>
                      <td colspan="6" class="text-center">No users found.</td>
                    </tr>
                  <?php else: ?>
                    <?php foreach ($users as $user): ?>
                      <tr>
                        <td><?php echo $user['UserID']; ?></td>
                        <td><?php echo htmlspecialchars($user['FirstName'] . ' ' . $user['LastName']); ?></td>
                        <td><?php echo htmlspecialchars($user['EmailAddress']); ?></td>
                        <td>
                          <span class="badge <?php echo $user['UserRole'] == 'admin' ? 'badge-danger' : 'badge-primary'; ?>">
                            <?php echo ucfirst($user['UserRole']); ?>
                          </span>
                        </td>
                        <td><?php echo htmlspecialchars($user['LevelName'] ?? 'Not Set'); ?></td>
                        <td>
                          <a href="view-user.php?id=<?php echo $user['UserID']; ?>" class="btn btn-info btn-sm" title="View User">
                            <i class="ti-eye"></i>
                          </a>
                          <?php if ($user['UserID'] != $_SESSION['user_id']): ?>
                            <button type="button" class="btn btn-danger btn-sm delete-user" 
                                    data-id="<?php echo $user['UserID']; ?>" 
                                    title="Delete User">
                              <i class="ti-trash"></i>
                            </button>
                          <?php endif; ?>
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

<script>
$(document).ready(function() {
    // Delete user with improved error handling
    $(document).on('click', '.delete-user', function() {
        var userId = $(this).data('id');
        var userName = $(this).closest('tr').find('td:nth-child(2)').text();
        
        if (confirm('Are you sure you want to delete user "' + userName + '"? This action cannot be undone.')) {
            // Show loading state
            $(this).prop('disabled', true).html('<i class="ti-reload"></i>');
            
            $.ajax({
                url: 'process-delete-user.php',
                type: 'POST',
                data: { userId: userId },
                success: function(response) {
                    console.log('Delete response:', response);
                    if (response.trim() === 'success') {
                        // Remove the row from table
                        $('button[data-id="' + userId + '"]').closest('tr').fadeOut(function() {
                            $(this).remove();
                            // Update user count
                            var currentCount = parseInt($('.font-weight-normal').text().match(/\d+/)[0]);
                            $('.font-weight-normal').text('Manage all registered users (' + (currentCount - 1) + ' total)');
                        });
                    } else {
                        alert('Error deleting user: ' + response);
                        // Reset button
                        $('button[data-id="' + userId + '"]').prop('disabled', false).html('<i class="ti-trash"></i>');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', error);
                    alert('Error deleting user. Please try again.');
                    // Reset button
                    $('button[data-id="' + userId + '"]').prop('disabled', false).html('<i class="ti-trash"></i>');
                }
            });
        }
    });
    
    // Enhanced filter functionality
    $('#role-filter, #access-level-filter').on('change', function() {
        filterTable();
    });
    
    $('#search-btn').on('click', function() {
        filterTable();
    });
    
    $('#search-input').on('keyup', function(e) {
        if (e.keyCode === 13) { // Enter key
            filterTable();
        }
        // Also filter on every keystroke for better UX
        filterTable();
    });
    
    // Clear filters button (optional)
    $('<button class="btn btn-secondary ml-2" id="clear-filters">Clear</button>').insertAfter('#search-btn');
    $('#clear-filters').on('click', function() {
        $('#role-filter').val('');
        $('#access-level-filter').val('');
        $('#search-input').val('');
        filterTable();
    });
    
    function filterTable() {
        var roleFilter = $('#role-filter').val().toLowerCase();
        var accessLevelFilter = $('#access-level-filter').val().toLowerCase();
        var searchText = $('#search-input').val().toLowerCase();
        
        console.log('Filtering with:', { role: roleFilter, accessLevel: accessLevelFilter, search: searchText });
        
        var visibleRows = 0;
        
        $('#users-table tbody tr').each(function() {
            var row = $(this);
            
            // Skip if this is the "no users found" row
            if (row.find('td').length === 1) {
                return;
            }
            
            // Get text content from each column
            var role = row.find('td:nth-child(4)').text().toLowerCase().trim();
            var accessLevel = row.find('td:nth-child(5)').text().toLowerCase().trim();
            var name = row.find('td:nth-child(2)').text().toLowerCase().trim();
            var email = row.find('td:nth-child(3)').text().toLowerCase().trim();
            var userId = row.find('td:nth-child(1)').text().toLowerCase().trim();
            
            // Check matches
            var roleMatch = roleFilter === '' || role.includes(roleFilter);
            var accessLevelMatch = accessLevelFilter === '' || accessLevel.includes(accessLevelFilter);
            var searchMatch = searchText === '' || 
                             name.includes(searchText) || 
                             email.includes(searchText) || 
                             userId.includes(searchText);
            
            if (roleMatch && accessLevelMatch && searchMatch) {
                row.show();
                visibleRows++;
            } else {
                row.hide();
            }
        });
        
        // Show/hide "no results" message
        var noResultsRow = $('#users-table tbody tr.no-results');
        if (visibleRows === 0 && $('#users-table tbody tr').length > 1) {
            if (noResultsRow.length === 0) {
                $('#users-table tbody').append('<tr class="no-results"><td colspan="6" class="text-center text-muted">No users match your search criteria.</td></tr>');
            } else {
                noResultsRow.show();
            }
        } else {
            noResultsRow.hide();
        }
        
        console.log('Visible rows:', visibleRows);
    }
});
</script>

<?php
include 'include/footer.php';
?>
