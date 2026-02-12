<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        <?= isset($title) ? $title : 'Sistem Peminjaman Ruangan' ?> - BAPPEDA Jawa Tengah
    </title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#eff6ff', 100: '#dbeafe', 200: '#bfdbfe', 300: '#93c5fd',
                            400: '#60a5fa', 500: '#3b82f6', 600: '#2563eb', 700: '#1d4ed8',
                            800: '#1e40af', 900: '#1e3a8a',
                        }
                    }
                }
            }
        }
    </script>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Flatpickr (Date & Time Picker) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://npmcdn.com/flatpickr/dist/l10n/id.js"></script> <!-- Locale Indonesia -->

    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');

        body {
            font-family: 'Inter', sans-serif;
        }

        .glass {
            background: rgba(255, 255, 255, 0.25);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.18);
        }

        /* Glass Card - Professional Glassmorphism */
        .glass-card {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.08);
        }

        .animate-fade-in {
            animation: fadeIn 0.5s ease-in;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* FullCalendar Custom Styling - Solid Blue */
        .fc {
            font-family: 'Inter', sans-serif;
        }

        .fc .fc-button-primary {
            background: #2563eb;
            border: none;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.875rem;
            padding: 0.5rem 1rem;
            transition: all 0.2s;
            box-shadow: 0 2px 4px rgba(37, 99, 235, 0.2);
        }

        .fc .fc-button-primary:hover {
            background: #1d4ed8;
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(37, 99, 235, 0.3);
        }

        .fc .fc-button-primary:not(:disabled):active,
        .fc .fc-button-primary:not(:disabled).fc-button-active {
            background: #1e40af;
            box-shadow: 0 1px 2px rgba(37, 99, 235, 0.2);
        }

        .fc-theme-standard .fc-scrollgrid {
            border: 1px solid #e5e7eb;
        }

        .fc-theme-standard td,
        .fc-theme-standard th {
            border-color: #e5e7eb;
        }

        .fc .fc-daygrid-day-number {
            font-weight: 600;
            color: #374151;
        }

        .fc .fc-daygrid-day.fc-day-today {
            background-color: #dbeafe !important;
        }

        .fc .fc-col-header-cell {
            background: #f9fafb;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 0.75rem;
            color: #4b5563;
            border-bottom: 2px solid #e5e7eb;
        }

        .fc-event {
            cursor: pointer;
            border-radius: 4px;
            padding: 2px 4px;
            font-size: 0.75rem;
            font-weight: 600;
            transition: all 0.2s;
        }

        .fc-event:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            z-index: 10;
        }

        .hover-lift {
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .hover-lift:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.1);
        }

        .animate-slide-up {
            animation: slideUp 0.6s ease-out;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>

<body class="bg-gradient-to-br from-blue-50 via-indigo-50 to-purple-50 min-h-screen">
    <!-- Header with glassmorphism -->
    <header class="glass sticky top-0 z-50 shadow-lg">
        <div class="max-w-7xl mx-auto px-4 md:px-6 py-4 flex items-center justify-between">
            <a href="<?= base_url() ?>" class="flex items-center gap-3 group">
                <img src="<?= base_url('assets/images/logo-bappeda.png') ?>" alt="BAPPEDA"
                    class="h-10 md:h-12 w-auto transition-transform group-hover:scale-110"
                    onerror="this.src='https://upload.wikimedia.org/wikipedia/commons/9/92/Logo_Jateng.png'">
                <span
                    class="hidden sm:block text-base md:text-lg font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent">
                    BAPPEDA Jawa Tengah
                </span>
            </a>

            <!-- Desktop Navigation -->
            <nav class="hidden md:flex gap-6 lg:gap-8 text-sm font-medium">
                <a href="<?= base_url('calendar.php') ?>"
                    class="text-gray-700 hover:text-blue-600 transition-colors flex items-center gap-1">
                    <i class="fas fa-calendar-alt"></i>
                    <span>Kalender</span>
                </a>
                <a href="<?= base_url() ?>" class="text-gray-700 hover:text-blue-600 transition-colors">Beranda</a>
                <a href="<?= base_url('rooms.php') ?>"
                    class="text-gray-700 hover:text-blue-600 transition-colors">Ruangan</a>
                <a href="<?= base_url('#jadwal') ?>"
                    class="text-gray-700 hover:text-blue-600 transition-colors">Jadwal</a>
                <a href="<?= base_url('booking_status.php') ?>"
                    class="text-gray-700 hover:text-blue-600 transition-colors">Cek Status</a>
                <a href="<?= base_url('admin/login.php') ?>"
                    class="text-gray-500 hover:text-blue-600 transition-colors"><i class="fas fa-lock"></i></a>
            </nav>

            <!-- Mobile Hamburger Button -->
            <button id="mobileMenuBtn" class="md:hidden text-gray-700 hover:text-blue-600 transition-colors">
                <i class="fas fa-bars text-2xl"></i>
            </button>
        </div>

        <!-- Mobile Navigation Menu -->
        <div id="mobileMenu" class="hidden md:hidden bg-white/95 backdrop-blur-md border-t border-gray-200">
            <nav class="max-w-7xl mx-auto px-4 py-4 flex flex-col gap-3">
                <a href="<?= base_url('calendar.php') ?>"
                    class="text-gray-700 hover:text-blue-600 transition-colors py-2 px-3 rounded-lg hover:bg-blue-50 flex items-center gap-2">
                    <i class="fas fa-calendar-alt w-5"></i>
                    <span>Kalender</span>
                </a>
                <a href="<?= base_url() ?>"
                    class="text-gray-700 hover:text-blue-600 transition-colors py-2 px-3 rounded-lg hover:bg-blue-50 flex items-center gap-2">
                    <i class="fas fa-home w-5"></i>
                    <span>Beranda</span>
                </a>
                <a href="<?= base_url('rooms.php') ?>"
                    class="text-gray-700 hover:text-blue-600 transition-colors py-2 px-3 rounded-lg hover:bg-blue-50 flex items-center gap-2">
                    <i class="fas fa-door-open w-5"></i>
                    <span>Ruangan</span>
                </a>
                <a href="<?= base_url('#jadwal') ?>"
                    class="text-gray-700 hover:text-blue-600 transition-colors py-2 px-3 rounded-lg hover:bg-blue-50 flex items-center gap-2">
                    <i class="fas fa-calendar-check w-5"></i>
                    <span>Jadwal</span>
                </a>
                <a href="<?= base_url('booking_status.php') ?>"
                    class="text-gray-700 hover:text-blue-600 transition-colors py-2 px-3 rounded-lg hover:bg-blue-50 flex items-center gap-2">
                    <i class="fas fa-search w-5"></i>
                    <span>Cek Status</span>
                </a>
                <a href="<?= base_url('admin/login.php') ?>"
                    class="text-gray-500 hover:text-blue-600 transition-colors py-2 px-3 rounded-lg hover:bg-blue-50 flex items-center gap-2">
                    <i class="fas fa-lock w-5"></i>
                    <span>Admin Login</span>
                </a>
            </nav>
        </div>
    </header>

    <!-- Mobile Menu Toggle Script -->
    <script>
        document.getElementById('mobileMenuBtn')?.addEventListener('click', function () {
            const menu = document.getElementById('mobileMenu');
            const icon = this.querySelector('i');
            menu.classList.toggle('hidden');
            icon.classList.toggle('fa-bars');
            icon.classList.toggle('fa-times');
        });
    </script>

    <!-- SweetAlert2 Flash Messages -->
    <?php if (isset($_SESSION['success'])): ?>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: '<?= addslashes($_SESSION['success']) ?>',
                    confirmButtonColor: '#2563eb',
                    confirmButtonText: 'OK',
                    timer: 3000,
                    timerProgressBar: true
                });
            });
        </script>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: '<?= addslashes($_SESSION['error']) ?>',
                    confirmButtonColor: '#2563eb',
                    confirmButtonText: 'OK'
                });
            });
        </script>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['warning'])): ?>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                Swal.fire({
                    icon: 'warning',
                    title: 'Perhatian!',
                    text: '<?= addslashes($_SESSION['warning']) ?>',
                    confirmButtonColor: '#2563eb',
                    confirmButtonText: 'OK'
                });
            });
        </script>
        <?php unset($_SESSION['warning']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['info'])): ?>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                Swal.fire({
                    icon: 'info',
                    title: 'Informasi',
                    text: '<?= addslashes($_SESSION['info']) ?>',
                    confirmButtonColor: '#2563eb',
                    confirmButtonText: 'OK'
                });
            });
        </script>
        <?php unset($_SESSION['info']); ?>
    <?php endif; ?>

    <!-- Main Content -->
    <main class="animate-fade-in">