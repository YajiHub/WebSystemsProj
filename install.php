<?php
// File: install.php
// This script sets up the database and initial data

// Include database setup
require_once 'public/include/db_setup.php';

// Redirect to login page
header("Location: public/login.php");
exit;
?>