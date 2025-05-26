<?php
// File: public/permanent-delete.php
// Include session management
require_once 'include/session.php';

// Require login
requireLogin();

// Check if document ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error'] = "No document specified.";
    header("Location: trash.php");
    exit;
}

$documentId = (int)$_GET['id'];

// Get document information
$document = getDocumentById($conn, $documentId);

// Check if document exists
if (!$document) {
    $_SESSION['error'] = "Document not found.";
    header("Location: trash.php");
    exit;
}

// Check if user has permission to delete the document
if ($_SESSION['user_id'] != $document['UserID'] && $_SESSION['user_role'] != 'admin') {
    $_SESSION['error'] = "You do not have permission to delete this document.";
    header("Location: trash.php");
    exit;
}

// Check if document is in trash
if ($document['IsDeleted'] != 1) {
    $_SESSION['error'] = "You must move a document to trash before permanently deleting it.";
    header("Location: trash.php");
    exit;
}

// Permanently delete the document
if (permanentlyDeleteDocument($conn, $documentId)) {
    $_SESSION['success'] = "Document permanently deleted.";
} else {
    $_SESSION['error'] = "Failed to delete document.";
}

// Redirect back to trash page
header("Location: trash.php");
exit;
?>