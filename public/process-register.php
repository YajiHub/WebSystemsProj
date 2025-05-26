<?php
// Include session management
require_once 'include/session.php';

// Check if form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $firstName = $_POST['firstName'] ?? '';
    $middleName = $_POST['middleName'] ?? '';
    $lastName = $_POST['lastName'] ?? '';
    $email = $_POST['email'] ?? '';
    $extension = $_POST['extension'] ?? '';
    $username = $_POST['username'] ?? '';
    $accessLevel = $_POST['accessLevel'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirmPassword'] ?? '';
    
    // Validate form data
    if (empty($firstName) || empty($lastName) || empty($email) || empty($username) || empty($password) || empty($confirmPassword)) {
        $_SESSION['error'] = "Please fill in all required fields.";
        header("Location: register.php");
        exit;
    }
    
    if ($password !== $confirmPassword) {
        $_SESSION['error'] = "Passwords do not match.";
        header("Location: register.php");
        exit;
    }
    
    // Check if email already exists
    $existingUser = getUserByEmail($conn, $email);
    if ($existingUser) {
        $_SESSION['error'] = "Email address is already registered.";
        header("Location: register.php");
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
        'userRole' => 'user', // Default role for new registrations
        'accessLevel' => $accessLevel
    ];
    
    $userId = addUser($conn, $userData);
    
    if ($userId) {
        $_SESSION['success'] = "Registration successful! You can now log in.";
        header("Location: login.php");
        exit;
    } else {
        $_SESSION['error'] = "Registration failed. Please try again.";
        header("Location: register.php");
        exit;
    }
} else {
    // If not a POST request, redirect to registration page
    header("Location: register.php");
    exit;
}
?>