<?php
require_once '../public/include/session.php';

// Require admin access
requireAdmin();

// Check if user ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error'] = "No user specified.";
    header("Location: manage-users.php");
    exit;
}

$userId = (int)$_GET['id'];

// Prevent deleting yourself
if ($userId == $_SESSION['user_id']) {
    $_SESSION['error'] = "You cannot delete your own account.";
    header("Location: manage-users.php");
    exit;
}

// Get user information
$user = getUserById($conn, $userId);

// Check if user exists
if (!$user) {
    $_SESSION['error'] = "User not found.";
    header("Location: manage-users.php");
    exit;
}

// Delete user's documents first (to avoid foreign key constraint issues)
$sql = "SELECT DocumentID FROM document WHERE UserID = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $userId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

while ($row = mysqli_fetch_assoc($result)) {
    $documentId = $row['DocumentID'];
    
    // Delete access logs for each document
    $sql_delete_logs = "DELETE FROM fileaccesslog WHERE DocumentID = ?";
    $stmt_logs = mysqli_prepare($conn, $sql_delete_logs);
    mysqli_stmt_bind_param($stmt_logs, "i", $documentId);
    mysqli_stmt_execute($stmt_logs);
    
    // Get file path to delete actual file
    $sql_get_path = "SELECT FileLocation FROM document WHERE DocumentID = ?";
    $stmt_path = mysqli_prepare($conn, $sql_get_path);
    mysqli_stmt_bind_param($stmt_path, "i", $documentId);
    mysqli_stmt_execute($stmt_path);
    $path_result = mysqli_stmt_get_result($stmt_path);
    
    if ($path_row = mysqli_fetch_assoc($path_result)) {
        $filePath = $path_row['FileLocation'];
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }
}

// Delete user's documents
$sql_delete_docs = "DELETE FROM document WHERE UserID = ?";
$stmt_docs = mysqli_prepare($conn, $sql_delete_docs);
mysqli_stmt_bind_param($stmt_docs, "i", $userId);
mysqli_stmt_execute($stmt_docs);

// Delete user's access logs
$sql_delete_user_logs = "DELETE FROM fileaccesslog WHERE UserID = ?";
$stmt_user_logs = mysqli_prepare($conn, $sql_delete_user_logs);
mysqli_stmt_bind_param($stmt_user_logs, "i", $userId);
mysqli_stmt_execute($stmt_user_logs);

// Delete user
$sql_delete_user = "DELETE FROM user WHERE UserID = ?";
$stmt_user = mysqli_prepare($conn, $sql_delete_user);
mysqli_stmt_bind_param($stmt_user, "i", $userId);

if (mysqli_stmt_execute($stmt_user)) {
    $_SESSION['success'] = "User deleted successfully.";
} else {
    $_SESSION['error'] = "Failed to delete user.";
}

// Redirect back to manage users page
header("Location: manage-users.php");
exit;
?>