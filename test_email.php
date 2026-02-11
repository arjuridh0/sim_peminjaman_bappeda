<?php
// test_email.php
// Script untuk test kirim email dan debug error

// Tampilkan semua error PHP
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🔍 Email Debugging Tool</h1>";
echo "<pre>";

// Load konfigurasi
if (file_exists('config/email.php')) {
    echo "✅ config/email.php ditemukan.\n";
    require_once 'config/email.php';
} else {
    die("❌ Error: config/email.php tidak ditemukan!");
}

// Load PHPMailer
if (file_exists('vendor/autoload.php')) {
    echo "✅ vendor/autoload.php ditemukan.\n";
    require_once 'vendor/autoload.php';
} else {
    die("❌ Error: vendor/autoload.php tidak ditemukan! Pastikan folder vendor sudah di-upload.");
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$mail = new PHPMailer(true);

try {
    // Server settings
    $mail->SMTPDebug = 2;                      // Enable verbose debug output
    $mail->Debugoutput = 'html';               // Format output as HTML
    $mail->isSMTP();                           // Send using SMTP

    echo "\n⚙️ Mencoba koneksi ke SMTP Server...\n";
    echo "   Host: " . SMTP_HOST . "\n";
    echo "   Port: " . SMTP_PORT . "\n";
    echo "   Auth: " . (SMTP_AUTH ? 'TRUE' : 'FALSE') . "\n";
    echo "   Username: " . SMTP_USERNAME . "\n";

    $mail->Host = SMTP_HOST;
    $mail->SMTPAuth = SMTP_AUTH;
    $mail->Username = SMTP_USERNAME;
    $mail->Password = SMTP_PASSWORD;
    $mail->SMTPSecure = SMTP_ENCRYPTION;
    $mail->Port = SMTP_PORT;

    // Recipients
    $mail->setFrom(EMAIL_FROM_ADDRESS, EMAIL_FROM_NAME);
    $mail->addAddress(ADMIN_EMAIL);            // Kirim ke email admin dulu untuk test

    // Content
    $mail->isHTML(true);
    $mail->Subject = 'Test Email dari Hosting - ' . date('Y-m-d H:i:s');
    $mail->Body = 'Ini adalah email test untuk memastikan konfigurasi SMTP berjalan lancar di hosting.';
    $mail->AltBody = 'Ini adalah email test (plain text).';

    $mail->send();
    echo "\n\n🚀 Message has been sent successfully!</pre>";
    echo "<h2 style='color:green'>✅ Sukses Kirim Email!</h2>";

} catch (Exception $e) {
    echo "\n\n❌ Message could not be sent.";
    echo "\nMailer Error: {$mail->ErrorInfo}</pre>";
    echo "<h2 style='color:red'>❌ Gagal Kirim Email</h2>";
    echo "<p>Tips Perbaikan:</p>";
    echo "<ul>";
    echo "<li>Cek apakah App Password sudah benar.</li>";
    echo "<li><b>Connection Refused/Timeout:</b> Hosting Anda mungkin memblokir port 587. Coba hubungi CS Hosting.</li>";
    echo "<li><b>SSL Error:</b> Sertifikat SSL hosting mungkin tidak valid. Coba disable SSL verify (lihat kode test_email.php).</li>";
    echo "</ul>";
}
?>