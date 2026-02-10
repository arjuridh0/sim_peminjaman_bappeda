<?php
require_once 'includes/functions.php';
$title = 'Kalender Peminjaman';
require 'includes/header.php';
?>

<!-- Calendar Page -->
<section class="py-8">
    <div class="max-w-7xl mx-auto px-4 md:px-6">
        <!-- Header -->
        <div class="mb-6">
            <h1 class="text-3xl md:text-4xl font-bold text-gray-900 mb-3">
                <i class="fas fa-calendar-alt mr-2 text-blue-600"></i>Kalender Peminjaman
            </h1>
            <p class="text-gray-600 text-sm md:text-base">
                Lihat jadwal peminjaman ruangan dalam tampilan kalender
            </p>
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
                transition: all 0.2s !important;
                box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05) !important;
            }

            .fc-button-primary:hover {
                background-color: var(--fc-button-hover-bg-color) !important;
                border-color: var(--fc-button-hover-border-color) !important;
                box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06) !important;
                transform: translateY(-1px);
            }

            .fc-button-primary:not(:disabled).fc-button-active,
            .fc-button-primary:not(:disabled):active {
                background-color: var(--fc-button-active-bg-color) !important;
                border-color: var(--fc-button-active-border-color) !important;
                box-shadow: inset 0 2px 4px 0 rgba(0, 0, 0, 0.06) !important;
            }

            .fc-toolbar-title {
                font-size: 1.25rem !important;
                font-weight: 700 !important;
                color: #1f2937 !important;
            }

            /* Mobile Responsive FullCalendar */
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
                }

                .fc-toolbar-title {
                    font-size: 1.1rem !important;
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
            }

            .fc-daygrid-more-link:hover {
                background-color: #2563eb !important;
                color: #ffffff !important;
                border-color: #2563eb !important;
                transform: translateY(-1px);
                box-shadow: 0 2px 4px rgba(37, 99, 235, 0.2) !important;
            }
        </style>

        <!-- Layout: Sidebar + Calendar -->
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">

            <!-- LEFT SIDEBAR (Legend + Filter) -->
            <div class="lg:col-span-1 space-y-6">

                <!-- Legend Card - Glass Effect -->
                <div class="glass-card p-5 rounded-xl">
                    <h3 class="font-bold text-gray-900 mb-4 flex items-center gap-2">
                        <i class="fas fa-info-circle text-blue-600"></i>
                        <span>Keterangan Status</span>
                    </h3>
                    <div class="space-y-3">
                        <div class="flex items-center gap-3">
                            <div class="w-5 h-5 rounded bg-green-500 flex-shrink-0 shadow-sm"></div>
                            <span class="text-sm text-gray-700">Disetujui</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="w-5 h-5 rounded bg-yellow-500 flex-shrink-0 shadow-sm"></div>
                            <span class="text-sm text-gray-700">Menunggu</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="w-5 h-5 rounded bg-red-500 flex-shrink-0 shadow-sm"></div>
                            <span class="text-sm text-gray-700">Ditolak</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="w-5 h-5 rounded bg-gray-400 flex-shrink-0 shadow-sm"></div>
                            <span class="text-sm text-gray-700">Dibatalkan</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="w-5 h-5 rounded bg-red-600 flex-shrink-0 shadow-sm border-4 border-red-800">
                            </div>
                            <span class="text-sm text-gray-700">Konflik!</span>
                        </div>
                    </div>
                </div>

                <!-- Filter Card - Glass Effect -->
                <div class="glass-card p-5 rounded-xl">
                    <h3 class="font-bold text-gray-900 mb-4 flex items-center gap-2">
                        <i class="fas fa-filter text-blue-600"></i>
                        <span>Filter</span>
                    </h3>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Ruangan
                            </label>
                            <select id="roomFilter"
                                class="w-full px-3 py-2.5 bg-white border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition text-sm shadow-sm">
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

                        <button id="todayBtn"
                            class="w-full px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-all duration-200 shadow-md hover:shadow-lg text-sm">
                            <i class="fas fa-calendar-day mr-2"></i>Hari Ini
                        </button>
                    </div>
                </div>

                <!-- Info Card - Glass Effect -->
                <div class="glass-card p-5 rounded-xl border border-blue-100 hidden lg:block">
                    <h3 class="font-bold text-gray-900 mb-3 flex items-center gap-2">
                        <i class="fas fa-lightbulb text-blue-600"></i>
                        <span>Tips</span>
                    </h3>
                    <p class="text-sm text-gray-600 leading-relaxed">
                        Klik pada event di kalender untuk melihat detail lengkap peminjaman ruangan.
                    </p>
                </div>
            </div>

            <!-- RIGHT SIDE (Calendar) -->
            <div class="lg:col-span-3">
                <!-- Export Toolbar - Horizontal Layout -->
                <!-- Calendar Card -->
                <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-4 md:p-6">
                    <div id="calendar"></div>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- Daily Summary Modal (for Date Click) -->
<div id="dailyModal"
    class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4 backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-3xl max-h-[90vh] overflow-y-auto">
        <div class="sticky top-0 bg-purple-600 px-6 py-4 flex justify-between items-center rounded-t-2xl">
            <h3 class="text-xl font-bold text-white">
                <i class="fas fa-calendar-day mr-2"></i><span id="dailyModalTitle">Jadwal Harian</span>
            </h3>
            <button onclick="closeDailyModal()" class="text-white hover:text-purple-100 transition">
                <i class="fas fa-times text-2xl"></i>
            </button>
        </div>
        <div id="dailyDetails" class="p-6">
            <!-- Content will be loaded dynamically -->
        </div>
    </div>
</div>

<!-- Event Detail Modal -->
<div id="eventModal"
    class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4 backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
        <div class="sticky top-0 bg-blue-600 px-6 py-4 flex justify-between items-center rounded-t-2xl">
            <h3 class="text-xl font-bold text-white">
                <i class="fas fa-info-circle mr-2"></i>Detail Peminjaman
            </h3>
            <button onclick="closeModal()" class="text-white hover:text-blue-100 transition">
                <i class="fas fa-times text-2xl"></i>
            </button>
        </div>
        <div id="eventDetails" class="p-6">
            <!-- Content will be loaded dynamically -->
        </div>
    </div>
</div>

<!-- Booking Form Modal -->
<div id="bookingModal"
    class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4 backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-3xl max-h-[90vh] overflow-hidden flex flex-col">
        <div
            class="bg-gradient-to-r from-blue-600 to-purple-600 px-6 py-4 flex justify-between items-center flex-shrink-0">
            <h3 class="text-xl font-bold text-white">
                <i class="fas fa-calendar-plus mr-2"></i>Form Peminjaman Ruangan
            </h3>
            <button onclick="closeBookingModal()" class="text-white hover:text-blue-100 transition">
                <i class="fas fa-times text-2xl"></i>
            </button>
        </div>
        <div id="bookingFormContent" class="p-6 overflow-y-auto flex-1">
            <!-- Content will be loaded dynamically -->
        </div>
    </div>
</div>

<!-- Success Modal -->
<div id="successModal"
    class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4 backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md">
        <div class="p-8 text-center">
            <div class="mb-6">
                <div class="mx-auto w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mb-4">
                    <i class="fas fa-check-circle text-5xl text-green-500"></i>
                </div>
                <h3 class="text-2xl font-bold text-gray-900 mb-2">Booking Berhasil!</h3>
                <p class="text-gray-600">Pengajuan Anda sedang diproses</p>
            </div>

            <div class="bg-blue-50 rounded-xl p-6 mb-6">
                <p class="text-sm text-gray-600 mb-2">Kode Booking Anda:</p>
                <div class="bg-white rounded-lg p-4 border-2 border-blue-200">
                    <p id="successBookingCode" class="text-2xl font-bold text-blue-600 font-mono tracking-wider"></p>
                </div>
                <p class="text-xs text-gray-500 mt-3">
                    <i class="fas fa-info-circle mr-1"></i>Simpan kode ini untuk cek status atau membatalkan booking
                </p>
            </div>

            <div class="space-y-3">
                <button onclick="closeSuccessModal()"
                    class="w-full bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white font-bold py-3 px-6 rounded-lg shadow-lg hover:shadow-xl transition-all duration-200">
                    <i class="fas fa-check mr-2"></i>Tutup
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Print CSS -->
<link href="assets/css/print.css" rel="stylesheet" media="print">

<!-- FullCalendar CSS & JS -->
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const calendarEl = document.getElementById('calendar');
        const roomFilter = document.getElementById('roomFilter');
        const todayBtn = document.getElementById('todayBtn');

        // Initialize FullCalendar
        const calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            headerToolbar: {
                left: 'prev,next',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,listMonth'
            },
            buttonText: {
                month: 'Bulan',
                week: 'Minggu',
                list: 'Daftar'
            },
            locale: 'id',
            firstDay: 1, // Monday
            height: 'auto',
            dayMaxEvents: 2, // Force max 2 events
            eventTimeFormat: {
                hour: '2-digit',
                minute: '2-digit',
                hour12: false
            },
            events: function (info, successCallback, failureCallback) {
                const roomId = roomFilter.value;
                const url = `api/get_calendar_events.php?start=${info.startStr}&end=${info.endStr}${roomId ? '&room_id=' + roomId : ''}`;

                fetch(url)
                    .then(response => response.json())
                    .then(data => successCallback(data))
                    .catch(error => {
                        console.error('Error loading events:', error);
                        failureCallback(error);
                    });
            },
            dateClick: function (info) {
                showDailySummary(info.date, info.dateStr);
            },
            eventClick: function (info) {
                showEventDetails(info.event);
            },
            eventDidMount: function (info) {
                // Add tooltip
                info.el.title = info.event.extendedProps.tooltip || '';
            }
        });

        calendar.render();

        // Room filter change
        roomFilter.addEventListener('change', function () {
            calendar.refetchEvents();
        });

        // Today button
        todayBtn.addEventListener('click', function () {
            calendar.today();
        });

        // Show daily summary when clicking a date
        function showDailySummary(date, dateStr) {
            const roomId = roomFilter.value;

            // Fetch events for this specific date
            fetch(`api/get_calendar_events.php?start=${dateStr}&end=${dateStr}${roomId ? '&room_id=' + roomId : ''}`)
                .then(response => response.json())
                .then(events => {
                    const formattedDate = formatDate(date);
                    document.getElementById('dailyModalTitle').textContent = formattedDate;

                    let html = '<div class="space-y-4">';

                    if (events.length === 0) {
                        html += `
                            <div class="text-center py-8">
                                <i class="fas fa-calendar-check text-6xl text-gray-300 mb-4"></i>
                                <p class="text-gray-500 mb-4">Tidak ada booking pada tanggal ini</p>
                            </div>
                        `;
                    } else {
                        html += `<h4 class="font-bold text-gray-700 mb-3"><i class="fas fa-list mr-2"></i>Daftar Booking (${events.length})</h4>`;
                        html += '<div class="space-y-2">';

                        events.forEach(event => {
                            const props = event.extendedProps;
                            const statusColors = {
                                'menunggu': 'bg-yellow-100 text-yellow-800 border-yellow-300',
                                'disetujui': 'bg-green-100 text-green-800 border-green-300',
                                'ditolak': 'bg-red-100 text-red-800 border-red-300',
                                'dibatalkan': 'bg-gray-100 text-gray-800 border-gray-300'
                            };
                            const statusClass = statusColors[props.status] || 'bg-gray-100 text-gray-800';

                            html += `
                                <div class="border rounded-lg p-3 hover:shadow-md transition cursor-pointer" onclick="showEventDetailsById(${event.id})">
                                    <div class="flex justify-between items-start mb-2">
                                        <h5 class="font-bold text-gray-900">${event.title}</h5>
                                        <span class="px-2 py-1 rounded-full text-xs font-bold border ${statusClass}">${props.status.toUpperCase()}</span>
                                    </div>
                                    <p class="text-sm text-gray-600"><i class="fas fa-clock mr-1"></i>${props.waktu}</p>
                                    <p class="text-sm text-gray-600"><i class="fas fa-user mr-1"></i>${props.nama_peminjam}</p>
                                    <p class="text-sm text-gray-600 truncate"><i class="fas fa-clipboard mr-1"></i>${props.kegiatan}</p>
                                </div>
                            `;
                        });

                        html += '</div>';
                    }

                    // Add booking button
                    html += `
                        <div class="pt-4 border-t">
                            <button onclick="showBookingForm('${dateStr}', ${roomId || 'null'})" class="block w-full text-center bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white font-bold py-3 px-6 rounded-lg shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-200">
                                <i class="fas fa-plus-circle mr-2"></i>Ajukan Peminjaman
                            </button>
                        </div>
                    `;

                    html += '</div>';

                    document.getElementById('dailyDetails').innerHTML = html;
                    document.getElementById('dailyModal').classList.remove('hidden');
                })
                .catch(error => {
                    console.error('Error loading daily events:', error);
                    alert('Gagal memuat data booking');
                });
        }

        // Show event details by ID (called from daily summary)
        window.showEventDetailsById = function (eventId) {
            const event = calendar.getEventById(eventId);
            if (event) {
                closeDailyModal();
                showEventDetails(event);
            }
        }

        // Show event details in modal
        function showEventDetails(event) {
            const props = event.extendedProps;
            const statusColors = {
                'menunggu': 'bg-yellow-100 text-yellow-800 border-yellow-300',
                'disetujui': 'bg-green-100 text-green-800 border-green-300',
                'ditolak': 'bg-red-100 text-red-800 border-red-300',
                'dibatalkan': 'bg-gray-100 text-gray-800 border-gray-300'
            };

            const statusClass = statusColors[props.status] || 'bg-gray-100 text-gray-800';

            let html = `
            <div class="space-y-3 text-sm" id="eventDetailsContent">
                <div class="flex items-center justify-between border-b pb-2">
                    <h4 class="text-xl font-bold text-gray-900 truncate pr-2">${event.title}</h4>
                    <span class="inline-block px-3 py-1 rounded-full text-xs font-bold border ${statusClass} whitespace-nowrap">
                        ${props.status.toUpperCase()}
                    </span>
                </div>
                `;

            // Show conflict warning if exists
            if (props.hasConflict && props.conflictDetails.length > 0) {
                html += `
                <div class="bg-red-50 border border-red-500 rounded p-3 mb-2">
                    <div class="flex items-start gap-2">
                        <i class="fas fa-exclamation-triangle text-red-600 mt-0.5"></i>
                        <div class="flex-1">
                            <p class="font-bold text-red-800 text-xs mb-1">DATA KONFLIK!</p>
                            <ul class="space-y-1">
                 `;
                props.conflictDetails.forEach(conflict => {
                    html += `<li class="text-xs text-red-700 list-disc ml-4">${conflict.kegiatan} (${conflict.waktu})</li>`;
                });
                html += `</ul></div></div></div>`;
            }

            html += `
                <div class="grid grid-cols-2 gap-3">
                    <div class="p-2 bg-gray-50 rounded border border-gray-100">
                        <p class="text-xs text-gray-500 mb-0.5">Tanggal</p>
                        <p class="font-semibold text-gray-900">${formatDate(event.start)}</p>
                    </div>
                    <div class="p-2 bg-gray-50 rounded border border-gray-100">
                        <p class="text-xs text-gray-500 mb-0.5">Waktu</p>
                        <p class="font-semibold text-gray-900">${formatTime(event.start)} - ${formatTime(event.end)}</p>
                    </div>
                    
                    <div class="p-2 bg-gray-50 rounded border border-gray-100">
                        <p class="text-xs text-gray-500 mb-0.5">Ruangan</p>
                        <p class="font-semibold text-gray-900 truncate" title="${props.room_name}">${props.room_name}</p>
                    </div>
                    <div class="p-2 bg-gray-50 rounded border border-gray-100">
                        <p class="text-xs text-gray-500 mb-0.5">Peminjam</p>
                        <p class="font-semibold text-gray-900 truncate" title="${props.nama_peminjam}">${props.nama_peminjam}</p>
                    </div>

                    <div class="col-span-2 p-2 bg-gray-50 rounded border border-gray-100">
                        <p class="text-xs text-gray-500 mb-0.5">Instansi / Unit Kerja</p>
                        <p class="font-semibold text-gray-900 truncate">${props.instansi}${props.divisi ? ' / ' + props.divisi : ''}</p>
                    </div>

                    <div class="col-span-2 p-2 bg-gray-50 rounded border border-gray-100">
                        <p class="text-xs text-gray-500 mb-0.5">Kegiatan</p>
                        <p class="font-semibold text-gray-900">${props.kegiatan}</p>
                    </div>

                    <div class="col-span-2 p-2 bg-gray-50 rounded border border-gray-100">
                        <p class="text-xs text-gray-500 mb-0.5">Jumlah Peserta</p>
                        <p class="font-semibold text-gray-900">${props.jumlah_peserta} orang</p>
                    </div>
                </div>
        `;

            if (props.rejection_reason) {
                html += `
                    <div class="mt-2 bg-red-50 p-2 rounded border border-red-100">
                        <p class="text-xs text-red-600 font-bold mb-0.5">Alasan Penolakan</p>
                        <p class="text-sm text-gray-900">${props.rejection_reason}</p>
                    </div>
            `;
            }

            if (props.cancel_reason) {
                html += `
                    <div class="mt-2 bg-gray-100 p-2 rounded border border-gray-200">
                        <p class="text-xs text-gray-600 font-bold mb-0.5">Alasan Pembatalan</p>
                        <p class="text-sm text-gray-900">${props.cancel_reason}</p>
                    </div>
            `;
            }

            // Add cancel button if booking is not already cancelled or rejected
            if (props.status !== 'dibatalkan' && props.status !== 'ditolak') {
                html += `
                    <div class="pt-3 border-t">
                        <button onclick="showCancelForm(${event.id}, '${props.qr_token}')" class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-6 rounded-lg shadow-lg hover:shadow-xl transition-all duration-200">
                            <i class="fas fa-times-circle mr-2"></i>Batalkan Booking
                        </button>
                    </div>
                `;
            }

            html += `</div>`; // Close space-y-3

            document.getElementById('eventDetails').innerHTML = html;
            document.getElementById('eventModal').classList.remove('hidden');
        }

        // Helper functions
        function formatDate(date) {
            const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
            return new Date(date).toLocaleDateString('id-ID', options);
        }

        function formatTime(date) {
            return new Date(date).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
        }

        // Show cancel form
        window.showCancelForm = function (bookingId, qrToken) {
            const html = `
                <div class="space-y-4">
                    <div class="bg-yellow-50 border border-yellow-300 rounded-lg p-4">
                        <div class="flex items-start gap-3">
                            <i class="fas fa-exclamation-triangle text-yellow-600 text-xl mt-0.5"></i>
                            <div>
                                <h4 class="font-bold text-yellow-900 mb-1">Konfirmasi Pembatalan</h4>
                                <p class="text-sm text-yellow-800">Untuk membatalkan booking, masukkan Kode Booking Anda.</p>
                            </div>
                        </div>
                    </div>
                    
                    <form id="cancelForm" onsubmit="handleCancelBooking(event, ${bookingId})">
                        <div class="mb-4">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                <i class="fas fa-key mr-1"></i>Kode Booking <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="cancelToken" required
                                placeholder="Masukkan kode booking"
                                class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all outline-none font-mono">
                            <p class="mt-1 text-xs text-gray-500">
                                <i class="fas fa-info-circle mr-1"></i>Kode booking dapat ditemukan di email konfirmasi Anda
                            </p>
                        </div>
                        
                        <div class="flex gap-3">
                            <button type="submit" class="flex-1 bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-6 rounded-lg shadow-lg hover:shadow-xl transition-all duration-200">
                                <i class="fas fa-check mr-2"></i>Konfirmasi Pembatalan
                            </button>
                            <button type="button" onclick="location.reload()" class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-3 px-6 rounded-lg shadow-md hover:shadow-lg transition-all duration-200">
                                <i class="fas fa-arrow-left mr-2"></i>Kembali
                            </button>
                        </div>
                    </form>
                </div>
            `;

            document.getElementById('eventDetails').innerHTML = html;
        }

        // Handle cancel booking submission
        window.handleCancelBooking = function (event, bookingId) {
            event.preventDefault();

            const token = document.getElementById('cancelToken').value.trim();

            if (!token) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Kode booking wajib diisi'
                });
                return;
            }

            // Show loading
            Swal.fire({
                title: 'Memproses...',
                text: 'Mohon tunggu',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // Send cancel request
            fetch('api/user_cancel_booking.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    booking_id: bookingId,
                    qr_token: token
                })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: data.message,
                            confirmButtonText: 'OK'
                        }).then(() => {
                            closeModal();
                            calendar.refetchEvents();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: data.message
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Terjadi kesalahan sistem'
                    });
                });
        }

        // Make closeModal global
        window.closeModal = function () {
            document.getElementById('eventModal').classList.add('hidden');
        }

        window.closeDailyModal = function () {
            document.getElementById('dailyModal').classList.add('hidden');
        }

        // Close modal on outside click
        document.getElementById('eventModal').addEventListener('click', function (e) {
            if (e.target === this) {
                closeModal();
            }
        });

        document.getElementById('dailyModal').addEventListener('click', function (e) {
            if (e.target === this) {
                closeDailyModal();
            }
        });

        // Show booking form modal
        window.showBookingForm = function (dateStr, preselectedRoomId = null) {
            closeDailyModal(); // Close daily modal first

            // Fetch rooms
            fetch('api/get_rooms.php')
                .then(response => response.json())
                .then(rooms => {
                    let roomOptions = '<option value="">Pilih Ruangan</option>';
                    rooms.forEach(room => {
                        const selected = preselectedRoomId && room.id == preselectedRoomId ? 'selected' : '';
                        roomOptions += `<option value="${room.id}" ${selected}>${room.name} (Kapasitas: ${room.capacity})</option>`;
                    });

                    const html = `
                        <form id="quickBookingForm" onsubmit="handleBookingSubmit(event, '${dateStr}')">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                <!-- Ruangan -->
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                                        <i class="fas fa-door-open text-blue-600 mr-1"></i>Ruangan <span class="text-red-500">*</span>
                                    </label>
                                    <select name="room_id" id="quickRoomId" required
                                        class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all outline-none">
                                        ${roomOptions}
                                    </select>
                                </div>

                                <!-- Nama Peminjam -->
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                                        <i class="fas fa-user text-blue-600 mr-1"></i>Nama Peminjam <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" name="nama_peminjam" required
                                        class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all outline-none">
                                </div>

                                <!-- Divisi -->
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                                        <i class="fas fa-building text-blue-600 mr-1"></i>Unit Kerja <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" name="divisi" required
                                        class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all outline-none">
                                </div>

                                <!-- Instansi -->
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                                        <i class="fas fa-landmark text-blue-600 mr-1"></i>Instansi <span class="text-red-500">*</span>
                                    </label>
                                    <select name="instansi" id="quickInstansi" required
                                        class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all outline-none">
                                        <option value="">Pilih Instansi</option>
                                        <option value="bappeda">BAPPEDA</option>
                                        <option value="lainnya">Lainnya</option>
                                    </select>
                                </div>

                                <!-- Kegiatan -->
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                                        <i class="fas fa-clipboard-list text-blue-600 mr-1"></i>Nama Kegiatan <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" name="kegiatan" required
                                        class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all outline-none">
                                </div>

                                <!-- Jumlah Peserta -->
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                                        <i class="fas fa-users text-blue-600 mr-1"></i>Jumlah Peserta <span class="text-red-500">*</span>
                                    </label>
                                    <input type="number" name="jumlah_peserta" min="1" required
                                        class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all outline-none">
                                </div>

                                <!-- Tanggal (readonly, pre-filled) -->
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                                        <i class="fas fa-calendar text-blue-600 mr-1"></i>Tanggal <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" name="tanggal" value="${dateStr}" readonly
                                        class="w-full px-4 py-3 rounded-lg border-2 border-gray-300 bg-gray-100 text-gray-700 font-semibold">
                                </div>

                                <!-- Waktu Mulai -->
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                                        <i class="fas fa-clock text-blue-600 mr-1"></i>Waktu Mulai <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" name="waktu_mulai" placeholder="00:00" required
                                        class="timepicker-quick w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all outline-none bg-white">
                                </div>

                                <!-- Waktu Selesai -->
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                                        <i class="fas fa-clock text-blue-600 mr-1"></i>Waktu Selesai <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" name="waktu_selesai" placeholder="00:00" required
                                        class="timepicker-quick w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all outline-none bg-white">
                                </div>

                                <!-- Email -->
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                                        <i class="fas fa-envelope text-blue-600 mr-1"></i>Email <span class="text-red-500">*</span>
                                    </label>
                                    <input type="email" name="user_email" placeholder="email@example.com" required
                                        class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all outline-none">
                                    <p class="mt-1 text-sm text-gray-500">
                                        <i class="fas fa-info-circle mr-1"></i>Email wajib diisi untuk menerima Kode Booking
                                    </p>
                                </div>

                                <!-- File Upload (Conditional) -->
                                <div id="quickFileWrapper" class="hidden md:col-span-2">
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                                        <i class="fas fa-file-upload text-blue-600 mr-1"></i>Upload Surat Permohonan <span class="text-red-500">*</span>
                                    </label>
                                    <input type="file" name="file_pendukung" accept=".pdf,.doc,.docx"
                                        class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all outline-none file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-blue-50 file:text-blue-700 file:font-semibold hover:file:bg-blue-100">
                                    <p class="mt-1 text-sm text-gray-500">Format: PDF, DOC, DOCX (Maks. 5MB)</p>
                                </div>
                            </div>

                            <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded-lg mb-6">
                                <p class="text-sm text-blue-900">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    Pengajuan akan diproses dalam waktu maksimal 1x24 jam
                                </p>
                            </div>

                            <div class="flex gap-3">
                                <button type="submit"
                                    class="flex-1 bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white font-bold py-4 px-6 rounded-lg shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-200">
                                    <i class="fas fa-paper-plane mr-2"></i>Ajukan Peminjaman
                                </button>
                                <button type="button" onclick="closeBookingModal()"
                                    class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-4 px-6 rounded-lg shadow-md hover:shadow-lg transition-all duration-200">
                                    <i class="fas fa-times mr-2"></i>Batal
                                </button>
                            </div>
                        </form>
                    `;

                    document.getElementById('bookingFormContent').innerHTML = html;
                    document.getElementById('bookingModal').classList.remove('hidden');

                    // Initialize timepickers
                    flatpickr(".timepicker-quick", {
                        enableTime: true,
                        noCalendar: true,
                        dateFormat: "H:i",
                        time_24hr: true,
                        disableMobile: "true"
                    });

                    // Handle instansi change for file upload
                    document.getElementById('quickInstansi').addEventListener('change', function () {
                        const fileWrapper = document.getElementById('quickFileWrapper');
                        const fileInput = fileWrapper.querySelector('input');
                        if (this.value === 'lainnya') {
                            fileWrapper.classList.remove('hidden');
                            fileInput.setAttribute('required', 'required');
                        } else {
                            fileWrapper.classList.add('hidden');
                            fileInput.removeAttribute('required');
                        }
                    });
                })
                .catch(error => {
                    console.error('Error loading rooms:', error);
                    alert('Gagal memuat data ruangan');
                });
        }

        window.closeBookingModal = function () {
            document.getElementById('bookingModal').classList.add('hidden');
        }

        window.handleBookingSubmit = function (event, dateStr) {
            event.preventDefault();

            const form = event.target;
            const formData = new FormData(form);

            // Add CSRF token
            formData.append('csrf_token', '<?= generate_csrf_token() ?>');

            // Show loading
            Swal.fire({
                title: 'Memproses...',
                text: 'Mohon tunggu, sedang mengirim data dan email konfirmasi.',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // Submit booking
            fetch('booking.php', {
                method: 'POST',
                body: formData
            })
                .then(response => {
                    // Check if redirect happened (success)
                    if (response.redirected) {
                        // Extract token from session or response
                        // For now, we'll show a generic success
                        return fetch('api/get_last_booking.php')
                            .then(r => r.json());
                    }
                    return response.text().then(text => {
                        throw new Error('Booking failed');
                    });
                })
                .then(data => {
                    Swal.close();
                    closeBookingModal();

                    // Show success modal with booking code
                    document.getElementById('successBookingCode').textContent = data.booking_code || 'Cek Email';
                    document.getElementById('successModal').classList.remove('hidden');

                    // Refresh calendar
                    calendar.refetchEvents();
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: 'Terjadi kesalahan saat memproses booking. Silakan coba lagi.'
                    });
                });
        }

        window.closeSuccessModal = function () {
            document.getElementById('successModal').classList.add('hidden');
        }

        // Close modals on outside click
        document.getElementById('bookingModal').addEventListener('click', function (e) {
            if (e.target === this) {
                closeBookingModal();
            }
        });

        document.getElementById('successModal').addEventListener('click', function (e) {
            if (e.target === this) {
                closeSuccessModal();
            }
        });
    });
</script>

<?php require 'includes/footer.php'; ?>