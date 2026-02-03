<?php
/**
 * Enhanced Export Calendar to PDF
 * Supports Monthly and Yearly reports with Statistics
 */

require_once '../config/database.php';
require_once '../includes/functions.php';

// Check if Composer autoload exists
if (file_exists('../vendor/autoload.php')) {
    require_once '../vendor/autoload.php';
} else {
    die(json_encode(['error' => 'TCPDF library not installed. Run: composer require tecnickcom/tcpdf']));
}

// Get parameters
$scope = $_GET['scope'] ?? 'month'; // 'month' or 'year'
$roomId = $_GET['room_id'] ?? null;

// Determine Date Range
if ($scope === 'year') {
    $year = $_GET['year'] ?? date('Y');
    $startDate = "$year-01-01";
    $endDate = "$year-12-31";
    $periodLabel = "Tahun $year";
    $filenameSuffix = "Tahun_$year";
} else {
    $monthParam = $_GET['month'] ?? date('Y-m');
    $dateObj = new DateTime($monthParam . '-01');
    $startDate = $dateObj->format('Y-m-01');
    $endDate = $dateObj->format('Y-m-t');
    $periodLabel = $dateObj->format('F Y');
    $filenameSuffix = $dateObj->format('F_Y');
}

// Get room name
$roomName = 'Semua Ruangan';
if ($roomId) {
    $stmt = $pdo->prepare("SELECT name FROM rooms WHERE id = ?");
    $stmt->execute([$roomId]);
    $room = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($room) {
        $roomName = $room['name'];
    }
}

// --- DATA FETCHING ---

// 1. Fetch Events
$query = "SELECT b.*, r.name as room_name 
          FROM bookings b 
          JOIN rooms r ON b.room_id = r.id 
          WHERE b.tanggal >= ? AND b.tanggal <= ?";
$params = [$startDate, $endDate];

if ($roomId) {
    $query .= " AND b.room_id = ?";
    $params[] = $roomId;
}

$query .= " ORDER BY b.tanggal ASC, b.waktu_mulai ASC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$events = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 2. Calculate Statistics
$totalBookings = count($events);
$statusCounts = ['disetujui' => 0, 'menunggu' => 0, 'ditolak' => 0, 'dibatalkan' => 0];
$roomCounts = [];

foreach ($events as $e) {
    // Status Count
    if (isset($statusCounts[$e['status']])) {
        $statusCounts[$e['status']]++;
    }

    // Room Popularity (Only count approved)
    if ($e['status'] === 'disetujui') {
        if (!isset($roomCounts[$e['room_name']])) {
            $roomCounts[$e['room_name']] = 0;
        }
        $roomCounts[$e['room_name']]++;
    }
}

// Determine Favorite Room
$favoriteRoom = '-';
if (!empty($roomCounts)) {
    $favoriteRoom = array_search(max($roomCounts), $roomCounts);
    $favoriteRoom .= " (" . max($roomCounts) . " booking)";
}

// --- PDF GENERATION ---
$pdf = new TCPDF('L', 'mm', 'A4', true, 'UTF-8', false);

// Document Info
$pdf->SetCreator('BAPPEDA Jawa Tengah');
$pdf->SetAuthor('System Admin');
$pdf->SetTitle("Laporan Peminjaman - $periodLabel");
$pdf->SetMargins(15, 15, 15);
$pdf->SetAutoPageBreak(true, 15);
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->AddPage();

// 1. HEADER
$pdf->SetFont('helvetica', 'B', 16);
$pdf->Cell(0, 8, 'LAPORAN PEMINJAMAN RUANGAN', 0, 1, 'C');
$pdf->SetFont('helvetica', '', 12);
$pdf->Cell(0, 6, 'BAPPEDA PROVINSI JAWA TENGAH', 0, 1, 'C');
$pdf->Ln(2);
$pdf->SetFont('helvetica', 'B', 11);
$pdf->Cell(0, 6, "Periode: $periodLabel", 0, 1, 'C');
$pdf->SetFont('helvetica', 'I', 10);
$pdf->Cell(0, 6, "Filter Ruangan: $roomName", 0, 1, 'C');
$pdf->Ln(5);

// 2. STATISTICS BOX
$pdf->SetFillColor(245, 247, 250); // Light gray/blue
$pdf->SetDrawColor(200, 200, 200);
$pdf->Rect(15, $pdf->GetY(), 267, 25, 'DF'); // Box background

$pdf->SetY($pdf->GetY() + 3);
$pdf->SetFont('helvetica', 'B', 10);

// Column 1: Total
$pdf->SetX(20);
$pdf->Cell(40, 5, 'TOTAL BOOKING', 0, 1);
$pdf->SetX(20);
$pdf->SetFont('helvetica', 'B', 14);
$pdf->Cell(40, 8, $totalBookings, 0, 0);

// Column 2: Approved
$pdf->SetY($pdf->GetY() - 5);
$pdf->SetX(70);
$pdf->SetFont('helvetica', 'B', 10);
$pdf->SetTextColor(34, 197, 94); // Green
$pdf->Cell(40, 5, 'DISETUJUI', 0, 1);
$pdf->SetX(70);
$pdf->SetFont('helvetica', 'B', 14);
$pdf->Cell(40, 8, $statusCounts['disetujui'], 0, 0);

// Column 3: Rejected/Cancelled
$pdf->SetY($pdf->GetY() - 5);
$pdf->SetX(120);
$pdf->SetFont('helvetica', 'B', 10);
$pdf->SetTextColor(239, 68, 68); // Red
$pdf->Cell(50, 5, 'DITOLAK / BATAL', 0, 1);
$pdf->SetX(120);
$pdf->SetFont('helvetica', 'B', 14);
$rejectedCount = $statusCounts['ditolak'] + $statusCounts['dibatalkan'];
$pdf->Cell(50, 8, $rejectedCount, 0, 0);

// Column 4: Favorite Room
$pdf->SetY($pdf->GetY() - 5);
$pdf->SetX(180);
$pdf->SetFont('helvetica', 'B', 10);
$pdf->SetTextColor(0, 0, 0); // Black
$pdf->Cell(80, 5, 'RUANGAN TERFAVORIT', 0, 1);
$pdf->SetX(180);
$pdf->SetFont('helvetica', '', 11);
$pdf->Cell(80, 8, substr($favoriteRoom, 0, 40), 0, 0);

$pdf->Ln(15); // Move down past the box

// 3. TABLE DATA
if ($totalBookings > 0) {
    $pdf->SetFont('helvetica', 'B', 9);
    $pdf->SetFillColor(59, 130, 246); // Blue Header
    $pdf->SetTextColor(255, 255, 255); // White Text

    // Header
    $h = 8;
    $pdf->Cell(25, $h, 'TANGGAL', 1, 0, 'C', true);
    $pdf->Cell(25, $h, 'WAKTU', 1, 0, 'C', true);
    $pdf->Cell(50, $h, 'RUANGAN', 1, 0, 'L', true);
    $pdf->Cell(50, $h, 'PEMINJAM', 1, 0, 'L', true);
    $pdf->Cell(75, $h, 'KEGIATAN', 1, 0, 'L', true);
    $pdf->Cell(42, $h, 'STATUS', 1, 1, 'C', true);

    // Rows
    $pdf->SetFont('helvetica', '', 9);
    $pdf->SetTextColor(0, 0, 0);

    $fill = false;
    foreach ($events as $e) {
        $pdf->SetFillColor(240, 245, 255); // Alternate row color

        // Height calculation for multi-line support could go here, but using single line for speed

        $tanggal = date('d/m/Y', strtotime($e['tanggal']));
        $waktu = substr($e['waktu_mulai'], 0, 5) . '-' . substr($e['waktu_selesai'], 0, 5);

        // Status Formatting
        $statusStr = ucfirst($e['status']);

        $pdf->Cell(25, 7, $tanggal, 1, 0, 'C', $fill);
        $pdf->Cell(25, 7, $waktu, 1, 0, 'C', $fill);
        $pdf->Cell(50, 7, substr($e['room_name'], 0, 28), 1, 0, 'L', $fill);
        $pdf->Cell(50, 7, substr($e['nama_peminjam'], 0, 28), 1, 0, 'L', $fill);
        $pdf->Cell(75, 7, substr($e['kegiatan'], 0, 40), 1, 0, 'L', $fill);
        $pdf->Cell(42, 7, $statusStr, 1, 1, 'C', $fill);

        $fill = !$fill;
    }

} else {
    $pdf->Ln(10);
    $pdf->SetFont('helvetica', 'I', 11);
    $pdf->Cell(0, 10, 'Tidak ada data peminjaman untuk periode ini.', 0, 1, 'C');
}

// Footer Timestamp
$pdf->SetY(-15);
$pdf->SetFont('helvetica', 'I', 8);
$pdf->Cell(0, 10, 'Dicetak pada: ' . date('d F Y H:i:s'), 0, 0, 'R');

// Output
$filename = "Laporan_{$filenameSuffix}.pdf";
$pdf->Output($filename, 'D');
