<?php
require_once '../public/include/session.php';
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $accessLevelId = intval($_POST['accessLevelId']);
    $levelName = trim($_POST['levelName']);
    
    if (!empty($levelName) && $accessLevelId > 0) {
        $sql = "UPDATE accesslevel SET LevelName = ? WHERE AccessLevelID = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "si", $levelName, $accessLevelId);
        
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['success'] = "Access level updated successfully!";
        } else {
            $_SESSION['error'] = "Error updating access level: " . mysqli_error($conn);
        }
        
        mysqli_stmt_close($stmt);
    } else {
        $_SESSION['error'] = "Invalid access level data.";
    }
}

header("Location: system-settings.php");
exit;
?>