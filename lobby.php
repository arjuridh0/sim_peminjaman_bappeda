<?php
require_once 'includes/functions.php';

// Lobby specific logic (Get today's approved bookings)
global $pdo;
$today = date('Y-m-d');
$sql = "
    SELECT b.*, r.name as room_name
    FROM bookings b
    JOIN rooms r ON b.room_id = r.id
    WHERE b.tanggal = ?
    AND b.status = 'disetujui'
    ORDER BY b.waktu_mulai ASC
";

$stmt = $pdo->prepare($sql);
$stmt->execute([$today]);
$todayBookings = $stmt->fetchAll();

// Custom Header for Lobby (No Nav)
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jadwal Ruangan - BAPPEDA Jateng</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <style>
        body {
            font-family: 'Outfit', sans-serif;
        }

        .glass {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
        }
    </style>
</head>

<body class="bg-gradient-to-br from-slate-900 to-slate-800 min-h-screen text-white overflow-hidden">

    <!-- Header -->
    <header class="bg-white/10 backdrop-blur-md border-b border-white/10 p-6 flex justify-between items-center">
        <div class="flex items-center gap-4">
            <img src="<?= base_url('assets/images/logo-bappeda.png') ?>" class="h-16 w-auto"
                onerror="this.src='https://upload.wikimedia.org/wikipedia/commons/9/92/Logo_Jateng.png'">
            <div>
                <h1 class="text-3xl font-bold tracking-tight">Jadwal Ruangan</h1>
                <p class="text-blue-200 text-lg">BAPPEDA Provinsi Jawa Tengah</p>
            </div>
        </div>
        <div class="text-right">
            <h2 id="clock" class="text-5xl font-mono font-bold text-yellow-400">00:00</h2>
            <p id="date" class="text-xl text-gray-300 mt-1">Senin, 1 Januari 2024</p>
        </div>
    </header>

    <!-- Content -->
    <main class="p-8 h-[calc(100vh-140px)]">
        <?php if (empty($todayBookings)): ?>
            <div class="h-full flex flex-col items-center justify-center text-gray-400/50">
                <i class="fas fa-calendar-check text-9xl mb-8 opacity-20"></i>
                <h2 class="text-4xl font-bold">Tidak Ada Jadwal Rapat Hari Ini</h2>
                <p class="text-2xl mt-4">Ruangan tersedia untuk digunakan.</p>
            </div>
        <?php else: ?>
            <div class="grid gap-6 h-full content-start overflow-y-auto pb-20 no-scrollbar">
                <?php foreach ($todayBookings as $booking):
                    // Calculate status (Now, Upcoming, Done)
                    $now = time();
                    $start = strtotime($booking['tanggal'] . ' ' . $booking['waktu_mulai']);
                    $end = strtotime($booking['tanggal'] . ' ' . $booking['waktu_selesai']);

                    $statusClass = 'border-l-8 border-blue-500 bg-white/5';
                    $statusText = 'Akan Datang';
                    $textClass = 'text-blue-400';

                    if ($now >= $start && $now <= $end) {
                        $statusClass = 'border-l-8 border-green-500 bg-green-500/10 shadow-[0_0_30px_rgba(34,197,94,0.3)] transform scale-[1.01]';
                        $statusText = 'SEDANG BERLANGSUNG';
                        $textClass = 'text-green-400 animate-pulse';
                    } elseif ($now > $end) {
                        $statusClass = 'border-l-8 border-gray-600 bg-gray-800/50 opacity-60';
                        $statusText = 'Selesai';
                        $textClass = 'text-gray-500';
                    }
                    ?>
                    <div
                        class="<?= $statusClass ?> rounded-r-2xl p-6 transition-all duration-500 flex items-center justify-between">
                        <div class="flex items-center gap-8">
                            <!-- Waktu -->
                            <div class="w-48 text-center border-r border-white/10 pr-6">
                                <h3 class="text-4xl font-bold mb-1">
                                    <?= date('H:i', strtotime($booking['waktu_mulai'])) ?>
                                </h3>
                                <p class="text-gray-400 text-xl">s/d
                                    <?= date('H:i', strtotime($booking['waktu_selesai'])) ?>
                                </p>
                            </div>

                            <!-- Info -->
                            <div>
                                <span class="text-sm font-bold tracking-wider uppercase mb-1 block <?= $textClass ?>">
                                    <?= $statusText ?>
                                </span>
                                <h2 class="text-3xl font-bold mb-2 truncate max-w-4xl">
                                    <?= htmlspecialchars($booking['kegiatan']) ?>
                                </h2>
                                <div class="flex items-center gap-6 text-xl text-gray-300">
                                    <div class="flex items-center gap-2">
                                        <i class="fas fa-door-open text-yellow-500"></i>
                                        <span>
                                            <?= htmlspecialchars($booking['room_name']) ?>
                                        </span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <i class="fas fa-building text-blue-400"></i>
                                        <span>
                                            <?= htmlspecialchars($booking['instansi']) ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>

    <!-- Marquee Footer -->
    <footer class="fixed bottom-0 w-full bg-blue-900 text-white py-3">
        <div class="relative overflow-hidden whitespace-nowrap">
            <div class="animate-marquee inline-block px-4">
                <span class="mx-4"><i class="fas fa-info-circle text-yellow-400 mr-2"></i> Selamat Datang di BAPPEDA
                    Provinsi Jawa Tengah</span>
                <span class="mx-4">•</span>
                <span class="mx-4">Harap menjaga kebersihan dan ketertiban ruangan</span>
                <span class="mx-4">•</span>
                <span class="mx-4">Informasi booking ruangan dapat diakses melalui website
                    sim-ruangan.bappeda.jatengprov.go.id</span>
            </div>
        </div>
    </footer>

    <script>
        // Clock Script
        function updateClock() {
            const now = new Date();
            const timeString = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
            const dateString = now.toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });

            document.getElementById('clock').innerText = timeString;
            document.getElementById('date').innerText = dateString;
        }
        setInterval(updateClock, 1000);
        updateClock();

        // Auto Refresh page every 5 minutes to fetch new data
        setTimeout(() => window.location.reload(), 300000);

        // Marquee Animation Config
        const style = document.createElement('style');
        style.textContent = `
            @keyframes marquee {
                0% { transform: translateX(100%); }
                100% { transform: translateX(-100%); }
            }
            .animate-marquee {
                animation: marquee 20s linear infinite;
            }
        `;
        document.head.appendChild(style);
    </script>
</body>

</html>