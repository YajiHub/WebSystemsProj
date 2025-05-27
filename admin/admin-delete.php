<?php
// File: admin/admin-delete.php
// Include session management
require_once '../public/include/session.php';

// Require admin login
requireAdmin();

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

// Check if admin has permission to delete the document (their own document)
if ($_SESSION['user_id'] != $document['UserID']) {
    $_SESSION['error'] = "You can only delete your own documents.";
    header("Location: my-documents.php");
    exit;
}

// Move the document to trash (soft delete)
// Use "Deleted by user" to ensure it shows up in the admin's personal trash
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