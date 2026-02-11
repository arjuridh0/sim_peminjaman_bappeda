<?php
require_once '../includes/functions.php';

// Logout Process
session_start();

// Unset all admin session variables
unset($_SESSION['admin_logged_in']);
unset($_SESSION['admin_id']);
unset($_SESSION['admin_username']);

session_destroy();

redirect('admin/login.php'); // Fixed redirect path
