<?php
// File: admin/process-add-access-level.php
require_once '../public/include/session.php';
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $levelName = trim($_POST['levelName']);
    
    if (!empty($levelName)) {
        $sql = "INSERT INTO accesslevel (LevelName) VALUES (?)";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "s", $levelName);
        
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['success'] = "Access level added successfully!";
        } else {
            $_SESSION['error'] = "Error adding access level: " . mysqli_error($conn);
        }
        
        mysqli_stmt_close($stmt);
    } else {
        $_SESSION['error'] = "Level name is required.";
    }
}

header("Location: system-settings.php");
exit;
?>