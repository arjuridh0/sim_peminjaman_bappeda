<?php
require_once '../includes/functions.php';
require_once 'header.php';

$title = 'Tambah Booking Manual';
?>

<div class="mb-6">
    <h2 class="text-3xl font-bold text-gray-900 mb-2">
        <i class="fas fa-plus-circle mr-2 text-blue-600"></i>Tambah Booking Manual
    </h2>
    <p class="text-gray-600">Buat jadwal peminjaman baru untuk user (Otomatis Disetujui).</p>
</div>

<div class="bg-white rounded-xl shadow-lg border border-gray-200 p-6 md:p-8 max-w-4xl mx-auto">
    <form id="createBookingForm" class="space-y-6">

        <!-- Room Selection -->
        <div>
            <div class="flex justify-between items-center mb-4">
                <label class="block text-sm font-bold text-gray-700">Pilih Ruangan <span
                        class="text-red-500">*</span></label>
                <!-- View Toggle -->
                <div class="flex bg-gray-100 rounded-lg p-1">
                    <button type="button" id="viewGridBtn" onclick="toggleRoomView('grid')"
                        class="px-3 py-1.5 rounded-md text-xs font-bold transition-all bg-white text-blue-600 shadow-sm">
                        <i class="fas fa-th-large mr-1"></i> Cards
                    </button>
                    <button type="button" id="viewListBtn" onclick="toggleRoomView('list')"
                        class="px-3 py-1.5 rounded-md text-xs font-bold text-gray-500 hover:text-gray-700 transition-all">
                        <i class="fas fa-list mr-1"></i> List
                    </button>
                </div>
            </div>

            <!-- Grid View (Cards) -->
            <div id="roomGridView" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <?php
                $rooms = get_all_rooms();
                foreach ($rooms as $room):
                    ?>
                    <label class="relative cursor-pointer group">
                        <input type="radio" name="room_id" value="<?= $room['id'] ?>" class="peer sr-only room-radio" required
                            onchange="syncRoomSelection(this.value)">
                        <div
                            class="p-4 border rounded-xl hover:bg-gray-50 peer-checked:border-blue-500 peer-checked:bg-blue-50 peer-checked:ring-2 peer-checked:ring-blue-200 transition-all h-full flex flex-col justify-between">
                            <div>
                                <div class="font-bold text-gray-900">
                                    <?= htmlspecialchars($room['name']) ?>
                                </div>
                                <div class="text-xs text-gray-500 mt-1">
                                    <i class="fas fa-users mr-1"></i> Max <?= $room['capacity'] ?> Orang
                                </div>
                            </div>
                            <div class="mt-3 flex justify-end">
                                <span
                                    class="text-blue-600 opacity-0 group-hover:opacity-100 peer-checked:opacity-100 transition-opacity">
                                    <i class="fas fa-check-circle"></i>
                                </span>
                            </div>
                        </div>
                    </label>
                <?php endforeach; ?>
            </div>

            <!-- List View (Dropdown) -->
            <div id="roomListView" class="hidden">
                <select id="roomSelect" name="room_id" disabled required
                    class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                    onchange="syncRoomSelection(this.value)">
                    <option value="">-- Pilih Ruangan --</option>
                    <?php foreach ($rooms as $room): ?>
                        <option value="<?= $room['id'] ?>">
                            <?= htmlspecialchars($room['name']) ?> (Kapasitas: <?= $room['capacity'] ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Date & Time -->
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Tanggal Peminjaman <span
                            class="text-red-500">*</span></label>
                    <input type="date" name="tanggal"
                        class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                        required>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Jam Mulai <span
                                class="text-red-500">*</span></label>
                        <input type="time" name="waktu_mulai"
                            class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                            required>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Jam Selesai <span
                                class="text-red-500">*</span></label>
                        <input type="time" name="waktu_selesai"
                            class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                            required>
                    </div>
                </div>
            </div>

            <!-- User Info -->
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Nama Peminjam <span
                            class="text-red-500">*</span></label>
                    <input type="text" name="nama_peminjam" placeholder="Nama Lengkap"
                        class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                        required>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Instansi <span
                                class="text-red-500">*</span></label>
                        <select name="instansi"
                            class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                            required>
                            <option value="">Pilih Instansi</option>
                            <option value="BAPPEDA">BAPPEDA</option>
                            <option value="Lainnya">Lainnya</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Unit Kerja <span
                                class="text-xs font-normal text-gray-500">(Opsional)</span></label>
                        <input type="text" name="divisi" placeholder="Cth: UMPEG"
                            class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
                    </div>
                </div>
            </div>
        </div>

        <!-- Activity Details -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="md:col-span-3">
                <label class="block text-sm font-bold text-gray-700 mb-2">Nama Kegiatan <span
                        class="text-red-500">*</span></label>
                <input type="text" name="kegiatan" placeholder="Contoh: Rapat Koordinasi Anggaran"
                    class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                    required>
            </div>
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">Jumlah Peserta <span
                        class="text-red-500">*</span></label>
                <input type="number" name="jumlah_peserta" placeholder="0"
                    class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                    required>
            </div>
        </div>

        <!-- Optional Contact Info -->
        <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
            <h3 class="font-bold text-gray-800 mb-3 text-sm">Informasi Kontak (Opsional)</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Email (Untuk Notifikasi)</label>
                    <input type="email" name="user_email" placeholder="email@contoh.com"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-1 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">No. WhatsApp</label>
                    <input type="text" name="phone_number" placeholder="08..."
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-1 focus:ring-blue-500">
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex items-center gap-4 pt-4 border-t border-gray-100">
            <button type="button" onclick="window.history.back()"
                class="px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold rounded-lg transition">
                Batal
            </button>
            <button type="submit"
                class="flex-1 px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-lg shadow-lg hover:shadow-xl transition flex justify-center items-center gap-2">
                <i class="fas fa-check-circle"></i> Simpan Booking
            </button>
        </div>

        <script>
            // Room View Toggler
            function toggleRoomView(view) {
                const gridView = document.getElementById('roomGridView');
                const listView = document.getElementById('roomListView');
                const gridBtn = document.getElementById('viewGridBtn');
                const listBtn = document.getElementById('viewListBtn');
                const roomSelect = document.getElementById('roomSelect');
                const roomRadios = document.querySelectorAll('.room-radio');

                if (view === 'grid') {
                    // Show Grid
                    gridView.classList.remove('hidden');
                    listView.classList.add('hidden');
                    
                    // Style Buttons
                    gridBtn.classList.add('bg-white', 'text-blue-600', 'shadow-sm');
                    gridBtn.classList.remove('text-gray-500');
                    listBtn.classList.remove('bg-white', 'text-blue-600', 'shadow-sm');
                    listBtn.classList.add('text-gray-500');
                    
                    // Handle Disabled
                    roomSelect.disabled = true;
                    roomRadios.forEach(r => r.disabled = false);
                    
                } else {
                    // Show List
                    gridView.classList.add('hidden');
                    listView.classList.remove('hidden');
                    
                    // Style Buttons
                    listBtn.classList.add('bg-white', 'text-blue-600', 'shadow-sm');
                    listBtn.classList.remove('text-gray-500');
                    gridBtn.classList.remove('bg-white', 'text-blue-600', 'shadow-sm');
                    gridBtn.classList.add('text-gray-500');
                    
                    // Handle Disabled
                    roomSelect.disabled = false;
                    roomRadios.forEach(r => r.disabled = true);
                }
            }

            // Sync Selection
            function syncRoomSelection(val) {
                // Sync Dropdown
                const select = document.getElementById('roomSelect');
                if(select.value !== val) select.value = val;
                
                // Sync Radio
                const radios = document.getElementsByName('room_id');
                for (let radio of radios) {
                    if (radio.value === val && !radio.checked) radio.checked = true;
                }
            }
        </script>

    </form>
</div>

<script>
    document.getElementById('createBookingForm').addEventListener('submit', function (e) {
        e.preventDefault();

        // Prepare Data
        const formData = new FormData(this);
        const data = Object.fromEntries(formData.entries());

        // Show Loading
        Swal.fire({
            title: 'Memproses...',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        // Send Request
        fetch('../api/admin_create_booking.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Booking Berhasil!',
                        html: `
                    <p class="mb-4 text-gray-600">Jadwal telah ditambahkan dan disetujui.</p>
                    <div class="bg-blue-50 p-4 rounded-xl border border-blue-100 mb-4">
                        <p class="text-xs text-blue-600 font-bold uppercase tracking-wide mb-1">Kode Booking Unik</p>
                        <p class="text-3xl font-mono font-bold text-gray-800 select-all tracking-wider">${result.booking_code}</p>
                    </div>
                    <p class="text-sm text-gray-500">Silakan catat kode ini untuk referensi peminjam.</p>
                `,
                        confirmButtonText: 'Buat Booking Baru',
                        showCancelButton: true,
                        cancelButtonText: 'Kembali ke Kalender'
                    }).then((res) => {
                        if (res.isConfirmed) {
                            document.getElementById('createBookingForm').reset();
                        } else {
                            window.location.href = 'calendar.php';
                        }
                    });
                } else {
                    Swal.fire('Gagal!', result.message || 'Terjadi kesalahan.', 'error');
                }
            })
            .catch(error => {
                Swal.fire('Error!', 'Terjadi kesalahan sistem.', 'error');
            });
    });
</script>

<?php require_once '../includes/footer.php'; ?>