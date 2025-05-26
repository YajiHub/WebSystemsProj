<?php
// File: public/delete.php
// Include session management
require_once 'include/session.php';

// Require login
requireLogin();

// Check if document ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error'] = "No document specified.";
    header("Location: my-documents.php");
    exit;
}

$documentId = (int)$_GET['id'];

// Get document information
$document = getDocumentById($conn, $documentId);

// Check if document exists
if (!$document) {
    $_SESSION['error'] = "Document not found.";
    header("Location: my-documents.php");
    exit;
}

// Check if user has permission to delete the document
if ($_SESSION['user_id'] != $document['UserID'] && $_SESSION['user_role'] != 'admin') {
    $_SESSION['error'] = "You do not have permission to delete this document.";
    header("Location: my-documents.php");
    exit;
}

// Move the document to trash (soft delete)
if (flagDocument($conn, $documentId, "Deleted by user")) {
    // Log the delete action
    $deleteAccessTypeId = 4; // Assuming 4 is the ID for 'Delete' in accesstype table
    logFileAccess($conn, $_SESSION['user_id'], $documentId, $deleteAccessTypeId);
    
    $_SESSION['success'] = "Document moved to trash.";
} else {
    $_SESSION['error'] = "Failed to delete document.";
}

// Redirect back to documents page
header("Location: my-documents.php");
exit;
?>