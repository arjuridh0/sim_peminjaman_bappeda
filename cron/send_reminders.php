<?php
/**
 * Cron Job: Send H-1 Reminder Notifications
 * 
 * This script should be run daily (recommended at 9 AM) to send reminder notifications
 * to users who have approved bookings for tomorrow.
 * 
 * Cron setup example (Linux):
 * 0 9 * * * php /path/to/sim_peminjaman_bappeda/cron/send_reminders.php
 * 
 * Windows Task Scheduler:
 * Program: C:\laragon\bin\php\php8.x.x\php.exe
 * Arguments: C:\laragon\www\sim_peminjaman_bappeda\cron\send_reminders.php
 * Schedule: Daily at 9:00 AM
 */

// Include required files
require_once __DIR__ . '/../includes/functions.php';

// Log start
$logFile = __DIR__ . '/reminder_log.txt';
$startTime = date('Y-m-d H:i:s');
log_message("=== Reminder Cron Started at $startTime ===");

try {
    // Get tomorrow's date
    $tomorrow = date('Y-m-d', strtotime('+1 day'));

    // Query bookings for tomorrow with status 'disetujui'
    global $pdo;
    $sql = "
        SELECT b.*, r.name as room_name 
        FROM bookings b
        JOIN rooms r ON b.room_id = r.id
        WHERE b.tanggal = ? 
        AND b.status = 'disetujui'
        AND b.phone_number IS NOT NULL
        AND b.phone_number != ''
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$tomorrow]);
    $bookings = $stmt->fetchAll();

    $sentCount = 0;
    $failedCount = 0;

    // Send reminder for each booking
    foreach ($bookings as $booking) {
        try {
            $result = send_reminder_notification($booking);

            if ($result) {
                $sentCount++;
                log_message("✓ Reminder sent to: {$booking['phone_number']} (Booking ID: {$booking['id']})");
            } else {
                $failedCount++;
                log_message("✗ Failed to send reminder to: {$booking['phone_number']} (Booking ID: {$booking['id']})");
            }
        } catch (Exception $e) {
            $failedCount++;
            log_message("✗ Exception sending reminder to: {$booking['phone_number']} (Booking ID: {$booking['id']}) - " . $e->getMessage());
        }
    }

    // Log summary
    $totalBookings = count($bookings);
    log_message("Summary: $totalBookings bookings found, $sentCount sent, $failedCount failed");
    log_message("=== Reminder Cron Completed ===\n");

    // Output for cron log
    echo "Reminder cron completed successfully.\n";
    echo "Total bookings: $totalBookings\n";
    echo "Sent: $sentCount\n";
    echo "Failed: $failedCount\n";

} catch (Exception $e) {
    $errorMsg = "ERROR: " . $e->getMessage();
    log_message($errorMsg);
    log_message("=== Reminder Cron Failed ===\n");

    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}

/**
 * Log message to file
 */
function log_message($message)
{
    global $logFile;
    $timestamp = date('Y-m-d H:i:s');
    $logEntry = "[$timestamp] $message\n";
    file_put_contents($logFile, $logEntry, FILE_APPEND);
}
