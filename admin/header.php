<?php require_once '../includes/auth_check.php'; ?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - BAPPEDA Jateng</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');

        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>

<body class="bg-gray-100 min-h-screen">

    <nav class="bg-slate-900 text-white shadow-lg sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between h-16">
                <div class="flex items-center gap-4">
                    <a href="index.php" class="font-bold text-xl flex items-center gap-2">
                        <i class="fas fa-shield-alt text-blue-400"></i> Admin Panel
                    </a>
                    <!-- Desktop Navigation -->
                    <div class="hidden md:flex ml-10 space-x-4">
                        <a href="index.php"
                            class="<?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'text-white bg-slate-800' : 'text-gray-300 hover:text-white'; ?> px-3 py-2 rounded-md text-sm font-medium">
                            <i class="fas fa-home mr-1"></i> Dashboard
                        </a>
                        <a href="calendar.php"
                            class="<?php echo basename($_SERVER['PHP_SELF']) == 'calendar.php' ? 'text-white bg-slate-800' : 'text-gray-300 hover:text-white'; ?> px-3 py-2 rounded-md text-sm font-medium">
                            <i class="fas fa-calendar-alt mr-1"></i> Kalender
                        </a>
                        <a href="booking_create.php"
                            class="<?php echo basename($_SERVER['PHP_SELF']) == 'booking_create.php' ? 'text-white bg-slate-800' : 'text-gray-300 hover:text-white'; ?> px-3 py-2 rounded-md text-sm font-medium">
                            <i class="fas fa-plus-circle mr-1"></i> Tambah Booking
                        </a>
                        <a href="bookings.php"
                            class="<?php echo basename($_SERVER['PHP_SELF']) == 'bookings.php' ? 'text-white bg-slate-800' : 'text-gray-300 hover:text-white'; ?> px-3 py-2 rounded-md text-sm font-medium">
                            <i class="fas fa-list mr-1"></i> Booking
                        </a>
                        <a href="rooms.php"
                            class="<?php echo basename($_SERVER['PHP_SELF']) == 'rooms.php' ? 'text-white bg-slate-800' : 'text-gray-300 hover:text-white'; ?> px-3 py-2 rounded-md text-sm font-medium">
                            <i class="fas fa-door-open mr-1"></i> Ruangan
                        </a>
                    </div>
                </div>
                <div class="flex items-center gap-4">
                    <span class="hidden sm:block text-sm text-gray-400">Halo,
                        <?= htmlspecialchars($_SESSION['admin_username'] ?? 'Admin') ?>
                    </span>
                    <!-- Logout Button - Responsive -->
                    <a href="logout.php"
                        class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-md text-sm font-medium transition">
                        <i class="fas fa-sign-out-alt"></i>
                        <span class="hidden sm:inline ml-1">Logout</span>
                    </a>
                    <!-- Mobile Menu Button -->
                    <button id="mobileMenuBtn" class="md:hidden text-gray-300 hover:text-white focus:outline-none">
                        <i class="fas fa-bars text-2xl"></i>
                    </button>
                </div>
            </div>

            <!-- Mobile Navigation Menu -->
            <div id="mobileMenu" class="hidden md:hidden bg-slate-800 border-t border-slate-700">
                <div class="px-4 py-3 space-y-2">
                    <a href="index.php"
                        class="<?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'bg-slate-700 text-white' : 'text-gray-300 hover:bg-slate-700 hover:text-white'; ?> block px-3 py-2 rounded-md text-base font-medium">
                        <i class="fas fa-home mr-2"></i> Dashboard
                    </a>
                    <a href="calendar.php"
                        class="<?php echo basename($_SERVER['PHP_SELF']) == 'calendar.php' ? 'bg-slate-700 text-white' : 'text-gray-300 hover:bg-slate-700 hover:text-white'; ?> block px-3 py-2 rounded-md text-base font-medium">
                        <i class="fas fa-calendar-alt mr-2"></i> Kalender
                    </a>
                    <a href="booking_create.php"
                        class="<?php echo basename($_SERVER['PHP_SELF']) == 'booking_create.php' ? 'bg-slate-700 text-white' : 'text-gray-300 hover:bg-slate-700 hover:text-white'; ?> block px-3 py-2 rounded-md text-base font-medium">
                        <i class="fas fa-plus-circle mr-2"></i> Tambah Booking
                    </a>
                    <a href="bookings.php"
                        class="<?php echo basename($_SERVER['PHP_SELF']) == 'bookings.php' ? 'bg-slate-700 text-white' : 'text-gray-300 hover:bg-slate-700 hover:text-white'; ?> block px-3 py-2 rounded-md text-base font-medium">
                        <i class="fas fa-list mr-2"></i> Booking
                    </a>
                    <a href="rooms.php"
                        class="<?php echo basename($_SERVER['PHP_SELF']) == 'rooms.php' ? 'bg-slate-700 text-white' : 'text-gray-300 hover:bg-slate-700 hover:text-white'; ?> block px-3 py-2 rounded-md text-base font-medium">
                        <i class="fas fa-door-open mr-2"></i> Ruangan
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <script>
        // Mobile menu toggle
        document.getElementById('mobileMenuBtn').addEventListener('click', function () {
            const menu = document.getElementById('mobileMenu');
            menu.classList.toggle('hidden');
        });
    </script>


    <div class="max-w-7xl mx-auto px-4 py-8 animate-fade-in">

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