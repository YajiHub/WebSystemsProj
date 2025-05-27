<?php
// File: admin/process-restore-document.php
// Include session management
require_once '../public/include/session.php';

// Require admin
requireAdmin();

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo "Invalid request method";
    exit;
}

// Check if document ID is provided
if (!isset($_POST['documentId']) || empty($_POST['documentId'])) {
    echo "No document specified.";
    exit;
}

$documentId = (int)$_POST['documentId'];

// Get document information
$document = getDocumentById($conn, $documentId);

// Check if document exists
if (!$document) {
    echo "Document not found.";
    exit;
}

// Check if document is flagged/deleted
if ($document['IsDeleted'] != 1) {
    echo "Document is not flagged or deleted.";
    exit;
}

// Restore the document (unflag)
if (restoreDocument($conn, $documentId)) {
    // Log the restore action
    // You might want to add a new access type for restore actions
    $viewAccessTypeId = 1; // Using 'View' as a placeholder
    logFileAccess($conn, $_SESSION['user_id'], $documentId, $viewAccessTypeId);
    
    echo "success";
} else {
    echo "Failed to restore document.";
}
exit;
?>