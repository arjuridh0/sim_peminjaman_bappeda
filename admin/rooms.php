<?php
require_once '../includes/functions.php';
// Handle Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        set_flash_message('error', 'Invalid security token.');
        redirect('rooms.php');
    }

    $roomId = $_POST['room_id'];
    delete_room($roomId);
    set_flash_message('success', 'Ruangan berhasil dihapus.');
    redirect('rooms.php');
}

require_once 'header.php';

require_once 'header.php';

$rooms = get_all_rooms();
?>

<div class="flex justify-between items-center mb-6">
    <h2 class="text-2xl font-bold text-gray-800">Manajemen Ruangan</h2>
    <a href="room_form.php"
        class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg shadow transition flex items-center gap-2">
        <i class="fas fa-plus"></i> Tambah Ruangan
    </a>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    <?php foreach ($rooms as $room): ?>
        <div class="bg-white rounded-xl shadow-lg overflow-hidden group hover:shadow-2xl transition duration-300">
            <a href="../room_detail.php?id=<?= $room['id'] ?>"
                class="h-48 overflow-hidden relative block group-hover:opacity-95 transition">
                <?php
                $imgSrc = base_url($room['image']);
                // Fallback check simple (client-side error handle is already in base_url usage context usually, but here just raw)
                ?>
                <img src="<?= $imgSrc ?>" alt="<?= htmlspecialchars($room['name']) ?>"
                    class="w-full h-full object-cover transform group-hover:scale-110 transition duration-500">
                <div class="absolute top-2 right-2 bg-black/50 text-white px-2 py-1 rounded text-xs backdrop-blur-sm">
                    <i class="fas fa-users mr-1"></i>
                    <?= $room['capacity'] ?> Org
                </div>
            </a>

            <div class="p-6">
                <a href="../room_detail.php?id=<?= $room['id'] ?>" class="block">
                    <h3 class="text-xl font-bold text-gray-800 mb-2 hover:text-blue-600 transition">
                        <?= htmlspecialchars($room['name']) ?>
                    </h3>
                </a>
                <p class="text-gray-600 text-sm mb-4 line-clamp-2">
                    <?= htmlspecialchars($room['short_desc']) ?>
                </p>

                <div class="border-t pt-4 flex justify-between items-center">
                    <a href="room_form.php?id=<?= $room['id'] ?>"
                        class="text-blue-600 hover:text-blue-800 font-semibold text-sm flex items-center gap-1">
                        <i class="fas fa-edit"></i> Edit
                    </a>

                    <form method="POST" id="deleteForm<?= $room['id'] ?>">
                        <?= csrf_field() ?>
                        <input type="hidden" name="room_id" value="<?= $room['id'] ?>">
                        <input type="hidden" name="action" value="delete">
                        <button type="button" onclick="confirmDelete(<?= $room['id'] ?>)"
                            class="text-red-500 hover:text-red-700 font-semibold text-sm flex items-center gap-1">
                            <i class="fas fa-trash-alt"></i> Hapus
                        </button>
                    </form>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<script>
    function confirmDelete(id) {
        Swal.fire({
            title: 'Hapus Ruangan?',
            text: "Data ruangan akan dihapus permanen dan tidak dapat dikembalikan!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('deleteForm' + id).submit();
            }
        });
    }
</script>

</body>

</html>