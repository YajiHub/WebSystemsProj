<?php
require_once '../public/include/session.php';

// Require admin login
requireAdmin();

// Check if document ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error'] = "No document specified.";
    header("Location: admin-trash.php");
    exit;
}

$documentId = (int)$_GET['id'];

// Get document information
$document = getDocumentById($conn, $documentId);

// Check if document exists
if (!$document) {
    $_SESSION['error'] = "Document not found.";
    header("Location: admin-trash.php");
    exit;
}

// Check if admin is the owner of the document
if ($_SESSION['user_id'] != $document['UserID']) {
    $_SESSION['error'] = "You can only restore your own documents.";
    header("Location: admin-trash.php");
    exit;
}

// Check if document is actually in trash
if ($document['IsDeleted'] != 1) {
    $_SESSION['error'] = "This document is not in trash.";
    header("Location: admin-trash.php");
    exit;
}

// Restore the document from trash
if (restoreDocument($conn, $documentId)) {
    $_SESSION['success'] = "Document restored successfully.";
} else {
    $_SESSION['error'] = "Failed to restore document.";
}

// Redirect back to trash page
header("Location: admin-trash.php");
exit;
?>