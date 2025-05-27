<?php
// File: admin/process-change-password.php
// Include session management
require_once '../public/include/session.php';

// Require admin access
requireAdmin();

// Check if form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $currentPassword = $_POST['currentPassword'] ?? '';
    $newPassword = $_POST['newPassword'] ?? '';
    $confirmPassword = $_POST['confirmPassword'] ?? '';
    
    // Validate form data
    if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
        $_SESSION['error'] = "Please fill in all password fields.";
        header("Location: profile.php");
        exit;
    }
    
    // Check if new passwords match
    if ($newPassword !== $confirmPassword) {
        $_SESSION['error'] = "New passwords do not match.";
        header("Location: profile.php");
        exit;
    }
    
    // Validate password strength (optional)
    if (strlen($newPassword) < 6) {
        $_SESSION['error'] = "New password must be at least 6 characters long.";
        header("Location: profile.php");
        exit;
    }
    
    // Get current user
    $currentUser = getCurrentUser($conn);
    if (!$currentUser) {
        $_SESSION['error'] = "Unable to load user information.";
        header("Location: profile.php");
        exit;
    }
    
    // Verify current password
    if (!password_verify($currentPassword, $currentUser['Password'])) {
        $_SESSION['error'] = "Current password is incorrect.";
        header("Location: profile.php");
        exit;
    }
    
    // Hash new password
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
    
    // Update password in database
    $sql = "UPDATE user SET Password = '" . mysqli_real_escape_string($conn, $hashedPassword) . "' WHERE UserID = " . $_SESSION['user_id'];
    
    if (mysqli_query($conn, $sql)) {
        $_SESSION['success'] = "Password changed successfully!";
    } else {
        $_SESSION['error'] = "Failed to change password. Error: " . mysqli_error($conn);
    }
    
    header("Location: profile.php");
    exit;
} else {
    // If not a POST request, redirect to profile page
    header("Location: profile.php");
    exit;
}
?>
