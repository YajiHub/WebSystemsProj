<?php
// Include session management
require_once 'include/session.php';

// Check if form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember_me']);
    
    // Validate form data
    if (empty($email) || empty($password)) {
        $_SESSION['error'] = "Please enter both email and password.";
        header("Location: login.php");
        exit;
    }
    
    // Check if user exists
    $user = getUserByEmail($conn, $email);
    
    if ($user && password_verify($password, $user['Password'])) {
        // Set user session
        setUserSession($user);
        
        // Set remember me cookie if requested
        if ($remember) {
            $token = bin2hex(random_bytes(32));
            setcookie('remember_token', $token, time() + 30 * 24 * 60 * 60, '/');
            
            // In a real application, you would store this token in the database
            // associated with the user for verification on subsequent visits
        }
        
        // Redirect based on role
        if ($user['UserRole'] == 'admin') {
            header("Location: ../admin/dashboard.php");
        } else {
            header("Location: index.php");
        }
        exit;
    } else {
        $_SESSION['error'] = "Invalid email or password.";
        header("Location: login.php");
        exit;
    }
} else {
    // If not a POST request, redirect to login page
    header("Location: login.php");
    exit;
}
?>