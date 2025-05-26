<?php
// File: admin/process-delete-user.php
require_once '../public/include/session.php';
requireAdmin();

// Set content type for AJAX response
header('Content-Type: text/plain');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $userId = intval($_POST['userId']);
    $documentAction = isset($_POST['documentAction']) ? $_POST['documentAction'] : 'reassign';
    
    if ($userId <= 0) {
        echo "Invalid user ID";
        exit;
    }
    
    // Check if user exists and is not the current admin
    if ($userId == $_SESSION['user_id']) {
        echo "Cannot delete your own account";
        exit;
    }
    
    // Check if user exists
    $checkSql = "SELECT UserID, Username, UserRole FROM user WHERE UserID = ?";
    $checkStmt = mysqli_prepare($conn, $checkSql);
    mysqli_stmt_bind_param($checkStmt, "i", $userId);
    mysqli_stmt_execute($checkStmt);
    $checkResult = mysqli_stmt_get_result($checkStmt);
    
    if (mysqli_num_rows($checkResult) == 0) {
        echo "User not found";
        exit;
    }
    
    $userData = mysqli_fetch_assoc($checkResult);
    
    // Log deletion attempt for audit purposes
    error_log("Admin user " . $_SESSION['user_id'] . " is deleting user " . $userId . " with document action: " . $documentAction);
    
    mysqli_stmt_close($checkStmt);
    
    // Get all documents owned by this user
    $docSql = "SELECT DocumentID, Title, FileLocation FROM document WHERE UserID = ?";
    $docStmt = mysqli_prepare($conn, $docSql);
    mysqli_stmt_bind_param($docStmt, "i", $userId);
    mysqli_stmt_execute($docStmt);
    $docResult = mysqli_stmt_get_result($docStmt);
    
    $userDocuments = [];
    while ($row = mysqli_fetch_assoc($docResult)) {
        $userDocuments[] = $row;
    }
    mysqli_stmt_close($docStmt);
    
    // Start transaction
    mysqli_begin_transaction($conn);
    
    try {
        // Handle user's documents based on the selected action
        switch ($documentAction) {
            case 'reassign':
                // Reassign documents to admin (current user)
                $adminId = $_SESSION['user_id'];
                $reassignSql = "UPDATE document SET UserID = ? WHERE UserID = ?";
                $reassignStmt = mysqli_prepare($conn, $reassignSql);
                mysqli_stmt_bind_param($reassignStmt, "ii", $adminId, $userId);
                mysqli_stmt_execute($reassignStmt);
                mysqli_stmt_close($reassignStmt);
                
                // Log document reassignment
                error_log("Reassigned " . count($userDocuments) . " documents from user " . $userId . " to admin " . $adminId);
                break;
                
            case 'orphan':
                // Set documents to have no owner (NULL UserID)
                $updateDocsSql = "UPDATE document SET UserID = NULL WHERE UserID = ?";
                $updateDocsStmt = mysqli_prepare($conn, $updateDocsSql);
                mysqli_stmt_bind_param($updateDocsStmt, "i", $userId);
                mysqli_stmt_execute($updateDocsStmt);
                mysqli_stmt_close($updateDocsStmt);
                
                // Log document orphaning
                error_log("Removed owner association from " . count($userDocuments) . " documents previously owned by user " . $userId);
                break;
                
            case 'delete':
                // Delete all the user's documents
                foreach ($userDocuments as $doc) {
                    // Delete physical file if it exists
                    if (file_exists($doc['FileLocation'])) {
                        unlink($doc['FileLocation']);
                    }
                    
                    // Delete access logs for this document
                    $deleteLogsSql = "DELETE FROM fileaccesslog WHERE DocumentID = ?";
                    $deleteLogsStmt = mysqli_prepare($conn, $deleteLogsSql);
                    mysqli_stmt_bind_param($deleteLogsStmt, "i", $doc['DocumentID']);
                    mysqli_stmt_execute($deleteLogsStmt);
                    mysqli_stmt_close($deleteLogsStmt);
                    
                    // Delete the document record
                    $deleteDocSql = "DELETE FROM document WHERE DocumentID = ?";
                    $deleteDocStmt = mysqli_prepare($conn, $deleteDocSql);
                    mysqli_stmt_bind_param($deleteDocStmt, "i", $doc['DocumentID']);
                    mysqli_stmt_execute($deleteDocStmt);
                    mysqli_stmt_close($deleteDocStmt);
                }
                
                // Log document deletion
                error_log("Permanently deleted " . count($userDocuments) . " documents owned by user " . $userId);
                break;
                
            default:
                // Default to reassign if invalid action
                $adminId = $_SESSION['user_id'];
                $reassignSql = "UPDATE document SET UserID = ? WHERE UserID = ?";
                $reassignStmt = mysqli_prepare($conn, $reassignSql);
                mysqli_stmt_bind_param($reassignStmt, "ii", $adminId, $userId);
                mysqli_stmt_execute($reassignStmt);
                mysqli_stmt_close($reassignStmt);
                break;
        }
        
        // Now delete the user's access logs
        $deleteAccessLogsSql = "DELETE FROM fileaccesslog WHERE UserID = ?";
        $deleteAccessLogsStmt = mysqli_prepare($conn, $deleteAccessLogsSql);
        mysqli_stmt_bind_param($deleteAccessLogsStmt, "i", $userId);
        mysqli_stmt_execute($deleteAccessLogsStmt);
        mysqli_stmt_close($deleteAccessLogsStmt);
        
        // Finally, delete the user
        $deleteSql = "DELETE FROM user WHERE UserID = ?";
        $deleteStmt = mysqli_prepare($conn, $deleteSql);
        mysqli_stmt_bind_param($deleteStmt, "i", $userId);
        
        if (mysqli_stmt_execute($deleteStmt)) {
            $affectedRows = mysqli_stmt_affected_rows($deleteStmt);
            if ($affectedRows > 0) {
                mysqli_commit($conn);
                echo "success";
                
                // Log successful deletion
                error_log("Successfully deleted user " . $userId . " (" . $userData['Username'] . ")");
            } else {
                mysqli_rollback($conn);
                echo "No user was deleted";
                
                // Log failed deletion
                error_log("Failed to delete user " . $userId . " - No rows affected");
            }
        } else {
            mysqli_rollback($conn);
            echo "Error deleting user: " . mysqli_error($conn);
            
            // Log error
            error_log("Error deleting user " . $userId . ": " . mysqli_error($conn));
        }
        
        mysqli_stmt_close($deleteStmt);
        
    } catch (Exception $e) {
        mysqli_rollback($conn);
        echo "Error: " . $e->getMessage();
        
        // Log exception
        error_log("Exception while deleting user " . $userId . ": " . $e->getMessage());
    }
} else {
    echo "Invalid request method";
}
?>