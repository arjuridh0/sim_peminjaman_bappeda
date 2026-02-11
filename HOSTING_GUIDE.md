# Panduan Hosting Project SIM Peminjaman Ruangan BAPPEDA

(InfinityFree / cPanel / Shared Hosting)

Panduan ini dibuat khusus untuk project Anda saat ini. Karena kita menggunakan **GitHub** sebagai sumber kode utama, ada beberapa hal penting yang perlu diperhatikan karena beberapa file (seperti folder `vendor` dan file konfigurasi) **tidak ada di GitHub** demi keamanan dan efisiensi.

Ikuti langkah-langkah ini secara berurutan.

---

## 🚀 Tahap 1: Persiapan File di Komputer Lokal

Sebelum upload ke hosting, kita perlu menyiapkan file-file yang "hilang" dari GitHub.

1.  **Pastikan Folder `vendor` Lengkap**
    - Karena folder `vendor/` di-_ignore_ oleh git, Anda harus memilikinya di komputer lokal.
    - Pastikan di folder project Anda saat ini (`c:\laragon\www\sim_peminjaman_bappeda`) sudah ada folder `vendor` yang berisi library `tcpdf`.
    - _Jika belum ada_, buka terminal di folder project dan jalankan: `composer install`.

2.  **Siapkan File Database**
    - File database sudah ada di dalam folder project: `config/bappeda_ruangan.sql`.
    - Kita akan pakai file ini untuk di-import ke hosting nanti.

3.  **Buat File ZIP Project**
    - Cara paling mudah untuk hosting adalah meng-upload satu file ZIP lalu di-ekstrak di sana.
    - Block semua file dan folder di dalam project Anda, **KECUALI**:
      - Folder `.git` (tidak perlu)
      - Folder `node_modules` (jika ada, tidak perlu untuk production)
      - File `.env` (jika ada)
    - **PENTING:** Pastikan folder `vendor`, `assets`, `includes`, `config`, dan file `index.php` ikut ter-zip.
    - Beri nama file: `sim_peminjaman_bappeda.zip`.

---

## 🌐 Tahap 2: Setup Database di Hosting (InfinityFree / cPanel)

Langkah ini hampir sama di semua hosting.

1.  **Buat Database Baru**
    - Masuk ke Control Panel hosting Anda (cPanel atau VistaPanel di InfinityFree).
    - Cari menu **"MySQL Databases"** (atau "MySQL Management").
    - Buat database baru.
      - _Contoh InfinityFree:_ Biasanya ada prefix, misal `epiz_12345_bappeda`.
    - **CATAT DATA INI (PENTING):**
      - **DB Host:** (Contoh: `sql123.infinityfree.com` atau `localhost`)
      - **DB Name:** (Contoh: `epiz_12345_bappeda`)
      - **DB User:** (Contoh: `epiz_12345`)
      - **DB Password:** (Password akun hosting/vpanel Anda)

2.  **Import Database (phpMyAdmin)**
    - Kembali ke menu utama Control Panel, cari **"phpMyAdmin"**.
    - Klik tombol "Connect" atau "Masuk" ke database yang baru Anda buat.
    - Pilih tab **"Import"** di bagian atas.
    - Klik **"Choose File"** dan pilih file `config/bappeda_ruangan.sql` dari komputer Anda.
    - Klik **"Go"** atau **"Kirim"**.
    - _Sukses!_ Database Anda sudah terisi tabel-tabel yang diperlukan.

---

## 📂 Tahap 3: Upload File Project

1.  **Buka File Manager**
    - Di Control Panel, cari menu **"Online File Manager"** (InfinityFree) atau **"File Manager"** (cPanel).
    - Masuk ke folder `htdocs` (InfinityFree) atau `public_html` (cPanel).
    - Hapus file default (seperti `index2.html` atau `default.php`) jika ada, agar folder kosong.

2.  **Upload & Ekstrak**
    - Klik tombol **"Upload"** -> **"Upload Zip"** (atau drag-and-drop file `sim_peminjaman_bappeda.zip` Anda).
    - Setelah selesai upload, klik kanan file zip tersebut dan pilih **"Extract"**.
    - Pastikan semua file (folder `admin`, `api`, `config`, `includes`, `vendor`, dll) sekarang berada langsung di dalam folder `htdocs` (atau `public_html`).
    - _Tips:_ Jangan sampai file berada di dalam sub-folder (misal: `htdocs/sim_peminjaman_bappeda/index.php`). Jika terjadi, pindahkan semua isinya keluar ke `htdocs` utama (Move).

---

## 📂 Skenario Khusus: Hosting di Subdirectory

(Misal: `bappeda.jateng.go.id/sim_ruangan/`)

Jika Anda ingin "menumpang" di domain utama tanpa subdomain, ikuti cara ini:

1.  **Ganti Nama Folder (Opsional)**
    - Saran saya, rename folder project lokal Anda dari `sim_peminjaman_bappeda` menjadi nama yang Anda inginkan di URL, misal `sim_ruangan`.
    - Lakukan rename folder root project sebelum di-zip.

2.  **Upload ke Folder Public HTML**
    - Masuk ke File Manager hosting.
    - Buka `public_html`.
    - Buat folder baru dengan nama yang diinginkan, misal `sim_ruangan`.
    - Upload dan Extract file project **DI DALAM** folder `sim_ruangan` tersebut.
    - **PENTING**: Struktur akhirnya harus seperti ini:
      - `public_html/sim_ruangan/index.php`
      - `public_html/sim_ruangan/config/...`
      - `public_html/sim_ruangan/admin/...`
      - (BUKAN `public_html/sim_ruangan/sim_peminjaman_bappeda/index.php`)

3.  **Cek `.htaccess`**
    - Jika hosting Anda ketat, file `.htaccess` bawaan mungkin perlu disesuaikan. Jika website error 500 (Internal Server Error), coba rename `.htaccess` menjadi `.htaccess_bak` untuk mematikannya sementara.

---

## 🛠️ Troubleshooting (Jika Masih Gagal)

### Website Blank / Error 500 / 404

Jika setelah upload website "tidak muncul" (blank putih) atau error:

1.  **Gunakan File Debugging**
    - Saya sudah menyertakan file `debug.php` di project.
    - Upload `debug.php` ke folder hosting Anda (sebelah `index.php`).
    - Buka di browser: `domainanda.com/folder/debug.php`.
    - Lihat apakah **Database Connection** berhasil dan **Base URL** sudah sesuai.

2.  **Cek Error Log**
    - Kadang error PHP tidak muncul di layar. Coba buat file `php.ini` atau `.user.ini` di folder project dengan isi:
      ```ini
      display_errors = On
      ```
    - Atau tambahkan baris ini di paling atas `index.php`:
      ```php
      ini_set('display_errors', 1);
      ini_set('display_startup_errors', 1);
      error_reporting(E_ALL);
      ```

3.  **Pastikan Folder `vendor` Ada**
    - Kasus paling sering: lupa upload folder `vendor` atau uploadnya tidak sempurna. Aplikasi akan langsung stop bekerja (fatal error) jika ini hilang.

---

## ⚙️ Tahap 4: Konfigurasi Koneksi Database

Karena di `.gitignore` kita memblokir file config asli, kemungkinan file `config/database.php` tidak ikut ter-upload atau perlu diedit manual.

1.  Di File Manager hosting, masuk ke folder `config`.
2.  Cari file `database.php`. Jika tidak ada, buat file baru bernama `database.php`.
3.  Edit file tersebut dan masukkan kode berikut (sesuaikan dengan data Tahap 2):

```php
<?php
// config/database.php

// GANTI SESUAI DATA DARI PANEL HOSTING ANDA
$host = 'sqlXXX.infinityfree.com'; // LIHAT DI MYSQL DATABASES (PENTING: Jangan 'localhost' jika di InfinityFree)
$dbname = 'epiz_3434_bappeda';     // LIHAT DI MYSQL DATABASES
$username = 'epiz_3434';           // LIHAT DI MYSQL DATABASES
$password = 'password_akun_anda';  // PASSWORD AKUN VISTA PANEL / CPANEL

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Tampilkan pesan error sederhana untuk keamanan
    die("Koneksi Database Gagal. Cek konfigurasi.");
}
?>
```

4.  **Save/Simpan**.

---

## 📱 Tahap 5: Konfigurasi WhatsApp (Opsional)

Jika fitur notifikasi WhatsApp penting, lakukan hal yang sama untuk `config/whatsapp.php`.

1.  Di folder `config`, buat/edit `whatsapp.php`.
2.  Pastikan isinya sesuai dengan konfigurasi API Fonnte Anda.

```php
<?php
// config/whatsapp.php
define('WA_API_TOKEN', 'token-fonnte-anda');
define('WA_ADMIN_PHONE', '628123456789'); // Format: 628xxx
?>
```

---

## ✅ Tahap 6: Cek Website

1.  Buka browser dan akses domain Anda (misal: `bappeda-test.infinityfreeapp.com`).
2.  Coba login sebagai admin.
    - User: `admin`
    - Pass: `admin123` (atau sesuai data di database lokal Anda).
3.  Jika muncul error "Connection failed", cek kembali **Tahap 4** (Hostname dan Password paling sering salah).
4.  Jika halaman putih (blank), coba aktifkan `display_errors` sementara melalui menu "Alter PHP Config" di hosting (jika ada) atau tambahkan `ini_set('display_errors', 1);` di baris paling atas `index.php` untuk debugging.

---

## 💡 Masalah Umum (Troubleshooting)

- **Masalah:** Gambar ruangan tidak muncul.
  - **Solusi:** Cek folder `assets/images/rooms`. Karena di `.gitignore` folder ini di-ignore, Anda mungkin perlu upload manual gambar-gambarnya dari komputer lokal ke folder yang sama di hosting via File Manager.
  - Pastikan permission folder `assets` adalah `755`.

- **Masalah:** Error `Class 'TCPDF' not found`.
  - **Solusi:** Ini berarti folder `vendor` belum ter-upload dengan benar. Ulangi upload folder `vendor` dari komputer lokal.

Selamat mencoba! Jika ada kendala di langkah tertentu, kabari saya pesan error-nya.
