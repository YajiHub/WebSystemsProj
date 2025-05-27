<?php
require_once '../public/include/session.php';
requireAdmin();

// Check if form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $userId = isset($_POST['userId']) ? intval($_POST['userId']) : 0;
    $firstName = $_POST['firstName'] ?? '';
    $middleName = $_POST['middleName'] ?? '';
    $lastName = $_POST['lastName'] ?? '';
    $email = $_POST['email'] ?? '';
    $username = $_POST['username'] ?? '';
    $role = $_POST['role'] ?? '';
    $accessLevel = $_POST['accessLevel'] ?? '';
    $extension = $_POST['extension'] ?? '';
    $notes = $_POST['notes'] ?? '';
    $newPassword = $_POST['newPassword'] ?? '';
    
    // Validate user ID
    if ($userId <= 0) {
        $_SESSION['error'] = "Invalid user ID.";
        header("Location: manage-users.php");
        exit;
    }
    
    // Check if user exists
    $checkUserSql = "SELECT * FROM user WHERE UserID = ?";
    $checkUserStmt = mysqli_prepare($conn, $checkUserSql);
    mysqli_stmt_bind_param($checkUserStmt, "i", $userId);
    mysqli_stmt_execute($checkUserStmt);
    $checkUserResult = mysqli_stmt_get_result($checkUserStmt);
    
    if (mysqli_num_rows($checkUserResult) == 0) {
        $_SESSION['error'] = "User not found.";
        header("Location: manage-users.php");
        exit;
    }
    
    $currentUser = mysqli_fetch_assoc($checkUserResult);
    mysqli_stmt_close($checkUserStmt);
    
    // Validate required fields
    if (empty($firstName) || empty($lastName) || empty($email) || empty($username) || empty($role) || empty($accessLevel)) {
        $_SESSION['error'] = "Please fill in all required fields.";
        header("Location: edit-user.php?id=" . $userId);
        exit;
    }
    
    // Check if email is already used by another user
    $checkEmailSql = "SELECT * FROM user WHERE EmailAddress = ? AND UserID != ?";
    $checkEmailStmt = mysqli_prepare($conn, $checkEmailSql);
    mysqli_stmt_bind_param($checkEmailStmt, "si", $email, $userId);
    mysqli_stmt_execute($checkEmailStmt);
    $checkEmailResult = mysqli_stmt_get_result($checkEmailStmt);
    
    if (mysqli_num_rows($checkEmailResult) > 0) {
        $_SESSION['error'] = "Email address is already used by another user.";
        header("Location: edit-user.php?id=" . $userId);
        exit;
    }
    mysqli_stmt_close($checkEmailStmt);
    
    // Check if username is already used by another user
    $checkUsernameSql = "SELECT * FROM user WHERE Username = ? AND UserID != ?";
    $checkUsernameStmt = mysqli_prepare($conn, $checkUsernameSql);
    mysqli_stmt_bind_param($checkUsernameStmt, "si", $username, $userId);
    mysqli_stmt_execute($checkUsernameStmt);
    $checkUsernameResult = mysqli_stmt_get_result($checkUsernameStmt);
    
    if (mysqli_num_rows($checkUsernameResult) > 0) {
        $_SESSION['error'] = "Username is already taken by another user.";
        header("Location: edit-user.php?id=" . $userId);
        exit;
    }
    mysqli_stmt_close($checkUsernameStmt);
    
    // Prepare update SQL
    $updateFields = [];
    $updateParams = [];
    $updateTypes = "";
    
    // Basic user info
    $updateFields[] = "FirstName = ?";
    $updateParams[] = $firstName;
    $updateTypes .= "s";
    
    $updateFields[] = "MiddleName = ?";
    $updateParams[] = $middleName;
    $updateTypes .= "s";
    
    $updateFields[] = "LastName = ?";
    $updateParams[] = $lastName;
    $updateTypes .= "s";
    
    $updateFields[] = "EmailAddress = ?";
    $updateParams[] = $email;
    $updateTypes .= "s";
    
    $updateFields[] = "Username = ?";
    $updateParams[] = $username;
    $updateTypes .= "s";
    
    $updateFields[] = "UserRole = ?";
    $updateParams[] = $role;
    $updateTypes .= "s";
    
    $updateFields[] = "AccessLevel = ?";
    $updateParams[] = $accessLevel;
    $updateTypes .= "i";
    
    $updateFields[] = "Extension = ?";
    $updateParams[] = $extension;
    $updateTypes .= "s";
    
    $updateFields[] = "Notes = ?";
    $updateParams[] = $notes;
    $updateTypes .= "s";
    
    // Check if password should be updated
    if (!empty($newPassword)) {
        // Validate password length
        if (strlen($newPassword) < 6) {
            $_SESSION['error'] = "Password must be at least 6 characters long.";
            header("Location: edit-user.php?id=" . $userId);
            exit;
        }
        
        // Hash the new password
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $updateFields[] = "Password = ?";
        $updateParams[] = $hashedPassword;
        $updateTypes .= "s";
    }
    
    // Add user ID to params (for WHERE clause)
    $updateParams[] = $userId;
    $updateTypes .= "i";
    
    // Construct update SQL
    $updateSql = "UPDATE user SET " . implode(", ", $updateFields) . " WHERE UserID = ?";
    
    // Execute update
    $updateStmt = mysqli_prepare($conn, $updateSql);
    mysqli_stmt_bind_param($updateStmt, $updateTypes, ...$updateParams);
    
    if (mysqli_stmt_execute($updateStmt)) {
        $_SESSION['success'] = "User information updated successfully.";
        
        // Log the user update
        error_log("Admin {$_SESSION['user_id']} updated user {$userId} ({$username})");
        
        // Update session data if the admin is updating their own account
        if ($userId == $_SESSION['user_id']) {
            $_SESSION['user_name'] = $firstName . ' ' . $lastName;
            $_SESSION['user_email'] = $email;
            $_SESSION['user_role'] = $role;
            $_SESSION['user_access_level'] = $accessLevel;
        }
        
        header("Location: view-user.php?id=" . $userId);
    } else {
        $_SESSION['error'] = "Failed to update user information: " . mysqli_error($conn);
        header("Location: edit-user.php?id=" . $userId);
    }
    
    mysqli_stmt_close($updateStmt);
    exit;
} else {
    // If not POST request, redirect to users page
    header("Location: manage-users.php");
    exit;
}
?>