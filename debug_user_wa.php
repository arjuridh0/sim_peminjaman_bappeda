<?php
/**
 * Debug Script - Test User WhatsApp Notification
 * 
 * This script simulates sending a booking confirmation to a user
 * to verify that WhatsApp notifications are working correctly.
 */

require_once 'includes/functions.php';

// Test data - GANTI dengan nomor WhatsApp Anda untuk testing
$testBooking = [
    'id' => 999,
    'qr_token' => 'TEST-' . date('YmdHis'),
    'user_email' => null, // Set null untuk test WA only
    'phone_number' => '08123456789', // GANTI dengan nomor WA Anda
    'nama_peminjam' => 'Test User',
    'room_id' => 1,
    'room_name' => 'Ruang Test',
    'tanggal' => date('Y-m-d'),
    'waktu_mulai' => '09:00',
    'waktu_selesai' => '11:00',
    'kegiatan' => 'Testing Notifikasi WA'
];

echo "<h2>Debug: Test User WhatsApp Notification</h2>";
echo "<pre>";
echo "Test Data:\n";
print_r($testBooking);
echo "\n\n";

echo "Sending notification...\n";
$result = send_booking_confirmation($testBooking);

if ($result) {
    echo "✅ SUCCESS: Notification sent!\n";
    echo "Check WhatsApp: " . $testBooking['phone_number'] . "\n";
} else {
    echo "❌ FAILED: Notification not sent\n";
    echo "Check error logs for details\n";
}

echo "</pre>";
