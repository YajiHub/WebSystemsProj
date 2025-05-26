<?php
// File: admin/process-flag-document.php
// Include session management
require_once '../public/include/session.php';

// Require admin
requireAdmin();

// Check if form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $documentId = $_POST['documentId'] ?? '';
    $flagReason = $_POST['flagReason'] ?? '';
    $flagComments = $_POST['flagComments'] ?? '';
    
    // Validate form data
    if (empty($documentId) || empty($flagReason)) {
        $_SESSION['error'] = "Please provide a reason for flagging the document.";
        header("Location: manage-documents.php");
        exit;
    }
    
    // Combine reason and comments
    $fullReason = $flagReason;
    if (!empty($flagComments)) {
        $fullReason .= ": " . $flagComments;
    }
    
    // Flag the document
    if (flagDocument($conn, $documentId, $fullReason)) {
        // Log the flag action
        $flagAccessTypeId = 5; // Assuming 5 is the ID for 'Flag' in accesstype table
        logFileAccess($conn, $_SESSION['user_id'], $documentId, $flagAccessTypeId);
        
        $_SESSION['success'] = "Document flagged successfully.";
    } else {
        $_SESSION['error'] = "Failed to flag document.";
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