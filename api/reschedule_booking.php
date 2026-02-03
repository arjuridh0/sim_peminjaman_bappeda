<?php
/**
 * Reschedule Booking API
 * Admin-only endpoint for drag & drop reschedule
 */

session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    echo json_encode(['success' => false, 'message' => 'Invalid JSON']);
    exit;
}

$booking_id = $input['booking_id'] ?? null;
$new_date = $input['new_date'] ?? null;
$new_start_time = $input['new_start_time'] ?? null;
$new_end_time = $input['new_end_time'] ?? null;

// Validate inputs
if (!$booking_id || !$new_date || !$new_start_time || !$new_end_time) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

try {
    // $pdo is defined in config/database.php

    // Get booking details
    $stmt = $pdo->prepare("SELECT * FROM bookings WHERE id = ?");
    $stmt->execute([$booking_id]);
    $booking = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$booking) {
        echo json_encode(['success' => false, 'message' => 'Booking not found']);
        exit;
    }

    // Format Date and Times Standardized
    $new_date = date('Y-m-d', strtotime($new_date));
    $new_start_time = date('H:i:s', strtotime($new_start_time));
    $new_end_time = date('H:i:s', strtotime($new_end_time));

    // Debug Log
    error_log("Reschedule Attempt: BookingID: $booking_id, Room: {$booking['room_id']}, Date: $new_date, Time: $new_start_time - $new_end_time");

    // Check for conflicts using shared helper
    if (check_booking_conflict($booking['room_id'], $new_date, $new_start_time, $new_end_time, $booking_id)) {
        error_log("Conflict DETECTED for BookingID $booking_id");
        echo json_encode([
            'success' => false,
            'message' => 'Konflik jadwal! Sudah ada booking lain pada waktu tersebut.'
        ]);
        exit;
    }
    error_log("No Conflict for BookingID $booking_id");

    // Update booking
    $updateSql = "
        UPDATE bookings
        SET tanggal = ?,
            waktu_mulai = ?,
            waktu_selesai = ?
        WHERE id = ?
    ";

    $updateStmt = $pdo->prepare($updateSql);
    $success = $updateStmt->execute([
        $new_date,
        $new_start_time,
        $new_end_time,
        $booking_id
    ]);

    if ($success) {
        // Log the reschedule action
        $logSql = "INSERT INTO admin_logs (admin_id, action, booking_id, details) VALUES (?, ?, ?, ?)";
        $logStmt = $pdo->prepare($logSql);
        $logDetails = "Reschedule dari {$booking['tanggal']} {$booking['waktu_mulai']}-{$booking['waktu_selesai']} ke {$new_date} {$new_start_time}-{$new_end_time}";

        // Try to log, but don't fail if logging fails
        try {
            $logStmt->execute([$_SESSION['admin_id'], 'reschedule', $booking_id, $logDetails]);
        } catch (Exception $e) {
            // Logging failed, but reschedule was successful
        }

        echo json_encode([
            'success' => true,
            'message' => 'Booking berhasil di-reschedule',
            'new_date' => $new_date,
            'new_time' => "$new_start_time - $new_end_time"
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal update database']);
    }

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
