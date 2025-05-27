<?php
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

// Check if user has permission to restore the document
if ($_SESSION['user_id'] != $document['UserID'] && $_SESSION['user_role'] != 'admin') {
    $_SESSION['error'] = "You do not have permission to restore this document.";
    header("Location: trash.php");
    exit;
}

// Check if document is actually in trash
if ($document['IsDeleted'] != 1) {
    $_SESSION['error'] = "This document is not in trash.";
    header("Location: trash.php");
    exit;
}

// Restore the document from trash
if (restoreDocument($conn, $documentId)) {
    $_SESSION['success'] = "Document restored successfully.";
} else {
    $_SESSION['error'] = "Failed to restore document.";
}

// Redirect back to trash page
header("Location: trash.php");
exit;
?>