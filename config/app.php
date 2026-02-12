<?php
// config/app.php
// Application Configuration

// Project folder name (sesuaikan dengan nama folder di hosting)
// Localhost: 'sim_peminjaman_bappeda'
// Production: 'siprada3'
define('PROJECT_FOLDER', 'sim_peminjaman_bappeda');

// Base URL (IMPORTANT: Set this correctly for production!)
// Localhost: 'http://localhost/sim_peminjaman_bappeda'
// Production: 'https://bappeda.jatengprov.go.id/siprada3'
define('BASE_URL', 'http://localhost/sim_peminjaman_bappeda/');

// Application Name
define('APP_NAME', 'SI-PRADA - Sistem Informasi Peminjaman Ruangan Daerah');

// Application Version
define('APP_VERSION', '1.0.0');

// Timezone
date_default_timezone_set('Asia/Jakarta');
