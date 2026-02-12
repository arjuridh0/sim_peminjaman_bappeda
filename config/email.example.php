<?php
// config/email.example.php
// Rename this file to 'email.php' and configure your SMTP credentials

// SMTP Server Configuration
define('SMTP_HOST', 'smtp.gmail.com');      // e.g., smtp.gmail.com, smtp.mailtrap.io
define('SMTP_PORT', 587);                   // 587 (TLS) or 465 (SSL)
define('SMTP_USERNAME', 'your-email@gmail.com');
define('SMTP_PASSWORD', 'your-app-password'); // Use App Password if 2FA is enabled
define('SMTP_AUTH', true);
define('SMTP_ENCRYPTION', 'tls');           // 'tls' or 'ssl'

// Email Sender Configuration
define('EMAIL_FROM_ADDRESS', 'your-email@gmail.com');
define('EMAIL_FROM_NAME', 'Sistem Peminjaman Ruangan BAPPEDA');
define('EMAIL_REPLY_TO', 'your-email@gmail.com'); // Optional
define('EMAIL_CHARSET', 'UTF-8');
