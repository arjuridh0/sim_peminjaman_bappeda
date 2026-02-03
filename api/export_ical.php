<?php
/**
 * Export Calendar to iCal (.ics) format
 * Compatible with Google Calendar, Outlook, Apple Calendar
 */

require_once '../config/database.php';
require_once '../includes/functions.php';

// Get parameters
$start_date = $_GET['start'] ?? date('Y-m-01');
$end_date = $_GET['end'] ?? date('Y-m-t');
$room_id = $_GET['room_id'] ?? null;

// Get events
// $pdo is defined in config/database.php
$query = "SELECT b.*, r.name as room_name 
          FROM bookings b 
          JOIN rooms r ON b.room_id = r.id 
          WHERE b.tanggal >= ? AND b.tanggal <= ?";

$params = [$start_date, $end_date];

if ($room_id) {
    $query .= " AND b.room_id = ?";
    $params[] = $room_id;
}

// Only export approved bookings
$query .= " AND b.status = 'disetujui' ORDER BY b.tanggal, b.waktu_mulai";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$events = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Generate iCal content
$ical = "BEGIN:VCALENDAR\r\n";
$ical .= "VERSION:2.0\r\n";
$ical .= "PRODID:-//BAPPEDA Jawa Tengah//Sistem Peminjaman Ruangan//ID\r\n";
$ical .= "CALSCALE:GREGORIAN\r\n";
$ical .= "METHOD:PUBLISH\r\n";
$ical .= "X-WR-CALNAME:Peminjaman Ruangan BAPPEDA\r\n";
$ical .= "X-WR-TIMEZONE:Asia/Jakarta\r\n";
$ical .= "X-WR-CALDESC:Jadwal Peminjaman Ruangan BAPPEDA Provinsi Jawa Tengah\r\n";

// Add timezone definition
$ical .= "BEGIN:VTIMEZONE\r\n";
$ical .= "TZID:Asia/Jakarta\r\n";
$ical .= "BEGIN:STANDARD\r\n";
$ical .= "DTSTART:19700101T000000\r\n";
$ical .= "TZOFFSETFROM:+0700\r\n";
$ical .= "TZOFFSETTO:+0700\r\n";
$ical .= "TZNAME:WIB\r\n";
$ical .= "END:STANDARD\r\n";
$ical .= "END:VTIMEZONE\r\n";

// Add events
foreach ($events as $event) {
    // Create unique ID
    $uid = 'booking-' . $event['id'] . '@bappeda-jateng.go.id';

    // Combine date and time
    $start_datetime = $event['tanggal'] . ' ' . $event['waktu_mulai'];
    $end_datetime = $event['tanggal'] . ' ' . $event['waktu_selesai'];

    // Format for iCal (YYYYMMDDTHHMMSS)
    $dtstart = date('Ymd\THis', strtotime($start_datetime));
    $dtend = date('Ymd\THis', strtotime($end_datetime));
    $dtstamp = date('Ymd\THis', strtotime($event['created_at']));

    // Event title
    $summary = $event['room_name'] . ' - ' . $event['kegiatan'];

    // Description
    $description = "Peminjam: " . $event['nama_peminjam'] . "\\n";
    $description .= "Instansi: " . $event['instansi'];
    if ($event['divisi']) {
        $description .= " / " . $event['divisi'];
    }
    $description .= "\\n";
    $description .= "Jumlah Peserta: " . $event['jumlah_peserta'] . " orang\\n";
    $description .= "Kode Booking: " . $event['qr_token'];

    // Location
    $location = $event['room_name'] . ', BAPPEDA Provinsi Jawa Tengah';

    // Build VEVENT
    $ical .= "BEGIN:VEVENT\r\n";
    $ical .= "UID:" . $uid . "\r\n";
    $ical .= "DTSTAMP:" . $dtstamp . "\r\n";
    $ical .= "DTSTART;TZID=Asia/Jakarta:" . $dtstart . "\r\n";
    $ical .= "DTEND;TZID=Asia/Jakarta:" . $dtend . "\r\n";
    $ical .= "SUMMARY:" . escapeIcalText($summary) . "\r\n";
    $ical .= "DESCRIPTION:" . escapeIcalText($description) . "\r\n";
    $ical .= "LOCATION:" . escapeIcalText($location) . "\r\n";
    $ical .= "STATUS:CONFIRMED\r\n";
    $ical .= "SEQUENCE:0\r\n";
    $ical .= "TRANSP:OPAQUE\r\n";

    // Add organizer
    $ical .= "ORGANIZER;CN=BAPPEDA Jawa Tengah:mailto:bappeda@jatengprov.go.id\r\n";

    // Add attendee (borrower)
    if (!empty($event['email'])) {
        $ical .= "ATTENDEE;CN=" . escapeIcalText($event['nama_peminjam']) . ";ROLE=REQ-PARTICIPANT:mailto:" . $event['email'] . "\r\n";
    }

    $ical .= "END:VEVENT\r\n";
}

$ical .= "END:VCALENDAR\r\n";

// Helper function to escape iCal text
function escapeIcalText($text)
{
    $text = str_replace('\\', '\\\\', $text);
    $text = str_replace(',', '\\,', $text);
    $text = str_replace(';', '\\;', $text);
    $text = str_replace("\n", '\\n', $text);
    return $text;
}

// Set headers for download
$filename = 'BAPPEDA_Kalender_' . date('Y-m-d') . '.ics';
header('Content-Type: text/calendar; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: no-cache, must-revalidate');
header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');

// Output iCal
echo $ical;
