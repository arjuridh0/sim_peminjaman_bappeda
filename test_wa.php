<?php
// test_wa.php
// Script untuk test kirim WhatsApp via Fonnte

require_once 'includes/functions.php';

echo "<h1>📱 WhatsApp Debugging Tool</h1>";
echo "<pre>";

// Load Config
if (defined('WA_API_TOKEN')) {
    echo "✅ Konfigurasi WA ditemukan.\n";
    echo "   Token: " . substr(WA_API_TOKEN, 0, 5) . "..." . substr(WA_API_TOKEN, -5) . "\n";
    echo "   Admin Phone: " . WA_ADMIN_PHONE . "\n";
    echo "   Status: " . (WA_ENABLED ? 'ENABLED' : 'DISABLED') . "\n";
} else {
    die("❌ Error: Konfigurasi WA tidak ditemukan!");
}

// Test Send
$target = WA_ADMIN_PHONE; // Kirim ke admin dulu
$message = "*TEST WHATSAPP DARI LOCALHOST*\n\n";
$message .= "Halo Admin,\n";
$message .= "Ini adalah pesan percobaan dari sistem peminjaman ruangan.\n";
$message .= "Waktu: " . date('Y-m-d H:i:s');

echo "\n🚀 Mengirim pesan ke $target...\n";

if (send_whatsapp($target, $message)) {
    echo "\n✅ BERHASIL! Pesan terkirim.";
    echo "\nSilahkan cek WhatsApp Anda.";
} else {
    echo "\n❌ GAGAL! Pesan tidak terkirim.";
    echo "\nCek error log atau pastikan Token benar.";
}

echo "</pre>";
?>