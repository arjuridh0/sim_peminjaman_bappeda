<?php
// includes/auth_check.php

// Pastikan session sudah start (biasanya udah dari functions.php)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: " . base_url('/admin/login.php'));
    exit;
}
