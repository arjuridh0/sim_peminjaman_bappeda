<?php
require_once 'includes/functions.php';

// ==============================================================================
// HANDLE LOGIC (POST)
// ==============================================================================

// 1. Cek Status (Search)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'check') {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        set_flash_message('error', "Invalid security token.");
        redirect('booking_status.php');
    }

    $searchQuery = trim($_POST['search_query'] ?? '');
    if (empty($searchQuery)) {
        // If empty, redirect to default page (recent bookings)
        redirect('booking_status.php');
    }

    // Try to find by token first
    $booking = get_booking_by_token($searchQuery);

    if ($booking) {
        // Found by token - Store in session and redirect to generic view to HIDE URL
        $_SESSION['access_token'] = $searchQuery;
        redirect('booking_status.php?view=access_granted');
    } else {
        // Search by name - redirect to search results
        redirect('booking_status.php?search=' . urlencode($searchQuery));
    }
}

// 2. Batalkan Booking
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'cancel') {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        set_flash_message('error', "Invalid security token.");
        redirect_back();
    }

    $bookingId = $_POST['booking_id'] ?? null;
    $reason = $_POST['cancel_reason'] ?? 'Dibatalkan oleh peminjam';
    $verification = trim($_POST['verification'] ?? ''); // Booking code verification

    if (empty($verification)) {
        set_flash_message('error', "Kode Booking wajib diisi untuk verifikasi pembatalan.");
        redirect_back();
    }

    // Get booking original
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM bookings WHERE id = ?");
    $stmt->execute([$bookingId]);
    $booking = $stmt->fetch();

    if (!$booking) {
        set_flash_message('error', "Booking tidak ditemukan");
        redirect_back();
    }

    // Security Check: Verify Booking Code (qr_token)
    if ($booking['qr_token'] !== $verification) {
        set_flash_message('error', "Kode Booking tidak valid! Pembatalan gagal.");
        redirect_back();
    }

    if (!in_array($booking['status'], ['menunggu', 'disetujui'])) {
        set_flash_message('error', "Hanya booking dengan status 'Menunggu' atau 'Disetujui' yang dapat dibatalkan.");
        redirect_back();
    }

    // Execute Cancel
    update_booking_status($bookingId, 'dibatalkan', $reason);

    // Send Notification (Non-blocking)
    try {
        // Update booking array with new status for notification
        $booking['status'] = 'dibatalkan';
        $booking['cancel_reason'] = $reason;

        send_booking_notification($booking, 'cancellation');
    } catch (Exception $e) {
        error_log("Cancellation Notification Failed: " . $e->getMessage());
    }

    set_flash_message('success', "Booking berhasil dibatalkan.");
    // Redirect back to access view if we have token, otherwise generic
    if (isset($_SESSION['access_token']) && $_SESSION['access_token'] === $booking['qr_token']) {
        redirect('booking_status.php?view=access_granted');
    } else {
        redirect('booking_status.php?id=' . $booking['id']);
    }
}


// ==============================================================================
// HANDLE VIEW (GET)
// ==============================================================================
$view = $_GET['view'] ?? null;
$id = $_GET['id'] ?? null;
$searchQuery = $_GET['search'] ?? null;

$booking = null;
$searchResults = [];
$recentBookings = [];

// Mode 1: Private Access (via Token in Session)
if ($view === 'access_granted' && isset($_SESSION['access_token'])) {
    $booking = get_booking_by_token($_SESSION['access_token']);
}

// Mode 2: Public Detail View (via ID)
if ($id && !$booking) {
    // Only fetch if not already loaded by token
    $booking = get_booking_by_id($id);
}

// Mode 3: Search results (by name)
if ($searchQuery && !$booking) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT b.*, r.name as room_name 
        FROM bookings b 
        JOIN rooms r ON b.room_id = r.id 
        WHERE b.nama_peminjam LIKE ? 
        ORDER BY b.created_at DESC 
        LIMIT 20
    ");
    $stmt->execute(['%' . $searchQuery . '%']);
    $rows = $stmt->fetchAll();

    // SECURITY: Remove qr_token from public results
    foreach ($rows as $row) {
        unset($row['qr_token']);
        $searchResults[] = $row;
    }
}

// Mode 3: Recent bookings listing (default view)
if (!$view && !$id && !$searchQuery) {
    global $pdo;
    $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
    $perPage = 10;
    $offset = ($page - 1) * $perPage;

    // Use direct values for LIMIT and OFFSET (PDO can't bind these as parameters in all MySQL versions)
    $stmt = $pdo->query("
        SELECT b.*, r.name as room_name 
        FROM bookings b 
        JOIN rooms r ON b.room_id = r.id 
        ORDER BY b.created_at DESC 
        LIMIT $perPage OFFSET $offset
    ");
    $rows = $stmt->fetchAll();

    // SECURITY: Remove qr_token from recent results
    foreach ($rows as $row) {
        unset($row['qr_token']);
        $recentBookings[] = $row;
    }

    // Get total count for pagination
    $totalStmt = $pdo->query("SELECT COUNT(*) FROM bookings");
    $totalBookings = $totalStmt->fetchColumn();
    $totalPages = ceil($totalBookings / $perPage);
}

$title = 'Cek Status Peminjaman';
require 'includes/header.php';
?>

<section class="py-12">
    <div class="max-w-6xl mx-auto px-6">
        <div class="text-center mb-8">
            <h2
                class="text-3xl font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent mb-2">
                Cek Status Peminjaman
            </h2>
            <p class="text-gray-600">Cari dengan <strong>Kode Booking</strong> atau <strong>Nama Penanggung
                    Jawab</strong></p>
        </div>

        <!-- Check Status Form (Always visible for search) -->
        <div class="bg-white rounded-2xl shadow-xl p-8 mb-8 animate-fade-in">
            <form action="booking_status.php" method="POST">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="check">
                <div class="mb-6">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-search text-blue-600 mr-2"></i>Cari Peminjaman
                    </label>
                    <input type="text" name="search_query"
                        class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all outline-none text-lg"
                        placeholder="Masukkan Kode Booking atau Nama Penanggung Jawab"
                        value="<?= htmlspecialchars($searchQuery ?? '') ?>" autofocus>
                    <p class="mt-2 text-sm text-gray-500">
                        <i class="fas fa-info-circle mr-1"></i>
                        Contoh: <code class="bg-gray-100 px-2 py-1 rounded">a1b2c3d4</code> atau <code
                            class="bg-gray-100 px-2 py-1 rounded">John Doe</code>
                    </p>
                </div>
                <button type="submit"
                    class="w-full bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white font-bold py-4 px-6 rounded-lg shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-200">
                    <i class="fas fa-search mr-2"></i>Cari Peminjaman
                </button>
            </form>
        </div>

        <!-- Search Results -->
        <?php if (!empty($searchResults)): ?>
            <div class="mb-8">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-2xl font-bold text-gray-900">
                        <i class="fas fa-search-plus text-blue-600 mr-2"></i>
                        Hasil Pencarian "<?= htmlspecialchars($searchQuery) ?>" (<?= count($searchResults) ?> ditemukan)
                    </h3>

                    <!-- View Mode Toggle -->
                    <div class="flex gap-2 bg-gray-100 p-1 rounded-lg">
                        <button onclick="setViewMode('card')" id="cardViewBtn"
                            class="px-4 py-2 rounded-md transition-all font-semibold text-sm">
                            <i class="fas fa-th-large mr-1"></i> Card
                        </button>
                        <button onclick="setViewMode('table')" id="tableViewBtn"
                            class="px-4 py-2 rounded-md transition-all font-semibold text-sm">
                            <i class="fas fa-list mr-1"></i> Table
                        </button>
                    </div>
                </div>

                <!-- Card View -->
                <div id="cardView" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <?php foreach ($searchResults as $result):
                        $statusColors = [
                            'menunggu' => 'bg-yellow-100 text-yellow-800 border-yellow-300',
                            'disetujui' => 'bg-green-100 text-green-800 border-green-300',
                            'ditolak' => 'bg-red-100 text-red-800 border-red-300',
                            'dibatalkan' => 'bg-gray-100 text-gray-800 border-gray-300'
                        ];
                        $statusColor = $statusColors[$result['status']] ?? 'bg-gray-100';
                        $borderColor = strpos($statusColor, 'yellow') !== false ? 'border-yellow-500' :
                            (strpos($statusColor, 'green') !== false ? 'border-green-500' :
                                (strpos($statusColor, 'red') !== false ? 'border-red-500' : 'border-gray-500'));
                        ?>
                        <button onclick='openBookingModal(<?= json_encode($result) ?>)'
                            class="block w-full text-left bg-white rounded-xl shadow-md hover:shadow-xl transition-all p-6 border-l-4 <?= $borderColor ?> cursor-pointer">
                            <div class="flex justify-between items-start mb-3">
                                <h4 class="font-bold text-lg text-gray-900"><?= htmlspecialchars($result['room_name']) ?></h4>
                                <span class="px-3 py-1 rounded-full text-xs font-bold border <?= $statusColor ?>">
                                    <?= ucfirst($result['status']) ?>
                                </span>
                            </div>
                            <p class="text-gray-700 font-semibold mb-2">
                                <i class="fas fa-user mr-2 text-blue-600"></i><?= htmlspecialchars($result['nama_peminjam']) ?>
                            </p>
                            <p class="text-gray-600 text-sm mb-2">
                                <i class="fas fa-calendar mr-2"></i><?= date('d M Y', strtotime($result['tanggal'])) ?>
                                <span class="ml-3"><i
                                        class="fas fa-clock mr-2"></i><?= date('H:i', strtotime($result['waktu_mulai'])) ?> -
                                    <?= date('H:i', strtotime($result['waktu_selesai'])) ?></span>
                            </p>
                            </p>
                        </button>
                    <?php endforeach; ?>
                </div>

                <!-- Table View -->
                <div id="tableView" class="hidden bg-white rounded-xl shadow-md overflow-hidden">
                    <table class="w-full">
                        <thead class="bg-gradient-to-r from-blue-600 to-purple-600 text-white">
                            <tr>
                                <th class="px-6 py-4 text-left text-sm font-bold">Ruangan</th>
                                <th class="px-6 py-4 text-left text-sm font-bold">Peminjam</th>
                                <th class="px-6 py-4 text-left text-sm font-bold">Tanggal & Waktu</th>
                                <th class="px-6 py-4 text-left text-sm font-bold">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php foreach ($searchResults as $result):
                                $statusColors = [
                                    'menunggu' => 'bg-yellow-100 text-yellow-800',
                                    'disetujui' => 'bg-green-100 text-green-800',
                                    'ditolak' => 'bg-red-100 text-red-800',
                                    'dibatalkan' => 'bg-gray-100 text-gray-800'
                                ];
                                $statusColor = $statusColors[$result['status']] ?? 'bg-gray-100';
                                ?>
                                <tr onclick='openBookingModal(<?= json_encode($result) ?>)'
                                    class="hover:bg-blue-50 cursor-pointer transition-colors">
                                    <td class="px-6 py-4 font-semibold text-gray-900">
                                        <?= htmlspecialchars($result['room_name']) ?>
                                    </td>
                                    <td class="px-6 py-4 text-gray-700">
                                        <?= htmlspecialchars($result['nama_peminjam']) ?>
                                    </td>
                                    <td class="px-6 py-4 text-gray-600 text-sm">
                                        <?= date('d M Y', strtotime($result['tanggal'])) ?><br>
                                        <span class="text-xs"><?= date('H:i', strtotime($result['waktu_mulai'])) ?> -
                                            <?= date('H:i', strtotime($result['waktu_selesai'])) ?></span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="px-3 py-1 rounded-full text-xs font-bold <?= $statusColor ?>">
                                            <?= ucfirst($result['status']) ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>

        <!-- Recent Bookings Listing -->
        <?php if (!empty($recentBookings) && !$booking): ?>
            <div class="mb-8">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-2xl font-bold text-gray-900">
                        <i class="fas fa-list text-blue-600 mr-2"></i>
                        Peminjaman Terbaru
                    </h3>

                    <!-- View Mode Toggle -->
                    <div class="flex gap-2 bg-gray-100 p-1 rounded-lg">
                        <button onclick="setViewMode('card')" id="cardViewBtn"
                            class="px-4 py-2 rounded-md transition-all font-semibold text-sm">
                            <i class="fas fa-th-large mr-1"></i> Card
                        </button>
                        <button onclick="setViewMode('table')" id="tableViewBtn"
                            class="px-4 py-2 rounded-md transition-all font-semibold text-sm">
                            <i class="fas fa-list mr-1"></i> Table
                        </button>
                    </div>
                </div>

                <!-- Card View -->
                <div id="cardView" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <?php foreach ($recentBookings as $recent):
                        $statusColors = [
                            'menunggu' => 'bg-yellow-100 text-yellow-800 border-yellow-300',
                            'disetujui' => 'bg-green-100 text-green-800 border-green-300',
                            'ditolak' => 'bg-red-100 text-red-800 border-red-300',
                            'dibatalkan' => 'bg-gray-100 text-gray-800 border-gray-300'
                        ];
                        $statusColor = $statusColors[$recent['status']] ?? 'bg-gray-100';
                        ?>
                        <button onclick='openBookingModal(<?= json_encode($recent) ?>)'
                            class="block w-full text-left bg-white rounded-xl shadow-md hover:shadow-xl transition-all p-5 cursor-pointer">
                            <div class="flex justify-between items-start mb-3">
                                <h4 class="font-bold text-gray-900"><?= htmlspecialchars($recent['room_name']) ?></h4>
                                <span class="px-2 py-1 rounded-full text-xs font-bold border <?= $statusColor ?>">
                                    <?= ucfirst($recent['status']) ?>
                                </span>
                            </div>
                            <p class="text-gray-700 text-sm mb-2">
                                <i class="fas fa-user mr-1 text-blue-600"></i><?= htmlspecialchars($recent['nama_peminjam']) ?>
                            </p>
                            <p class="text-gray-600 text-xs">
                                <i class="fas fa-calendar mr-1"></i><?= date('d M Y', strtotime($recent['tanggal'])) ?>
                            </p>
                        </button>
                    <?php endforeach; ?>
                </div>

                <!-- Table View -->
                <div id="tableView" class="hidden bg-white rounded-xl shadow-md overflow-hidden">
                    <table class="w-full">
                        <thead class="bg-gradient-to-r from-blue-600 to-purple-600 text-white">
                            <tr>
                                <th class="px-6 py-4 text-left text-sm font-bold">Ruangan</th>
                                <th class="px-6 py-4 text-left text-sm font-bold">Peminjam</th>
                                <th class="px-6 py-4 text-left text-sm font-bold">Tanggal & Waktu</th>
                                <th class="px-6 py-4 text-left text-sm font-bold">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php foreach ($recentBookings as $recent):
                                $statusColors = [
                                    'menunggu' => 'bg-yellow-100 text-yellow-800',
                                    'disetujui' => 'bg-green-100 text-green-800',
                                    'ditolak' => 'bg-red-100 text-red-800',
                                    'dibatalkan' => 'bg-gray-100 text-gray-800'
                                ];
                                $statusColor = $statusColors[$recent['status']] ?? 'bg-gray-100';
                                ?>
                                <tr onclick='openBookingModal(<?= json_encode($recent) ?>)'
                                    class="hover:bg-blue-50 cursor-pointer transition-colors">
                                    <td class="px-6 py-4 font-semibold text-gray-900">
                                        <?= htmlspecialchars($recent['room_name']) ?>
                                    </td>
                                    <td class="px-6 py-4 text-gray-700">
                                        <?= htmlspecialchars($recent['nama_peminjam']) ?>
                                    </td>
                                    <td class="px-6 py-4 text-gray-600 text-sm">
                                        <?= date('d M Y', strtotime($recent['tanggal'])) ?><br>
                                        <span class="text-xs"><?= date('H:i', strtotime($recent['waktu_mulai'])) ?> -
                                            <?= date('H:i', strtotime($recent['waktu_selesai'])) ?></span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="px-3 py-1 rounded-full text-xs font-bold <?= $statusColor ?>">
                                            <?= ucfirst($recent['status']) ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if (isset($totalPages) && $totalPages > 1): ?>
                    <div class="flex justify-center gap-2 mt-6">
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <a href="?page=<?= $i ?>"
                                class="px-4 py-2 rounded-lg <?= ($page ?? 1) == $i ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-100' ?> font-semibold transition">
                                <?= $i ?>
                            </a>
                        <?php endfor; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- Booking Detail (if token provided) -->
        <?php if ($booking): ?>

            <!-- Booking Details Card -->
            <div class="bg-white rounded-2xl shadow-xl overflow-hidden animate-fade-in">
                <?php
                $statusConfig = [
                    'menunggu' => [
                        'bg' => 'bg-yellow-50',
                        'border' => 'border-yellow-500',
                        'text' => 'text-yellow-800',
                        'icon' => 'fa-clock',
                        'label' => 'Menunggu Persetujuan'
                    ],
                    'disetujui' => [
                        'bg' => 'bg-green-50',
                        'border' => 'border-green-500',
                        'text' => 'text-green-800',
                        'icon' => 'fa-check-circle',
                        'label' => 'Disetujui'
                    ],
                    'ditolak' => [
                        'bg' => 'bg-red-50',
                        'border' => 'border-red-500',
                        'text' => 'text-red-800',
                        'icon' => 'fa-times-circle',
                        'label' => 'Ditolak'
                    ],
                    'dibatalkan' => [
                        'bg' => 'bg-gray-50',
                        'border' => 'border-gray-500',
                        'text' => 'text-gray-800',
                        'icon' => 'fa-ban',
                        'label' => 'Dibatalkan'
                    ]
                ];
                $status = $statusConfig[$booking['status']] ?? $statusConfig['menunggu'];
                ?>

                <!-- Status Header -->
                <div class="<?= $status['bg'] ?> border-b-4 <?= $status['border'] ?> p-6 text-center">
                    <div class="inline-flex items-center gap-3 px-6 py-3 bg-white rounded-full shadow-md">
                        <i class="fas <?= $status['icon'] ?> <?= $status['text'] ?> text-2xl"></i>
                        <span class="<?= $status['text'] ?> font-bold text-lg">
                            <?= $status['label'] ?>
                        </span>
                    </div>
                </div>

                <!-- Booking Code Section (Only shown on FIRST VIEW after booking) -->
                <?php
                $showBookingCode = isset($_SESSION['new_booking_token']) && $_SESSION['new_booking_token'] === $booking['qr_token'];
                if ($showBookingCode):
                    // Clear the session after displaying
                    unset($_SESSION['new_booking_token']);
                    ?>
                    <div class="p-6 bg-gradient-to-r from-green-50 to-emerald-50 border-b-4 border-green-500">
                        <div class="bg-green-100 border-l-4 border-green-600 p-4 rounded-lg mb-4">
                            <div class="flex items-start gap-3">
                                <i class="fas fa-exclamation-triangle text-green-700 text-2xl mt-1"></i>
                                <div>
                                    <p class="text-green-900 font-bold mb-1">⚠️ PENTING: Simpan Kode Booking Anda!</p>
                                    <p class="text-green-800 text-sm">
                                        Kode ini <strong>HANYA DITAMPILKAN SEKALI</strong> dan diperlukan untuk membatalkan
                                        peminjaman.
                                        Kode juga telah dikirim ke email Anda. Jangan bagikan kode ini kepada orang lain.
                                    </p>
                                </div>
                            </div>
                        </div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-key mr-1 text-green-600"></i>Kode Booking Anda (Rahasia)
                        </label>
                        <div class="flex items-center gap-2">
                            <input type="text" id="bookingCode" value="<?= htmlspecialchars($booking['qr_token']) ?>" readonly
                                class="flex-1 px-4 py-3 bg-white border-2 border-green-300 rounded-lg font-mono text-lg text-green-700 font-bold">
                            <button onclick="copyBookingCode()"
                                class="px-6 py-3 bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 text-white font-bold rounded-lg shadow-md hover:shadow-lg transform hover:scale-105 transition-all duration-200 flex items-center gap-2">
                                <i class="fas fa-copy"></i>
                                <span class="hidden sm:inline">Copy</span>
                            </button>
                        </div>
                        <p id="copyFeedback" class="mt-2 text-sm text-green-600 font-semibold hidden">
                            <i class="fas fa-check-circle mr-1"></i>Kode berhasil disalin! Simpan di tempat aman.
                        </p>
                    </div>
                <?php endif; ?>

                <!-- Details -->
                <div class="p-6">
                    <h3 class="text-xl font-bold text-gray-900 mb-4 flex items-center gap-2">
                        <i class="fas fa-info-circle text-blue-600"></i>Detail Peminjaman
                    </h3>

                    <div class="space-y-4">
                        <div class="flex items-start gap-4 p-4 bg-gray-50 rounded-lg">
                            <i class="fas fa-door-open text-blue-600 text-xl mt-1"></i>
                            <div class="flex-1">
                                <p class="text-sm text-gray-600">Ruangan</p>
                                <p class="font-semibold text-gray-900 text-lg">
                                    <?= htmlspecialchars($booking['room_name']) ?>
                                </p>
                            </div>
                        </div>

                        <div class="flex items-start gap-4 p-4 bg-gray-50 rounded-lg">
                            <i class="fas fa-user text-blue-600 text-xl mt-1"></i>
                            <div class="flex-1">
                                <p class="text-sm text-gray-600">Peminjam</p>
                                <p class="font-semibold text-gray-900">
                                    <?= htmlspecialchars($booking['nama_peminjam']) ?>
                                </p>
                                <p class="text-sm text-gray-500">
                                    <?= htmlspecialchars($booking['instansi']) ?>
                                </p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="flex items-start gap-4 p-4 bg-gray-50 rounded-lg">
                                <i class="fas fa-calendar text-blue-600 text-xl mt-1"></i>
                                <div class="flex-1">
                                    <p class="text-sm text-gray-600">Tanggal</p>
                                    <p class="font-semibold text-gray-900">
                                        <?= date('d F Y', strtotime($booking['tanggal'])) ?>
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-start gap-4 p-4 bg-gray-50 rounded-lg">
                                <i class="fas fa-clock text-blue-600 text-xl mt-1"></i>
                                <div class="flex-1">
                                    <p class="text-sm text-gray-600">Waktu</p>
                                    <p class="font-semibold text-gray-900">
                                        <?= date('H:i', strtotime($booking['waktu_mulai'])) ?> -
                                        <?= date('H:i', strtotime($booking['waktu_selesai'])) ?>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <?php if (!empty($booking['rejection_reason'])): ?>
                            <div class="bg-red-50 p-4 rounded-lg mt-4 border border-red-200">
                                <p class="text-red-800 font-bold">Alasan ditolak:</p>
                                <p class="text-red-700">
                                    <?= htmlspecialchars($booking['rejection_reason']) ?>
                                </p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Actions -->
                <div class="p-6 bg-gray-50 border-t border-gray-200 flex flex-col sm:flex-row gap-3">
                    <?php if (in_array($booking['status'], ['menunggu', 'disetujui'])): ?>
                        <form id="cancelForm" action="booking_status.php" method="POST" class="flex-1">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="cancel">
                            <input type="hidden" name="booking_id" value="<?= $booking['id'] ?>">
                            <input type="hidden" name="cancel_reason" value="Dibatalkan oleh peminjam">
                            <input type="hidden" name="verification" id="cancelVerification">
                            <button type="button" onclick="confirmCancel()"
                                class="w-full bg-red-500 hover:bg-red-600 text-white font-bold py-3 px-6 rounded-lg shadow-md hover:shadow-lg transition-all duration-200">
                                <i class="fas fa-ban mr-2"></i>Batalkan Peminjaman
                            </button>
                        </form>
                    <?php endif; ?>

                    <a href="booking_status.php"
                        class="flex-1 text-center bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-3 px-6 rounded-lg shadow-md hover:shadow-lg transition-all duration-200">
                        <i class="fas fa-search mr-2"></i>Cek Booking Lain
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<script>
    function copyBookingCode() {
        const codeInput = document.getElementById('bookingCode');
        const feedback = document.getElementById('copyFeedback');
        codeInput.select();
        codeInput.setSelectionRange(0, 99999);
        if (navigator.clipboard) {
            navigator.clipboard.writeText(codeInput.value).then(() => showCopyFeedback());
        } else {
            document.execCommand('copy');
            showCopyFeedback();
        }
        function showCopyFeedback() {
            feedback.classList.remove('hidden');
            setTimeout(() => feedback.classList.add('hidden'), 3000);
        }
    }

    function confirmCancel() {
        Swal.fire({
            title: 'Batalkan Peminjaman?',
            text: "Masukkan Kode Booking Anda untuk verifikasi pembatalan.",
            input: 'text',
            inputPlaceholder: 'Kode Booking (contoh: a1b2c3d4)',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Batalkan!',
            cancelButtonText: 'Kembali',
            inputValidator: (value) => {
                if (!value) {
                    return 'Kode Booking tidak boleh kosong!'
                }
            }
        }).then((result) => {
            if (result.isConfirmed) {
                // Set value verification
                document.getElementById('cancelVerification').value = result.value;

                // Show loading
                Swal.fire({
                    title: 'Memproses...',
                    text: 'Mohon tunggu sebentar, sistem sedang memverifikasi dan mengirim email.',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                // Submit form
                document.getElementById('cancelForm').submit();
            }
        });
    }
</script>

<!-- Booking Detail Modal -->
<div id="bookingModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50 p-4"
    onclick="closeModalOnBackdrop(event)">
    <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto"
        onclick="event.stopPropagation()">
        <!-- Modal Header -->
        <div class="bg-gradient-to-r from-blue-600 to-purple-600 text-white p-6 rounded-t-2xl">
            <div class="flex justify-between items-start">
                <div>
                    <h3 class="text-2xl font-bold mb-1" id="modalRoomName">-</h3>
                </div>
                <button onclick="closeBookingModal()"
                    class="text-white hover:bg-white hover:bg-opacity-20 rounded-full p-2 transition">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
        </div>

        <!-- Modal Body -->
        <div class="p-6">
            <!-- Status Badge -->
            <div class="mb-6 text-center">
                <span id="modalStatus" class="inline-block px-6 py-2 rounded-full text-sm font-bold">-</span>
            </div>

            <!-- Booking Details -->
            <div class="grid grid-cols-2 gap-3 text-sm">
                <div class="p-2 bg-gray-50 rounded border border-gray-100">
                    <p class="text-xs text-gray-500 mb-0.5">Peminjam</p>
                    <p class="font-semibold text-gray-900 truncate" id="modalNama">-</p>
                </div>
                <div class="p-2 bg-gray-50 rounded border border-gray-100">
                    <p class="text-xs text-gray-500 mb-0.5">Instansi</p>
                    <p class="font-semibold text-gray-900 truncate" id="modalInstansi">-</p>
                </div>

                <div class="p-2 bg-gray-50 rounded border border-gray-100">
                    <p class="text-xs text-gray-500 mb-0.5">Tanggal</p>
                    <p class="font-semibold text-gray-900" id="modalTanggal">-</p>
                </div>
                <div class="p-2 bg-gray-50 rounded border border-gray-100">
                    <p class="text-xs text-gray-500 mb-0.5">Waktu</p>
                    <p class="font-semibold text-gray-900" id="modalWaktu">-</p>
                </div>

                <div class="col-span-2 p-2 bg-gray-50 rounded border border-gray-100">
                    <p class="text-xs text-gray-500 mb-0.5">Kegiatan</p>
                    <p class="font-semibold text-gray-900 break-words" id="modalKegiatan">-</p>
                </div>

                <div class="p-2 bg-gray-50 rounded border border-gray-100">
                    <p class="text-xs text-gray-500 mb-0.5">Peserta</p>
                    <p class="font-semibold text-gray-900" id="modalPeserta">-</p>
                </div>
                <div class="p-2 bg-gray-50 rounded border border-gray-100">
                    <p class="text-xs text-gray-500 mb-0.5">Email</p>
                    <p class="font-semibold text-gray-900 truncate" id="modalEmail">-</p>
                </div>
            </div>

            <!-- Rejection Reason (if any) -->
            <div id="rejectionReasonSection" class="hidden bg-red-50 border-l-4 border-red-500 p-4 rounded">
                <p class="text-red-900 font-semibold mb-1"><i class="fas fa-exclamation-circle mr-2"></i>Alasan
                    Penolakan:</p>
                <p class="text-red-800" id="modalRejectionReason">-</p>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="mt-6 flex gap-3">
            <button onclick="closeBookingModal()"
                class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-3 px-6 rounded-lg transition">
                <i class="fas fa-times mr-2"></i>Tutup
            </button>
            <a id="modalDetailLink" href="#"
                class="flex-1 bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white font-bold py-3 px-6 rounded-lg transition text-center">
                <i class="fas fa-external-link-alt mr-2"></i>Lihat Detail Lengkap
            </a>
        </div>
    </div>
</div>
</div>

<script>
    // View Mode Toggle
    function setViewMode(mode) {
        const cardView = document.getElementById('cardView');
        const tableView = document.getElementById('tableView');
        const cardBtn = document.getElementById('cardViewBtn');
        const tableBtn = document.getElementById('tableViewBtn');

        // Check if elements exist (only on search results page)
        if (!cardView || !tableView || !cardBtn || !tableBtn) {
            return; // Exit if elements don't exist
        }

        if (mode === 'card') {
            cardView.classList.remove('hidden');
            tableView.classList.add('hidden');
            cardBtn.classList.add('bg-white', 'text-blue-600');
            cardBtn.classList.remove('text-gray-600');
            tableBtn.classList.remove('bg-white', 'text-blue-600');
            tableBtn.classList.add('text-gray-600');
            localStorage.setItem('viewMode', 'card');
        } else {
            cardView.classList.add('hidden');
            tableView.classList.remove('hidden');
            tableBtn.classList.add('bg-white', 'text-blue-600');
            tableBtn.classList.remove('text-gray-600');
            cardBtn.classList.remove('bg-white', 'text-blue-600');
            cardBtn.classList.add('text-gray-600');
            localStorage.setItem('viewMode', 'table');
        }
    }

    // Initialize view mode from localStorage
    document.addEventListener('DOMContentLoaded', function () {
        const savedMode = localStorage.getItem('viewMode') || 'card';
        setViewMode(savedMode);
    });

    // Open Booking Modal
    function openBookingModal(booking) {
        // Populate modal with booking data
        document.getElementById('modalRoomName').textContent = booking.room_name;
        document.getElementById('modalNama').textContent = booking.nama_peminjam;
        document.getElementById('modalInstansi').textContent = booking.instansi;
        document.getElementById('modalTanggal').textContent = formatDate(booking.tanggal);
        document.getElementById('modalWaktu').textContent = formatTime(booking.waktu_mulai) + ' - ' + formatTime(booking.waktu_selesai);
        document.getElementById('modalKegiatan').textContent = booking.kegiatan;
        document.getElementById('modalPeserta').textContent = booking.jumlah_peserta + ' orang';
        document.getElementById('modalEmail').textContent = booking.email || '-';

        // Link to Detail by ID (Public)
        document.getElementById('modalDetailLink').href = 'booking_status.php?id=' + booking.id;

        // Set status badge
        const statusColors = {
            'menunggu': 'bg-yellow-100 text-yellow-800 border-2 border-yellow-300',
            'disetujui': 'bg-green-100 text-green-800 border-2 border-green-300',
            'ditolak': 'bg-red-100 text-red-800 border-2 border-red-300',
            'dibatalkan': 'bg-gray-100 text-gray-800 border-2 border-gray-300'
        };
        const statusBadge = document.getElementById('modalStatus');
        statusBadge.className = 'inline-block px-6 py-2 rounded-full text-sm font-bold ' + (statusColors[booking.status] || 'bg-gray-100');
        statusBadge.textContent = booking.status.charAt(0).toUpperCase() + booking.status.slice(1);

        // Show rejection reason if exists
        const rejectionSection = document.getElementById('rejectionReasonSection');
        if (booking.status === 'ditolak' && booking.rejection_reason) {
            document.getElementById('modalRejectionReason').textContent = booking.rejection_reason;
            rejectionSection.classList.remove('hidden');
        } else {
            rejectionSection.classList.add('hidden');
        }

        // Show modal
        const modal = document.getElementById('bookingModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.style.overflow = 'hidden';
    }

    // Close Modal
    function closeBookingModal() {
        const modal = document.getElementById('bookingModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.body.style.overflow = 'auto';
    }

    // Close modal when clicking backdrop
    function closeModalOnBackdrop(event) {
        if (event.target.id === 'bookingModal') {
            closeBookingModal();
        }
    }

    // Helper: Format date
    function formatDate(dateStr) {
        const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        const date = new Date(dateStr);
        return date.getDate() + ' ' + months[date.getMonth()] + ' ' + date.getFullYear();
    }

    // Helper: Format time
    function formatTime(timeStr) {
        return timeStr.substring(0, 5);
    }

    // Close modal with ESC key
    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            closeBookingModal();
        }
    });
</script>

<?php require 'includes/footer.php'; ?>