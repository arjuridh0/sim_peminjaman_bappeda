<?php
// api/admin_create_booking.php
require_once '../includes/functions.php';

// Check if user is logged in as admin
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit;
}

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Read JSON input
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    if (empty($data['room_id']) || empty($data['tanggal']) || empty($data['waktu_mulai']) || empty($data['waktu_selesai'])) {
        echo json_encode(['success' => false, 'message' => 'Data tidak lengkap.']);
        exit;
    }

    // Check Conflict
    if (check_booking_conflict($data['room_id'], $data['tanggal'], $data['waktu_mulai'], $data['waktu_selesai'])) {
        echo json_encode(['success' => false, 'message' => 'Ruangan sudah dipesan pada waktu tersebut.']);
        exit;
    }

    // Prepare data for creation
    $bookingData = [
        'room_id' => $data['room_id'],
        'nama_peminjam' => $data['nama_peminjam'],
        'instansi' => $data['instansi'],
        'divisi' => $data['divisi'] ?? '-',
        'kegiatan' => $data['kegiatan'],
        'jumlah_peserta' => $data['jumlah_peserta'],
        'tanggal' => $data['tanggal'],
        'waktu_mulai' => $data['waktu_mulai'],
        'waktu_selesai' => $data['waktu_selesai'],
        'user_email' => null, // Optional for admin
        'phone_number' => null, // Optional for admin
        'file_pendukung' => null // Optional for admin
    ];

    try {
        $result = create_admin_booking($bookingData);
        if ($result) {
            echo json_encode([
                'success' => true,
                'message' => 'Booking berhasil dibuat.',
                'booking_code' => $result['qr_token']
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Gagal membuat booking.']);
        }
    } catch (Exception $e) {
        error_log($e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan sistem.']);
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
