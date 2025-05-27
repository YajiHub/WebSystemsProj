<?php
require_once 'include/session.php';

// Clear user session
clearUserSession();

// Redirect to login page
header("Location: login.php");
exit;
?>