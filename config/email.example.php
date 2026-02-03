<?php
// config/email.example.php
// Rename this file to 'email.php' and configure your credentials

// SMTP Configuration
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_ENCRYPTION', 'tls'); // 'tls' or 'ssl'
define('SMTP_AUTH', true);

// SMTP Credentials
// IMPORTANT: Replace these with your actual Gmail credentials
// For Gmail, use App Password instead of regular password
define('SMTP_USERNAME', 'your-email@gmail.com');
define('SMTP_PASSWORD', 'your-app-password');

// Email Settings
define('EMAIL_FROM_ADDRESS', 'your-email@gmail.com');
define('EMAIL_FROM_NAME', 'Sistem Peminjaman BAPPEDA');
define('EMAIL_REPLY_TO', 'your-email@gmail.com');

// Admin Email (receives new booking notifications)
define('ADMIN_EMAIL', 'admin-email@gmail.com');

// Email Options
define('EMAIL_DEBUG', 0); // 0 = off, 1 = client messages, 2 = client and server messages
define('EMAIL_CHARSET', 'UTF-8');
