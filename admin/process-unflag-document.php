<?php
// File: admin/process-unflag-document.php
// Include session management
require_once '../public/include/session.php';

// Require admin
requireAdmin();

// Check if document ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error'] = "No document specified.";
    header("Location: manage-documents.php");
    exit;
}

$documentId = (int)$_GET['id'];

// Unflag the document
if (unflagDocument($conn, $documentId)) {
    $_SESSION['success'] = "Document unflagged successfully.";
} else {
    $_SESSION['error'] = "Failed to unflag document.";
}

// Redirect back to documents page
header("Location: manage-documents.php");
exit;
?>