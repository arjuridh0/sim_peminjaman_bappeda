<?php
require_once '../includes/functions.php';
require_once 'header.php';

// Get Data
// Get Data
$selectedYear = $_GET['year'] ?? date('Y');
$availableYears = get_available_years();

$stats = get_booking_stats();
$overSLA = get_pending_over_sla();
$monthlyStats = get_monthly_stats($selectedYear);
$roomUsage = get_room_usage_stats();

// Get today's bookings
global $pdo;
$today = date('Y-m-d');
$stmt = $pdo->prepare("
    SELECT b.*, r.name as room_name 
    FROM bookings b 
    JOIN rooms r ON b.room_id = r.id 
    WHERE b.tanggal = ? 
    ORDER BY b.waktu_mulai ASC 
    LIMIT 5
");
$stmt->execute([$today]);
$todayBookings = $stmt->fetchAll();
?>

<!-- Page Header -->
<div class="mb-6">
    <h1 class="text-3xl font-bold text-gray-900 mb-2">
        <i class="fas fa-tachometer-alt mr-2 text-blue-600"></i>Dashboard Admin
    </h1>
    <p class="text-gray-600">Selamat datang, <?= htmlspecialchars($_SESSION['admin_username'] ?? 'Admin') ?>! Berikut
        ringkasan sistem peminjaman ruangan.</p>
</div>

<!-- Quick Actions -->
<div class="bg-gradient-to-r from-blue-600 to-blue-700 rounded-xl shadow-lg p-6 mb-8">
    <h3 class="text-white font-bold text-lg mb-4 flex items-center gap-2">
        <i class="fas fa-bolt"></i>
        <span>Quick Actions</span>
    </h3>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
        <a href="calendar.php"
            class="bg-white/10 hover:bg-white/20 backdrop-blur-sm text-white rounded-lg p-4 text-center transition group">
            <i class="fas fa-calendar-alt text-3xl mb-2 group-hover:scale-110 transition"></i>
            <p class="text-sm font-semibold">Lihat Kalender</p>
        </a>
        <a href="bookings.php?status=menunggu"
            class="bg-white/10 hover:bg-white/20 backdrop-blur-sm text-white rounded-lg p-4 text-center transition group">
            <i class="fas fa-clock text-3xl mb-2 group-hover:scale-110 transition"></i>
            <p class="text-sm font-semibold">Pending (<?= $stats['menunggu'] ?>)</p>
        </a>
        <a href="room_form.php"
            class="bg-white/10 hover:bg-white/20 backdrop-blur-sm text-white rounded-lg p-4 text-center transition group">
            <i class="fas fa-plus-circle text-3xl mb-2 group-hover:scale-110 transition"></i>
            <p class="text-sm font-semibold">Tambah Ruangan</p>
        </a>
        <a href="bookings.php"
            class="bg-white/10 hover:bg-white/20 backdrop-blur-sm text-white rounded-lg p-4 text-center transition group">
            <i class="fas fa-list text-3xl mb-2 group-hover:scale-110 transition"></i>
            <p class="text-sm font-semibold">Semua Booking</p>
        </a>
    </div>
</div>

<!-- Stats Grid -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
    <div class="bg-white p-6 rounded-lg shadow border-l-4 border-blue-500">
        <div class="flex justify-between items-center">
            <div>
                <p class="text-gray-500 text-sm font-semibold">Total Booking</p>
                <h3 class="text-3xl font-bold text-gray-800">
                    <?= $stats['total'] ?>
                </h3>
            </div>
            <i class="fas fa-calendar-alt text-blue-200 text-4xl"></i>
        </div>
    </div>
    <div class="bg-white p-6 rounded-lg shadow border-l-4 border-yellow-500">
        <div class="flex justify-between items-center">
            <div>
                <p class="text-gray-500 text-sm font-semibold">Menunggu</p>
                <h3 class="text-3xl font-bold text-gray-800">
                    <?= $stats['menunggu'] ?>
                </h3>
            </div>
            <i class="fas fa-clock text-yellow-200 text-4xl"></i>
        </div>
    </div>
    <div class="bg-white p-6 rounded-lg shadow border-l-4 border-green-500">
        <div class="flex justify-between items-center">
            <div>
                <p class="text-gray-500 text-sm font-semibold">Disetujui</p>
                <h3 class="text-3xl font-bold text-gray-800">
                    <?= $stats['disetujui'] ?>
                </h3>
            </div>
            <i class="fas fa-check-circle text-green-200 text-4xl"></i>
        </div>
    </div>
    <div class="bg-white p-6 rounded-lg shadow border-l-4 border-red-500">
        <div class="flex justify-between items-center">
            <div>
                <p class="text-gray-500 text-sm font-semibold">Ditolak/Batal</p>
                <h3 class="text-3xl font-bold text-gray-800">
                    <?= $stats['ditolak'] + $stats['dibatalkan'] ?>
                </h3>
            </div>
            <i class="fas fa-ban text-red-200 text-4xl"></i>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

    <!-- Left Column (Alerts & Monthly) -->
    <div class="lg:col-span-2 space-y-8">

        <!-- SLA Alerts -->
        <?php if (!empty($overSLA)): ?>
            <div class="bg-red-50 border border-red-200 rounded-lg shadow-sm overflow-hidden">
                <div class="px-6 py-4 bg-red-100 border-b border-red-200 flex items-center gap-3">
                    <i class="fas fa-exclamation-triangle text-red-600"></i>
                    <h3 class="font-bold text-red-800">Perhatian: Booking Pending > 24 Jam</h3>
                </div>
                <div class="p-6">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="text-left text-gray-500 border-b border-red-200">
                                    <th class="pb-2">Peminjam</th>
                                    <th class="pb-2">Instansi</th>
                                    <th class="pb-2">Durasi Pending</th>
                                    <th class="pb-2">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-red-100">
                                <?php foreach ($overSLA as $booking): ?>
                                    <tr>
                                        <td class="py-3 font-semibold">
                                            <?= htmlspecialchars($booking['nama_peminjam']) ?>
                                        </td>
                                        <td class="py-3">
                                            <?= htmlspecialchars($booking['instansi']) ?>
                                        </td>
                                        <td class="py-3 text-red-600 font-bold">
                                            <?= $booking['hours_pending'] ?> Jam
                                        </td>
                                        <td class="py-3">
                                            <a href="bookings.php" class="text-blue-600 hover:underline">Proses</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Today's Bookings -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="px-6 py-4 bg-blue-50 border-b flex justify-between items-center">
                <h3 class="font-bold text-gray-800 flex items-center gap-2">
                    <i class="fas fa-calendar-day text-blue-600"></i>
                    <span>Booking Hari Ini (<?= date('d F Y') ?>)</span>
                </h3>
                <span class="bg-blue-600 text-white px-3 py-1 rounded-full text-sm font-bold">
                    <?= count($todayBookings) ?>
                </span>
            </div>
            <div class="p-6">
                <?php if (empty($todayBookings)): ?>
                    <div class="text-center py-8 text-gray-500">
                        <i class="fas fa-calendar-times text-4xl mb-3 opacity-50"></i>
                        <p>Tidak ada booking untuk hari ini</p>
                    </div>
                <?php else: ?>
                    <div class="space-y-3">
                        <?php foreach ($todayBookings as $booking):
                            $statusColors = [
                                'menunggu' => 'bg-yellow-100 text-yellow-800 border-yellow-300',
                                'disetujui' => 'bg-green-100 text-green-800 border-green-300',
                                'ditolak' => 'bg-red-100 text-red-800 border-red-300',
                                'dibatalkan' => 'bg-gray-100 text-gray-800 border-gray-300'
                            ];
                            $statusColor = $statusColors[$booking['status']] ?? 'bg-gray-100 text-gray-800';
                            ?>
                            <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition">
                                <div class="flex justify-between items-start mb-2">
                                    <div class="flex-1">
                                        <h4 class="font-bold text-gray-900"><?= htmlspecialchars($booking['room_name']) ?></h4>
                                        <p class="text-sm text-gray-600"><?= htmlspecialchars($booking['kegiatan']) ?></p>
                                    </div>
                                    <span class="px-3 py-1 rounded-full text-xs font-bold border <?= $statusColor ?>">
                                        <?= ucfirst($booking['status']) ?>
                                    </span>
                                </div>
                                <div class="flex items-center gap-4 text-sm text-gray-600">
                                    <span><i
                                            class="fas fa-user mr-1"></i><?= htmlspecialchars($booking['nama_peminjam']) ?></span>
                                    <span><i
                                            class="fas fa-clock mr-1"></i><?= date('H:i', strtotime($booking['waktu_mulai'])) ?>
                                        - <?= date('H:i', strtotime($booking['waktu_selesai'])) ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="mt-4 text-center">
                        <a href="calendar.php" class="text-blue-600 hover:text-blue-800 font-semibold text-sm">
                            Lihat Semua di Kalender <i class="fas fa-arrow-right ml-1"></i>
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Monthly Chart (Interaktif dengan Chart.js) -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="px-6 py-4 border-b flex justify-between items-center">
                <h3 class="font-bold text-gray-800 flex items-center gap-2">
                    <i class="fas fa-chart-bar text-blue-600"></i>
                    Statistik Bulanan
                </h3>
                
                <!-- Year Filter Form -->
                <form method="GET" action="index.php" class="flex items-center gap-2">
                    <label for="year" class="text-sm font-semibold text-gray-600">Tahun:</label>
                    <div class="relative">
                        <select name="year" id="year" onchange="this.form.submit()"
                            class="appearance-none bg-gray-50 border border-gray-300 text-gray-700 py-1 pl-3 pr-8 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 font-semibold cursor-pointer">
                            <?php foreach ($availableYears as $year): ?>
                                <option value="<?= $year ?>" <?= $year == $selectedYear ? 'selected' : '' ?>>
                                    <?= $year ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
                            <i class="fas fa-chevron-down text-xs"></i>
                        </div>
                    </div>
                </form>
            </div>
            
            <div class="p-6">
                <!-- Chart Canvas -->
                <div class="relative h-64 w-full">
                    <canvas id="monthlyChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Column (Room Usage) -->
    <div class="bg-white rounded-lg shadow overflow-hidden h-fit">
        <div class="px-6 py-4 border-b">
            <h3 class="font-bold text-gray-800">Ruangan Terpopuler</h3>
        </div>
        <div class="divide-y divide-gray-100">
            <?php foreach ($roomUsage as $index => $room): ?>
                <div class="px-6 py-4 flex items-center justify-between hover:bg-gray-50">
                    <div class="flex items-center gap-3">
                        <span
                            class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold <?= $index < 3 ? 'bg-yellow-100 text-yellow-700' : 'bg-gray-100 text-gray-500' ?>">
                            <?= $index + 1 ?>
                        </span>
                        <span class="font-medium text-gray-700">
                            <?= htmlspecialchars($room['room_name']) ?>
                        </span>
                    </div>
                    <span class="bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded-full font-bold">
                        <?= $room['booking_count'] ?> x
                    </span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

</div>

<!-- Chart.js Library -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    // Prepare Data
    <?php
    $months = array_fill(1, 12, 0); // Init 12 months with 0
    foreach ($monthlyStats as $stat) {
        $months[$stat['month']] = $stat['count'];
    }
    ?>
    
    const chartData = <?= json_encode(array_values($months)) ?>;
    const ctx = document.getElementById('monthlyChart').getContext('2d');
    
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
            datasets: [{
                label: 'Jumlah Peminjaman',
                data: chartData,
                backgroundColor: 'rgba(59, 130, 246, 0.8)', // blue-500
                borderColor: 'rgb(37, 99, 235)', // blue-600
                borderWidth: 1,
                borderRadius: 4,
                hoverBackgroundColor: 'rgba(37, 99, 235, 1)'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: 'rgba(17, 24, 39, 0.9)',
                    padding: 12,
                    cornerRadius: 8,
                    callbacks: {
                        label: function(context) {
                            return context.parsed.y + ' Peminjaman';
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1,
                        precision: 0
                    },
                    grid: {
                        color: 'rgba(156, 163, 175, 0.1)'
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            },
            animation: {
                duration: 1000,
                easing: 'easeOutQuart'
            }
        }
    });
</script>

</body>
</html>