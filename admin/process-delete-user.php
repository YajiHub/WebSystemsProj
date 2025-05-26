<?php
// File: admin/process-delete-user.php
require_once '../public/include/session.php';
requireAdmin();

// Set content type for AJAX response
header('Content-Type: text/plain');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $userId = intval($_POST['userId']);
    
    if ($userId > 0) {
        // Check if user exists and is not the current admin
        if ($userId == $_SESSION['user_id']) {
            echo "Cannot delete your own account";
            exit;
        }
        
        // Check if user exists
        $checkSql = "SELECT UserID, Username FROM user WHERE UserID = ?";
        $checkStmt = mysqli_prepare($conn, $checkSql);
        mysqli_stmt_bind_param($checkStmt, "i", $userId);
        mysqli_stmt_execute($checkStmt);
        $checkResult = mysqli_stmt_get_result($checkStmt);
        
        if (mysqli_num_rows($checkResult) == 0) {
            echo "User not found";
            exit;
        }
        
        mysqli_stmt_close($checkStmt);
        
        // Start transaction
        mysqli_begin_transaction($conn);
        
        try {
            // First, handle related records
            // Option 1: Delete user's documents (if you want to remove them completely)
            // $deleteDocsSql = "DELETE FROM document WHERE UserID = ?";
            
            // Option 2: Keep documents but remove user reference (recommended)
            $updateDocsSql = "UPDATE document SET UserID = NULL WHERE UserID = ?";
            $updateDocsStmt = mysqli_prepare($conn, $updateDocsSql);
            mysqli_stmt_bind_param($updateDocsStmt, "i", $userId);
            mysqli_stmt_execute($updateDocsStmt);
            mysqli_stmt_close($updateDocsStmt);
            
            // Delete the user
            $deleteSql = "DELETE FROM user WHERE UserID = ?";
            $deleteStmt = mysqli_prepare($conn, $deleteSql);
            mysqli_stmt_bind_param($deleteStmt, "i", $userId);
            
            if (mysqli_stmt_execute($deleteStmt)) {
                $affectedRows = mysqli_stmt_affected_rows($deleteStmt);
                if ($affectedRows > 0) {
                    mysqli_commit($conn);
                    echo "success";
                } else {
                    mysqli_rollback($conn);
                    echo "No user was deleted";
                }
            } else {
                mysqli_rollback($conn);
                echo "Error deleting user: " . mysqli_error($conn);
            }
            
            mysqli_stmt_close($deleteStmt);
            
        } catch (Exception $e) {
            mysqli_rollback($conn);
            echo "Error: " . $e->getMessage();
        }
    } else {
        echo "Invalid user ID";
    }
} else {
    echo "Invalid request method";
}
?>
