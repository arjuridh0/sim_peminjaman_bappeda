<?php
// config/whatsapp.example.php
// WhatsApp Configuration Template (Fonnte API)
// 
// INSTRUKSI:
// 1. Copy file ini menjadi 'whatsapp.php' di folder yang sama
// 2. Daftar di https://fonnte.com untuk mendapatkan API Token
// 3. Ganti nilai-nilai di bawah dengan kredensial Anda
// 4. Jangan commit file 'whatsapp.php' ke Git (sudah ada di .gitignore)

// ============================================
// FONNTE API CONFIGURATION
// ============================================

// Fonnte API Token
// Cara mendapatkan:
// 1. Daftar di https://fonnte.com
// 2. Login dan buka menu "API"
// 3. Copy token yang diberikan
// 4. Paste di bawah (ganti 'TOKEN_ANDA_DISINI')
define('WA_API_TOKEN', 'TOKEN_ANDA_DISINI');

// Admin Phone Number (untuk menerima notifikasi)
// Format: 
// - Internasional: '628123456789' (tanpa +)
// - Lokal: '081234567890'
// Contoh: '081234567890' atau '628123456789'
define('WA_ADMIN_PHONE', '081234567890');

// ============================================
// WHATSAPP SETTINGS
// ============================================

// Enable/Disable WhatsApp Notifications
// true = Aktif (kirim notifikasi)
// false = Nonaktif (tidak kirim notifikasi)
define('WA_ENABLED', true);

// ============================================
// CONTOH KONFIGURASI
// ============================================

/*
DEVELOPMENT (Testing):
define('WA_API_TOKEN', 'test_token_123abc');
define('WA_ADMIN_PHONE', '081234567890');
define('WA_ENABLED', false); // Nonaktifkan saat development

PRODUCTION (Live):
define('WA_API_TOKEN', 'g7x66SbDpsFerCWaxe2o');
define('WA_ADMIN_PHONE', '6281585058128');
define('WA_ENABLED', true); // Aktifkan saat production
*/

// ============================================
// CATATAN PENTING
// ============================================

/*
1. TOKEN HARUS VALID
   - Token yang salah akan menyebabkan notifikasi gagal
   - Cek status token di dashboard Fonnte

2. NOMOR ADMIN HARUS TERDAFTAR
   - Nomor harus terdaftar di WhatsApp
   - Gunakan format internasional (628xxx) untuk lebih aman

3. TESTING
   - Set WA_ENABLED = false saat development
   - Set WA_ENABLED = true saat production

4. KEAMANAN
   - JANGAN commit file whatsapp.php ke Git
   - JANGAN share token ke orang lain
   - Ganti token secara berkala untuk keamanan
*/
?>