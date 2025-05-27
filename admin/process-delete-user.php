<?php
require_once '../public/include/session.php';
requireAdmin();

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Set content type for AJAX response
header('Content-Type: text/plain');

// Log request information to help debug issues
error_log("Starting user deletion process. POST data: " . print_r($_POST, true));

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $userId = isset($_POST['userId']) ? intval($_POST['userId']) : 0;
    $documentAction = isset($_POST['documentAction']) ? $_POST['documentAction'] : 'reassign';
    
    error_log("Processing delete for user ID: $userId with action: $documentAction");
    
    if ($userId <= 0) {
        echo "Invalid user ID";
        error_log("Delete failed: Invalid user ID ($userId)");
        exit;
    }
    
    // Check if user exists and is not the current admin
    if ($userId == $_SESSION['user_id']) {
        echo "Cannot delete your own account";
        error_log("Delete failed: Attempted to delete own account");
        exit;
    }
    
    // Check if user exists
    $checkSql = "SELECT UserID, Username, UserRole FROM user WHERE UserID = ?";
    $checkStmt = mysqli_prepare($conn, $checkSql);
    if (!$checkStmt) {
        echo "Database error: " . mysqli_error($conn);
        error_log("Prepare failed: " . mysqli_error($conn));
        exit;
    }
    
    mysqli_stmt_bind_param($checkStmt, "i", $userId);
    if (!mysqli_stmt_execute($checkStmt)) {
        echo "Database error: " . mysqli_stmt_error($checkStmt);
        error_log("Execute failed: " . mysqli_stmt_error($checkStmt));
        mysqli_stmt_close($checkStmt);
        exit;
    }
    
    $checkResult = mysqli_stmt_get_result($checkStmt);
    
    if (mysqli_num_rows($checkResult) == 0) {
        echo "User not found";
        error_log("Delete failed: User not found (ID: $userId)");
        mysqli_stmt_close($checkStmt);
        exit;
    }
    
    $userData = mysqli_fetch_assoc($checkResult);
    mysqli_stmt_close($checkStmt);
    
    // Get all documents owned by this user
    $docSql = "SELECT DocumentID, Title, FileLocation FROM document WHERE UserID = ?";
    $docStmt = mysqli_prepare($conn, $docSql);
    if (!$docStmt) {
        echo "Database error: " . mysqli_error($conn);
        error_log("Prepare failed for document query: " . mysqli_error($conn));
        exit;
    }
    
    mysqli_stmt_bind_param($docStmt, "i", $userId);
    if (!mysqli_stmt_execute($docStmt)) {
        echo "Database error: " . mysqli_stmt_error($docStmt);
        error_log("Execute failed for document query: " . mysqli_stmt_error($docStmt));
        mysqli_stmt_close($docStmt);
        exit;
    }
    
    $docResult = mysqli_stmt_get_result($docStmt);
    
    $userDocuments = [];
    while ($row = mysqli_fetch_assoc($docResult)) {
        $userDocuments[] = $row;
    }
    mysqli_stmt_close($docStmt);
    
    error_log("Found " . count($userDocuments) . " documents for user $userId");
    
    // Start transaction
    if (!mysqli_begin_transaction($conn)) {
        echo "Failed to start transaction: " . mysqli_error($conn);
        error_log("Failed to start transaction: " . mysqli_error($conn));
        exit;
    }
    
    try {
        // Handle user's documents based on the selected action
        switch ($documentAction) {
            case 'reassign':
                // Reassign documents to admin (current user)
                $adminId = $_SESSION['user_id'];
                $reassignSql = "UPDATE document SET UserID = ? WHERE UserID = ?";
                $reassignStmt = mysqli_prepare($conn, $reassignSql);
                if (!$reassignStmt) {
                    throw new Exception("Failed to prepare reassign statement: " . mysqli_error($conn));
                }
                
                mysqli_stmt_bind_param($reassignStmt, "ii", $adminId, $userId);
                if (!mysqli_stmt_execute($reassignStmt)) {
                    throw new Exception("Failed to reassign documents: " . mysqli_stmt_error($reassignStmt));
                }
                
                mysqli_stmt_close($reassignStmt);
                error_log("Reassigned " . count($userDocuments) . " documents from user $userId to admin $adminId");
                break;
                
            case 'orphan':
                // Set documents to have no owner (NULL UserID)
                // First check if NULL is allowed for UserID in document table
                $updateDocsSql = "UPDATE document SET UserID = NULL WHERE UserID = ?";
                $updateDocsStmt = mysqli_prepare($conn, $updateDocsSql);
                if (!$updateDocsStmt) {
                    throw new Exception("Failed to prepare orphan statement: " . mysqli_error($conn));
                }
                
                mysqli_stmt_bind_param($updateDocsStmt, "i", $userId);
                if (!mysqli_stmt_execute($updateDocsStmt)) {
                    // If NULL is not allowed, try setting it to 0 instead
                    mysqli_stmt_close($updateDocsStmt);
                    $zeroId = 0;
                    $updateDocsSql = "UPDATE document SET UserID = ? WHERE UserID = ?";
                    $updateDocsStmt = mysqli_prepare($conn, $updateDocsSql);
                    if (!$updateDocsStmt) {
                        throw new Exception("Failed to prepare orphan statement (alt): " . mysqli_error($conn));
                    }
                    
                    mysqli_stmt_bind_param($updateDocsStmt, "ii", $zeroId, $userId);
                    if (!mysqli_stmt_execute($updateDocsStmt)) {
                        throw new Exception("Failed to orphan documents: " . mysqli_stmt_error($updateDocsStmt));
                    }
                }
                
                mysqli_stmt_close($updateDocsStmt);
                error_log("Orphaned " . count($userDocuments) . " documents from user $userId");
                break;
                
            case 'delete':
                // Delete all the user's documents
                foreach ($userDocuments as $doc) {
                    // First delete related fileaccesslog entries
                    $deleteLogsSql = "DELETE FROM fileaccesslog WHERE DocumentID = ?";
                    $deleteLogsStmt = mysqli_prepare($conn, $deleteLogsSql);
                    if (!$deleteLogsStmt) {
                        throw new Exception("Failed to prepare delete logs statement: " . mysqli_error($conn));
                    }
                    
                    mysqli_stmt_bind_param($deleteLogsStmt, "i", $doc['DocumentID']);
                    if (!mysqli_stmt_execute($deleteLogsStmt)) {
                        throw new Exception("Failed to delete document logs: " . mysqli_stmt_error($deleteLogsStmt));
                    }
                    mysqli_stmt_close($deleteLogsStmt);
                    
                    // Delete the document record
                    $deleteDocSql = "DELETE FROM document WHERE DocumentID = ?";
                    $deleteDocStmt = mysqli_prepare($conn, $deleteDocSql);
                    if (!$deleteDocStmt) {
                        throw new Exception("Failed to prepare delete document statement: " . mysqli_error($conn));
                    }
                    
                    mysqli_stmt_bind_param($deleteDocStmt, "i", $doc['DocumentID']);
                    if (!mysqli_stmt_execute($deleteDocStmt)) {
                        throw new Exception("Failed to delete document: " . mysqli_stmt_error($deleteDocStmt));
                    }
                    mysqli_stmt_close($deleteDocStmt);
                    
                    // Delete physical file if it exists
                    if (file_exists($doc['FileLocation'])) {
                        if (!unlink($doc['FileLocation'])) {
                            error_log("Warning: Failed to delete physical file: " . $doc['FileLocation']);
                        }
                    }
                }
                
                error_log("Deleted " . count($userDocuments) . " documents from user $userId");
                break;
                
            default:
                throw new Exception("Invalid document action: $documentAction");
        }
        
        // Now delete the user's access logs
        $deleteAccessLogsSql = "DELETE FROM fileaccesslog WHERE UserID = ?";
        $deleteAccessLogsStmt = mysqli_prepare($conn, $deleteAccessLogsSql);
        if (!$deleteAccessLogsStmt) {
            throw new Exception("Failed to prepare delete access logs statement: " . mysqli_error($conn));
        }
        
        mysqli_stmt_bind_param($deleteAccessLogsStmt, "i", $userId);
        if (!mysqli_stmt_execute($deleteAccessLogsStmt)) {
            throw new Exception("Failed to delete user access logs: " . mysqli_stmt_error($deleteAccessLogsStmt));
        }
        mysqli_stmt_close($deleteAccessLogsStmt);
        
        // Finally, delete the user
        $deleteSql = "DELETE FROM user WHERE UserID = ?";
        $deleteStmt = mysqli_prepare($conn, $deleteSql);
        if (!$deleteStmt) {
            throw new Exception("Failed to prepare delete user statement: " . mysqli_error($conn));
        }
        
        mysqli_stmt_bind_param($deleteStmt, "i", $userId);
        if (!mysqli_stmt_execute($deleteStmt)) {
            throw new Exception("Failed to delete user: " . mysqli_stmt_error($deleteStmt));
        }
        
        $affectedRows = mysqli_stmt_affected_rows($deleteStmt);
        mysqli_stmt_close($deleteStmt);
        
        if ($affectedRows > 0) {
            mysqli_commit($conn);
            echo "success";
            error_log("Successfully deleted user $userId (" . $userData['Username'] . ")");
        } else {
            throw new Exception("No rows affected when deleting user");
        }
        
    } catch (Exception $e) {
        mysqli_rollback($conn);
        echo "Error: " . $e->getMessage();
        error_log("Exception while deleting user $userId: " . $e->getMessage());
    }
} else {
    echo "Invalid request method";
    error_log("Delete failed: Invalid request method (expected POST, got " . $_SERVER['REQUEST_METHOD'] . ")");
}
?>