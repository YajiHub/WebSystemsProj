<?php
require_once '../public/include/session.php';
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $accessLevelId = intval($_POST['accessLevelId']);
    
    if ($accessLevelId > 0) {
        // Check if access level has users
        $checkSql = "SELECT COUNT(*) as count FROM user WHERE AccessLevel = ?";
        $checkStmt = mysqli_prepare($conn, $checkSql);
        mysqli_stmt_bind_param($checkStmt, "i", $accessLevelId);
        mysqli_stmt_execute($checkStmt);
        $result = mysqli_stmt_get_result($checkStmt);
        $row = mysqli_fetch_assoc($result);
        
        if ($row['count'] == 0) {
            $sql = "DELETE FROM accesslevel WHERE AccessLevelID = ?";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "i", $accessLevelId);
            
            if (mysqli_stmt_execute($stmt)) {
                echo "success";
            } else {
                echo "Error: " . mysqli_error($conn);
            }
            
            mysqli_stmt_close($stmt);
        } else {
            echo "Cannot delete access level with users";
        }
        
        mysqli_stmt_close($checkStmt);
    } else {
        echo "Invalid access level ID";
    }
}
?>