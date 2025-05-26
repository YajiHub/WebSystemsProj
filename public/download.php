<?php
// File: public/download.php
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

// Check if user has access to the document
if (!hasDocumentAccess($conn, $_SESSION['user_id'], $documentId)) {
    $_SESSION['error'] = "You do not have permission to download this document.";
    header("Location: my-documents.php");
    exit;
}

// Log the download action
$downloadAccessTypeId = 2; // Assuming 2 is the ID for 'Download' in accesstype table
logFileAccess($conn, $_SESSION['user_id'], $documentId, $downloadAccessTypeId);

// Get file path
$filePath = $document['FileLocation'];

// Check if file exists
if (!file_exists($filePath)) {
    $_SESSION['error'] = "File not found on server.";
    header("Location: my-documents.php");
    exit;
}

// Set headers for download
header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . basename($filePath) . '"');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize($filePath));

// Clear output buffer
ob_clean();
flush();

// Read file and output to browser
readfile($filePath);
exit;
?>