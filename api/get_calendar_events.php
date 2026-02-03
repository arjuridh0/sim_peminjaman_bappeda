<?php
// API endpoint untuk FullCalendar
require_once '../includes/functions.php';

header('Content-Type: application/json');

// Get parameters
$start = $_GET['start'] ?? null;
$end = $_GET['end'] ?? null;
$roomId = $_GET['room_id'] ?? null;
$statuses = $_GET['statuses'] ?? null; // Comma-separated status values

if (!$start || !$end) {
    echo json_encode(['error' => 'Missing required parameters']);
    exit;
}

// Get bookings from database
global $pdo;

$sql = "
    SELECT 
        b.*,
        r.name as room_name,
        rp.recurrence_type,
        rp.end_date as recurrence_end_date,
        parent.tanggal as series_start_date
    FROM bookings b
    JOIN rooms r ON b.room_id = r.id
    LEFT JOIN bookings parent ON b.parent_booking_id = parent.id
    LEFT JOIN recurring_patterns rp ON (
        (b.parent_booking_id IS NOT NULL AND rp.booking_id = b.parent_booking_id) 
        OR 
        (b.parent_booking_id IS NULL AND b.is_recurring = 1 AND rp.booking_id = b.id)
    )
    WHERE b.tanggal BETWEEN ? AND ?
";

$params = [$start, $end];

// Add room filter if specified
if ($roomId) {
    $sql .= " AND b.room_id = ?";
    $params[] = $roomId;
}

// Add status filter if specified (for admin calendar)
if ($statuses) {
    $statusArray = explode(',', $statuses);
    $placeholders = str_repeat('?,', count($statusArray) - 1) . '?';
    $sql .= " AND b.status IN ($placeholders)";
    $params = array_merge($params, $statusArray);
}

$sql .= " ORDER BY b.tanggal ASC, b.waktu_mulai ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$bookings = $stmt->fetchAll();

// Format events for FullCalendar
$events = [];

foreach ($bookings as $booking) {
    // Determine color based on status
    $colors = [
        'menunggu' => '#EAB308',   // Yellow
        'disetujui' => '#22C55E',  // Green
        'ditolak' => '#EF4444',    // Red
        'dibatalkan' => '#9CA3AF'  // Gray
    ];

    $color = $colors[$booking['status']] ?? '#6B7280';

    // Check for conflicts (overlapping approved bookings in same room)
    $hasConflict = false;
    $conflictDetails = [];

    if ($booking['status'] === 'disetujui' || $booking['status'] === 'menunggu') {
        $conflictSql = "
            SELECT b.id, b.kegiatan, b.waktu_mulai, b.waktu_selesai, b.nama_peminjam
            FROM bookings b
            WHERE b.room_id = ?
            AND b.id < ?  -- Check only against OLDER bookings (so only the NEW one gets flagged)
            AND b.status IN ('disetujui', 'menunggu')
            AND b.tanggal = ?
            AND (
                (b.waktu_mulai < ? AND b.waktu_selesai > ?)
            )
        ";

        $conflictStmt = $pdo->prepare($conflictSql);
        $conflictStmt->execute([
            $booking['room_id'],
            $booking['id'], // Current ID
            $booking['tanggal'],
            $booking['waktu_selesai'],
            $booking['waktu_mulai']
        ]);

        $conflicts = $conflictStmt->fetchAll();

        if (count($conflicts) > 0) {
            $hasConflict = true;
            foreach ($conflicts as $conflict) {
                $conflictDetails[] = [
                    'id' => $conflict['id'],
                    'kegiatan' => $conflict['kegiatan'],
                    'waktu' => date('H:i', strtotime($conflict['waktu_mulai'])) . ' - ' . date('H:i', strtotime($conflict['waktu_selesai'])),
                    'peminjam' => $conflict['nama_peminjam']
                ];
            }

            // Change style to indicate conflict (User request: RED TEXT for the latest one)
            // We keep the background readable but make it distinct
            $color = '#FEF2F2'; // Light Red background
            $borderColor = '#DC2626'; // Red border
            $textColor = '#DC2626'; // Red text
        } else {
            $borderColor = $color;
            $textColor = '#FFFFFF';
        }
    } else {
        $borderColor = $color;
        $textColor = '#FFFFFF';
    }

    // Create event object
    $event = [
        'id' => $booking['id'],
        'title' => $booking['room_name'],
        'start' => $booking['tanggal'] . 'T' . $booking['waktu_mulai'],
        'end' => $booking['tanggal'] . 'T' . $booking['waktu_selesai'],
        'backgroundColor' => $color,
        'borderColor' => $borderColor,
        'borderWidth' => $hasConflict ? '2' : '1',
        'textColor' => $textColor,
        'extendedProps' => [
            'id' => $booking['id'],
            'status' => $booking['status'],
            'room_name' => $booking['room_name'],
            'nama_peminjam' => $booking['nama_peminjam'],
            'instansi' => $booking['instansi'],
            'divisi' => $booking['divisi'],
            'kegiatan' => $booking['kegiatan'],
            'jumlah_peserta' => $booking['jumlah_peserta'],
            'tanggal_formatted' => date('d F Y', strtotime($booking['tanggal'])),
            'waktu' => date('H:i', strtotime($booking['waktu_mulai'])) . ' - ' . date('H:i', strtotime($booking['waktu_selesai'])),
            'qr_token' => $booking['qr_token'],
            'rejection_reason' => $booking['rejection_reason'],
            'cancel_reason' => $booking['cancel_reason'],
            'tooltip' => $booking['kegiatan'] . ' - ' . $booking['nama_peminjam'],
            'hasConflict' => $hasConflict,
            'conflictDetails' => $conflictDetails,
            'recurrence_type' => $booking['recurrence_type'],
            'series_start_date' => $booking['series_start_date'] ? date('d F Y', strtotime($booking['series_start_date'])) : date('d F Y', strtotime($booking['tanggal'])), // Fallback to own date if parent
            'recurrence_end_date' => $booking['recurrence_end_date'] ? date('d F Y', strtotime($booking['recurrence_end_date'])) : null
        ]
    ];

    $events[] = $event;
}

echo json_encode($events);
