<?php
// includes/functions.php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/email.php';
require_once __DIR__ . '/email_templates.php';

// Load Composer autoloader for PHPMailer
require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Start Session with Security Enhancements
if (session_status() === PHP_SESSION_NONE) {
    // Set secure session cookie parameters
    session_set_cookie_params([
        'lifetime' => 0, // Session cookie (expires when browser closes)
        'path' => '/',
        'domain' => $_SERVER['HTTP_HOST'] ?? '',
        'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on', // HTTPS only if available
        'httponly' => true, // Prevent JavaScript access
        'samesite' => 'Strict' // CSRF protection
    ]);

    session_start();
    date_default_timezone_set('Asia/Jakarta');

    // Session timeout (30 minutes of inactivity)
    $timeout_duration = 1800; // 30 minutes in seconds

    if (isset($_SESSION['LAST_ACTIVITY'])) {
        if (time() - $_SESSION['LAST_ACTIVITY'] > $timeout_duration) {
            // Session expired
            session_unset();
            session_destroy();
            session_start();
            $_SESSION['session_expired'] = true;
        }
    }

    $_SESSION['LAST_ACTIVITY'] = time(); // Update last activity time

    // Regenerate session ID periodically (every 30 minutes)
    if (!isset($_SESSION['CREATED'])) {
        $_SESSION['CREATED'] = time();
    } else if (time() - $_SESSION['CREATED'] > 1800) {
        session_regenerate_id(true);
        $_SESSION['CREATED'] = time();
    }
}

// ===================================
// HELPER FUNCTIONS
// ===================================


// Helper: Base URL
function base_url($path = '')
{
    // Deteksi otomatis base URL - ALWAYS dari root project
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
    $host = $_SERVER['HTTP_HOST'];

    // Dapatkan path ke root project (bukan current script directory)
    $scriptPath = str_replace('\\', '/', $_SERVER['SCRIPT_NAME']);

    // Cari posisi project folder
    $projectFolder = defined('PROJECT_FOLDER') ? PROJECT_FOLDER : 'sim_peminjaman_bappeda';

    if (preg_match('#^(.*?/' . preg_quote($projectFolder, '#') . ')/#', $scriptPath, $matches)) {
        $projectPath = $matches[1];
    } else {
        // Fallback: ambil dirname dari SCRIPT_NAME
        $projectPath = dirname($scriptPath);
        // Kalau ada /admin atau /includes, naik satu level
        while (preg_match('#/(admin|includes|api|cron)$#', $projectPath)) {
            $projectPath = dirname($projectPath);
        }
    }

    $baseUrl = "$protocol://$host$projectPath";

    return $path ? "$baseUrl/" . ltrim($path, '/') : $baseUrl;
}

// Helper: Flash Message (Set)
function set_flash_message($type, $message)
{
    $_SESSION[$type] = $message;
}

// Helper: Redirect
function redirect($path)
{
    // Jika path dimulai dengan '/', anggap absolute dari root project
    // Jika tidak, anggap relative dari current directory
    if (strpos($path, '/') === 0) {
        // Absolute path dari root project
        header("Location: " . base_url($path));
    } elseif (strpos($path, 'http://') === 0 || strpos($path, 'https://') === 0) {
        // Full URL
        header("Location: " . $path);
    } else {
        // Relative path - redirect relative to current script location
        header("Location: " . $path);
    }
    exit;
}

// ==============================================================================
// SECURITY FUNCTIONS
// ==============================================================================

/**
 * Generate CSRF Token
 * Creates a unique token for form submissions
 */
function generate_csrf_token()
{
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF Token
 * Validates token from form submission
 */
function verify_csrf_token($token)
{
    if (!isset($_SESSION['csrf_token'])) {
        return false;
    }

    // Use hash_equals to prevent timing attacks
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Get CSRF Token Input Field
 * Returns HTML input field for forms
 */
function csrf_field()
{
    $token = generate_csrf_token();
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token) . '">';
}

/**
 * Sanitize Input
 * Clean user input to prevent XSS
 */
function sanitize_input($data)
{
    if (is_array($data)) {
        return array_map('sanitize_input', $data);
    }

    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

// ==============================================================================
// EMAIL FUNCTIONS
// ==============================================================================

/**
 * Send Email using PHPMailer
 * 
 * @param string $to Recipient email address
 * @param string $subject Email subject
 * @param string $htmlBody HTML email body
 * @param string $textBody Plain text alternative (optional)
 * @return bool Success status
 */
function send_email($to, $subject, $htmlBody, $textBody = '')
{
    try {
        $mail = new PHPMailer(true);

        // Server settings
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->SMTPAuth = SMTP_AUTH;
        $mail->Username = SMTP_USERNAME;
        $mail->Password = SMTP_PASSWORD;
        $mail->SMTPSecure = SMTP_ENCRYPTION;
        $mail->Port = SMTP_PORT;
        $mail->CharSet = EMAIL_CHARSET;

        // Debug mode (0 = off, 1 = client, 2 = client + server)
        $mail->SMTPDebug = EMAIL_DEBUG;

        // Recipients
        $mail->setFrom(EMAIL_FROM_ADDRESS, EMAIL_FROM_NAME);
        $mail->addAddress($to);
        $mail->addReplyTo(EMAIL_REPLY_TO, EMAIL_FROM_NAME);

        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $htmlBody;
        $mail->AltBody = $textBody ?: strip_tags($htmlBody);

        // Send email
        $mail->send();

        // Log success (optional)
        error_log("Email sent successfully to: $to - Subject: $subject");

        return true;

    } catch (Exception $e) {
        // Log error
        error_log("Email sending failed to: $to - Error: {$mail->ErrorInfo}");
        return false;
    }
}

/**
 * Send New Booking Notification to Admin
 * 
 * @param array $booking Booking data with room info
 * @return bool Success status
 */
function send_booking_notification_to_admin($booking)
{
    // Get booking with room name if not already included
    if (!isset($booking['room_name'])) {
        $room = get_room_by_id($booking['room_id']);
        $booking['room_name'] = $room['name'] ?? 'Unknown Room';
    }

    $subject = '📋 Peminjaman Ruangan Baru - ' . $booking['room_name'];
    $htmlBody = template_new_booking_admin($booking);
    $textBody = get_plain_text_version($booking, 'new_booking');

    return send_email(ADMIN_EMAIL, $subject, $htmlBody, $textBody);
}

/**
 * Send Booking Confirmation to User
 * Sent immediately after user creates a booking
 * 
 * @param array $booking Booking data
 * @return bool Success status
 */
function send_booking_confirmation($booking)
{
    // Get booking with room name if not already included
    if (!isset($booking['room_name'])) {
        $room = get_room_by_id($booking['room_id']);
        $booking['room_name'] = $room['name'] ?? 'Unknown Room';
    }

    $subject = '✅ Konfirmasi Peminjaman - ' . $booking['room_name'];
    $htmlBody = template_booking_confirmation($booking);
    $textBody = get_plain_text_version($booking, 'confirmation');

    // Get user email
    $userEmail = $booking['email'] ?? null;
    if (!$userEmail) {
        error_log("Cannot send confirmation: No email found for booking ID " . $booking['id']);
        return false;
    }

    return send_email($userEmail, $subject, $htmlBody, $textBody);
}

/**
 * Send Approval Notification to User
 * 
 * @param array $booking Booking data
 * @return bool Success status
 */
function send_approval_notification($booking)
{
    // Skip if no email provided
    if (empty($booking['user_email'])) {
        error_log("Approval notification skipped - no email for booking ID: " . $booking['id']);
        return false;
    }

    $subject = '✅ Peminjaman Ruangan Disetujui - ' . $booking['room_name'];
    $htmlBody = template_booking_approved($booking);
    $textBody = get_plain_text_version($booking, 'approved');

    return send_email($booking['user_email'], $subject, $htmlBody, $textBody);
}

/**
 * Send Rejection Notification to User
 * 
 * @param array $booking Booking data with rejection reason
 * @return bool Success status
 */
function send_rejection_notification($booking)
{
    // Skip if no email provided
    if (empty($booking['user_email'])) {
        error_log("Rejection notification skipped - no email for booking ID: " . $booking['id']);
        return false;
    }

    $subject = '❌ Peminjaman Ruangan Ditolak - ' . $booking['room_name'];
    $htmlBody = template_booking_rejected($booking);
    $textBody = get_plain_text_version($booking, 'rejected');

    return send_email($booking['user_email'], $subject, $htmlBody, $textBody);
}

/**
 * Send H-1 Reminder Notification to User
 * 
 * @param array $booking Booking data
 * @return bool Success status
 */
function send_reminder_notification($booking)
{
    // Skip if no email provided
    if (empty($booking['user_email'])) {
        error_log("Reminder notification skipped - no email for booking ID: " . $booking['id']);
        return false;
    }

    $subject = '🔔 Pengingat: Peminjaman Besok - ' . $booking['room_name'];
    $htmlBody = template_booking_reminder($booking);
    $textBody = get_plain_text_version($booking, 'reminder');

    return send_email($booking['user_email'], $subject, $htmlBody, $textBody);
}


// ==============================================================================
// DATABASE FUNCTIONS (Pengganti Models)
// ==============================================================================

// ROOM FUNCTIONS
function get_all_rooms()
{
    global $pdo;
    $stmt = $pdo->query("SELECT * FROM rooms ORDER BY name ASC");
    return $stmt->fetchAll();
}

function get_room_by_id($id)
{
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM rooms WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function search_rooms($filters = [])
{
    global $pdo;
    $sql = "SELECT * FROM rooms WHERE 1=1";
    $params = [];

    if (!empty($filters['capacity'])) {
        $sql .= " AND capacity >= ?";
        $params[] = $filters['capacity'];
    }

    if (!empty($filters['search'])) {
        $sql .= " AND (name LIKE ? OR description LIKE ?)";
        $term = '%' . $filters['search'] . '%';
        $params[] = $term;
        $params[] = $term;
    }

    $sql .= " ORDER BY name ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function get_available_rooms($date, $startTime, $endTime)
{
    global $pdo;
    $sql = "
        SELECT r.* FROM rooms r
        WHERE r.id NOT IN (
            SELECT b.room_id FROM bookings b
            WHERE b.tanggal = ?
            AND b.status IN ('menunggu', 'disetujui')
            AND (
                (b.waktu_mulai < ? AND b.waktu_selesai > ?)
                OR (b.waktu_mulai >= ? AND b.waktu_mulai < ?)
            )
        )
        ORDER BY r.name ASC
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$date, $endTime, $startTime, $startTime, $endTime]);
    return $stmt->fetchAll();
}

// BOOKING FUNCTIONS
function get_approved_bookings_home($limit = null, $offset = 0)
{
    global $pdo;
    $sql = "
        SELECT b.*, r.name as room_name 
        FROM bookings b
        JOIN rooms r ON b.room_id = r.id
        WHERE b.status = 'disetujui'
        AND b.tanggal >= CURDATE()
        ORDER BY b.tanggal ASC, b.waktu_mulai ASC
    ";

    if ($limit !== null) {
        $sql .= " LIMIT " . (int) $limit . " OFFSET " . (int) $offset;
    }

    return $pdo->query($sql)->fetchAll();
}

function count_approved_bookings_home()
{
    global $pdo;
    $sql = "
        SELECT COUNT(*) 
        FROM bookings b
        WHERE b.status = 'disetujui'
        AND b.tanggal >= CURDATE()
    ";
    return $pdo->query($sql)->fetchColumn();
}

function check_booking_conflict($roomId, $date, $startTime, $endTime, $excludeId = null, $statuses = ['menunggu', 'disetujui'])
{
    global $pdo;

    // Create placeholders for statuses
    $statusPlaceholders = implode(',', array_fill(0, count($statuses), '?'));

    $sql = "
        SELECT COUNT(*) as count FROM bookings
        WHERE room_id = ?
        AND tanggal = ?
        AND status IN ($statusPlaceholders)
        AND (waktu_mulai < ? AND waktu_selesai > ?)
    ";

    // Logging Debugging
    $logMsg = date('Y-m-d H:i:s') . " | Conflict Check | Room: $roomId | Date: $date | Time: $startTime-$endTime | Exclude: $excludeId | Statuses: " . json_encode($statuses) . PHP_EOL;

    // Params: roomId, date, ...statuses, endTime, startTime
    $params = array_merge([$roomId, $date], $statuses, [$endTime, $startTime]);

    if ($excludeId) {
        $sql .= " AND id != ?";
        $params[] = $excludeId;
    }

    // Log Query and Params
    $logMsg .= "Query: $sql" . PHP_EOL;
    $logMsg .= "Params: " . json_encode($params) . PHP_EOL;

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $count = $stmt->fetchColumn();
        $logMsg .= "Result Count: $count" . PHP_EOL . "-----------------------------------" . PHP_EOL;
        file_put_contents(__DIR__ . '/../conflict.log', $logMsg, FILE_APPEND);

        return (int) $count > 0;
    } catch (Exception $e) {
        file_put_contents(__DIR__ . '/../conflict.log', "ERROR: " . $e->getMessage() . PHP_EOL, FILE_APPEND);
        return false;
    }
}

function create_booking($data)
{
    global $pdo;

    // Generate QR Token (Readable Format)
    $qrToken = generate_booking_code($data['tanggal'], $data['waktu_mulai']);

    $sql = "
        INSERT INTO bookings 
        (room_id, nama_peminjam, user_email, phone_number, divisi, instansi, 
            kegiatan, jumlah_peserta, tanggal, waktu_mulai, waktu_selesai, 
            file_pendukung, qr_token, status)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'menunggu')
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $data['room_id'],
        $data['nama_peminjam'],
        $data['user_email'] ?? null,
        $data['phone_number'] ?? null,
        $data['divisi'],
        $data['instansi'],
        $data['kegiatan'],
        $data['jumlah_peserta'],
        $data['tanggal'],
        $data['waktu_mulai'],
        $data['waktu_selesai'],
        $data['file_pendukung'] ?? null,
        $qrToken
    ]);

    return [
        'id' => $pdo->lastInsertId(),
        'qr_token' => $qrToken
    ];
}

function create_admin_booking($data)
{
    global $pdo;

    // Generate QR Token / Unique Code (Readable Format)
    $qrToken = generate_booking_code($data['tanggal'], $data['waktu_mulai']);

    // Admin bookings are automatically APPROVED
    $sql = "
        INSERT INTO bookings 
        (room_id, nama_peminjam, user_email, phone_number, divisi, instansi, 
            kegiatan, jumlah_peserta, tanggal, waktu_mulai, waktu_selesai, 
            file_pendukung, qr_token, status)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'disetujui')
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $data['room_id'],
        $data['nama_peminjam'],
        $data['user_email'] ?? null,  // Email is optional for admin
        $data['phone_number'] ?? null,
        $data['divisi'],
        $data['instansi'],
        $data['kegiatan'],
        $data['jumlah_peserta'],
        $data['tanggal'],
        $data['waktu_mulai'],
        $data['waktu_selesai'],
        $data['file_pendukung'] ?? null, // File is optional
        $qrToken
    ]);

    return [
        'id' => $pdo->lastInsertId(),
        'qr_token' => $qrToken
    ];
}

// Helper to generate readable unique booking code
// Format: MMMDD-HHMM (e.g., FEB02-0900)
function generate_booking_code($date, $startTime)
{
    global $pdo;

    // 1. Base Code: MMMDD-HHMM
    // MMM = Short Month (FEB)
    // DD = Day (02)
    // HHMM = Time (0900) - removing colon

    $timestamp = strtotime($date);
    $month = strtoupper(date('M', $timestamp)); // JAN, FEB
    $day = date('d', $timestamp); // 01, 02

    // Clean time (remove :)
    $time = str_replace(':', '', substr($startTime, 0, 5)); // 09:00:00 -> 0900

    $baseCode = "$month$day-$time"; // FEB02-0900

    $code = $baseCode;
    $suffix = 0;

    do {
        // Ensure uniqueness
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE qr_token = ?");
        $stmt->execute([$code]);
        $exists = $stmt->fetchColumn();

        if ($exists > 0) {
            $suffix++;
            // Append suffix: FEB02-0900-2, FEB02-0900-3
            $code = "$baseCode-$suffix";
        }
    } while ($exists > 0);

    return $code;
}

function get_booking_by_token($token)
{
    global $pdo;
    $sql = "
        SELECT b.*, r.name as room_name 
        FROM bookings b
        JOIN rooms r ON b.room_id = r.id
        WHERE b.qr_token = ?
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$token]);
    return $stmt->fetch();
}

function get_booking_by_id($id)
{
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
        WHERE b.id = ?
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function update_booking_status($id, $status, $reason = null)
{
    global $pdo;
    $sql = "UPDATE bookings SET status = ?, updated_at = NOW()";
    $params = [$status];

    if ($reason) {
        if ($status === 'ditolak') {
            $sql .= ", rejection_reason = ?";
            $params[] = $reason;
        } elseif ($status === 'dibatalkan') {
            $sql .= ", cancel_reason = ?";
            $params[] = $reason;
        }
    }

    $sql .= " WHERE id = ?";
    $params[] = $id;

    $stmt = $pdo->prepare($sql);
    return $stmt->execute($params);
}

// ADMIN BOOKING FUNCTIONS
function get_all_bookings_admin($statusFilter = null)
{
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
        WHERE 1=1
    ";
    $params = [];

    if ($statusFilter) {
        $sql .= " AND b.status = ?";
        $params[] = $statusFilter;
    }

    $sql .= " ORDER BY b.created_at DESC, b.tanggal DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function get_booking_stats()
{
    global $pdo;
    $sql = "
        SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN status = 'menunggu' THEN 1 ELSE 0 END) as menunggu,
            SUM(CASE WHEN status = 'disetujui' THEN 1 ELSE 0 END) as disetujui,
            SUM(CASE WHEN status = 'ditolak' THEN 1 ELSE 0 END) as ditolak,
            SUM(CASE WHEN status = 'dibatalkan' THEN 1 ELSE 0 END) as dibatalkan
        FROM bookings
    ";
    $stmt = $pdo->query($sql);
    return $stmt->fetch();
}

function get_pending_over_sla()
{
    global $pdo;
    $sql = "
        SELECT 
            b.*,
            TIMESTAMPDIFF(HOUR, b.created_at, NOW()) as hours_pending
        FROM bookings b
        WHERE b.status = 'menunggu'
        AND TIMESTAMPDIFF(HOUR, b.created_at, NOW()) > 24
        ORDER BY b.created_at ASC
    ";
    $stmt = $pdo->query($sql);
    return $stmt->fetchAll();
}

function get_monthly_stats($year = null)
{
    global $pdo;
    $year = $year ?? date('Y');

    $sql = "
        SELECT 
            MONTH(tanggal) as month,
            COUNT(*) as count
        FROM bookings
        WHERE YEAR(tanggal) = ? AND status = 'disetujui'
        GROUP BY MONTH(tanggal)
        ORDER BY month ASC
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$year]);
    return $stmt->fetchAll();
}

function get_available_years()
{
    global $pdo;
    $stmt = $pdo->query("SELECT DISTINCT YEAR(tanggal) as year FROM bookings ORDER BY year DESC");
    $years = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // Ensure current year always exists
    $currentYear = date('Y');
    if (!in_array($currentYear, $years)) {
        array_unshift($years, $currentYear);
    }

    return $years;
}

function get_room_usage_stats()
{
    global $pdo;
    $sql = "
        SELECT 
            r.name as room_name,
            COUNT(b.id) as booking_count
        FROM rooms r
        LEFT JOIN bookings b ON r.id = b.room_id AND b.status = 'disetujui'
        GROUP BY r.name
        ORDER BY booking_count DESC
        LIMIT 5
    ";
    $stmt = $pdo->query($sql);
    return $stmt->fetchAll();
}

function get_popular_rooms($limit = 3)
{
    global $pdo;
    $sql = "
        SELECT r.*, COUNT(b.id) as booking_count 
        FROM rooms r
        LEFT JOIN bookings b ON r.id = b.room_id
        GROUP BY r.id
        ORDER BY booking_count DESC
        LIMIT " . (int) $limit;

    $stmt = $pdo->query($sql);
    return $stmt->fetchAll();
}


// ADMIN AUTHENTICATION
function login_admin($username, $password)
{
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ?");
    $stmt->execute([$username]);
    $admin = $stmt->fetch();

    if ($admin && password_verify($password, $admin['password'])) {
        return $admin;
    }

    return false;
}

// ROOM CRUD FUNCTIONS
function create_room($data)
{
    global $pdo;
    $sql = "
        INSERT INTO rooms (name, capacity, area_size, short_desc, description, facilities, image)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([
        $data['name'],
        $data['capacity'],
        $data['area_size'] ?? null,
        $data['short_desc'],
        $data['description'],
        $data['facilities'] ?? null,
        $data['image'] ?? null
    ]);
}

function update_room($id, $data)
{
    global $pdo;
    $sql = "
        UPDATE rooms 
        SET name = ?, capacity = ?, area_size = ?, short_desc = ?, description = ?, facilities = ?, image = ?
        WHERE id = ?
    ";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([
        $data['name'],
        $data['capacity'],
        $data['area_size'] ?? null,
        $data['short_desc'],
        $data['description'],
        $data['facilities'] ?? null,
        $data['image'] ?? null,
        $id
    ]);
}

function delete_room($id)
{
    global $pdo;
    $stmt = $pdo->prepare("DELETE FROM rooms WHERE id = ?");
    return $stmt->execute([$id]);
}
