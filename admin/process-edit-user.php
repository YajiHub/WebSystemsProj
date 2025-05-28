<?php
require_once '../public/include/session.php';

// Require admin access
requireAdmin();

// Check if form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $userId = $_POST['userId'] ?? '';
    $firstName = $_POST['firstName'] ?? '';
    $middleName = $_POST['middleName'] ?? '';
    $lastName = $_POST['lastName'] ?? '';
    $email = $_POST['email'] ?? '';
    $extension = $_POST['extension'] ?? '';
    $userRole = $_POST['userRole'] ?? '';
    $accessLevel = $_POST['accessLevel'] ?? '';
    $resetPassword = isset($_POST['resetPassword']) && $_POST['resetPassword'] == '1';
    $newPassword = $_POST['newPassword'] ?? '';
    
    // Validate form data
    if (empty($userId) || empty($firstName) || empty($lastName) || empty($email) || empty($userRole) || empty($accessLevel)) {
        $_SESSION['error'] = "Please fill in all required fields.";
        header("Location: edit-user.php?id=$userId");
        exit;
    }
    
    // Validate user ID
    $userId = (int)$userId;
    if ($userId <= 0) {
        $_SESSION['error'] = "Invalid user ID.";
        header("Location: manage-users.php");
        exit;
    }
    
    // Check if user exists
    $user = getUserById($conn, $userId);
    if (!$user) {
        $_SESSION['error'] = "User not found.";
        header("Location: manage-users.php");
        exit;
    }
    
    // Check if email is already used by another user
    $sql = "SELECT * FROM user WHERE EmailAddress = ? AND UserID != ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "si", $email, $userId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($result) > 0) {
        $_SESSION['error'] = "Email address is already used by another user.";
        header("Location: edit-user.php?id=$userId");
        exit;
    }
    
    // Prepare user data
    $userData = [
        'firstName' => $firstName,
        'middleName' => $middleName,
        'lastName' => $lastName,
        'extension' => $extension,
        'email' => $email,
        'userRole' => $userRole,
        'accessLevel' => $accessLevel
    ];
    
    // Update user information
    if (updateUser($conn, $userId, $userData)) {
        // Reset password if requested
        if ($resetPassword) {
            if (empty($newPassword)) {
                $_SESSION['error'] = "Please provide a new password.";
                header("Location: edit-user.php?id=$userId");
                exit;
            }
            
            if (updateUserPassword($conn, $userId, $newPassword)) {
                $_SESSION['success'] = "User information and password updated successfully.";
            } else {
                $_SESSION['error'] = "User information updated, but failed to reset password.";
            }
        } else {
            $_SESSION['success'] = "User information updated successfully.";
        }
    } else {
        $_SESSION['error'] = "Failed to update user information.";
    }
    
    header("Location: manage-users.php");
    exit;
} else {
    // If not a POST request, redirect to manage users page
    header("Location: manage-users.php");
    exit;
}
?>