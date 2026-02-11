<?php
// api/admin_update_booking_status.php
require_once '../includes/functions.php';

// Check if user is logged in as admin
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit;
}

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    if (empty($data['booking_id']) || empty($data['action'])) {
        echo json_encode(['success' => false, 'message' => 'Data tidak lengkap.']);
        exit;
    }

    $bookingId = $data['booking_id'];
    $action = $data['action'];
    $reason = $data['reason'] ?? null;

    $applyToSeries = $data['apply_to_series'] ?? false;

    try {
        $booking = get_booking_by_id($bookingId);
        if (!$booking) {
            echo json_encode(['success' => false, 'message' => 'Booking not found.']);
            exit;
        }

        // Determine affected IDs
        // Determine affected bookings (IDs + Details)
        $affectedBookings = [];
        if ($applyToSeries && ($booking['parent_booking_id'] || $booking['is_recurring'])) {
            $parentId = $booking['parent_booking_id'] ?: $booking['id'];

            // Get all bookings in the series
            $stmtSeries = $pdo->prepare("SELECT id, room_id, tanggal, waktu_mulai, waktu_selesai FROM bookings WHERE id = ? OR parent_booking_id = ?");
            $stmtSeries->execute([$parentId, $parentId]);
            $affectedBookings = $stmtSeries->fetchAll(PDO::FETCH_ASSOC);
        } else {
            // Just the single booking
            $affectedBookings[] = [
                'id' => $booking['id'],
                'room_id' => $booking['room_id'],
                'tanggal' => $booking['tanggal'],
                'waktu_mulai' => $booking['waktu_mulai'],
                'waktu_selesai' => $booking['waktu_selesai']
            ];
        }

        // VALIDATION STEP: Check for conflicts before approving
        if ($action === 'approve') {
            foreach ($affectedBookings as $b) {
                // Check against ALREADY APPROVED bookings only.
                // This allows resolving conflicts between two 'menunggu' bookings by approving one.
                if (check_booking_conflict($b['room_id'], $b['tanggal'], $b['waktu_mulai'], $b['waktu_selesai'], $b['id'], ['disetujui'])) {
                    echo json_encode(['success' => false, 'message' => "Gagal: Terdapat jadwal yang SUDAH DISETUJUI dan bentrok pada tanggal " . date('d/m/Y', strtotime($b['tanggal']))]);
                    exit;
                }
            }
        }

        $successCount = 0;
        foreach ($affectedBookings as $b) {
            $id = $b['id'];
            if ($action === 'approve') {
                update_booking_status($id, 'disetujui');
                $successCount++;
            } elseif ($action === 'reject') {
                update_booking_status($id, 'ditolak', $reason);
                $successCount++;
            } elseif ($action === 'cancel') {
                $cancelReason = $reason ?? "Dibatalkan oleh Admin";
                update_booking_status($id, 'dibatalkan', $cancelReason);
                $successCount++;
            }
        }

        // Send Notification ONLY ONCE (for the triggered booking) to avoid spam
        // Wrapped in try-catch to prevent blocking the success response
        if ($action === 'approve') {
            try {
                send_approval_notification($booking);
            } catch (Exception $e) {
                error_log("Approval notification failed: " . $e->getMessage());
            }
            $msg = $applyToSeries ? "Seluruh seri booking berhasil disetujui." : "Booking berhasil disetujui.";
            echo json_encode(['success' => true, 'message' => $msg]);

        } elseif ($action === 'reject') {
            try {
                send_rejection_notification($booking);
            } catch (Exception $e) {
                error_log("Rejection notification failed: " . $e->getMessage());
            }
            $msg = $applyToSeries ? "Seluruh seri booking telah ditolak." : "Booking telah ditolak.";
            echo json_encode(['success' => true, 'message' => $msg]);

        } elseif ($action === 'cancel') {
            $msg = $applyToSeries ? "Seluruh seri booking berhasil dibatalkan." : "Booking berhasil dibatalkan.";
            echo json_encode(['success' => true, 'message' => $msg]);

        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid action.']);
        }

    } catch (Exception $e) {
        error_log($e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan sistem.']);
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
}
