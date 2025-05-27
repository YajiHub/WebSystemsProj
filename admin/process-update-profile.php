<?php
// File: admin/process-update-profile.php
// Include session management
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
    $username = $_POST['username'] ?? '';
    $extension = $_POST['extension'] ?? '';
    
    // Validate form data
    if (empty($firstName) || empty($lastName) || empty($email) || empty($username)) {
        $_SESSION['error'] = "Please fill in all required fields.";
        header("Location: profile.php");
        exit;
    }
    
    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "Please enter a valid email address.";
        header("Location: profile.php");
        exit;
    }
    
    // Check if email is already used by another user
    $existingUser = getUserByEmail($conn, $email);
    if ($existingUser && $existingUser['UserID'] != $_SESSION['user_id']) {
        $_SESSION['error'] = "Email address is already used by another user.";
        header("Location: profile.php");
        exit;
    }
    
    // Check if username is already used by another user
    $sql = "SELECT * FROM user WHERE Username = '" . mysqli_real_escape_string($conn, $username) . "' AND UserID != " . $_SESSION['user_id'];
    $result = mysqli_query($conn, $sql);
    if ($result && mysqli_num_rows($result) > 0) {
        $_SESSION['error'] = "Username is already taken by another user.";
        header("Location: profile.php");
        exit;
    }
    
    // Update user information
    $firstName = mysqli_real_escape_string($conn, $firstName);
    $middleName = mysqli_real_escape_string($conn, $middleName);
    $lastName = mysqli_real_escape_string($conn, $lastName);
    $email = mysqli_real_escape_string($conn, $email);
    $username = mysqli_real_escape_string($conn, $username);
    $extension = mysqli_real_escape_string($conn, $extension);
    
    $sql = "UPDATE user SET 
            FirstName = '$firstName',
            MiddleName = '$middleName',
            LastName = '$lastName',
            EmailAddress = '$email',
            Username = '$username',
            Extension = '$extension'
            WHERE UserID = " . $_SESSION['user_id'];
    
    if (mysqli_query($conn, $sql)) {
        // Update session variables with new information
        $_SESSION['user_name'] = $firstName . ' ' . $lastName;
        $_SESSION['user_email'] = $email;
        $_SESSION['username'] = $username;
        
        $_SESSION['success'] = "Profile updated successfully!";
    } else {
        $_SESSION['error'] = "Failed to update profile. Error: " . mysqli_error($conn);
    }
    
    header("Location: profile.php");
    exit;
} else {
    // If not a POST request, redirect to profile page
    header("Location: profile.php");
    exit;
}
?>
