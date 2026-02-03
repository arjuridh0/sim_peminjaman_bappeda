<?php
// includes/email_templates.php
// Email Templates for Room Booking System

/**
 * Get email template wrapper with header and footer
 */
function get_email_wrapper($content)
{
    return '
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #f4f4f4; }
        .container { max-width: 600px; margin: 20px auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .header { background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%); color: #ffffff; padding: 30px 20px; text-align: center; }
        .header h1 { margin: 0; font-size: 24px; }
        .content { padding: 30px 20px; color: #333333; line-height: 1.6; }
        .footer { background: #f8f9fa; padding: 20px; text-align: center; font-size: 12px; color: #666666; border-top: 1px solid #e9ecef; }
        .button { display: inline-block; padding: 12px 30px; background: #2563eb; color: #ffffff !important; text-decoration: none; border-radius: 6px; margin: 10px 5px; font-weight: bold; }
        .button:hover { background: #1d4ed8; }
        .info-box { background: #f0f9ff; border-left: 4px solid #2563eb; padding: 15px; margin: 20px 0; border-radius: 4px; }
        .detail-row { padding: 10px 0; border-bottom: 1px solid #e9ecef; }
        .detail-label { font-weight: bold; color: #2563eb; display: inline-block; width: 150px; }
        .warning-box { background: #fef2f2; border-left: 4px solid #ef4444; padding: 15px; margin: 20px 0; border-radius: 4px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🏛️ BAPPEDA Jawa Tengah</h1>
            <p style="margin: 5px 0 0 0; opacity: 0.9;">Sistem Peminjaman Ruangan</p>
        </div>
        <div class="content">
            ' . $content . '
        </div>
        <div class="footer">
            <p><strong>BAPPEDA Provinsi Jawa Tengah</strong></p>
            <p>Email ini dikirim secara otomatis. Mohon tidak membalas email ini.</p>
            <p style="margin-top: 10px; color: #999;">© ' . date('Y') . ' BAPPEDA Jawa Tengah. All rights reserved.</p>
        </div>
    </div>
</body>
</html>';
}

/**
 * Template: New Booking Notification to Admin
 */
function template_new_booking_admin($booking)
{
    $content = '
        <h2 style="color: #2563eb; margin-top: 0;">📋 Peminjaman Ruangan Baru</h2>
        <p>Halo Admin,</p>
        <p>Ada pengajuan peminjaman ruangan baru yang memerlukan persetujuan Anda:</p>
        
        <div class="info-box">
            <div class="detail-row">
                <span class="detail-label">Ruangan:</span>
                <span>' . htmlspecialchars($booking['room_name']) . '</span>
            </div>
            ' . (!empty($booking['recurrence_type']) ? '
            <div class="detail-row">
                <span class="detail-label">Jenis:</span>
                <span style="background: #e0f2fe; color: #0369a1; padding: 2px 8px; rounded: 4px; font-size: 12px; font-weight: bold; border-radius: 4px;">🔄 Rutin ' . ucfirst($booking['recurrence_type']) . '</span>
            </div>' : '') . '
            <div class="detail-row">
                <span class="detail-label">Peminjam:</span>
                <span>' . htmlspecialchars($booking['nama_peminjam']) . '</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Instansi:</span>
                <span>' . htmlspecialchars($booking['instansi']) . '</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Kegiatan:</span>
                <span>' . htmlspecialchars($booking['kegiatan']) . '</span>
            </div>
            ' . (!empty($booking['recurrence_type']) ? '
            <div class="detail-row">
                <span class="detail-label">Periode:</span>
                <span>' . date('d M Y', strtotime($booking['series_start_date'] ?? $booking['tanggal'])) . ' s/d ' . date('d M Y', strtotime($booking['recurrence_end_date'])) . '</span>
            </div>
            ' : '
            <div class="detail-row">
                <span class="detail-label">Tanggal:</span>
                <span>' . date('d F Y', strtotime($booking['tanggal'])) . '</span>
            </div>
            ') . '
            <div class="detail-row">
                <span class="detail-label">Waktu:</span>
                <span>' . date('H:i', strtotime($booking['waktu_mulai'])) . ' - ' . date('H:i', strtotime($booking['waktu_selesai'])) . '</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Jumlah Peserta:</span>
                <span>' . $booking['jumlah_peserta'] . ' orang</span>
            </div>
            <div class="detail-row" style="border-bottom: none;">
                <span class="detail-label">Kode Booking:</span>
                <span style="font-family: monospace; font-weight: bold; color: #2563eb;">' . htmlspecialchars($booking['qr_token']) . '</span>
            </div>
        </div>
        
        <p style="text-align: center; margin: 30px 0;">
            <a href="' . base_url('admin/bookings.php') . '" class="button">Lihat Detail & Setujui</a>
        </p>
        
        <p style="color: #666; font-size: 14px;">Silakan login ke admin panel untuk menyetujui atau menolak peminjaman ini.</p>
    ';

    return get_email_wrapper($content);
}

/**
 * Template: Booking Confirmation to User (Immediately after booking)
 */
function template_booking_confirmation($booking)
{
    $content = '
        <h2 style="color: #2563eb; margin-top: 0;">✅ Peminjaman Berhasil Dibuat!</h2>
        <p>Halo <strong>' . htmlspecialchars($booking['nama_peminjam']) . '</strong>,</p>
        <p>Terima kasih! Pengajuan peminjaman ruangan Anda telah <strong style="color: #2563eb;">BERHASIL DIBUAT</strong> dan sedang menunggu persetujuan admin.</p>
        
        <div class="info-box">
            <h3 style="margin-top: 0; color: #2563eb;">Detail Peminjaman:</h3>
            <div class="detail-row">
                <span class="detail-label">Ruangan:</span>
                <span>' . htmlspecialchars($booking['room_name']) . '</span>
            </div>
            ' . (!empty($booking['recurrence_type']) ? '
            <div class="detail-row">
                <span class="detail-label">Jenis:</span>
                <span style="background: #e0f2fe; color: #0369a1; padding: 2px 8px; rounded: 4px; font-size: 12px; font-weight: bold; border-radius: 4px;">🔄 Rutin ' . ucfirst($booking['recurrence_type']) . '</span>
            </div>' : '') . '
            ' . (!empty($booking['recurrence_type']) ? '
            <div class="detail-row">
                <span class="detail-label">Periode:</span>
                <span>' . date('d M Y', strtotime($booking['series_start_date'] ?? $booking['tanggal'])) . ' s/d ' . date('d M Y', strtotime($booking['recurrence_end_date'])) . '</span>
            </div>
            ' : '
            <div class="detail-row">
                <span class="detail-label">Tanggal:</span>
                <span>' . date('d F Y', strtotime($booking['tanggal'])) . '</span>
            </div>
            ') . '
            <div class="detail-row">
                <span class="detail-label">Waktu:</span>
                <span>' . date('H:i', strtotime($booking['waktu_mulai'])) . ' - ' . date('H:i', strtotime($booking['waktu_selesai'])) . '</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Kegiatan:</span>
                <span>' . htmlspecialchars($booking['kegiatan']) . '</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Jumlah Peserta:</span>
                <span>' . $booking['jumlah_peserta'] . ' orang</span>
            </div>
        </div>
        
        <div style="background: #fef3c7; border-left: 4px solid #f59e0b; padding: 20px; margin: 20px 0; border-radius: 4px; text-align: center;">
            <p style="margin: 0 0 10px 0; color: #92400e; font-weight: bold; font-size: 16px;">🔑 KODE BOOKING ANDA:</p>
            <p style="margin: 0; font-family: monospace; font-weight: bold; color: #2563eb; font-size: 24px; letter-spacing: 2px;">' . htmlspecialchars($booking['qr_token']) . '</p>
            <p style="margin: 10px 0 0 0; color: #92400e; font-size: 14px;">Simpan kode ini untuk cek status peminjaman</p>
        </div>
        
        <div style="background: #dbeafe; border-left: 4px solid #2563eb; padding: 15px; margin: 20px 0; border-radius: 4px;">
            <p style="margin: 0 0 10px 0; font-weight: bold; color: #1e40af;">ℹ️ Informasi Penting:</p>
            <ul style="margin: 0; padding-left: 20px; color: #1e3a8a;">
                <li>Peminjaman Anda sedang dalam proses review oleh admin</li>
                <li>Anda akan menerima email notifikasi saat peminjaman disetujui/ditolak</li>
                <li>Gunakan kode booking di atas untuk mengecek status peminjaman</li>
                <li>Simpan email ini sebagai bukti pengajuan</li>
            </ul>
        </div>
        
        <p style="text-align: center; margin: 30px 0;">
            <a href="' . base_url('/booking_status.php?id=' . $booking['id']) . '" class="button">Cek Status Peminjaman</a>
        </p>
        
        <p style="color: #666; font-size: 14px;">Jika Anda tidak melakukan peminjaman ini, silakan abaikan email ini atau hubungi admin.</p>
    ';

    return get_email_wrapper($content);
}

/**
 * Template: Booking Approved Notification to User
 */
function template_booking_approved($booking)
{
    $content = '
        <h2 style="color: #10b981; margin-top: 0;">✅ Peminjaman Disetujui!</h2>
        <p>Halo <strong>' . htmlspecialchars($booking['nama_peminjam']) . '</strong>,</p>
        <p>Selamat! Pengajuan peminjaman ruangan Anda telah <strong style="color: #10b981;">DISETUJUI</strong>.</p>
        
        <div class="info-box">
            <h3 style="margin-top: 0; color: #2563eb;">Detail Peminjaman:</h3>
            <div class="detail-row">
                <span class="detail-label">Ruangan:</span>
                <span>' . htmlspecialchars($booking['room_name']) . '</span>
            </div>
            ' . (!empty($booking['recurrence_type']) ? '
            <div class="detail-row">
                <span class="detail-label">Jenis:</span>
                <span style="background: #e0f2fe; color: #0369a1; padding: 2px 8px; rounded: 4px; font-size: 12px; font-weight: bold; border-radius: 4px;">🔄 Rutin ' . ucfirst($booking['recurrence_type']) . '</span>
            </div>' : '') . '
            ' . (!empty($booking['recurrence_type']) ? '
            <div class="detail-row">
                <span class="detail-label">Periode:</span>
                <span>' . date('d M Y', strtotime($booking['series_start_date'] ?? $booking['tanggal'])) . ' s/d ' . date('d M Y', strtotime($booking['recurrence_end_date'])) . '</span>
            </div>
            ' : '
            <div class="detail-row">
                <span class="detail-label">Tanggal:</span>
                <span>' . date('d F Y', strtotime($booking['tanggal'])) . '</span>
            </div>
            ') . '
            <div class="detail-row">
                <span class="detail-label">Waktu:</span>
                <span>' . date('H:i', strtotime($booking['waktu_mulai'])) . ' - ' . date('H:i', strtotime($booking['waktu_selesai'])) . '</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Kegiatan:</span>
                <span>' . htmlspecialchars($booking['kegiatan']) . '</span>
            </div>
            <div class="detail-row" style="border-bottom: none;">
                <span class="detail-label">Kode Booking:</span>
                <span style="font-family: monospace; font-weight: bold; color: #2563eb; font-size: 16px;">' . htmlspecialchars($booking['qr_token']) . '</span>
            </div>
        </div>
        
        <div style="background: #fef3c7; border-left: 4px solid #f59e0b; padding: 15px; margin: 20px 0; border-radius: 4px;">
            <p style="margin: 0; color: #92400e;"><strong>⚠️ Penting:</strong></p>
            <ul style="margin: 10px 0; padding-left: 20px; color: #92400e;">
                <li>Simpan kode booking Anda untuk keperluan check-in</li>
                <li>Datang 15 menit sebelum waktu mulai</li>
                <li>Pastikan ruangan dikembalikan dalam kondisi bersih</li>
            </ul>
        </div>
        
        <p style="text-align: center; margin: 30px 0;">
            <a href="' . base_url('booking_status.php?id=' . $booking['id']) . '" class="button">Lihat Detail Lengkap</a>
        </p>
        
        <p style="color: #666; font-size: 14px;">Terima kasih telah menggunakan layanan peminjaman ruangan BAPPEDA Jawa Tengah.</p>
    ';

    return get_email_wrapper($content);
}

/**
 * Template: Booking Rejected Notification to User
 */
function template_booking_rejected($booking)
{
    $content = '
        <h2 style="color: #ef4444; margin-top: 0;">❌ Peminjaman Ditolak</h2>
        <p>Halo <strong>' . htmlspecialchars($booking['nama_peminjam']) . '</strong>,</p>
        <p>Mohon maaf, pengajuan peminjaman ruangan Anda telah <strong style="color: #ef4444;">DITOLAK</strong>.</p>
        
        <div class="warning-box">
            <p style="margin: 0 0 10px 0; font-weight: bold; color: #991b1b;">Alasan Penolakan:</p>
            <p style="margin: 0; color: #991b1b;">' . htmlspecialchars($booking['rejection_reason'] ?? 'Tidak ada alasan yang diberikan') . '</p>
        </div>
        
        <div class="info-box">
            <h3 style="margin-top: 0; color: #2563eb;">Detail Peminjaman yang Ditolak:</h3>
            <div class="detail-row">
                <span class="detail-label">Ruangan:</span>
                <span>' . htmlspecialchars($booking['room_name']) . '</span>
            </div>
            ' . (!empty($booking['recurrence_type']) ? '
            <div class="detail-row">
                <span class="detail-label">Jenis:</span>
                <span style="background: #e0f2fe; color: #0369a1; padding: 2px 8px; rounded: 4px; font-size: 12px; font-weight: bold; border-radius: 4px;">🔄 Rutin ' . ucfirst($booking['recurrence_type']) . '</span>
            </div>' : '') . '
            ' . (!empty($booking['recurrence_type']) ? '
            <div class="detail-row">
                <span class="detail-label">Periode:</span>
                <span>' . date('d M Y', strtotime($booking['series_start_date'] ?? $booking['tanggal'])) . ' s/d ' . date('d M Y', strtotime($booking['recurrence_end_date'])) . '</span>
            </div>
            ' : '
            <div class="detail-row">
                <span class="detail-label">Tanggal:</span>
                <span>' . date('d F Y', strtotime($booking['tanggal'])) . '</span>
            </div>
            ') . '
            <div class="detail-row">
                <span class="detail-label">Waktu:</span>
                <span>' . date('H:i', strtotime($booking['waktu_mulai'])) . ' - ' . date('H:i', strtotime($booking['waktu_selesai'])) . '</span>
            </div>
            <div class="detail-row" style="border-bottom: none;">
                <span class="detail-label">Kode Booking:</span>
                <span style="font-family: monospace; color: #666;">' . htmlspecialchars($booking['qr_token']) . '</span>
            </div>
        </div>
        
        <p>Anda dapat mengajukan peminjaman baru dengan tanggal atau ruangan yang berbeda.</p>
        
        <p style="text-align: center; margin: 30px 0;">
            <a href="' . base_url('rooms.php') . '" class="button">Lihat Ruangan Tersedia</a>
        </p>
        
        <p style="color: #666; font-size: 14px;">Jika ada pertanyaan, silakan hubungi admin BAPPEDA.</p>
    ';

    return get_email_wrapper($content);
}

/**
 * Template: H-1 Reminder Notification
 */
function template_booking_reminder($booking)
{
    $content = '
        <h2 style="color: #f59e0b; margin-top: 0;">🔔 Pengingat: Peminjaman Besok!</h2>
        <p>Halo <strong>' . htmlspecialchars($booking['nama_peminjam']) . '</strong>,</p>
        <p>Ini adalah pengingat bahwa Anda memiliki peminjaman ruangan <strong>besok</strong>:</p>
        
        <div class="info-box">
            <h3 style="margin-top: 0; color: #2563eb;">Detail Peminjaman:</h3>
            <div class="detail-row">
                <span class="detail-label">Ruangan:</span>
                <span>' . htmlspecialchars($booking['room_name']) . '</span>
            </div>
             ' . (!empty($booking['recurrence_type']) ? '
            <div class="detail-row">
                <span class="detail-label">Jenis:</span>
                <span style="background: #e0f2fe; color: #0369a1; padding: 2px 8px; rounded: 4px; font-size: 12px; font-weight: bold; border-radius: 4px;">🔄 Rutin ' . ucfirst($booking['recurrence_type']) . '</span>
            </div>' : '') . '
            <div class="detail-row">
                <span class="detail-label">Tanggal:</span>
                <span style="font-weight: bold; color: #f59e0b;">' . date('d F Y', strtotime($booking['tanggal'])) . '</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Waktu:</span>
                <span style="font-weight: bold;">' . date('H:i', strtotime($booking['waktu_mulai'])) . ' - ' . date('H:i', strtotime($booking['waktu_selesai'])) . '</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Kegiatan:</span>
                <span>' . htmlspecialchars($booking['kegiatan']) . '</span>
            </div>
            <div class="detail-row" style="border-bottom: none;">
                <span class="detail-label">Kode Booking:</span>
                <span style="font-family: monospace; font-weight: bold; color: #2563eb;">' . htmlspecialchars($booking['qr_token']) . '</span>
            </div>
        </div>
        
        <div style="background: #dbeafe; border-left: 4px solid #2563eb; padding: 15px; margin: 20px 0; border-radius: 4px;">
            <p style="margin: 0 0 10px 0; font-weight: bold; color: #1e40af;">📝 Checklist Persiapan:</p>
            <ul style="margin: 0; padding-left: 20px; color: #1e3a8a;">
                <li>Siapkan materi/presentasi Anda</li>
                <li>Datang 15 menit lebih awal</li>
                <li>Bawa kode booking untuk check-in</li>
                <li>Pastikan semua peserta sudah dikonfirmasi</li>
            </ul>
        </div>
        
        <p style="text-align: center; margin: 30px 0;">
            <a href="' . base_url('booking_status.php?id=' . $booking['id']) . '" class="button">Lihat Detail Booking</a>
        </p>
        
        <p style="color: #666; font-size: 14px;">Jika ada perubahan atau pembatalan, segera hubungi admin.</p>
    ';

    return get_email_wrapper($content);
}

/**
 * Get plain text version of email
 */
function get_plain_text_version($booking, $type)
{
    $text = "BAPPEDA Jawa Tengah - Sistem Peminjaman Ruangan\n";
    $text .= str_repeat("=", 50) . "\n\n";

    switch ($type) {
        case 'new_booking':
            $text .= "PEMINJAMAN RUANGAN BARU\n\n";
            break;
        case 'confirmation':
            $text .= "KONFIRMASI PEMINJAMAN BERHASIL\n\n";
            break;
        case 'approved':
            $text .= "PEMINJAMAN DISETUJUI\n\n";
            break;
        case 'rejected':
            $text .= "PEMINJAMAN DITOLAK\n\n";
            break;
        case 'reminder':
            $text .= "PENGINGAT PEMINJAMAN BESOK\n\n";
            break;
    }

    $text .= "Detail Peminjaman:\n";
    $text .= "- Ruangan: " . $booking['room_name'] . "\n";
    $text .= "- Peminjam: " . $booking['nama_peminjam'] . "\n";
    if (!empty($booking['recurrence_type'])) {
        $text .= "- Periode: " . date('d M Y', strtotime($booking['series_start_date'] ?? $booking['tanggal'])) . " s/d " . date('d M Y', strtotime($booking['recurrence_end_date'])) . "\n";
        $text .= "- Jenis: Rutin " . ucfirst($booking['recurrence_type']) . "\n";
    } else {
        $text .= "- Tanggal: " . date('d F Y', strtotime($booking['tanggal'])) . "\n";
    }
    $text .= "- Waktu: " . date('H:i', strtotime($booking['waktu_mulai'])) . " - " . date('H:i', strtotime($booking['waktu_selesai'])) . "\n";
    $text .= "- Kode Booking: " . $booking['qr_token'] . "\n\n";

    if ($type === 'rejected' && !empty($booking['rejection_reason'])) {
        $text .= "Alasan Penolakan: " . $booking['rejection_reason'] . "\n\n";
    }

    $text .= "\nEmail ini dikirim secara otomatis.\n";
    $text .= "© " . date('Y') . " BAPPEDA Jawa Tengah\n";

    return $text;
}
