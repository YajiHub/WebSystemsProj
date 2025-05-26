<?php
// File: admin/process-delete-document.php
// Include session management
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
    
    // Permanently delete the document
    if (deleteDocument($conn, $documentId)) {
        $_SESSION['success'] = "Document permanently deleted.";
    } else {
        $_SESSION['error'] = "Failed to delete document.";
    }
    
    // Redirect back to documents page
    header("Location: manage-documents.php");
    exit;
} else {
    // If not a POST request, redirect to documents page
    header("Location: manage-documents.php");
    exit;
}
?>