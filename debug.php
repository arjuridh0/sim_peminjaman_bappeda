<?php
// debug.php
// Upload file ini ke folder hosting Anda (sebelah index.php)
// Akses via browser: domain.com/folder-anda/debug.php

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🔍 Debugging Server & Paths</h1>";
echo "<p>Gunakan informasi ini untuk memastikan konfigurasi hosting benar.</p>";

echo "<h2>1. PHP Environment</h2>";
echo "PHP Version: " . phpversion() . "<br>";
echo "Server Software: " . $_SERVER['SERVER_SOFTWARE'] . "<br>";

echo "<h2>2. Path Variables</h2>";
echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
echo "<tr><th>Variable</th><th>Value</th></tr>";
echo "<tr><td>SCRIPT_NAME</td><td>" . $_SERVER['SCRIPT_NAME'] . "</td></tr>";
echo "<tr><td>SCRIPT_FILENAME</td><td>" . $_SERVER['SCRIPT_FILENAME'] . "</td></tr>";
echo "<tr><td>DOCUMENT_ROOT</td><td>" . $_SERVER['DOCUMENT_ROOT'] . "</td></tr>";
echo "<tr><td>HTTP_HOST</td><td>" . $_SERVER['HTTP_HOST'] . "</td></tr>";
echo "</table>";

echo "<h2>3. Test Database Connection</h2>";
// Coba include database
$dbFile = __DIR__ . '/config/database.php';
if (file_exists($dbFile)) {
    echo "✅ File config/database.php ditemukan.<br>";
    try {
        require_once $dbFile;
        if (isset($pdo)) {
            echo "✅ Koneksi Database Berhasil!";
        } else {
            echo "❌ Variable \$pdo tidak ditemukan di database.php";
        }
    } catch (Exception $e) {
        echo "❌ Koneksi Gagal: " . $e->getMessage();
    }
} else {
    echo "❌ File config/database.php TIDAK ditemukan di: " . $dbFile;
}

echo "<h2>4. Base URL Calculation</h2>";

// COPY of variables
$folderName = 'sim_peminjaman_bappeda';

// SIMULASI LOGIC base_url()
$scriptPath = str_replace('\\', '/', $_SERVER['SCRIPT_NAME']);
$projectFolder = defined('PROJECT_FOLDER') ? PROJECT_FOLDER : $folderName;

echo "Target Project Folder Name: <strong>$projectFolder</strong><br>";

if (preg_match('#^(.*?/' . preg_quote($projectFolder, '#') . ')/#', $scriptPath, $matches)) {
    $projectPath = $matches[1];
    echo "Using Match Logic: " . $projectPath . "<br>";
} else {
    $projectPath = dirname($scriptPath);
    while (preg_match('#/(admin|includes|api|cron)$#', $projectPath)) {
        $projectPath = dirname($projectPath);
    }
    echo "Using Fallback (Dynamic) Logic: " . $projectPath . "<br>";
}

$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$host = $_SERVER['HTTP_HOST'];
$finalBaseUrl = "$protocol://$host$projectPath";

echo "<h3>Final Calculated Base URL:</h3>";
echo "<div style='background:#eee; padding:10px; font-size:1.2em; font-weight:bold;'>" . $finalBaseUrl . "</div>";

echo "<hr>";
echo "<p><em>Jika link aset (css/js/gambar) mengarah ke URL di atas, seharusnya sudah benar. Jika URL di atas salah (misal terlalu panjang atau terlalu pendek), berarti folder project tidak terdeteksi dengan benar.</em></p>";
?>