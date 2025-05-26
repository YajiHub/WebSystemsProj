<?php
// File: admin/process-add-user.php
// Include session management
require_once '../public/include/session.php';

// Require admin
requireAdmin();

// Check if form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $firstName = $_POST['firstName'] ?? '';
    $middleName = $_POST['middleName'] ?? '';
    $lastName = $_POST['lastName'] ?? '';
    $email = $_POST['email'] ?? '';
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? '';
    $accessLevel = $_POST['accessLevel'] ?? '';
    $extension = $_POST['extension'] ?? '';
    
    // Validate form data
    if (empty($firstName) || empty($lastName) || empty($email) || empty($username) || empty($password) || empty($role) || empty($accessLevel)) {
        $_SESSION['error'] = "Please fill in all required fields.";
        header("Location: manage-users.php");
        exit;
    }
    
    // Check if email already exists
    $existingUser = getUserByEmail($conn, $email);
    if ($existingUser) {
        $_SESSION['error'] = "Email address is already registered.";
        header("Location: manage-users.php");
        exit;
    }
    
    // Create new user
    $userData = [
        'username' => $username,
        'password' => $password,
        'firstName' => $firstName,
        'middleName' => $middleName,
        'lastName' => $lastName,
        'extension' => $extension,
        'email' => $email,
        'userRole' => $role,
        'accessLevel' => $accessLevel
    ];
    
    $userId = addUser($conn, $userData);
    
    if ($userId) {
        $_SESSION['success'] = "User added successfully.";
        header("Location: manage-users.php");
        exit;
    } else {
        $_SESSION['error'] = "Failed to add user.";
        header("Location: manage-users.php");
        exit;
    }
} else {
    // If not a POST request, redirect to user management page
    header("Location: manage-users.php");
    exit;
}
?>