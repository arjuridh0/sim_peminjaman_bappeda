<?php
require_once '../includes/functions.php';

header('Content-Type: application/json');

// Check if there's a new booking token in session
if (isset($_SESSION['new_booking_token'])) {
    $token = $_SESSION['new_booking_token'];

    // Clear the session
    unset($_SESSION['new_booking_token']);
    unset($_SESSION['booking_id']);

    echo json_encode([
        'success' => true,
        'booking_code' => $token
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'No booking found'
    ]);
}
