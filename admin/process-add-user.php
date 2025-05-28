<?php
require_once '../public/include/session.php';

// Require admin access
requireAdmin();

// Check if form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $firstName = $_POST['firstName'] ?? '';
    $middleName = $_POST['middleName'] ?? '';
    $lastName = $_POST['lastName'] ?? '';
    $email = $_POST['email'] ?? '';
    $extension = $_POST['extension'] ?? '';
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $userRole = $_POST['userRole'] ?? '';
    $accessLevel = $_POST['accessLevel'] ?? '';
    
    // Validate form data
    if (empty($firstName) || empty($lastName) || empty($email) || empty($username) || empty($password) || empty($userRole) || empty($accessLevel)) {
        $_SESSION['error'] = "Please fill in all required fields.";
        header("Location: add-user.php");
        exit;
    }
    
    // Check if email already exists
    $existingUser = getUserByEmail($conn, $email);
    if ($existingUser) {
        $_SESSION['error'] = "Email address is already registered.";
        header("Location: add-user.php");
        exit;
    }
    
    // Check if username already exists
    $sql = "SELECT * FROM user WHERE Username = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($result) > 0) {
        $_SESSION['error'] = "Username is already taken.";
        header("Location: add-user.php");
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
        'userRole' => $userRole,
        'accessLevel' => $accessLevel
    ];
    
    $userId = addUser($conn, $userData);
    
    if ($userId) {
        $_SESSION['success'] = "User added successfully.";
        header("Location: manage-users.php");
        exit;
    } else {
        $_SESSION['error'] = "Failed to add user.";
        header("Location: add-user.php");
        exit;
    }
} else {
    // If not a POST request, redirect to add user page
    header("Location: add-user.php");
    exit;
}
?>