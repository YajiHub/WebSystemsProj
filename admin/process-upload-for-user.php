<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Log errors to file
ini_set('log_errors', 1);
ini_set('error_log', '../admin_upload_errors.log');

// Include session management
require_once '../public/include/session.php';

// Include database functions
require_once '../public/include/db_functions.php';

// Require admin login
requireAdmin();

// Check if form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $userId = isset($_POST['userId']) ? intval($_POST['userId']) : 0;
    $title = $_POST['documentTitle'] ?? '';
    $description = $_POST['documentDescription'] ?? '';
    $tags = $_POST['documentTags'] ?? '';
    $categoryId = !empty($_POST['categoryId']) ? $_POST['categoryId'] : null;
    $accessLevel = isset($_POST['accessLevel']) ? intval($_POST['accessLevel']) : 1;
    $notifyUser = isset($_POST['notifyUser']);
    
    // Validate form data
    if ($userId <= 0) {
        $_SESSION['error'] = "No valid user selected.";
        header("Location: upload-for-user.php");
        exit;
    }
    
    // Verify user exists
    $targetUser = getUserById($conn, $userId);
    if (!$targetUser) {
        $_SESSION['error'] = "Selected user does not exist.";
        header("Location: upload-for-user.php");
        exit;
    }
    
    if (empty($title)) {
        $_SESSION['error'] = "Please enter a document title.";
        header("Location: upload-for-user.php?user_id=" . $userId);
        exit;
    }
    
    // Check if file was uploaded
    if (!isset($_FILES['documentFile']) || $_FILES['documentFile']['error'] !== UPLOAD_ERR_OK) {
        $errorCode = $_FILES['documentFile']['error'] ?? 'No file selected';
        $errorMessage = "Please select a file to upload. ";
        
        // Provide specific error messages based on error code
        switch ($errorCode) {
            case UPLOAD_ERR_INI_SIZE:
                $errorMessage .= "The uploaded file exceeds the upload_max_filesize directive in php.ini.";
                break;
            case UPLOAD_ERR_FORM_SIZE:
                $errorMessage .= "The uploaded file exceeds the MAX_FILE_SIZE directive in the HTML form.";
                break;
            case UPLOAD_ERR_PARTIAL:
                $errorMessage .= "The uploaded file was only partially uploaded.";
                break;
            case UPLOAD_ERR_NO_FILE:
                $errorMessage .= "No file was uploaded.";
                break;
            case UPLOAD_ERR_NO_TMP_DIR:
                $errorMessage .= "Missing a temporary folder.";
                break;
            case UPLOAD_ERR_CANT_WRITE:
                $errorMessage .= "Failed to write file to disk.";
                break;
            case UPLOAD_ERR_EXTENSION:
                $errorMessage .= "File upload stopped by extension.";
                break;
            default:
                $errorMessage .= "Unknown upload error.";
        }
        
        $_SESSION['error'] = $errorMessage;
        header("Location: upload-for-user.php?user_id=" . $userId);
        exit;
    }
    
    // Get file information
    $file = $_FILES['documentFile'];
    $fileName = $file['name'];
    $fileTmpPath = $file['tmp_name'];
    $fileSize = $file['size'];
    $fileError = $file['error'];
    
    // Get file extension
    $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    
    // Check file extension
    $allowedExts = ['pdf', 'jpg', 'jpeg', 'png'];
    if (!in_array($fileExt, $allowedExts)) {
        $_SESSION['error'] = "Only PDF, JPG, and PNG files are allowed.";
        header("Location: upload-for-user.php?user_id=" . $userId);
        exit;
    }
    
    // Check file size (10MB max)
    $maxSize = 10 * 1024 * 1024; // 10MB in bytes
    if ($fileSize > $maxSize) {
        $_SESSION['error'] = "File size exceeds the maximum limit of 10MB.";
        header("Location: upload-for-user.php?user_id=" . $userId);
        exit;
    }
    
    // Create upload directory for the user if it doesn't exist
    $uploadDir = '../uploads/' . $userId . '/';
    if (!file_exists($uploadDir)) {
        if (!mkdir($uploadDir, 0755, true)) {
            error_log("Failed to create directory: " . $uploadDir);
            $_SESSION['error'] = "Failed to create upload directory. Please check permissions.";
            header("Location: upload-for-user.php?user_id=" . $userId);
            exit;
        }
    }
    
    // Generate unique filename
    $newFileName = uniqid() . '.' . $fileExt;
    $uploadPath = $uploadDir . $newFileName;
    
    // Move uploaded file
    if (move_uploaded_file($fileTmpPath, $uploadPath)) {
        // Standardize file extension for database
        if ($fileExt == 'jpeg') {
            $fileExt = 'jpg';
        }
        
        // Prepare document data
        $documentData = [
            'title' => $title,
            'fileType' => $fileExt,
            'description' => $description,
            'categoryId' => $categoryId,
            'userId' => $userId,  // Important: This is the target user's ID, not the admin's
            'accessLevel' => $accessLevel
        ];
        
        // Add document to database
        $documentId = addDocument($conn, $documentData, $uploadPath);
        
        if ($documentId) {
            // Log the upload action by the admin
            $uploadAccessTypeId = 3; // Assuming 3 is the ID for 'Upload' in accesstype table
            
            // Important: Log that the admin (not the user) performed this upload
            // This will be used to show who actually uploaded the file
            logFileAccess($conn, $_SESSION['user_id'], $documentId, $uploadAccessTypeId);
            
            // Add a note about the upload in the document description if one wasn't provided
            if (empty($description)) {
                $adminInfo = getUserById($conn, $_SESSION['user_id']);
                $adminName = $adminInfo['FirstName'] . ' ' . $adminInfo['LastName'];
                $updateSql = "UPDATE document SET FileTypeDescription = CONCAT('Uploaded by admin: ', ?) WHERE DocumentID = ?";
                $updateStmt = mysqli_prepare($conn, $updateSql);
                mysqli_stmt_bind_param($updateStmt, "si", $adminName, $documentId);
                mysqli_stmt_execute($updateStmt);
                mysqli_stmt_close($updateStmt);
            }
            
            // TODO: If notification is enabled, add code to notify the user here
            // This could be done via email or an internal notification system
            
            $_SESSION['success'] = "Document uploaded successfully for user " . htmlspecialchars($targetUser['FirstName'] . ' ' . $targetUser['LastName']) . ".";
            
            // Redirect to view-user.php with the user ID to show the user's documents
            header("Location: view-user.php?id=" . $userId);
            exit;
        } else {
            // Delete the uploaded file if database insertion failed
            unlink($uploadPath);
            
            $_SESSION['error'] = "Failed to add document to database. Error: " . mysqli_error($conn);
            header("Location: upload-for-user.php?user_id=" . $userId);
            exit;
        }
    } else {
        error_log("Failed to move uploaded file from {$fileTmpPath} to {$uploadPath}");
        $_SESSION['error'] = "Failed to upload file. Please check file permissions.";
        header("Location: upload-for-user.php?user_id=" . $userId);
        exit;
    }
} else {
    // If not a POST request, redirect to upload page
    header("Location: upload-for-user.php");
    exit;
}
?>