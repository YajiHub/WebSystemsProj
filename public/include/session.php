<?php
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include database functions
require_once 'db_functions.php';

// Function to check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Function to check if user is admin
function isAdmin() {
    return isLoggedIn() && $_SESSION['user_role'] == 'admin';
}

// Function to require login
function requireLogin() {
    if (!isLoggedIn()) {
        $_SESSION['error'] = "You must be logged in to access this page.";
        header("Location: login.php");
        exit;
    }
}

// Function to require admin
function requireAdmin() {
    if (!isAdmin()) {
        $_SESSION['error'] = "You must be an administrator to access this page.";
        header("Location: ../public/index.php");
        exit;
    }
}

// Function to get current user
function getCurrentUser($conn) {
    if (isLoggedIn()) {
        return getUserById($conn, $_SESSION['user_id']);
    }
    return null;
}

// Function to set user session
function setUserSession($user) {
    $_SESSION['user_id'] = $user['UserID'];
    $_SESSION['user_name'] = $user['FirstName'] . ' ' . $user['LastName'];
    $_SESSION['user_email'] = $user['EmailAddress'];
    $_SESSION['user_role'] = $user['UserRole'];
    $_SESSION['user_access_level'] = $user['AccessLevel'];
}

// Function to clear user session
function clearUserSession() {
    session_unset();
    session_destroy();
}
?>