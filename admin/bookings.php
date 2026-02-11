<?php
require_once '../includes/functions.php';

// Handle Actions (Approve/Reject)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        set_flash_message('error', 'Invalid security token.');
        redirect('admin/bookings.php');
    }

    if (isset($_POST['action'])) {
        $bookingId = $_POST['booking_id'];
        $action = $_POST['action'];

        if ($action === 'approve') {
            // Cek conflict lagi sebelum approve? Optional tapi good practice.
            // Di sini kita langsung approve saja.
            update_booking_status($bookingId, 'disetujui');

            // Get booking details for notification
            $booking = get_booking_by_id($bookingId);
            if ($booking) {
                // Send notification (wrapped in try-catch to prevent blocking)
                try {
                    send_approval_notification($booking);
                } catch (Exception $e) {
                    error_log("Approval notification failed: " . $e->getMessage());
                }
            }

            set_flash_message('success', 'Booking berhasil disetujui.');
        } elseif ($action === 'reject') {
            $reason = $_POST['rejection_reason'];
            update_booking_status($bookingId, 'ditolak', $reason);

            // Get booking details for notification
            $booking = get_booking_by_id($bookingId);
            if ($booking) {
                // Send notification (wrapped in try-catch to prevent blocking)
                try {
                    send_rejection_notification($booking);
                } catch (Exception $e) {
                    error_log("Rejection notification failed: " . $e->getMessage());
                }
            }

            set_flash_message('success', 'Booking telah ditolak.');
        } elseif ($action === 'cancel') {
            // Admin canceling a booking
            $reason = "Dibatalkan oleh Admin";
            update_booking_status($bookingId, 'dibatalkan', $reason);

            // Notification could be added here
            set_flash_message('success', 'Booking berhasil dibatalkan.');
        }
    }
    redirect('admin/bookings.php');
}

require_once 'header.php';

// Get Filters
$statusFilter = $_GET['status'] ?? null;
$bookings = get_all_bookings_admin($statusFilter);

?>

<div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="px-6 py-4 border-b flex flex-col md:flex-row justify-between items-center gap-4">
        <div>
            <h2 class="text-xl font-bold text-gray-800">Daftar Peminjaman Ruangan</h2>
            <p class="text-xs text-gray-500 mt-1">Kelola semua jadwal peminjaman masuk.</p>
        </div>

        <div class="flex flex-col sm:flex-row gap-3 items-center w-full md:w-auto">
            <!-- Search Box -->
            <div class="relative w-full sm:w-64">
                <input type="text" id="searchInput" placeholder="Cari Nama / Instansi / Unit Kerja / Kode..."
                    class="w-full pl-9 pr-3 py-1.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
            </div>

            <div class="flex gap-2 w-full sm:w-auto overflow-x-auto pb-1 sm:pb-0">
                <a href="bookings.php"
                    class="px-3 py-1 rounded-full text-xs font-medium whitespace-nowrap transition <?= !$statusFilter ? 'bg-blue-600 text-white shadow-sm' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' ?>">Semua</a>
                <a href="bookings.php?status=menunggu"
                    class="px-3 py-1 rounded-full text-xs font-medium whitespace-nowrap transition <?= $statusFilter === 'menunggu' ? 'bg-yellow-500 text-white shadow-sm' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' ?>">Menunggu</a>
                <a href="bookings.php?status=disetujui"
                    class="px-3 py-1 rounded-full text-xs font-medium whitespace-nowrap transition <?= $statusFilter === 'disetujui' ? 'bg-green-600 text-white shadow-sm' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' ?>">Disetujui</a>
                <a href="bookings.php?status=ditolak"
                    class="px-3 py-1 rounded-full text-xs font-medium whitespace-nowrap transition <?= $statusFilter === 'ditolak' ? 'bg-red-600 text-white shadow-sm' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' ?>">Ditolak</a>
            </div>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left text-gray-500" id="bookingTable">
            <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                <tr>
                    <th class="px-6 py-3">Tanggal & Waktu</th>
                    <th class="px-6 py-3">Ruangan</th>
                    <th class="px-6 py-3">Peminjam</th>
                    <th class="px-6 py-3">Kegiatan</th>
                    <th class="px-6 py-3">Kode Booking</th>
                    <th class="px-6 py-3">Unit Kerja</th>
                    <th class="px-6 py-3">Status</th>
                    <th class="px-6 py-3 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Group bookings logic
                $groupedBookings = [];
                foreach ($bookings as $b) {
                    // Determine Series ID
                    // If it has a parent, use parent_id.
                    // If it IS a recurring parent (is_recurring=1 & no parent), use id.
                    // Otherwise, use unique id (stand-alone).
                    $isSeries = !empty($b['recurrence_type']);
                    $seriesId = ($isSeries && !empty($b['parent_booking_id'])) ? $b['parent_booking_id'] : $b['id'];

                    // If this key doesn't exist, init it
                    if (!isset($groupedBookings[$seriesId])) {
                        $groupedBookings[$seriesId] = $b; // Keep main info from the first encountered (usually latest due to sort)
                        $groupedBookings[$seriesId]['series_count'] = 0;
                        $groupedBookings[$seriesId]['series_items'] = [];

                        // Ensure we have the "earliest" and "latest" dates correct if we are grouping
                        // Since sorting is DESC, the first one we meet is the LATEST date.
                        // We might want to loop properly to find min/max if the SQL sort isn't perfect for range finding, 
                        // but get_all_bookings_admin joins pattern so we likely have series_start_date and recurrence_end_date from the DB columns.
                    }

                    $groupedBookings[$seriesId]['series_count']++;
                    $groupedBookings[$seriesId]['series_items'][] = $b;
                }

                if (empty($groupedBookings)): ?>
                    <tr>
                        <td colspan="7" class="px-6 py-8 text-center text-gray-400">
                            Tidak ada data booking.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($groupedBookings as $seriesId => $booking):
                        $statusColors = [
                            'menunggu' => 'bg-yellow-100 text-yellow-800',
                            'disetujui' => 'bg-green-100 text-green-800',
                            'ditolak' => 'bg-red-100 text-red-800',
                            'dibatalkan' => 'bg-gray-100 text-gray-800',
                        ];
                        $statusColor = $statusColors[$booking['status']] ?? 'bg-gray-100 text-gray-800';
                        $isRecurringGroup = ($booking['series_count'] > 1) || !empty($booking['recurrence_type']);

                        // Prepare Date Display
                        $dateDisplay = date('d M Y', strtotime($booking['tanggal']));
                        $seriesStartDate = $booking['series_start_date'] ?? $booking['tanggal'];

                        if ($isRecurringGroup && !empty($booking['recurrence_end_date'])) {
                            $dateDisplay = '<span class="font-bold text-blue-600">' . date('d M Y', strtotime($seriesStartDate)) . '</span>' .
                                '<span class="text-gray-400 mx-1">-</span>' .
                                '<span class="font-bold text-blue-600">' . date('d M Y', strtotime($booking['recurrence_end_date'])) . '</span>';
                        }
                        ?>
                        <tr class="bg-white border-b hover:bg-gray-50 transition booking-row">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="font-medium text-gray-900">
                                    <?= $dateDisplay ?>
                                </div>
                                <div class="text-xs text-gray-500">
                                    <?= date('H:i', strtotime($booking['waktu_mulai'])) ?> -
                                    <?= date('H:i', strtotime($booking['waktu_selesai'])) ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 font-semibold text-gray-900">
                                <?= htmlspecialchars($booking['room_name']) ?>
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-medium text-gray-900 search-name">
                                    <?= htmlspecialchars($booking['nama_peminjam']) ?>
                                </div>
                                <div class="text-xs text-gray-500 search-instansi">
                                    <?= htmlspecialchars($booking['instansi']) ?>
                                </div>
                                <?php if ($booking['file_pendukung']): ?>
                                    <a href="../<?= $booking['file_pendukung'] ?>" target="_blank"
                                        class="text-blue-600 hover:underline text-xs mt-1 block">
                                        <i class="fas fa-paperclip"></i> Lihat Surat
                                    </a>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4">
                                <span class="search-kegiatan"><?= htmlspecialchars($booking['kegiatan']) ?></span>
                                <div class="text-xs text-gray-500 mt-1">
                                    <?= $booking['jumlah_peserta'] ?> Peserta
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="font-mono text-xs font-bold text-blue-600 bg-blue-50 px-2 py-1 rounded select-all">
                                    <?= htmlspecialchars($booking['qr_token']) ?>
                                </span>
                                <?php if ($isRecurringGroup): ?>
                                    <div class="mt-2 flex flex-col gap-1">
                                        <span
                                            class="inline-flex w-fit items-center px-2 py-0.5 rounded text-xs font-medium bg-purple-100 text-purple-800"
                                            title="Booking Berulang">
                                            <i class="fas fa-sync-alt mr-1"></i> Rutin
                                            <?= ucfirst($booking['recurrence_type'] ?? 'Series') ?>
                                        </span>
                                        <span class="text-[10px] text-gray-500 bg-gray-100 px-2 py-0.5 rounded w-fit">
                                            Total: <?= $booking['series_count'] ?> Jadwal
                                        </span>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4">
                                <span class="<?= $statusColor ?> px-2.5 py-0.5 rounded-full text-xs font-semibold uppercase">
                                    <?= $booking['status'] ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <?php if ($booking['status'] === 'menunggu'):
                                    // If group, always treat as recurring/bulk action
                                    $isRecurringAction = $isRecurringGroup ? 'true' : 'false';
                                    ?>
                                    <div class="flex flex-col gap-2">
                                        <!-- Approve Button -->
                                        <button type="button"
                                            onclick="confirmApprove(<?= $booking['id'] ?>, <?= $isRecurringAction ?>)"
                                            class="w-full text-white bg-green-600 hover:bg-green-700 px-3 py-1.5 rounded text-xs font-bold transition">
                                            <i class="fas fa-check mr-1"></i> SETUJUI
                                        </button>

                                        <!-- Reject Button (Trigger Modal) -->
                                        <button onclick="openRejectModal(<?= $booking['id'] ?>, <?= $isRecurringAction ?>)"
                                            class="w-full text-white bg-red-600 hover:bg-red-700 px-3 py-1.5 rounded text-xs font-bold transition">
                                            <i class="fas fa-times mr-1"></i> TOLAK
                                        </button>
                                    </div>
                                <?php else: ?>
                                    <span class="text-gray-400 text-xs">-</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    // Simple Search Function
    document.getElementById('searchInput').addEventListener('keyup', function () {
        const searchText = this.value.toLowerCase();
        const rows = document.querySelectorAll('.booking-row');

        rows.forEach(row => {
            const text = row.innerText.toLowerCase();
            if (text.includes(searchText)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });
</script>

<!-- Reject Modal -->
<div id="rejectModal"
    class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4 backdrop-blur-sm">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-md p-6 transform transition-all scale-100">
        <h3 class="text-lg font-bold text-gray-900 mb-4">Tolak Peminjaman</h3>
        <form method="POST">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="reject">
            <input type="hidden" name="booking_id" id="rejectBookingId">

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Alasan Penolakan</label>
                <textarea name="rejection_reason" required rows="3"
                    class="w-full border rounded-lg p-2 focus:ring-2 focus:ring-red-500 focus:border-red-500"
                    placeholder="Contoh: Ruangan sedang direnovasi"></textarea>
            </div>

            <div class="flex justify-end gap-3">
                <button type="button" onclick="closeRejectModal()"
                    class="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded-lg">Batal</button>
                <button type="submit"
                    class="px-4 py-2 bg-red-600 text-white hover:bg-red-700 rounded-lg font-bold">Tolak Booking</button>
            </div>
        </form>
    </div>
</div>

<script>
    function confirmApprove(id, isRecurring) {
        // Reuse generic function if available, or define it here.
        // We will define a comprehensive updateBookingStatus similar to calendar.php
        updateBookingStatus(id, 'approve', null, isRecurring);
    }

    function openRejectModal(id, isRecurring) {
        // Pass isRecurring to the modal logic if needed, or simply pass it to the final submit.
        // Simplest way: Store it in a global or data attribute.
        document.getElementById('rejectBookingId').value = id;
        document.getElementById('rejectIsRecurring').value = isRecurring ? '1' : '0';
        document.getElementById('rejectModal').classList.remove('hidden');
    }

    function closeRejectModal() {
        document.getElementById('rejectModal').classList.add('hidden');
    }

    // Add submit listener for reject form
    document.querySelector('#rejectModal form').addEventListener('submit', function (e) {
        e.preventDefault();
        closeRejectModal();

        const id = document.getElementById('rejectBookingId').value;
        const reason = this.rejection_reason.value;
        const isRecurring = document.getElementById('rejectIsRecurring').value === '1';

        updateBookingStatus(id, 'reject', reason, isRecurring);
    });

    // Unified Status Update Function (Copied/Adapted from Calendar)
    window.updateBookingStatus = function (bookingId, action, reason = null, isRecurring = false) {
        let confirmTitle, confirmText, loadingText;
        let showDeny = false;
        let confirmBtnText = 'Ya, Lanjutkan';
        let denyBtnText = 'Batal';

        if (isRecurring) {
            // showDeny remains false to force bulk action
            confirmBtnText = 'Ya, Update Semua Seri';
            confirmText = 'Booking ini adalah bagian dari seri berulang. Tindakan ini akan diterapkan pada SEMUA jadwal dalam seri ini. Lanjutkan?';
        }

        if (action === 'approve') {
            confirmTitle = 'Setujui Peminjaman?';
            if (!isRecurring) confirmText = 'User akan menerima notifikasi.';
            loadingText = 'Menyetujui...';
        } else if (action === 'reject') {
            confirmTitle = 'Tolak Peminjaman?';
            if (!isRecurring) confirmText = 'Yakin ingin menolak?';
            loadingText = 'Memproses penolakan...';
        } else if (action === 'cancel') {
            confirmTitle = 'Batalkan Jadwal?';
            if (!isRecurring) confirmText = "Status peminjaman akan diubah menjadi 'Dibatalkan'.";
            loadingText = 'Membatalkan...';
        }

        Swal.fire({
            title: confirmTitle,
            text: confirmText,
            icon: 'question',
            showCancelButton: true,
            showDenyButton: showDeny,
            confirmButtonColor: action === 'approve' ? '#10b981' : '#ef4444',
            denyButtonColor: '#3b82f6',
            confirmButtonText: confirmBtnText,
            denyButtonText: denyBtnText,
            cancelButtonText: 'Batal'
        }).then((result) => {
            let applyToSeries = false;
            let proceed = false;

            if (result.isConfirmed) {
                applyToSeries = isRecurring;
                proceed = true;
            } else if (result.isDenied) {
                applyToSeries = false;
                proceed = true;
            }

            if (proceed) {
                Swal.fire({
                    title: loadingText,
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading()
                });

                fetch('../api/admin_update_booking_status.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        booking_id: bookingId,
                        action: action,
                        reason: reason,
                        apply_to_series: applyToSeries
                    })
                })
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire('Berhasil!', data.message, 'success')
                                .then(() => location.reload()); // Reload for table update
                        } else {
                            Swal.fire('Gagal!', data.message, 'error');
                        }
                    })
                    .catch(e => {
                        console.error(e);
                        Swal.fire('Error', 'Terjadi kesalahan sistem.', 'error');
                    });
            }
        });
    }

    function openRejectModal(id) {
        document.getElementById('rejectBookingId').value = id;
        document.getElementById('rejectModal').classList.remove('hidden');
    }

    function closeRejectModal() {
        document.getElementById('rejectModal').classList.add('hidden');
    }

    // Add submit listener for reject form
    document.querySelector('#rejectModal form').addEventListener('submit', function (e) {
        // Prevent default submission to show loading first
        e.preventDefault();

        // Hide modal
        closeRejectModal();

        // Show loading
        Swal.fire({
            title: 'Memproses...',
            text: 'Sedang mengirim email penolakan ke peminjam...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        // Submit form programmatically
        e.target.submit();
    });
</script>

</body>

</html>