<?php
require_once 'includes/functions.php';
$title = 'Daftar Ruangan';

// Get search parameters
$searchTerm = $_GET['q'] ?? null;
$capacity = $_GET['capacity'] ?? null;

// Get rooms
if ($searchTerm || $capacity) {
    $filters = [
        'search' => $searchTerm,
        'capacity' => $capacity
    ];
    $rooms = search_rooms($filters);
    $isSearch = true;
} else {
    $rooms = get_all_rooms();
    $isSearch = false;
}

require 'includes/header.php';
?>

<!-- Rooms Page -->
<section class="py-8">
    <div class="max-w-7xl mx-auto px-4 md:px-6">
        <!-- Header -->
        <div class="mb-6">
            <h1 class="text-3xl md:text-4xl font-bold text-gray-900 mb-3">
                <i class="fas fa-door-open mr-2 text-blue-600"></i>Daftar Ruangan
            </h1>
            <p class="text-gray-600 text-sm md:text-base">
                Pilih ruangan yang sesuai dengan kebutuhan Anda
            </p>
        </div>

        <!-- Search & Filter -->
        <div class="bg-white rounded-xl shadow-lg p-4 md:p-6 mb-6">
            <form method="GET" class="flex flex-col md:flex-row gap-4">
                <div class="flex-1">
                    <input type="text" name="q" value="<?= htmlspecialchars($searchTerm ?? '') ?>"
                        placeholder="Cari nama ruangan..."
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
                </div>
                <div class="md:w-48">
                    <input type="number" name="capacity" value="<?= htmlspecialchars($capacity ?? '') ?>"
                        placeholder="Min. kapasitas"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
                </div>
                <button type="submit"
                    class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-all duration-200 shadow-md hover:shadow-lg whitespace-nowrap">
                    <i class="fas fa-search mr-2"></i>Cari
                </button>
                <?php if ($isSearch): ?>
                    <a href="rooms.php"
                        class="px-6 py-3 bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold rounded-lg transition text-center">
                        Reset
                    </a>
                <?php endif; ?>
            </form>
        </div>

        <!-- Rooms Grid -->
        <?php if (empty($rooms)): ?>
            <div class="text-center py-12 bg-white rounded-xl shadow-lg">
                <i class="fas fa-info-circle text-5xl text-gray-300 mb-4"></i>
                <p class="text-gray-500 text-lg">Tidak ada ruangan yang ditemukan.</p>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($rooms as $room): ?>
                    <div class="group bg-white rounded-2xl overflow-hidden shadow-lg hover-lift flex flex-col h-full">
                        <a href="room_detail.php?id=<?= $room['id'] ?>"
                            class="relative overflow-hidden h-48 block group-hover:opacity-95 transition-opacity">
                            <?php
                            $imgSrc = $room['image'];
                            $isUrl = strpos($imgSrc, 'http') !== false;
                            $absPath = __DIR__ . '/' . $imgSrc;

                            if (!$isUrl && !file_exists($absPath)) {
                                $imgSrc = 'https://via.placeholder.com/400x300?text=Ruang+Rapat';
                            } else if (!$isUrl) {
                                $imgSrc = base_url($imgSrc);
                            }
                            ?>
                            <img src="<?= htmlspecialchars($imgSrc) ?>" alt="<?= htmlspecialchars($room['name']) ?>"
                                class="w-full h-full object-cover transform group-hover:scale-110 transition-transform duration-500">
                            <div
                                class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                            </div>
                            <div
                                class="absolute top-3 right-3 bg-blue-600 text-white px-3 py-1 rounded-full text-sm font-semibold shadow-lg">
                                <i class="fas fa-users mr-1"></i>
                                <?= $room['capacity'] ?> Orang
                            </div>
                        </a>
                        <div class="p-6 flex flex-col grow">
                            <a href="room_detail.php?id=<?= $room['id'] ?>" class="block">
                                <h3 class="text-xl font-bold text-gray-900 mb-2 hover:text-blue-600 transition-colors">
                                    <?= htmlspecialchars($room['name']) ?>
                                </h3>
                            </a>
                            <p class="text-gray-600 text-sm mb-4 line-clamp-2 flex-grow">
                                <?= htmlspecialchars($room['short_desc']) ?>
                            </p>
                            <?php if ($room['facilities']): ?>
                                <div class="mb-4 flex flex-wrap gap-2">
                                    <?php
                                    $facilities = explode(',', $room['facilities']);
                                    foreach (array_slice($facilities, 0, 3) as $facility):
                                        ?>
                                        <span class="text-xs bg-blue-50 text-blue-700 px-2 py-1 rounded-full">
                                            <?= htmlspecialchars(trim($facility)) ?>
                                        </span>
                                    <?php endforeach; ?>
                                    <?php if (count($facilities) > 3): ?>
                                        <span class="text-xs bg-gray-100 text-gray-600 px-2 py-1 rounded-full">
                                            +
                                            <?= count($facilities) - 3 ?> lagi
                                        </span>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                            <a href="booking.php?room_id=<?= $room['id'] ?>"
                                class="inline-flex items-center justify-center w-full px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transform hover:scale-[1.02] transition-all duration-200 shadow-md hover:shadow-lg mt-auto">
                                <i class="fas fa-calendar-check mr-2"></i>Booking Sekarang
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php require 'includes/footer.php'; ?>