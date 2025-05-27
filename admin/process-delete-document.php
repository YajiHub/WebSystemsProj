<?php
require_once '../public/include/session.php';

// Require admin
requireAdmin();

// Check if form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $documentId = $_POST['documentId'] ?? '';
    $deleteReason = $_POST['deleteReason'] ?? '';
    
    // Validate form data
    if (empty($documentId) || empty($deleteReason)) {
        $_SESSION['error'] = "Please provide a reason for deleting the document.";
        header("Location: manage-documents.php");
        exit;
    }

    // Get document information
    $document = getDocumentById($conn, $documentId);
    
    // Check if document exists
    if (!$document) {
        $_SESSION['error'] = "Document not found.";
        header("Location: manage-documents.php");
        exit;
    }
    
    // Check if document is already flagged/deleted
    if ($document['IsDeleted'] == 1) {
        // Document is already in trash, perform permanent deletion
        if (permanentlyDeleteDocument($conn, $documentId)) {
            // Log the permanent delete action
            $deleteAccessTypeId = 4; // Assuming 4 is the ID for 'Delete' in accesstype table
            logFileAccess($conn, $_SESSION['user_id'], $documentId, $deleteAccessTypeId);
            
            $_SESSION['success'] = "Document permanently deleted.";
        } else {
            $_SESSION['error'] = "Failed to delete document permanently.";
        }
    } else {
        // Soft delete (flag) the document
        if (flagDocument($conn, $documentId, $deleteReason)) {
            // Log the flag action
            $flagAccessTypeId = 5; // Assuming 5 is the ID for 'Flag' in accesstype table
            logFileAccess($conn, $_SESSION['user_id'], $documentId, $flagAccessTypeId);
            
            $_SESSION['success'] = "Document moved to trash.";
        } else {
            $_SESSION['error'] = "Failed to move document to trash.";
        }
    }
    
    // If the delete request came from the view-document page, return there
    if (isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], 'view-document.php') !== false) {
        header("Location: manage-documents.php");
    } else {
        // Otherwise, return to the manage documents page
        header("Location: manage-documents.php");
    }
    exit;
} else {
    // If not a POST request, redirect to documents page
    header("Location: manage-documents.php");
    exit;
}
?>