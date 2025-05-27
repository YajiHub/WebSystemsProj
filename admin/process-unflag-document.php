<?php
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

// Get document information
$document = getDocumentById($conn, $documentId);

// Check if document exists
if (!$document) {
    $_SESSION['error'] = "Document not found.";
    header("Location: manage-documents.php");
    exit;
}

// Check if document is flagged
if ($document['IsDeleted'] != 1 || empty($document['FlagReason'])) {
    $_SESSION['error'] = "Document is not flagged.";
    header("Location: manage-documents.php");
    exit;
}

// Restore the document (unflag)
if (restoreDocument($conn, $documentId)) {
    // Log the restore action
    // Note: You might want to add a new access type for restore actions
    $viewAccessTypeId = 1; // Using 'View' as a placeholder
    logFileAccess($conn, $_SESSION['user_id'], $documentId, $viewAccessTypeId);
    
    $_SESSION['success'] = "Document unflagged successfully.";
} else {
    $_SESSION['error'] = "Failed to unflag document.";
}

// Check if we should redirect to flagged documents page
if (isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], 'flagged-documents.php') !== false) {
    header("Location: flagged-documents.php");
} else {
    // Otherwise, return to the manage documents page
    header("Location: manage-documents.php");
}
exit;
?>