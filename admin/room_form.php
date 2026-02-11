<?php
require_once '../includes/functions.php';

$id = $_GET['id'] ?? null;
$item = null;
$isEdit = false;

if ($id) {
    $item = get_room_by_id($id);
    if (!$item) {
        set_flash_message('error', 'Ruangan tidak ditemukan.');
        redirect('admin/rooms.php');
    }
    $isEdit = true;
}

// Handle Submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        set_flash_message('error', 'Invalid security token.');
        redirect('admin/rooms.php');
    }

    $name = $_POST['name'];
    $capacity = $_POST['capacity'];
    $shortDesc = $_POST['short_desc'];
    $description = $_POST['description'];
    $facilities = $_POST['facilities'];

    // Default Image (Current or Placeholder)
    $imagePath = $isEdit ? $item['image'] : 'assets/images/default-room.jpg';

    // Handle File Upload
    if (!empty($_FILES['image']['name'])) {
        $file = $_FILES['image'];
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (in_array($ext, $allowed) && $file['size'] <= 10 * 1024 * 1024) { // 10MB
            $fileName = time() . '_' . preg_replace('/[^a-zA-Z0-9]/', '', $name) . '.' . $ext;
            $uploadDir = __DIR__ . '/../assets/images/rooms/';

            if (!is_dir($uploadDir))
                mkdir($uploadDir, 0755, true); // Secure permission

            if (move_uploaded_file($file['tmp_name'], $uploadDir . $fileName)) {
                $imagePath = 'assets/images/rooms/' . $fileName;
            } else {
                set_flash_message('error', 'Gagal mengupload gambar. Periksa folder permissions.');
                // Jangan redirect dulu, biar inputan form tidak hilang (walaupun implementasi ini akan lanjut simpan tanpa gambar)
                // Idealnya: redirect kembali ke form dengan data lama, tapi di sini kita biarkan simpan dengan gambar default/lama dan beri notif error.
                // Atau, kita hentikan proses simpan? 
                // Kita beri pesan error dan hentikan proses.
                set_flash_message('error', 'Gagal memindahkan file gambar.');
                redirect($isEdit ? "admin/room_form.php?id=$id" : "admin/room_form.php");
            }
        } else {
            set_flash_message('error', 'Format gambar tidak valid atau ukuran > 2MB (Hanya JPG, PNG, WEBP).');
            redirect($isEdit ? "admin/room_form.php?id=$id" : "admin/room_form.php");
        }
    }

    $data = [
        'name' => $name,
        'capacity' => $capacity,
        'area_size' => $_POST['area_size'] ?? null,
        'short_desc' => $shortDesc,
        'description' => $description,
        'facilities' => $facilities,
        'image' => $imagePath
    ];

    if ($isEdit) {
        update_room($id, $data);
        set_flash_message('success', 'Ruangan berhasil diperbarui.');
    } else {
        create_room($data);
        set_flash_message('success', 'Ruangan berhasil ditambahkan.');
    }

    redirect('admin/rooms.php');
}

require_once 'header.php';
?>

<div class="max-w-4xl mx-auto bg-white rounded-lg shadow-lg overflow-hidden">
    <div class="bg-slate-800 px-6 py-4 border-b border-gray-700">
        <h2 class="text-xl font-bold text-white flex items-center gap-2">
            <i class="fas <?= $isEdit ? 'fa-edit' : 'fa-plus-circle' ?>"></i>
            <?= $isEdit ? 'Edit Ruangan' : 'Tambah Ruangan Baru' ?>
        </h2>
    </div>

    <form method="POST" enctype="multipart/form-data" class="p-8">
        <?= csrf_field() ?>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <!-- Nama Ruangan -->
            <div class="col-span-2 md:col-span-1">
                <label class="block text-gray-700 font-bold mb-2">Nama Ruangan</label>
                <input type="text" name="name" value="<?= htmlspecialchars($item['name'] ?? '') ?>" required
                    class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <!-- Kapasitas -->
            <div class="col-span-2 md:col-span-1">
                <label class="block text-gray-700 font-bold mb-2">Kapasitas (Orang)</label>
                <input type="number" name="capacity" value="<?= htmlspecialchars($item['capacity'] ?? '') ?>" required
                    class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <!-- Luas Ruangan -->
            <div class="col-span-2 md:col-span-1">
                <label class="block text-gray-700 font-bold mb-2">Luas Ruangan (m2)</label>
                <input type="text" name="area_size" value="<?= htmlspecialchars($item['area_size'] ?? '') ?>"
                    placeholder="Contoh: 120 m²"
                    class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <!-- Deskripsi Singkat -->
            <div class="col-span-2">
                <label class="block text-gray-700 font-bold mb-2">Deskripsi Singkat (List View)</label>
                <input type="text" name="short_desc" value="<?= htmlspecialchars($item['short_desc'] ?? '') ?>" required
                    class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <!-- Fasilitas -->
            <div class="col-span-2">
                <label class="block text-gray-700 font-bold mb-2">Fasilitas (Dipisahkan koma, cth: AC, Projector,
                    WiFi)</label>
                <input type="text" name="facilities" value="<?= htmlspecialchars($item['facilities'] ?? '') ?>"
                    class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <!-- Deskripsi Lengkap -->
            <div class="col-span-2">
                <label class="block text-gray-700 font-bold mb-2">Deskripsi Lengkap</label>
                <textarea name="description" rows="4" required
                    class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"><?= htmlspecialchars($item['description'] ?? '') ?></textarea>
            </div>

            <!-- Image Upload -->
            <div class="col-span-2 border-t pt-6">
                <label class="block text-gray-700 font-bold mb-2">Foto Ruangan</label>
                <div class="flex items-center gap-4">
                    <?php if ($isEdit && $item['image']): ?>
                        <div class="w-32 h-20 bg-gray-100 rounded overflow-hidden shadow">
                            <img src="<?= base_url($item['image']) ?>" alt="Current" class="w-full h-full object-cover">
                        </div>
                    <?php endif; ?>
                    <input type="file" name="image" accept="image/*" class="w-full text-sm text-gray-500
                        file:mr-4 file:py-2 file:px-4
                        file:rounded-full file:border-0
                        file:text-sm file:font-semibold
                        file:bg-blue-50 file:text-blue-700
                        hover:file:bg-blue-100">
                </div>
                <p class="text-sm text-gray-500 mt-2">Format: JPG, PNG, WebP (Max 10MB). Kosongkan jika tidak ingin
                    mengubah gambar.</p>
            </div>
        </div>

        <div class="flex justify-end gap-4 mt-8">
            <a href="rooms.php"
                class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg font-bold hover:bg-gray-300 transition">Batal</a>
            <button type="submit"
                class="px-6 py-2 bg-blue-600 text-white rounded-lg font-bold hover:bg-blue-700 shadow-lg transition">
                <i class="fas fa-save mr-2"></i> Simpan Ruangan
            </button>
        </div>
    </form>
</div>

</body>

</html>