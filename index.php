<?php
require_once 'includes/functions.php';

// --- LOGIC PENCARIAN & DATA ---
$date = $_GET['date'] ?? null;
$startTime = $_GET['start_time'] ?? null;
$endTime = $_GET['end_time'] ?? null;
$searchTerm = $_GET['q'] ?? null;

$rooms = [];
$isSearch = false;

// 1. Logika Pencarian
if ($date && $startTime && $endTime) {
    // Cari yang available berdasarkan waktu
    $rooms = get_available_rooms($date, $startTime, $endTime);
    $isSearch = true;
} elseif ($searchTerm || $date) {
    // Cari berdasarkan nama atau filter biasa
    $filters = ['search' => $searchTerm];
    $rooms = search_rooms($filters);
    $isSearch = true;
} else {
    // DEFAULT: Tampilkan 3 Ruangan Terpopuler
    $rooms = get_popular_rooms(3);
    $isSearch = false;
}

// 2. Data Statistik & Jadwal
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$perPage = 10;
$offset = ($page - 1) * $perPage;

$jadwalDisetujui = get_approved_bookings_home($perPage, $offset);
$totalJadwal = count_approved_bookings_home(); // We need to create this function
$totalPages = ceil($totalJadwal / $perPage);

$totalRooms = count(get_all_rooms()); // Total aset ruangan
$totalBookings = $totalJadwal; // Total booking aktif
$title = 'Beranda';

require 'includes/header.php';
?>

<!-- Hero Section with Gradient & Glassmorphism -->
<section class="relative overflow-hidden bg-gradient-to-br from-blue-600 via-blue-700 to-indigo-800 text-white py-20">
    <!-- Animated background shapes -->
    <div class="absolute top-0 left-0 w-full h-full overflow-hidden opacity-20">
        <div
            class="absolute top-20 left-10 w-72 h-72 bg-purple-300 rounded-full mix-blend-multiply filter blur-xl animate-blob">
        </div>
        <div
            class="absolute top-40 right-10 w-72 h-72 bg-yellow-300 rounded-full mix-blend-multiply filter blur-xl animate-blob animation-delay-2000">
        </div>
        <div
            class="absolute -bottom-8 left-20 w-72 h-72 bg-pink-300 rounded-full mix-blend-multiply filter blur-xl animate-blob animation-delay-4000">
        </div>
    </div>

    <div class="relative max-w-7xl mx-auto px-6 text-center">
        <h1 class="text-4xl md:text-5xl font-bold mb-4 animate-slide-up">
            Sistem Informasi Peminjaman Ruangan
        </h1>
        <p class="text-xl text-blue-100 mb-12 animate-slide-up" style="animation-delay: 0.1s;">
            Badan Perencanaan Pembangunan Daerah Provinsi Jawa Tengah
        </p>

        <!-- Smart Search Bar with Glassmorphism -->
        <div class="max-w-4xl mx-auto glass rounded-2xl p-8 shadow-2xl animate-slide-up" style="animation-delay: 0.2s;">
            <h3 class="text-white text-lg font-semibold mb-6 text-center">
                <i class="fas fa-search mr-2"></i>Cari Ruangan
            </h3>

            <form action="index.php" method="GET" class="flex flex-col gap-6">

                <!-- Row 1: Search Box with Dropdown -->
                <div>
                    <label class="block text-sm font-semibold text-white mb-2 drop-shadow-md">
                        <i class="fas fa-keyboard mr-1"></i> Nama Ruangan
                    </label>
                    <input type="text" name="q" value="<?= htmlspecialchars($searchTerm ?? '') ?>" list="roomList"
                        class="w-full px-5 py-4 rounded-xl bg-white/95 backdrop-blur-sm border-0 focus:ring-4 focus:ring-yellow-400/50 transition text-gray-900 text-lg shadow-inner placeholder-gray-400"
                        placeholder="Ketik atau pilih nama ruangan...">
                    <datalist id="roomList">
                        <?php
                        $allRooms = get_all_rooms();
                        foreach ($allRooms as $r):
                            ?>
                            <option value="<?= htmlspecialchars($r['name']) ?>">
                                <?= htmlspecialchars($r['name']) ?> (Kapasitas: <?= $r['capacity'] ?> orang)
                            </option>
                        <?php endforeach; ?>
                    </datalist>
                </div>

                <!-- Row 2: Filters (Date & Time) -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 bg-white/10 p-4 rounded-xl border border-white/20">
                    <div>
                        <label class="block text-sm font-semibold text-white mb-2 drop-shadow-md">
                            <i class="fas fa-calendar-alt mr-1"></i> Tanggal
                        </label>
                        <input type="text" name="date" value="<?= htmlspecialchars($date ?? '') ?>"
                            placeholder="Pilih Tanggal" readonly
                            class="datepicker w-full px-4 py-3 rounded-lg bg-white/95 backdrop-blur-sm border-0 focus:ring-2 focus:ring-yellow-400 transition text-gray-900 cursor-pointer">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-white mb-2 drop-shadow-md">
                            <i class="fas fa-clock mr-1"></i> Jam Mulai
                        </label>
                        <input type="text" name="start_time" value="<?= htmlspecialchars($startTime ?? '') ?>"
                            placeholder="00:00" readonly
                            class="timepicker w-full px-4 py-3 rounded-lg bg-white/95 backdrop-blur-sm border-0 focus:ring-2 focus:ring-yellow-400 transition text-gray-900 cursor-pointer">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-white mb-2 drop-shadow-md">
                            <i class="fas fa-clock mr-1"></i> Jam Selesai
                        </label>
                        <input type="text" name="end_time" value="<?= htmlspecialchars($endTime ?? '') ?>"
                            placeholder="00:00" readonly
                            class="timepicker w-full px-4 py-3 rounded-lg bg-white/95 backdrop-blur-sm border-0 focus:ring-2 focus:ring-yellow-400 transition text-gray-900 cursor-pointer">
                    </div>
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
                            disableMobile: "true",
                            allowInput: false // Disable manual typing
                        });

                        // Init Timepicker
                        flatpickr(".timepicker", {
                            enableTime: true,
                            noCalendar: true,
                            dateFormat: "H:i",
                            time_24hr: true,
                            disableMobile: "true",
                            allowInput: false // Disable manual typing
                        });
                    });
                </script>

                <!-- Row 3: Action Button -->
                <div>
                    <button type="submit"
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-4 px-8 rounded-xl shadow-lg hover:shadow-xl transform hover:-translate-y-1 transition-all duration-200 text-lg flex items-center justify-center gap-2">
                        <i class="fas fa-search text-xl"></i>
                        <span class="tracking-wide">TEMUKAN RUANGAN</span>
                    </button>
                    <?php if ($isSearch): ?>
                        <a href="index.php" class="text-white underline mt-2 text-sm block">Reset Pencarian</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>
</section>

<!-- Stats Cards with Glassmorphism -->
<div class="max-w-7xl mx-auto px-6 -mt-12 relative z-10">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 animate-slide-up">
        <div class="glass-card bg-white rounded-2xl p-8 shadow-xl hover-lift">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 mb-2">Ruang Tersedia</p>
                    <p class="text-5xl font-bold text-blue-600">
                        <?= $totalRooms ?>
                    </p>
                </div>
                <div class="w-16 h-16 bg-blue-600 rounded-2xl flex items-center justify-center shadow-lg">
                    <i class="fas fa-door-open text-white text-2xl"></i>
                </div>
            </div>
        </div>

        <div class="glass-card bg-white rounded-2xl p-8 shadow-xl hover-lift">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 mb-2">Jadwal Terpakai</p>
                    <p class="text-5xl font-bold text-green-600">
                        <?= $totalBookings ?>
                    </p>
                </div>
                <div class="w-16 h-16 bg-green-600 rounded-2xl flex items-center justify-center shadow-lg">
                    <i class="fas fa-calendar-check text-white text-2xl"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Daftar Ruangan Section -->
<section id="ruangan" class="py-20">
    <div class="max-w-7xl mx-auto px-6">
        <div class="text-center mb-12 animate-fade-in">
            <h2
                class="text-4xl font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent mb-4">
                <?= $isSearch ? 'Hasil Pencarian' : 'Ruangan Terpopuler' ?>
            </h2>
            <p class="text-gray-600 text-lg">
                <?= $isSearch ? 'Menampilkan ruangan yang sesuai dengan kriteria Anda' : 'Pilihan ruangan favorit yang paling sering digunakan' ?>
            </p>
        </div>

        <?php if (empty($rooms)): ?>
            <div class="text-center py-10 bg-gray-50 rounded-xl">
                <i class="fas fa-info-circle text-4xl text-gray-300 mb-3"></i>
                <p class="text-gray-500">Tidak ada ruangan yang ditemukan.</p>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php foreach ($rooms as $room): ?>
                    <div class="group bg-white rounded-2xl overflow-hidden shadow-lg hover-lift flex flex-col h-full">
                        <a href="room_detail.php?id=<?= $room['id'] ?>"
                            class="relative overflow-hidden shrink-0 block group-hover:opacity-95 transition-opacity">
                            <div class="relative overflow-hidden h-56">
                                <?php
                                // Pastikan path gambar valid
                                $imgSrc = $room['image'];
                                $isUrl = strpos($imgSrc, 'http') !== false;
                                $absPath = __DIR__ . '/' . $imgSrc;

                                if (!$isUrl && !file_exists($absPath)) {
                                    // Default image kalau gak ada
                                    $imgSrc = 'https://via.placeholder.com/400x300?text=Ruang+Rapat';
                                } else if (!$isUrl) {
                                    $imgSrc = base_url($imgSrc);
                                }
                                ?>
                                <img src="<?= htmlspecialchars($imgSrc) ?>" alt="<?= htmlspecialchars($room['name']) ?>"
                                    class="w-full h-full object-cover transform group-hover:scale-110 transition-transform duration-500">

                                <!-- Popular Badge -->
                                <?php if (!$isSearch): ?>
                                    <div
                                        class="absolute top-4 right-4 bg-yellow-400 text-yellow-900 text-xs font-bold px-3 py-1 rounded-full shadow-lg flex items-center gap-1 z-10">
                                        <i class="fas fa-star"></i> Populer
                                    </div>
                                <?php endif; ?>

                                <div
                                    class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                                </div>
                            </div>
                        </a>
                        <div class="p-6 flex flex-col grow">
                            <a href="room_detail.php?id=<?= $room['id'] ?>" class="block">
                                <h3 class="text-xl font-bold text-gray-900 mb-2 hover:text-blue-600 transition-colors">
                                    <?= htmlspecialchars($room['name']) ?>
                                </h3>
                            </a>
                            <p class="text-gray-600 text-sm mb-4 line-clamp-2">
                                <?= htmlspecialchars($room['short_desc']) ?>
                            </p>
                            <div class="flex items-center text-gray-500 text-sm mb-6 mt-auto">
                                <i class="fas fa-users mr-2 text-blue-500"></i>
                                <span><?= $room['capacity'] ?> orang</span>
                            </div>
                            <a href="booking.php?room_id=<?= $room['id'] ?>"
                                class="inline-flex items-center justify-center w-full px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transform hover:scale-[1.02] transition-all duration-200 shadow-md hover:shadow-lg">
                                Booking Sekarang
                                <i class="fas fa-arrow-right ml-2"></i>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <?php if (!$isSearch): ?>
                <!-- See All Button -->
                <div class="text-center mt-12">
                    <a href="rooms.php"
                        class="inline-flex items-center gap-2 px-8 py-4 bg-white border-2 border-blue-600 text-blue-600 font-bold rounded-xl hover:bg-blue-50 transition-all duration-200 transform hover:-translate-y-1 shadow-md hover:shadow-lg group">
                        <span>Lihat Semua Ruangan</span>
                        <i class="fas fa-arrow-right transform group-hover:translate-x-1 transition-transform"></i>
                    </a>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</section>

<!-- Jadwal Peminjaman Section -->
<section id="jadwal" class="py-20 bg-gradient-to-br from-blue-50 to-indigo-50">
    <div class="max-w-7xl mx-auto px-6">
        <div class="text-center mb-12">
            <h2 class="text-4xl font-bold text-gray-900 mb-4">
                <i class="fas fa-calendar-check mr-2 text-blue-600"></i>
                Jadwal Peminjaman Ruangan
            </h2>
            <p class="text-gray-600 text-lg">
                Daftar peminjaman ruangan yang telah disetujui (Hari Ini & Mendatang)
            </p>
        </div>

        <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-blue-600 text-white">
                        <tr>
                            <th class="px-6 py-4 text-left font-semibold">Tanggal</th>
                            <th class="px-6 py-4 text-left font-semibold">Waktu</th>
                            <th class="px-6 py-4 text-left font-semibold">Ruangan</th>
                            <th class="px-6 py-4 text-left font-semibold">Kegiatan</th>
                            <th class="px-6 py-4 text-left font-semibold">Instansi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php if (empty($jadwalDisetujui)): ?>
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                    <i class="fas fa-calendar-times text-4xl mb-4 text-gray-300"></i>
                                    <p>Belum ada jadwal peminjaman yang disetujui</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($jadwalDisetujui as $jadwal): ?>
                                <?php
                                $tanggal = date('d M Y', strtotime($jadwal['tanggal']));
                                $mulai = date('H:i', strtotime($jadwal['waktu_mulai']));
                                $selesai = date('H:i', strtotime($jadwal['waktu_selesai']));
                                ?>
                                <tr class="hover:bg-blue-50 transition-colors">
                                    <td class="px-6 py-4 text-gray-900"><?= $tanggal ?></td>
                                    <td class="px-6 py-4">
                                        <span
                                            class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-blue-100 text-blue-800">
                                            <i class="fas fa-clock mr-1"></i>
                                            <?= $mulai ?> – <?= $selesai ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 font-semibold text-gray-900">
                                        <?= htmlspecialchars($jadwal['room_name']) ?>
                                    </td>
                                    <td class="px-6 py-4 text-gray-700"><?= htmlspecialchars($jadwal['kegiatan']) ?></td>
                                    <td class="px-6 py-4 text-gray-600 text-sm">
                                        <?= htmlspecialchars($jadwal['instansi']) ?>
                                        <?= $jadwal['divisi'] ? ' / ' . htmlspecialchars($jadwal['divisi']) : '' ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                    <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                        <div class="flex items-center justify-between">
                            <div class="text-sm text-gray-600">
                                Menampilkan halaman <?= $page ?> dari <?= $totalPages ?> 
                                (Total: <?= $totalJadwal ?> jadwal)
                            </div>
                            <div class="flex gap-2">
                                <?php if ($page > 1): ?>
                                        <a href="?page=<?= $page - 1 ?>#jadwal" 
                                            class="px-4 py-2 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition text-gray-700 font-semibold">
                                            <i class="fas fa-chevron-left mr-1"></i> Prev
                                        </a>
                                <?php else: ?>
                                        <span class="px-4 py-2 bg-gray-100 border border-gray-200 rounded-lg text-gray-400 font-semibold cursor-not-allowed">
                                            <i class="fas fa-chevron-left mr-1"></i> Prev
                                        </span>
                                <?php endif; ?>
                            
                                <?php if ($page < $totalPages): ?>
                                        <a href="?page=<?= $page + 1 ?>#jadwal" 
                                            class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition font-semibold">
                                            Next <i class="fas fa-chevron-right ml-1"></i>
                                        </a>
                                <?php else: ?>
                                        <span class="px-4 py-2 bg-gray-100 border border-gray-200 rounded-lg text-gray-400 font-semibold cursor-not-allowed">
                                            Next <i class="fas fa-chevron-right ml-1"></i>
                                        </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- CSS Animations -->
<style>
    @keyframes blob {
        0% {
            transform: translate(0px, 0px) scale(1);
        }

        33% {
            transform: translate(30px, -50px) scale(1.1);
        }

        66% {
            transform: translate(-20px, 20px) scale(0.9);
        }

        100% {
            transform: translate(0px, 0px) scale(1);
        }
    }

    .animate-blob {
        animation: blob 7s infinite;
    }

    .animation-delay-2000 {
        animation-delay: 2s;
    }

    .animation-delay-4000 {
        animation-delay: 4s;
    }
</style>

<?php require 'includes/footer.php'; ?>