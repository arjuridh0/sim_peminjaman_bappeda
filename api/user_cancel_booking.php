<?php
require_once '../includes/functions.php';

header('Content-Type: application/json');

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

// Fallback to POST data if JSON is not provided
if (!$input) {
    $input = $_POST;
}

// Validate required fields
if (empty($input['booking_id']) || empty($input['qr_token'])) {
    echo json_encode(['success' => false, 'message' => 'Booking ID dan Kode Booking wajib diisi']);
    exit;
}

$bookingId = $input['booking_id'];
$qrToken = trim($input['qr_token']);

global $pdo;

try {
    // Get booking details
    $stmt = $pdo->prepare("SELECT * FROM bookings WHERE id = ?");
    $stmt->execute([$bookingId]);
    $booking = $stmt->fetch();

    if (!$booking) {
        echo json_encode(['success' => false, 'message' => 'Booking tidak ditemukan']);
        exit;
    }

    // Verify QR Token
    if ($booking['qr_token'] !== $qrToken) {
        echo json_encode(['success' => false, 'message' => 'Kode Booking tidak valid']);
        exit;
    }

    // Check if already cancelled
    if ($booking['status'] === 'dibatalkan') {
        echo json_encode(['success' => false, 'message' => 'Booking sudah dibatalkan sebelumnya']);
        exit;
    }

    // Update booking status to cancelled
    $updateStmt = $pdo->prepare("UPDATE bookings SET status = 'dibatalkan', cancel_reason = 'Dibatalkan oleh user' WHERE id = ?");
    $updateStmt->execute([$bookingId]);

    // If this is a recurring booking (parent), cancel all children too
    if ($booking['is_recurring'] == 1 && $booking['parent_booking_id'] === null) {
        $cancelChildrenStmt = $pdo->prepare("UPDATE bookings SET status = 'dibatalkan', cancel_reason = 'Dibatalkan oleh user (series)' WHERE parent_booking_id = ?");
        $cancelChildrenStmt->execute([$bookingId]);
    }

    echo json_encode([
        'success' => true,
        'message' => 'Booking berhasil dibatalkan',
        'booking_code' => $booking['qr_token']
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()]);
}
