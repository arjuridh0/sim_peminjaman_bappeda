<?php
// config/email.php
// Email Configuration for PHPMailer

// SMTP Configuration
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_ENCRYPTION', 'tls'); // 'tls' or 'ssl'
define('SMTP_AUTH', true);

// SMTP Credentials
// IMPORTANT: Replace these with your actual Gmail credentials
// For Gmail, use App Password instead of regular password
// How to create: https://support.google.com/accounts/answer/185833
define('SMTP_USERNAME', 'arjuridho77@gmail.com'); // Replace with your Gmail
define('SMTP_PASSWORD', 'xzll nwgi yxyc izuq');    // Replace with your App Password

// Email Settings
define('EMAIL_FROM_ADDRESS', 'arjuridho77@gmail.com');
define('EMAIL_FROM_NAME', 'BAPPEDA Jawa Tengah');
define('EMAIL_REPLY_TO', 'arjuridho77@gmail.com');

// Admin Email (receives new booking notifications)
define('ADMIN_EMAIL', 'arjuridho77@gmail.com'); // Replace with actual admin email

// Email Options
define('EMAIL_DEBUG', 0); // 0 = off, 1 = client messages, 2 = client and server messages
define('EMAIL_CHARSET', 'UTF-8');
