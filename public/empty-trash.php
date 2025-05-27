<?php
require_once 'include/session.php';

// Require login
requireLogin();

// Empty trash for the current user
if (emptyTrash($conn, $_SESSION['user_id'])) {
    $_SESSION['success'] = "Trash emptied successfully.";
} else {
    $_SESSION['error'] = "Failed to empty trash.";
}

// Redirect back to trash page
header("Location: trash.php");
exit;
?>