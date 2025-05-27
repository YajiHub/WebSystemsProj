<?php
require_once '../public/include/session.php';

// Require admin login
requireAdmin();

// Empty trash for the current admin
if (emptyTrash($conn, $_SESSION['user_id'])) {
    $_SESSION['success'] = "Trash emptied successfully.";
} else {
    $_SESSION['error'] = "Failed to empty trash.";
}

// Redirect back to trash page
header("Location: admin-trash.php");
exit;
?>