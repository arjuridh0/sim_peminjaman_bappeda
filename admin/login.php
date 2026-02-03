<?php
require_once '../includes/functions.php';

// Jika sudah login, lempar ke dashboard
if (isset($_SESSION['admin_logged_in'])) {
    redirect('index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        set_flash_message('error', "Invalid security token. Please try again.");
    } else {
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';

        $admin = login_admin($username, $password);

        if ($admin) {
            // Regenerate session ID to prevent session fixation
            session_regenerate_id(true);

            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            $_SESSION['CREATED'] = time(); // Reset session creation time

            redirect('index.php');
        } else {
            set_flash_message('error', "Username atau password salah");
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - BAPPEDA Jateng</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        .glass {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
        }
    </style>
</head>

<body class="bg-gradient-to-br from-blue-600 to-indigo-900 min-h-screen flex items-center justify-center p-4">

    <div class="glass w-full max-w-md rounded-2xl shadow-2xl overflow-hidden p-8">
        <div class="text-center mb-8">
            <img src="../assets/images/logo-bappeda.png" alt="Logo" class="h-16 w-auto mx-auto mb-4"
                onerror="this.src='https://upload.wikimedia.org/wikipedia/commons/9/92/Logo_Jateng.png'">
            <h1 class="text-2xl font-bold text-gray-800">Login Admin</h1>
            <p class="text-gray-600">Sistem Peminjaman Ruangan</p>
        </div>

        <!-- SweetAlert2 Error Message -->
        <?php if (isset($_SESSION['error'])): ?>
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    Swal.fire({
                        icon: 'error',
                        title: 'Login Gagal',
                        text: '<?= addslashes($_SESSION['error']) ?>',
                        confirmButtonColor: '#2563eb',
                        confirmButtonText: 'Coba Lagi'
                    });
                });
            </script>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <form method="POST">
            <?= csrf_field() ?>

            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-bold mb-2">Username</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                        <i class="fas fa-user text-gray-400"></i>
                    </span>
                    <input type="text" name="username" required
                        class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                        placeholder="Masukkan username">
                </div>
            </div>

            <div class="mb-8">
                <label class="block text-gray-700 text-sm font-bold mb-2">Password</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                        <i class="fas fa-lock text-gray-400"></i>
                    </span>
                    <input type="password" name="password" required
                        class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                        placeholder="Masukkan password">
                </div>
            </div>

            <button type="submit"
                class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-lg shadow-lg transform active:scale-95 transition duration-200">
                MASUK
            </button>
        </form>

        <div class="mt-8 text-center">
            <a href="<?= base_url() ?>" class="text-gray-500 hover:text-blue-600 text-sm">
                <i class="fas fa-arrow-left mr-1"></i> Kembali ke Beranda
            </a>
        </div>
    </div>

</body>

</html>