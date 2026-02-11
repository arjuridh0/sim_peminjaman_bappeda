# 🛠️ Panduan Setup Sistem Peminjaman Ruangan BAPPEDA

Panduan ini menjelaskan cara instalasi dan konfigurasi sistem ini dari awal (Localhost). Pastikan Anda mengikuti setiap langkah secara berurutan.

## 📋 Prasyarat Sistem

Sebelum memulai, pastikan komputer Anda sudah terinstall:

1.  **Web Server**: XAMPP / Laragon (Recommended)
2.  **PHP Versi**: 7.4 atau lebih tinggi
3.  **Database**: MySQL / MariaDB

---

## 🚀 Langkah 1: Persiapan Folder

### Opsi A: Instalasi dari GitHub (Recommended)

1.  Buka terminal / git bash di folder `htdocs` atau `www`.
2.  Clone repository:
    ```bash
    git clone https://github.com/username/sim_peminjaman_bappeda.git
    cd sim_peminjaman_bappeda
    ```
3.  Install Dependencies (via Composer):
    ```bash
    composer install
    ```
    _(Jika tidak punya composer, download folder `vendor` secara manual atau pastikan sudah ada di dalam project)_

### Opsi B: Manual Extrct

1.  Ekstrak source code project ke folder `htdocs` (XAMPP) atau `www` (Laragon).
2.  Pastikan nama folder project adalah `sim_peminjaman_bappeda`.
    - URL akses nanti: `http://localhost/sim_peminjaman_bappeda/`

---

## 💾 Langkah 2: Setup Database

Sistem ini membutuhkan database MySQL.

1.  Buka **phpMyAdmin** (`http://localhost/phpmyadmin`).
2.  Buat database baru dengan nama: `bappeda_ruangan`.
3.  Pilih tab **Import**.
4.  Upload file SQL yang ada di folder project:
    - **Lokasi**: `config/bappeda_ruangan.sql`
5.  Klik **Go** / **Kirim** untuk import.

> **Catatan**: File `bappeda_ruangan.sql` sudah mencakup seluruh struktur tabel dan data awal yang dibutuhkan.

---

## ⚙️ Langkah 3: Konfigurasi Sistem

### 3.1 Konfigurasi Database

1.  **Duplicate Template**: Copy file `config/database.example.php` menjadi `config/database.php`.
2.  Buka `config/database.php` dan sesuaikan:

```php
// config/database.php

$host = 'localhost';
$dbname = 'bappeda_ruangan'; // Sesuaikan jika nama database beda
$username = 'root';          // Default XAMPP/Laragon: root
$password = '';              // Default XAMPP/Laragon: kosong
```

### 3.2 Konfigurasi WhatsApp (Untuk Notifikasi)

1.  **Duplicate Template**: Copy file `config/whatsapp.example.php` menjadi `config/whatsapp.php`.
2.  Buka `config/whatsapp.php` dan sesuaikan:

```php
// config/whatsapp.php

// 1. Masukkan API Token dari Fonnte
define('WA_API_TOKEN', 'token_fonnte_anda');

// 2. Masukkan Nomor WhatsApp Admin (Penerima notifikasi booking baru)
define('WA_ADMIN_PHONE', '628123456789'); // Format: 628xxx
```

> **Cara Mendapatkan API Token Fonnte**:
>
> 1. Daftar di https://fonnte.com
> 2. Login dan masuk ke Dashboard.
> 3. Copy API Token yang tersedia.
> 4. Paste ke `WA_API_TOKEN`.

---

## 📂 Langkah 4: Pengaturan File Permission (Opsional)

Jika menggunakan Windows (Localhost), langkah ini biasanya **tidak perlu**. Namun jika ada error gagal upload, pastikan folder berikut bisa ditulis:

- `assets/images`
- `assets/files`

---

## 🧪 Langkah 5: Testing Sistem

1.  **Akses Website**: Buka `http://localhost/sim_peminjaman_bappeda/`
2.  **Coba Login Admin**:
    - URL: `http://localhost/sim_peminjaman_bappeda/admin`
    - Username: `bappedajateng`
    - Password: `bappeda2026`
3.  **Coba Booking**:
    - Lakukan simulasi peminjaman ruangan dari halaman depan.
    - Pastikan notifikasi WhatsApp terkirim.

---

## 🆘 Troubleshooting Umum

**Q: Database error / koneksi gagal?**

- A: Cek kembali `config/database.php`. Pastikan username/password MySQL benar.

**Q: WhatsApp tidak terkirim?**

- A: Pastikan API Token Fonnte valid dan masih aktif. Cek juga format nomor WhatsApp (harus 628xxx).

**Q: Tidak bisa upload gambar/file?**

- A: Pastikan folder `assets/` ada dan memiliki permission write.

---

**Selesai! Sistem siap digunakan.** 🚀
