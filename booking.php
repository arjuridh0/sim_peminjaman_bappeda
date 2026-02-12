<?php
require_once 'includes/functions.php';

// ==============================================================================
// HANDLE SUBMIT BOOKING (POST)
// ==============================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 0. Verify CSRF token
    if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        set_flash_message('error', "Invalid security token. Please try again.");
        redirect_back();
    }

    // 1. Validasi Input
    $required = ['room_id', 'nama_peminjam', 'divisi', 'instansi', 'kegiatan', 'jumlah_peserta', 'tanggal', 'waktu_mulai', 'waktu_selesai'];
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            set_flash_message('error', "Semua field wajib diisi");
            redirect_back();
        }
    }

    // 1.1 Custom Validation: Email OR Phone Required
    $phoneNumber = $_POST['phone_number'] ?? '';
    $userEmail = $_POST['user_email'] ?? '';

    if (empty($phoneNumber) && empty($userEmail)) {
        set_flash_message('error', "Wajib mengisi salah satu: Nomor WhatsApp atau Email untuk notifikasi.");
        redirect_back();
    }

    $roomId = $_POST['room_id'];
    $tanggal = $_POST['tanggal'];
    $waktuMulai = $_POST['waktu_mulai'];
    $waktuSelesai = $_POST['waktu_selesai'];

    // 1.5 Handle Recurring Logic
    $isRecurring = isset($_POST['is_recurring']);
    $recurrenceDates = []; // Stores date strings 'Y-m-d'
    $recurrenceType = $_POST['recurrence_type'] ?? null;
    $endDate = $_POST['recurrence_end_date'] ?? null;

    if ($isRecurring) {
        if (!$recurrenceType || !$endDate) {
            set_flash_message('error', "Tipe pengulangan dan tanggal akhir wajib diisi untuk booking berulang.");
            redirect_back();
        }

        // Generate Dates
        $currentDate = new DateTime($tanggal);
        $endDateTime = new DateTime($endDate);

        // Safety Limit: Max 1 Year
        $maxDate = clone $currentDate;
        $maxDate->modify('+1 year');

        if ($endDateTime > $maxDate) {
            set_flash_message('error', "Maksimal booking berulang adalah 1 tahun.");
            redirect_back();
        }

        // Add pattern logic 
        // Skip the first date (it's the main booking, handled separately but checked for conflict)
        // Actually, let's include all dates in the conflict check array

        $intervalStr = '+1 day'; // default
        if ($recurrenceType === 'daily')
            $intervalStr = '+1 day';
        if ($recurrenceType === 'weekly')
            $intervalStr = '+1 week';
        if ($recurrenceType === 'monthly')
            $intervalStr = '+1 month';

        // Loop to find all instances
        $iterDate = clone $currentDate;
        // Move to next instance immediately? 
        // No, check conflict for the initial date too.

        // Check initial date conflict
        if (check_booking_conflict($roomId, $iterDate->format('Y-m-d'), $waktuMulai, $waktuSelesai)) {
            set_flash_message('error', "Maaf, ruangan sudah terpakai pada tanggal " . $iterDate->format('d/m/Y'));
            redirect_back();
        }

        // Advance to next
        $iterDate->modify($intervalStr);

        while ($iterDate <= $endDateTime) {
            $dateStr = $iterDate->format('Y-m-d');

            // Check Conflict
            if (check_booking_conflict($roomId, $dateStr, $waktuMulai, $waktuSelesai)) {
                set_flash_message('error', "Gagal booking berulang: Ruangan sudah terpakai pada tanggal " . $iterDate->format('d/m/Y') . ". Mohon pilih tanggal lain atau sesuaikan jadwal.");
                redirect_back();
            }

            $recurrenceDates[] = $dateStr;
            $iterDate->modify($intervalStr);
        }
    } else {
        // Single Booking Conflict Check
        if (check_booking_conflict($roomId, $tanggal, $waktuMulai, $waktuSelesai)) {
            set_flash_message('error', "Maaf, ruangan sudah terpakai pada waktu tersebut");
            redirect_back();
        }
    }

    // 3. Handle Upload Surat (SECURE)
    $filePath = null;
    if ($_POST['instansi'] === 'lainnya') {
        if (empty($_FILES['file_pendukung']['name'])) {
            set_flash_message('error', "File pendukung wajib diunggah untuk instansi lainnya");
            redirect_back();
        }

        $file = $_FILES['file_pendukung'];
        $allowed = ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($ext, $allowed)) {
            set_flash_message('error', "Format file tidak valid");
            redirect_back();
        }

        if ($file['size'] > 5 * 1024 * 1024) { // 5MB
            set_flash_message('error', "Ukuran file terlalu besar (Max 5MB)");
            redirect_back();
        }

        $uploadDir = __DIR__ . '/assets/files/';
        if (!is_dir($uploadDir))
            mkdir($uploadDir, 0755, true); // Secure permission: owner can write, others can only read

        $fileName = time() . '_' . preg_replace('/[^a-zA-Z0-9]/', '', pathinfo($file['name'], PATHINFO_FILENAME)) . '.' . $ext;

        if (move_uploaded_file($file['tmp_name'], $uploadDir . $fileName)) {
            $filePath = 'assets/files/' . $fileName;
        } else {
            set_flash_message('error', "Gagal upload file");
            redirect_back();
        }
    }

    // 4. Simpan ke Database
    try {
        $pdo->beginTransaction(); // Start Transaction

        // Data array base
        $bookingData = [
            'room_id' => $roomId,
            'nama_peminjam' => $_POST['nama_peminjam'],
            'divisi' => $_POST['divisi'],
            'instansi' => $_POST['instansi'],
            'kegiatan' => $_POST['kegiatan'],
            'jumlah_peserta' => $_POST['jumlah_peserta'],
            'tanggal' => $tanggal,
            'waktu_mulai' => $waktuMulai,
            'waktu_selesai' => $waktuSelesai,
            'file_pendukung' => $filePath,
            'status' => 'menunggu', // Explicitly set status
            'user_email' => $userEmail,
            'phone_number' => $phoneNumber
        ];

        // Insert Main Booking
        // Note: create_booking helper might act independently, but we need transaction safety.
        // We will modify logic to use manual insertion here or just use create_booking if it's safe.
        // Analyzing create_booking: it just prepares and executes. It should respect the transaction if using same PDO.
        // Assuming create_booking uses the global $pdo.

        // Add recurring flags to $bookingData if needed, but create_booking function might not accept extra fields unless updated.
        // Easier to direct insert or update create_booking. Let's direct insert for full control in this block.

        $stmt = $pdo->prepare("INSERT INTO bookings (room_id, nama_peminjam, phone_number, user_email, divisi, instansi, kegiatan, jumlah_peserta, tanggal, waktu_mulai, waktu_selesai, file_pendukung, qr_token, status, is_recurring) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'menunggu', ?)");

        // Generate QR Token (Use the new readable format function)
        $qrToken = generate_booking_code($tanggal, $waktuMulai);
        $isRecurringVal = $isRecurring ? 1 : 0;

        $stmt->execute([
            $roomId,
            $_POST['nama_peminjam'],
            $phoneNumber,
            $userEmail,
            $_POST['divisi'],
            $_POST['instansi'],
            $_POST['kegiatan'],
            $_POST['jumlah_peserta'],
            $tanggal,
            $waktuMulai,
            $waktuSelesai,
            $filePath,
            $qrToken,
            $isRecurringVal
        ]);

        $parentId = $pdo->lastInsertId();

        // If Recurring, Insert Pattern and Children
        if ($isRecurring) {
            // Insert Pattern
            $stmtPattern = $pdo->prepare("INSERT INTO recurring_patterns (booking_id, recurrence_type, end_date) VALUES (?, ?, ?)");
            $stmtPattern->execute([$parentId, $recurrenceType, $endDate]);

            // Insert Children
            $stmtChild = $pdo->prepare("INSERT INTO bookings (room_id, nama_peminjam, phone_number, user_email, divisi, instansi, kegiatan, jumlah_peserta, tanggal, waktu_mulai, waktu_selesai, file_pendukung, qr_token, status, parent_booking_id, recurrence_instance_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'menunggu', ?, ?)");

            foreach ($recurrenceDates as $rDate) {
                $childToken = generate_booking_code($rDate, $waktuMulai);
                $stmtChild->execute([
                    $roomId,
                    $_POST['nama_peminjam'],
                    $phoneNumber,
                    $userEmail,
                    $_POST['divisi'],
                    $_POST['instansi'],
                    $_POST['kegiatan'],
                    $_POST['jumlah_peserta'],
                    $rDate,
                    $waktuMulai,
                    $waktuSelesai,
                    $filePath,
                    $childToken,
                    $parentId,
                    $rDate
                ]);
            }
        }

        $pdo->commit();

        // Set session for Success Page
        $_SESSION['new_booking_token'] = $qrToken;
        $_SESSION['booking_id'] = $parentId;

        // Prepare booking data for notifications
        $bookingData['id'] = $parentId;
        $bookingData['qr_token'] = $qrToken;
        $bookingData['phone_number'] = $_POST['phone_number'] ?? null;
        $bookingData['nama_peminjam'] = $_POST['nama_peminjam'];
        $bookingData['tanggal'] = $tanggal;
        $bookingData['waktu_mulai'] = $waktuMulai;
        $bookingData['waktu_selesai'] = $waktuSelesai;

        // Send notifications (wrapped in try-catch to prevent blocking the booking process)
        // Even if notification fails, booking is already saved
        try {
            send_booking_notification_to_admin($bookingData);
            send_booking_notification($bookingData, 'booking'); // Unified notification
        } catch (Exception $notifError) {
            // Log error but don't stop the process
            error_log("Notification failed: " . $notifError->getMessage());
        }

        // SECURE REDIRECT: Store token in session and redirect to access granted view
        $_SESSION['access_token'] = $qrToken;
        redirect('booking_status.php?view=access_granted');

    } catch (Exception $e) {
        if ($pdo->inTransaction())
            $pdo->rollBack();
        set_flash_message('error', "Terjadi kesalahan sistem: " . $e->getMessage());
        redirect_back();
    }

    exit;
}

// ==============================================================================
// TAMPILKAN FORM (GET)
// ==============================================================================
$roomId = $_GET['room_id'] ?? null;

if (!$roomId) {
    redirect('index.php');
}

$room = get_room_by_id($roomId);

if (!$room) {
    die("Ruangan tidak ditemukan");
}

$title = 'Form Peminjaman';
require 'includes/header.php';
?>

<section class="py-12 bg-gradient-to-br from-blue-50 to-indigo-50 min-h-screen">
    <div class="max-w-4xl mx-auto px-6">
        <!-- Header -->
        <div class="text-center mb-8">
            <h2
                class="text-3xl font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent mb-2">
                Form Peminjaman Ruangan
            </h2>
            <p class="text-gray-600 text-lg">
                Ruangan: <span class="font-bold text-blue-600">
                    <?= htmlspecialchars($room['name'] ?? '') ?>
                </span>
            </p>
        </div>

        <!-- Form Card -->
        <div class="bg-white rounded-2xl shadow-xl p-8">
            <form action="booking.php" method="POST" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <input type="hidden" name="room_id" value="<?= $room['id'] ?>">

                <!-- Grid Layout -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <!-- Nama Peminjam -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-user text-blue-600 mr-1"></i>Nama Peminjam <span
                                class="text-red-500">*</span>
                        </label>
                        <input type="text" name="nama_peminjam" required
                            class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all outline-none">
                    </div>

                    <!-- Divisi -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-building text-blue-600 mr-1"></i>Unit Kerja <span
                                class="text-red-500">*</span>
                        </label>
                        <input type="text" name="divisi" required placeholder="Unit Kerja"
                            class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all outline-none">
                    </div>

                    <!-- Instansi -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-landmark text-blue-600 mr-1"></i>Instansi <span
                                class="text-red-500">*</span>
                        </label>
                        <select name="instansi" id="instansi" required
                            class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all outline-none">
                            <option value="">Pilih Instansi</option>
                            <option value="bappeda">BAPPEDA</option>
                            <option value="lainnya">Lainnya</option>
                        </select>
                    </div>

                    <!-- Nama Kegiatan -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-clipboard-list text-blue-600 mr-1"></i>Nama Kegiatan <span
                                class="text-red-500">*</span>
                        </label>
                        <input type="text" name="kegiatan" required
                            class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all outline-none">
                    </div>

                    <!-- Jumlah Peserta -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-users text-blue-600 mr-1"></i>Jumlah Peserta <span
                                class="text-red-500">*</span>
                        </label>
                        <input type="number" name="jumlah_peserta" min="1" max="<?= $room['capacity'] ?>" required
                            class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all outline-none">
                        <p class="mt-1 text-sm text-gray-500">Maksimal:
                            <?= $room['capacity'] ?> orang
                        </p>
                    </div>

                    <!-- Tanggal -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-calendar text-blue-600 mr-1"></i>Tanggal <span
                                class="text-red-500">*</span>
                        </label>
                        <input type="text" name="tanggal" placeholder="Pilih Tanggal" required
                            value="<?= htmlspecialchars($_GET['date'] ?? '') ?>"
                            class="datepicker w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all outline-none bg-white">
                    </div>

                    <!-- Waktu Mulai -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-clock text-blue-600 mr-1"></i>Waktu Mulai <span
                                class="text-red-500">*</span>
                        </label>
                        <input type="text" name="waktu_mulai" placeholder="00:00" required
                            class="timepicker w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all outline-none bg-white">
                    </div>

                    <!-- Waktu Selesai -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-clock text-blue-600 mr-1"></i>Waktu Selesai <span
                                class="text-red-500">*</span>
                        </label>
                        <input type="text" name="waktu_selesai" placeholder="00:00" required
                            class="timepicker w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all outline-none bg-white">
                    </div>

                    <script>
                        document.addEventListener('DOMContentLoaded', function () {
                            // Init Datepicker
                            flatpickr(".datepicker", {
                                locale: "id",
                                altInput: true,
                                altFormat: "j F Y",
                                dateFormat: "Y-m-d",
                                minDate: "today",
                                disableMobile: "true" // Force Custom UI on Mobile
                            });

                            // Init Timepicker
                            flatpickr(".timepicker", {
                                enableTime: true,
                                noCalendar: true,
                                dateFormat: "H:i",
                                time_24hr: true,
                                disableMobile: "true" // Force Custom UI on Mobile (Scroll view)
                            });
                        });
                    </script>

                    <!-- RECURRING OPTION (HIDDEN) -->
                    <div class="col-span-2 bg-purple-50 p-4 rounded-lg border border-purple-100" style="display: none;">
                        <div class="flex items-center gap-3 mb-2">
                            <input type="checkbox" id="is_recurring" name="is_recurring"
                                class="w-5 h-5 text-purple-600 rounded focus:ring-purple-500">
                            <label for="is_recurring" class="font-bold text-gray-700 select-none cursor-pointer">Ulangi
                                Booking ini?</label>
                        </div>

                        <div id="recurringOptions" class="hidden mt-3 grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-600 mb-1">Frekuensi
                                    Pengulangan</label>
                                <select name="recurrence_type"
                                    class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-purple-300 outline-none">
                                    <option value="daily">Harian (Setiap Hari)</option>
                                    <option value="weekly">Mingguan (Setiap Minggu)</option>
                                    <option value="monthly">Bulanan (Setiap Bulan)</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-600 mb-1">Sampai Tanggal</label>
                                <input type="text" name="recurrence_end_date"
                                    class="datepicker w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-purple-300 outline-none bg-white font-medium"
                                    placeholder="Pilih Tanggal Akhir">
                            </div>
                            <div class="col-span-2 text-xs text-purple-700">
                                <i class="fas fa-info-circle mr-1"></i> Sistem akan otomatis mengecek ketersediaan untuk
                                semua tanggal yang dipilih. Jika ada satu tanggal yang bentrok, pengajuan akan ditolak.
                            </div>
                        </div>
                    </div>


                    <!-- Contact Wrapper (Grid 2 Cols) -->
                    <div
                        class="col-span-1 md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-6 bg-gray-50 p-4 rounded-lg border border-gray-100">
                        <div class="col-span-1 md:col-span-2 mb-1">
                            <label class="block text-sm font-bold text-gray-700">
                                <i class="fas fa-address-book text-blue-600 mr-1"></i>Informasi Kontak (Wajib Isi Salah
                                Satu)
                            </label>
                            <p class="text-xs text-gray-500">Anda akan menerima notifikasi status peminjaman melalui
                                kontak yang diisi.</p>
                        </div>

                        <!-- WhatsApp (Opsional jika email diisi) -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                <i class="fab fa-whatsapp text-green-600 mr-1"></i>No. WhatsApp
                            </label>
                            <input type="tel" name="phone_number" id="phoneInput"
                                placeholder="08xxx (Wajib jika tanpa Email)"
                                class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all outline-none">
                        </div>

                        <!-- Email (Opsional jika WA diisi) -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                <i class="fas fa-envelope text-red-600 mr-1"></i>Email
                            </label>
                            <input type="email" name="user_email" id="emailInput"
                                placeholder="contoh@email.com (Wajib jika tanpa WA)"
                                class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all outline-none">
                        </div>
                    </div>


                </div>

                <!-- File Upload (Conditional) -->
                <div id="fileWrapper" class="hidden mb-6">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-file-upload text-blue-600 mr-1"></i>Upload Surat Permohonan <span
                            class="text-red-500">*</span>
                    </label>
                    <input type="file" name="file_pendukung" accept=".pdf,.doc,.docx"
                        class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all outline-none file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-blue-50 file:text-blue-700 file:font-semibold hover:file:bg-blue-100">
                    <p class="mt-1 text-sm text-gray-500">Format: PDF, DOC, DOCX (Maks. 5MB). Wajib untuk Instansi Luar.
                    </p>
                </div>

                <!-- Info Box -->
                <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded-lg mb-6">
                    <div class="flex items-start gap-3">
                        <i class="fas fa-info-circle text-blue-600 text-xl mt-0.5"></i>
                        <div>
                            <p class="text-sm text-blue-900 font-semibold mb-1">Catatan Penting:</p>
                            <p class="text-sm text-blue-800">
                                Pengajuan Anda akan diproses dalam waktu maksimal 1x24 jam.
                                Silakan cek status pengajuan Anda secara berkala melalui menu "Cek Status".
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex flex-col sm:flex-row gap-3">
                    <button type="submit"
                        class="flex-1 bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white font-bold py-4 px-6 rounded-lg shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-200">
                        <i class="fas fa-paper-plane mr-2"></i> Ajukan Peminjaman
                    </button>
                    <a href="index.php"
                        class="flex-1 text-center bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-4 px-6 rounded-lg shadow-md hover:shadow-lg transition-all duration-200">
                        <i class="fas fa-times mr-2"></i> Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
</section>

<script>
    const instansiSelect = document.getElementById('instansi');
    const fileWrapper = document.getElementById('fileWrapper');
    // const fileInput = fileWrapper.querySelector('input[type="file"]');

    // Recurring Toggle
    const recurringCheckbox = document.getElementById('is_recurring');
    const recurringOptions = document.getElementById('recurringOptions');

    recurringCheckbox.addEventListener('change', function () {
        if (this.checked) {
            recurringOptions.classList.remove('hidden');
            recurringOptions.querySelectorAll('select, input').forEach(el => el.required = true);
        } else {
            recurringOptions.classList.add('hidden');
            recurringOptions.querySelectorAll('select, input').forEach(el => el.required = false);
        }
    });

    instansiSelect.addEventListener('change', function () {
        if (this.value === 'lainnya') { // Kept 'lainnya' as per original, instruction had 'Luar' but original HTML has 'lainnya'
            fileWrapper.classList.remove('hidden');
            fileWrapper.querySelector('input').setAttribute('required', 'required');
        } else {
            fileWrapper.classList.add('hidden');
            fileWrapper.querySelector('input').removeAttribute('required');
        }
    });

    // Add loading on submit
    document.querySelector('form').addEventListener('submit', function (e) {
        // Form validation is handled by browser because of 'required' attributes
        // If valid, show loading
        if (this.checkValidity()) {
            Swal.fire({
                title: 'Memproses...',
                text: 'Mohon tunggu, sedang mengirim data.',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
        }
    });

    // Cek kondisi awal saat halaman load (misal kalau ada validasi error dan form keisi ulang)
    if (instansiSelect.value === 'lainnya') { // Kept 'lainnya' for consistency with the select options
        fileWrapper.classList.remove('hidden');
        fileInput.required = true;
    }
</script>

<?php require 'includes/footer.php'; ?>