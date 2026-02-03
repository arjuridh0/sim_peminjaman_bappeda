<?php
require_once '../includes/functions.php';
require_once 'header.php';

$title = 'Kalender Peminjaman - Admin';
?>

<!-- Responsive Layout -->
<div class="flex flex-col min-h-[calc(100vh-200px)]">

    <!-- HEADER: Title + Actions (Admin Mode & Export) -->
    <div class="flex flex-col md:flex-row md:items-center justify-between mb-6 shrink-0 gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
                <i class="fas fa-calendar-alt text-blue-600"></i>
                Kalender Peminjaman
            </h2>
            <p class="text-xs text-gray-500 mt-0.5">Kelola jadwal peminjaman ruangan</p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <!-- Admin Mode Indicator -->
            <div class="flex items-center gap-3 bg-blue-50 border border-blue-100 px-3 py-1.5 rounded-lg shadow-sm">
                <div class="flex items-center gap-2">
                    <i class="fas fa-user-shield text-blue-600"></i>
                    <span class="text-xs font-bold text-blue-900 uppercase">Admin Mode</span>
                </div>
                <!-- Hidden on small mobile -->
                <div class="hidden sm:block w-px h-4 bg-blue-200"></div>
                <div class="hidden sm:flex items-center gap-2 text-xs">
                    <span class="flex h-2 w-2 relative">
                        <span
                            class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-red-500"></span>
                    </span>
                    <span class="font-medium text-gray-600">Drag & Drop Active</span>
                </div>
            </div>

            <!-- Export Button -->
            <button id="exportPdfBtn"
                class="px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white border border-red-700 rounded-lg transition-all text-sm font-medium shadow-sm flex items-center justify-center gap-2"
                title="Export PDF">
                <i class="fas fa-file-pdf"></i> <span>Export</span>
            </button>
        </div>
    </div>

    <!-- MAIN GRID -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 h-full pb-8">

        <!-- LEFT SIDEBAR: Filters (Room + Status) -->
        <div class="lg:col-span-3 flex flex-col gap-4">

            <!-- Room Filter Card -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-2">
                    <i class="fas fa-door-open mr-1 text-gray-400"></i> Filter Ruangan
                </label>
                <div class="relative">
                    <select id="roomFilter"
                        class="w-full pl-3 pr-8 py-2.5 bg-gray-50 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition cursor-pointer hover:bg-white text-gray-700 font-medium">
                        <option value="">Semua Ruangan</option>
                        <?php
                        $rooms = get_all_rooms();
                        foreach ($rooms as $room):
                            ?>
                            <option value="<?= $room['id'] ?>">
                                <?= htmlspecialchars($room['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <!-- Status Filter Card -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 flex-1 flex flex-col">
                <h3
                    class="font-bold text-gray-900 mb-3 text-xs uppercase tracking-wide flex items-center justify-between">
                    <span><i class="fas fa-filter text-blue-600 mr-1"></i> Status</span>
                </h3>

                <div class="space-y-2">
                    <label
                        class="flex items-center justify-between p-2 rounded-lg hover:bg-gray-50 cursor-pointer transition border border-transparent hover:border-gray-100">
                        <div class="flex items-center gap-2">
                            <input type="checkbox"
                                class="status-filter w-4 h-4 rounded text-yellow-500 focus:ring-yellow-500 border-gray-300 transition"
                                value="menunggu" checked>
                            <span class="text-sm text-gray-700">Menunggu</span>
                        </div>
                        <span class="w-2.5 h-2.5 rounded-full bg-yellow-400 shadow-sm"></span>
                    </label>

                    <label
                        class="flex items-center justify-between p-2 rounded-lg hover:bg-gray-50 cursor-pointer transition border border-transparent hover:border-gray-100">
                        <div class="flex items-center gap-2">
                            <input type="checkbox"
                                class="status-filter w-4 h-4 rounded text-green-500 focus:ring-green-500 border-gray-300 transition"
                                value="disetujui" checked>
                            <span class="text-sm text-gray-700">Disetujui</span>
                        </div>
                        <span class="w-2.5 h-2.5 rounded-full bg-green-500 shadow-sm"></span>
                    </label>

                    <label
                        class="flex items-center justify-between p-2 rounded-lg hover:bg-gray-50 cursor-pointer transition border border-transparent hover:border-gray-100">
                        <div class="flex items-center gap-2">
                            <input type="checkbox"
                                class="status-filter w-4 h-4 rounded text-red-500 focus:ring-red-500 border-gray-300 transition"
                                value="ditolak">
                            <span class="text-sm text-gray-700">Ditolak</span>
                        </div>
                        <span class="w-2.5 h-2.5 rounded-full bg-red-500 shadow-sm"></span>
                    </label>

                    <label
                        class="flex items-center justify-between p-2 rounded-lg hover:bg-gray-50 cursor-pointer transition border border-transparent hover:border-gray-100">
                        <div class="flex items-center gap-2">
                            <input type="checkbox"
                                class="status-filter w-4 h-4 rounded text-gray-500 focus:ring-gray-500 border-gray-300 transition"
                                value="dibatalkan">
                            <span class="text-sm text-gray-700">Dibatalkan</span>
                        </div>
                        <span class="w-2.5 h-2.5 rounded-full bg-gray-400 shadow-sm"></span>
                    </label>
                </div>

                <!-- Legend / Tip -->
                <div class="mt-4 pt-3 border-t border-gray-100 text-[11px] text-gray-500 leading-snug">
                    <div class="flex items-start gap-1">
                        <i class="fas fa-info-circle text-blue-400 mt-0.5"></i>
                        <span>Klik event untuk detail. <br>Drag drop untuk pindah jadwal.</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- RIGHT MAIN: Calendar -->
        <div class="lg:col-span-9">
            <div class="bg-white rounded-xl shadow-md border border-gray-200 p-4 md:p-6 min-h-[600px]">
                <!-- Calendar Container -->
                <div id="calendar" class="fc-responsive"></div>
            </div>
        </div>

    </div>
</div>

<style>
    /* FullCalendar Customization */
    :root {
        --fc-button-bg-color: #3b82f6;
        --fc-button-border-color: #3b82f6;
        --fc-button-hover-bg-color: #2563eb;
        --fc-button-hover-border-color: #2563eb;
        --fc-button-active-bg-color: #1d4ed8;
        --fc-button-active-border-color: #1d4ed8;
    }

    .fc-button-primary {
        background-color: var(--fc-button-bg-color) !important;
        border-color: var(--fc-button-border-color) !important;
        font-weight: 600 !important;
        text-transform: capitalize !important;
        padding: 0.4rem 0.8rem !important;
        border-radius: 0.5rem !important;
    }

    .fc-toolbar-title {
        font-size: 1.25rem !important;
        font-weight: 700 !important;
        color: #1f2937 !important;
    }

    .fc .fc-view-harness {
        background-color: #fff;
    }

    /* Mobile Responsive Logic */
    @media (max-width: 640px) {
        .fc-header-toolbar {
            flex-direction: column;
            gap: 1rem;
        }

        .fc-toolbar-chunk {
            display: flex;
            justify-content: center;
            width: 100%;
            gap: 0.5rem;
        }

        .fc-button {
            font-size: 0.75rem !important;
            padding: 0.35rem 0.6rem !important;
            width: auto;
        }

        /* Hide Today button on mobile */
        .fc-today-button {
            display: none !important;
        }

        .fc-toolbar-title {
            font-size: 1.1rem !important;
            text-align: center;
        }
    }

    /* Custom "+ More" Link Styling */
    .fc-daygrid-more-link {
        background-color: #eff6ff !important;
        /* blue-50 */
        color: #2563eb !important;
        /* blue-600 */
        font-size: 0.75rem !important;
        /* text-xs */
        font-weight: 700 !important;
        padding: 1px 8px !important;
        border-radius: 9999px !important;
        text-decoration: none !important;
        display: inline-block !important;
        margin-top: 2px !important;
        transition: all 0.2s ease !important;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05) !important;
        border: 1px solid #dbeafe !important;
        /* blue-100 */
    }

    .fc-daygrid-more-link:hover {
        background-color: #2563eb !important;
        /* blue-600 */
        color: #ffffff !important;
        border-color: #2563eb !important;
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(37, 99, 235, 0.2) !important;
    }
</style>

<!-- Event Detail Modal -->
<div id="eventModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4 backdrop-blur-sm"
    onclick="if(event.target === this) closeModal()">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
        <div class="sticky top-0 bg-blue-600 px-6 py-4 flex justify-between items-center rounded-t-2xl">
            <h3 class="text-xl font-bold text-white">Detail Peminjaman</h3>
            <button onclick="closeModal()" class="text-white hover:text-blue-100 transition">
                <i class="fas fa-times text-2xl"></i>
            </button>
        </div>

        <div id="modalContent" class="p-6">
            <!-- Content will be loaded dynamically -->
        </div>
    </div>
</div>

<!-- Export Options Modal -->
<div id="exportModal"
    class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4 backdrop-blur-sm"
    onclick="if(event.target === this) closeExportModal()">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-md p-6">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-xl font-bold text-gray-900">
                <i class="fas fa-file-pdf text-red-600 mr-2"></i>Export Laporan PDF
            </h3>
            <button onclick="closeExportModal()" class="text-gray-400 hover:text-gray-600 transition">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>

        <div class="space-y-4">
            <!-- Period Type Selector -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Pilih Periode Laporan</label>
                <div class="grid grid-cols-2 gap-3 p-1 bg-gray-100 rounded-lg">
                    <button
                        class="export-type-btn active px-4 py-2 rounded-md text-sm font-medium transition-all bg-white text-blue-600 shadow-sm"
                        onclick="setExportType('monthly')">
                        Bulanan
                    </button>
                    <button
                        class="export-type-btn px-4 py-2 rounded-md text-sm font-medium text-gray-600 hover:text-gray-900 transition-all"
                        onclick="setExportType('yearly')">
                        Tahunan
                    </button>
                </div>
                <input type="hidden" id="exportType" value="monthly">
            </div>

            <!-- Month Selector (shown for Monthly) -->
            <div id="monthSelector">
                <label class="block text-sm font-medium text-gray-700 mb-1">Bulan & Tahun</label>
                <input type="month" id="exportMonth"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 outline-none"
                    value="<?= date('Y-m') ?>">
            </div>

            <!-- Year Selector (shown for Yearly) -->
            <div id="yearSelector" class="hidden">
                <label class="block text-sm font-medium text-gray-700 mb-1">Tahun</label>
                <select id="exportYear"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 outline-none">
                    <?php
                    $startYear = date('Y'); // Current year
                    $endYear = $startYear - 2; // Be able to go back 2 years
                    for ($y = $startYear + 1; $y >= $endYear; $y--) {
                        $selected = ($y == $startYear) ? 'selected' : '';
                        echo "<option value='$y' $selected>$y</option>";
                    }
                    ?>
                </select>
            </div>

            <div class="pt-4 flex gap-3">
                <button onclick="closeExportModal()"
                    class="flex-1 px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg font-medium transition">
                    Batal
                </button>
                <button onclick="processExport()"
                    class="flex-1 px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg font-bold shadow-lg transition flex justify-center items-center gap-2">
                    <i class="fas fa-download"></i> Download PDF
                </button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const calendarEl = document.getElementById('calendar');
        let calendar;

        // Initialize calendar with DRAG & DROP
        calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay'
            },
            buttonText: {
                today: 'Hari Ini',
                month: 'Bulan',
                week: 'Minggu',
                day: 'Hari'
            },
            locale: 'id',
            height: 'auto',
            // ENABLE DRAG & DROP
            editable: true,
            dayMaxEvents: 2, // Force max 2 events before "+more" link
            eventTimeFormat: { // like '14:30:00'
                hour: '2-digit',
                minute: '2-digit',
                meridiem: false
            },
            eventDurationEditable: true,
            eventStartEditable: true,
            events: function (info, successCallback, failureCallback) {
                fetchEvents(info, successCallback, failureCallback);
            },
            // Handle drag & drop
            eventDrop: function (info) {
                const event = info.event;
                const oldEvent = info.oldEvent;

                // Get new date and original times
                const newDateStr = event.start.toLocaleDateString('sv');
                const startTimeStr = oldEvent.start.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', hour12: false }).replace('.', ':');
                const endTimeStr = oldEvent.end.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', hour12: false }).replace('.', ':');

                showRescheduleForm(event, newDateStr, startTimeStr, endTimeStr, () => info.revert());
            },
            eventClick: function (info) {
                showEventDetail(info.event);
            },
            // CLICK TO VIEW DAILY OVERVIEW
            dateClick: function (info) {
                showDailyOverview(info.dateStr);
            },
            eventDidMount: function (info) {
                info.el.title = info.event.extendedProps.tooltip;
                info.el.style.cursor = 'move';
            }
        });

        // Daily Overview Modal
        function showDailyOverview(dateStr) {
            // Get events for this day
            const allEvents = calendar.getEvents();
            const dayEvents = allEvents.filter(e => {
                const eDate = e.startStr.split('T')[0];
                return eDate === dateStr;
            });

            // Sort by start time
            dayEvents.sort((a, b) => a.start - b.start);

            let eventListHtml = '';

            if (dayEvents.length > 0) {
                eventListHtml = '<div class="space-y-2 max-h-[40vh] overflow-y-auto pr-1 mb-4">';
                dayEvents.forEach(e => {
                    const props = e.extendedProps;
                    const timeRange = e.start.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }) +
                        ' - ' +
                        (e.end ? e.end.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }) : '??:??');

                    const statusColors = {
                        'menunggu': 'bg-yellow-100 text-yellow-800',
                        'disetujui': 'bg-green-100 text-green-800',
                        'ditolak': 'bg-red-100 text-red-800',
                        'dibatalkan': 'bg-gray-100 text-gray-600'
                    };
                    const statusClass = statusColors[props.status] || 'bg-gray-100 text-gray-800';

                    eventListHtml += `
                        <div class="flex items-start p-3 bg-white border border-gray-100 rounded-lg hover:bg-gray-50 transition cursor-pointer" onclick="calendar.getEventById('${e.id}').click()">
                            <div class="w-1.5 self-stretch rounded-full mr-3" style="background-color: ${e.backgroundColor}"></div>
                            <div class="flex-1 text-left">
                                <p class="text-sm font-bold text-gray-900 line-clamp-1">${props.room_name}</p>
                                <p class="text-xs text-gray-600 mb-1">${timeRange}</p>
                                <p class="text-xs text-blue-600 font-medium">${props.kegiatan}</p>
                            </div>
                             <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide ${statusClass}">
                                ${props.status}
                            </span>
                        </div>
                    `;
                });
                eventListHtml += '</div>';
            } else {
                eventListHtml = `
                    <div class="text-center py-8 text-gray-400">
                        <i class="fas fa-calendar-times text-4xl mb-2"></i>
                        <p class="text-sm">Tidak ada jadwal pada tanggal ini.</p>
                    </div>
                `;
            }

            // Create formatted date for title
            const dateObj = new Date(dateStr);
            const formattedDate = dateObj.toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });

            Swal.fire({
                title: `<div class="text-lg font-bold">Jadwal: ${formattedDate}</div>`,
                html: `
                    <div class="text-left">
                        ${eventListHtml}
                        <button id="btn-add-booking-daily" class="w-full py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-bold shadow transition flex items-center justify-center gap-2">
                            <i class="fas fa-plus-circle"></i> Tambah Jadwal Baru
                        </button>
                    </div>
                `,
                showConfirmButton: false, // We use custom button
                showCloseButton: true,
                width: '500px',
                didOpen: () => {
                    // Bind click event for custom button
                    document.getElementById('btn-add-booking-daily').addEventListener('click', () => {
                        Swal.close(); // Close this modal
                        setTimeout(() => showAddBookingModal(dateStr), 300); // Open add form
                    });
                }
            });
        }

        // Add Booking Modal Function
        function showAddBookingModal(dateStr) {
            Swal.fire({
                title: 'Tambah Jadwal Manual',
                html: `
                    <div class="text-left space-y-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal <span class="text-red-500">*</span></label>
                            <input type="date" id="add-date" class="w-full border border-gray-300 rounded-lg px-3 py-2" value="${dateStr}">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Ruangan <span class="text-red-500">*</span></label>
                            <select id="add-room" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                                <?php foreach ($rooms as $room): ?>
                                    <option value="<?= $room['id'] ?>"><?= htmlspecialchars($room['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Jam Mulai <span class="text-red-500">*</span></label>
                                <input type="time" id="add-start" class="w-full border border-gray-300 rounded-lg px-3 py-2" value="08:00">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Jam Selesai <span class="text-red-500">*</span></label>
                                <input type="time" id="add-end" class="w-full border border-gray-300 rounded-lg px-3 py-2" value="10:00">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nama Peminjam <span class="text-red-500">*</span></label>
                            <input type="text" id="add-name" class="w-full border border-gray-300 rounded-lg px-3 py-2" placeholder="Nama Lengkap">
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Instansi <span class="text-red-500">*</span></label>
                                <select id="add-instansi" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                                    <option value="">Pilih Instansi</option>
                                    <option value="BAPPEDA">BAPPEDA</option>
                                    <option value="Lainnya">Lainnya</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Divisi</label>
                                <input type="text" id="add-divisi" class="w-full border border-gray-300 rounded-lg px-3 py-2" placeholder="Contoh: Umum">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Kegiatan <span class="text-red-500">*</span></label>
                            <input type="text" id="add-activity" class="w-full border border-gray-300 rounded-lg px-3 py-2" placeholder="Contoh: Rapat Evaluasi">
                        </div>
                         <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Jumlah Peserta <span class="text-red-500">*</span></label>
                            <input type="number" id="add-participants" class="w-full border border-gray-300 rounded-lg px-3 py-2" value="10">
                        </div>
                    </div>
                `,
                width: '600px',
                showCancelButton: true,
                confirmButtonColor: '#3b82f6',
                confirmButtonText: 'Simpan Jadwal',
                cancelButtonText: 'Batal',
                preConfirm: () => {
                    return {
                        room_id: document.getElementById('add-room').value,
                        tanggal: document.getElementById('add-date').value,
                        waktu_mulai: document.getElementById('add-start').value,
                        waktu_selesai: document.getElementById('add-end').value,
                        nama_peminjam: document.getElementById('add-name').value,
                        instansi: document.getElementById('add-instansi').value,
                        divisi: document.getElementById('add-divisi').value,
                        kegiatan: document.getElementById('add-activity').value,
                        jumlah_peserta: document.getElementById('add-participants').value
                    }
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    const data = result.value;

                    // Basic Validation
                    if (!data.nama_peminjam || !data.kegiatan) {
                        Swal.fire('Error', 'Nama Peminjam dan Kegiatan wajib diisi!', 'error');
                        return;
                    }

                    Swal.fire({
                        title: 'Menyimpan...',
                        allowOutsideClick: false,
                        didOpen: () => Swal.showLoading()
                    });

                    fetch('../api/admin_create_booking.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(data)
                    })
                        .then(response => response.json())
                        .then(resp => {
                            if (resp.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil!',
                                    html: `
                                    <p class="mb-2">Jadwal berhasil ditambahkan.</p>
                                    <div class="bg-gray-100 p-3 rounded-lg text-center">
                                        <p class="text-sm text-gray-500 mb-1">Kode Booking Unik:</p>
                                        <p class="text-2xl font-mono font-bold text-blue-600 select-all">${resp.booking_code}</p>
                                    </div>
                                    <p class="text-xs text-gray-400 mt-2">Simpan kode ini untuk referensi.</p>
                                `
                                });
                                calendar.refetchEvents();
                            } else {
                                Swal.fire('Gagal!', resp.message, 'error');
                            }
                        })
                        .catch(error => {
                            Swal.fire('Error!', 'Terjadi kesalahan sistem.', 'error');
                        });
                }
            });
        }

        // Reusable Reschedule Form
        function showRescheduleForm(event, defaultDate, defaultStart, defaultEnd, onCancel = () => { }) {
            Swal.fire({
                title: 'Reschedule Booking',
                html: `
                    <div class="text-left space-y-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Baru</label>
                            <input type="date" id="swal-date" class="w-full border border-gray-300 rounded-lg px-3 py-2" value="${defaultDate}">
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Jam Mulai</label>
                                <input type="time" id="swal-start" class="w-full border border-gray-300 rounded-lg px-3 py-2" value="${defaultStart}">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Jam Selesai</label>
                                <input type="time" id="swal-end" class="w-full border border-gray-300 rounded-lg px-3 py-2" value="${defaultEnd}">
                            </div>
                        </div>
                        <div class="bg-blue-50 p-3 rounded-lg text-xs text-blue-700">
                            <i class="fas fa-info-circle mr-1"></i> Perubahan akan langsung disimpan.
                        </div>
                    </div>
                `,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3b82f6',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Simpan Perubahan',
                cancelButtonText: 'Batal',
                preConfirm: () => {
                    return {
                        new_date: document.getElementById('swal-date').value,
                        new_start_time: document.getElementById('swal-start').value,
                        new_end_time: document.getElementById('swal-end').value
                    }
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    const formValues = result.value;
                    Swal.fire({
                        title: 'Menyimpan...',
                        allowOutsideClick: false,
                        didOpen: () => Swal.showLoading()
                    });

                    fetch('../api/reschedule_booking.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            booking_id: event.id,
                            new_date: formValues.new_date,
                            new_start_time: formValues.new_start_time,
                            new_end_time: formValues.new_end_time
                        })
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire('Berhasil!', 'Jadwal berhasil diperbarui', 'success');
                                calendar.refetchEvents();
                                closeModal(); // Close detail modal if open
                            } else {
                                Swal.fire('Gagal!', data.message || 'Gagal reschedule', 'error');
                                onCancel();
                            }
                        })
                        .catch(error => {
                            Swal.fire('Error!', 'Terjadi kesalahan sistem', 'error');
                            onCancel();
                        });
                } else {
                    onCancel();
                }
            });
        }

        calendar.render();

        // Filter handlers...
        document.querySelectorAll('.status-filter').forEach(checkbox => {
            checkbox.addEventListener('change', () => calendar.refetchEvents());
        });

        document.getElementById('roomFilter').addEventListener('change', () => {
            calendar.refetchEvents();
        });

        // Export handlers...
        document.getElementById('exportPdfBtn').addEventListener('click', () => {
            document.getElementById('exportModal').classList.remove('hidden');
        });

        // Export Modal Functions
        window.closeExportModal = function () {
            document.getElementById('exportModal').classList.add('hidden');
        };

        window.setExportType = function (type) {
            document.getElementById('exportType').value = type;

            const monthSelector = document.getElementById('monthSelector');
            const yearSelector = document.getElementById('yearSelector');
            const buttons = document.querySelectorAll('.export-type-btn');

            if (type === 'monthly') {
                monthSelector.classList.remove('hidden');
                yearSelector.classList.add('hidden');
                buttons[0].classList.add('bg-white', 'text-blue-600', 'shadow-sm');
                buttons[0].classList.remove('text-gray-600');
                buttons[1].classList.remove('bg-white', 'text-blue-600', 'shadow-sm');
                buttons[1].classList.add('text-gray-600');
            } else {
                monthSelector.classList.add('hidden');
                yearSelector.classList.remove('hidden');
                buttons[1].classList.add('bg-white', 'text-blue-600', 'shadow-sm');
                buttons[1].classList.remove('text-gray-600');
                buttons[0].classList.remove('bg-white', 'text-blue-600', 'shadow-sm');
                buttons[0].classList.add('text-gray-600');
            }
        };

        window.processExport = function () {
            const type = document.getElementById('exportType').value;
            const roomId = document.getElementById('roomFilter').value;
            let url = '../api/export_calendar_pdf.php?';

            // Check if we have room_id
            if (roomId) {
                url += `room_id=${roomId}&`;
            }

            if (type === 'monthly') {
                const monthInfo = document.getElementById('exportMonth').value;
                url += `scope=month&month=${monthInfo}`;
            } else {
                const yearInfo = document.getElementById('exportYear').value;
                url += `scope=year&year=${yearInfo}`;
            }

            window.open(url, '_blank');
            closeExportModal();
        };

        function fetchEvents(info, successCallback, failureCallback) {
            // ... existing fetch logic ...
            const selectedStatuses = Array.from(document.querySelectorAll('.status-filter:checked')).map(cb => cb.value);
            const selectedRoom = document.getElementById('roomFilter').value;
            const params = new URLSearchParams();
            params.append('start', info.startStr.split('T')[0]);
            params.append('end', info.endStr.split('T')[0]);
            if (selectedStatuses.length > 0) params.append('statuses', selectedStatuses.join(','));
            if (selectedRoom) params.append('room_id', selectedRoom);

            fetch(`../api/get_calendar_events.php?${params}`)
                .then(r => r.json())
                .then(data => successCallback(data))
                .catch(e => failureCallback(e));
        }

        function showEventDetail(event) {
            const props = event.extendedProps;
            const statusColors = {
                'menunggu': 'bg-yellow-100 text-yellow-800 border-yellow-300',
                'disetujui': 'bg-green-100 text-green-800 border-green-300',
                'ditolak': 'bg-red-100 text-red-800 border-red-300',
                'dibatalkan': 'bg-gray-100 text-gray-800 border-gray-300'
            };
            const statusLabels = {
                'menunggu': 'Menunggu Persetujuan',
                'disetujui': 'Disetujui',
                'ditolak': 'Ditolak',
                'dibatalkan': 'Dibatalkan'
            };

            // Setup Manual Reschedule Button
            // Parse event start/end for defaults
            const eventDate = event.start.toLocaleDateString('sv');
            const startTime = event.start.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', hour12: false }).replace('.', ':');
            const endTime = event.end ? event.end.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', hour12: false }).replace('.', ':') : startTime;

            // Trigger function for manual edit
            window.manualReschedule = function () {
                showRescheduleForm(event, eventDate, startTime, endTime);
            };

            let quickActions = '';
            if (props.status === 'menunggu') {
                const isRecurring = !!props.recurrence_type;
                quickActions = `
                <div class="flex gap-3 mt-6 pt-6 border-t">
                    <button onclick="updateBookingStatus(${props.id}, 'approve', null, ${isRecurring})" class="flex-1 bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-4 rounded-lg transition">
                        <i class="fas fa-check mr-2"></i>Setujui
                    </button>
                    <button onclick="showRejectForm(${props.id}, ${isRecurring})" class="flex-1 bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-4 rounded-lg transition">
                        <i class="fas fa-times mr-2"></i>Tolak
                    </button>
                </div>
                `;
            } else if (props.status === 'disetujui') {
                // Additional actions for approved events could go here
            }

            // Always show Edit button for Admin in modal
            const editButton = `
                 <button onclick="manualReschedule()" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition">
                    <i class="fas fa-edit mr-2"></i>Edit Jadwal
                </button>
            `;

            let rejectionReason = '';
            if (props.rejection_reason) {
                rejectionReason = `
                <div class="bg-red-50 border border-red-200 rounded-lg p-4 mt-4">
                    <p class="font-bold text-red-900 mb-1">Alasan Penolakan:</p>
                    <p class="text-red-800">${props.rejection_reason}</p>
                </div>`;
            }

            const content = `
            <div class="space-y-3">
                <div class="flex items-center justify-between border-b pb-2">
                    <h4 class="text-xl font-bold text-gray-900">${event.title}</h4>
                    <span class="px-3 py-1 rounded-full text-xs font-bold border ${statusColors[props.status]}">
                        ${statusLabels[props.status]}
                    </span>
                </div>
                
                ${props.recurrence_type ? `
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 flex items-start gap-3">
                    <div class="bg-blue-100 p-2 rounded-full text-blue-600">
                        <i class="fas fa-sync-alt"></i>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-blue-900 uppercase tracking-wide">Booking Berulang (${props.recurrence_type})</p>
                        <p class="text-sm text-blue-800">
                            Periode: <span class="font-semibold">${props.series_start_date}</span> s/d <span class="font-semibold">${props.recurrence_end_date}</span>
                        </p>
                    </div>
                </div>
                ` : ''}

                <div class="grid grid-cols-2 gap-3 text-sm">
                    <div class="p-2 bg-gray-50 rounded border border-gray-100">
                        <p class="text-xs text-gray-500 mb-0.5">Peminjam</p>
                        <p class="font-semibold text-gray-900 truncate" title="${props.nama_peminjam}">${props.nama_peminjam}</p>
                        <p class="text-xs text-gray-600 truncate" title="${props.instansi}">${props.instansi}</p>
                    </div>
                    <div class="p-2 bg-gray-50 rounded border border-gray-100">
                        <p class="text-xs text-gray-500 mb-0.5">Waktu</p>
                        <p class="font-semibold text-gray-900">${props.tanggal_formatted}</p>
                        <p class="text-xs text-gray-600">${props.waktu}</p>
                    </div>
                    <div class="col-span-2 p-2 bg-gray-50 rounded border border-gray-100">
                        <p class="text-xs text-gray-500 mb-0.5">Kegiatan</p>
                        <p class="font-semibold text-gray-900">${props.kegiatan}</p>
                    </div>
                    <div class="p-2 bg-gray-50 rounded border border-gray-100">
                        <p class="text-xs text-gray-500 mb-0.5">Peserta</p>
                        <p class="font-semibold text-gray-900">${props.jumlah_peserta} orang</p>
                    </div>
                    <div class="p-2 bg-gray-50 rounded border border-gray-100">
                        <p class="text-xs text-gray-500 mb-0.5">Kode Booking</p>
                        <p class="font-mono font-bold text-blue-600">${props.qr_token}</p>
                    </div>
                </div>
                
                ${rejectionReason}
                ${quickActions}
                
                <div class="flex gap-2 mt-2 pt-2 border-t">
                    ${editButton}
                    <button onclick="cancelBooking(${props.id}, ${!!props.recurrence_type})" class="flex-1 bg-red-50 hover:bg-red-100 text-red-600 font-bold py-2 px-3 text-sm rounded transition border border-red-200">
                        <i class="fas fa-trash-alt mr-1"></i>Batalkan
                    </button>
                </div>
            </div>
        `;
            document.getElementById('modalContent').innerHTML = content;
            document.getElementById('eventModal').classList.remove('hidden');
        }

        // Generic Update Status Function
        window.updateBookingStatus = function (bookingId, action, reason = null, isRecurring = false) {
            let confirmTitle, confirmText, loadingText;
            let showDeny = false;
            let confirmBtnText = 'Ya, Lanjutkan';
            let denyBtnText = 'Batal';

            if (isRecurring) {
                showDeny = true;
                confirmBtnText = 'Update Semua Seri';
                denyBtnText = 'Hanya Jadwal Ini';
                confirmText = 'Booking ini adalah bagian dari seri berulang.';
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
                    // Main button (Yes / All Series)
                    applyToSeries = isRecurring;
                    proceed = true;
                } else if (result.isDenied) {
                    // Secondary button (Hanya Ini)
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
                                Swal.fire('Berhasil!', data.message, 'success');
                                calendar.refetchEvents();
                                closeModal();
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

        window.cancelBooking = function (bookingId, isRecurring) {
            updateBookingStatus(bookingId, 'cancel', null, isRecurring);
        };

        window.showRejectForm = function (bookingId, isRecurring) {
            Swal.fire({
                title: 'Tolak Peminjaman',
                input: 'textarea',
                inputLabel: 'Alasan Penolakan',
                inputPlaceholder: 'Tulis alasan penolakan di sini...',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                confirmButtonText: 'Lanjut',
                cancelButtonText: 'Batal',
                inputValidator: (value) => {
                    if (!value) {
                        return 'Alasan harus diisi!'
                    }
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    // Pass to generic handler to show Series confirmation
                    updateBookingStatus(bookingId, 'reject', result.value, isRecurring);
                }
            });
        };

        window.closeModal = function () {
            document.getElementById('eventModal').classList.add('hidden');
        };
    });
</script>

</body>

</html>