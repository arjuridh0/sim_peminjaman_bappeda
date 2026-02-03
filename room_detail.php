<?php
require_once 'includes/functions.php';

$roomId = $_GET['id'] ?? null;
if (!$roomId) {
    header("Location: rooms.php");
    exit;
}

$room = get_room_by_id($roomId);

if (!$room) {
    header("Location: rooms.php");
    exit;
}

// Logic Navigasi Admin vs User
$isAdmin = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
$backLink = $isAdmin ? 'admin/rooms.php' : 'rooms.php'; // Jika admin balik ke admin panel, user balik ke public list

$title = $room['name'];
require 'includes/header.php';
?>

<!-- Hero Section with Background Image -->
<div class="relative h-[400px] md:h-[500px] w-full overflow-hidden group">

    <!-- Navigation Overlay (Back & Edit) -->
    <div class="absolute top-0 left-0 w-full p-6 z-30 flex justify-between items-start">
        <a href="<?= $backLink ?>"
            class="inline-flex items-center bg-white/20 hover:bg-white/30 backdrop-blur-md text-white px-4 py-2 rounded-full transition-all duration-300 border border-white/30 group">
            <i class="fas fa-arrow-left mr-2 transform group-hover:-translate-x-1 transition-transform"></i> Kembali
        </a>

        <?php if ($isAdmin): ?>
            <a href="admin/room_form.php?id=<?= $room['id'] ?>"
                class="inline-flex items-center bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-2 px-6 rounded-full shadow-lg transition transform hover:scale-105">
                <i class="fas fa-edit mr-2"></i> Edit Ruangan
            </a>
        <?php endif; ?>
    </div>

    <!-- Image -->
    <?php
    $imgSrc = $room['image'];
    if (!file_exists($imgSrc) && strpos($imgSrc, 'http') === false) {
        $imgSrc = 'https://via.placeholder.com/1200x600?text=Ruang+Rapat';
    }
    ?>
    <img src="<?= htmlspecialchars($imgSrc) ?>" alt="<?= htmlspecialchars($room['name']) ?>"
        class="w-full h-full object-cover">

    <!-- Gradient Overlay -->
    <div class="absolute inset-0 bg-gradient-to-t from-gray-900 via-gray-900/40 to-transparent"></div>

    <!-- Content -->
    <div class="absolute bottom-0 left-0 w-full p-6 md:p-12 text-white z-10">
        <div class="max-w-7xl mx-auto">
            <span class="inline-block px-3 py-1 bg-blue-600 text-white text-xs font-bold rounded-full mb-3 shadow-lg">
                <i class="fas fa-building mr-1"></i> Ruang Rapat
            </span>
            <h1 class="text-4xl md:text-5xl font-bold mb-2 drop-shadow-lg leading-tight">
                <?= htmlspecialchars($room['name']) ?>
            </h1>
            <p class="text-lg text-gray-200 max-w-2xl drop-shadow-md">
                <?= htmlspecialchars($room['short_desc']) ?>
            </p>
        </div>
    </div>
</div>

<!-- Main Content -->
<section class="py-12 bg-gray-50 min-h-screen">
    <div class="max-w-7xl mx-auto px-6">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

            <!-- Left Column: Description & Facilities -->
            <div class="lg:col-span-2 space-y-8">

                <!-- Description Card -->
                <div class="bg-white rounded-2xl shadow-lg p-8 border border-gray-100">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4 border-b pb-4 flex items-center">
                        <i class="fas fa-align-left text-blue-600 mr-3"></i>Deskripsi Lengkap
                    </h2>
                    <div class="prose max-w-none text-gray-700 leading-relaxed">
                        <?= nl2br(htmlspecialchars($room['description'])) ?>
                    </div>
                </div>

                <!-- Facilities Card -->
                <?php if (!empty($room['facilities'])): ?>
                    <div class="bg-white rounded-2xl shadow-lg p-8 border border-gray-100">
                        <h2 class="text-2xl font-bold text-gray-900 mb-6 border-b pb-4 flex items-center">
                            <i class="fas fa-concierge-bell text-green-600 mr-3"></i>Fasilitas
                        </h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <?php
                            $facilities = explode(',', $room['facilities']);
                            foreach ($facilities as $facility):
                                ?>
                                <div
                                    class="flex items-center p-3 bg-gray-50 rounded-xl hover:bg-blue-50 transition border border-gray-100">
                                    <div
                                        class="w-10 h-10 rounded-full bg-white flex items-center justify-center shadow-sm text-blue-600 mr-3 border border-gray-200">
                                        <i class="fas fa-check"></i>
                                    </div>
                                    <span class="font-medium text-gray-700">
                                        <?= trim(htmlspecialchars($facility)) ?>
                                    </span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

            </div>

            <!-- Right Column: Sidebar Specs & Action -->
            <div class="lg:col-span-1 space-y-6">

                <!-- Quick Specs Card -->
                <div class="bg-white rounded-2xl shadow-lg p-6 border border-gray-100 sticky top-24">
                    <h3 class="text-lg font-bold text-gray-900 mb-6 flex items-center">
                        <i class="fas fa-info-circle text-blue-600 mr-2"></i> Detail & Spesifikasi
                    </h3>

                    <div class="space-y-4">
                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl">
                            <div class="flex items-center">
                                <i class="fas fa-users text-gray-400 text-xl mr-3 w-6 text-center"></i>
                                <span class="text-gray-600 font-medium">Kapasitas</span>
                            </div>
                            <span class="text-xl font-bold text-gray-900">
                                <?= $room['capacity'] ?> Orang
                            </span>
                        </div>

                        <!-- Example: Floor/Location if available (Static for now as DB doesn't have it explicitly) -->
                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl">
                            <div class="flex items-center">
                                <i class="fas fa-map-marker-alt text-gray-400 text-xl mr-3 w-6 text-center"></i>
                                <span class="text-gray-600 font-medium">Lokasi</span>
                            </div>
                            <span class="text-gray-900 font-medium">Kantor Bappeda</span>
                        </div>

                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl">
                            <div class="flex items-center">
                                <i class="fas fa-ruler-combined text-gray-400 text-xl mr-3 w-6 text-center"></i>
                                <span class="text-gray-600 font-medium">Luas</span>
                            </div>
                            <span
                                class="text-gray-900 font-medium"><?= htmlspecialchars($room['area_size'] ?? '-') ?></span>
                        </div>
                    </div>

                    <div class="border-t border-gray-100 my-6"></div>

                    <!-- Call to Action -->
                    <a href="booking.php?room_id=<?= $room['id'] ?>"
                        class="block w-full py-4 px-6 bg-blue-600 hover:bg-blue-700 text-white text-center font-bold text-lg rounded-xl shadow-lg hover:shadow-xl transform hover:-translate-y-1 transition-all duration-200">
                        <i class="fas fa-calendar-plus mr-2"></i>Booking Sekarang
                    </a>

                    <p class="text-xs text-center text-gray-500 mt-4">
                        <i class="fas fa-check-circle text-green-500 mr-1"></i> Tersedia untuk booking online
                    </p>
                </div>
            </div>

        </div>
    </div>
</section>

<?php require 'includes/footer.php'; ?>