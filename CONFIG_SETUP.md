# 🔧 Setup Konfigurasi - SI-PRADA

## 📋 File Konfigurasi yang Diperlukan

Sistem ini memerlukan beberapa file konfigurasi yang **TIDAK** di-commit ke Git karena berisi informasi sensitif. File-file tersebut sudah disediakan dalam bentuk template (`.example.php`).

---

## 🚀 Cara Setup (Pertama Kali)

### 1. Database Configuration

**File:** `config/database.php`

**Langkah:**

```bash
# 1. Copy file template
cp config/database.example.php config/database.php

# 2. Edit file database.php
# Ganti nilai-nilai berikut:
```

```php
$host = 'localhost';           // Host database
$dbname = 'bappeda_ruangan';   // Nama database
$username = 'root';            // Username database
$password = '';                // Password database
```

**Contoh untuk Hosting:**

```php
$host = 'localhost';
$dbname = 'bappedaj_ruangan';
$username = 'bappedaj_admin';
$password = 'P@ssw0rd_Kuat_123';
```

---

### 2. WhatsApp Configuration

**File:** `config/whatsapp.php`

**Langkah:**

```bash
# 1. Copy file template
cp config/whatsapp.example.php config/whatsapp.php

# 2. Daftar di Fonnte
# Buka: https://fonnte.com
# Login dan dapatkan API Token

# 3. Edit file whatsapp.php
# Ganti nilai-nilai berikut:
```

```php
define('WA_API_TOKEN', 'g7x66SbDpsFerCWaxe2o');  // Token dari Fonnte
define('WA_ADMIN_PHONE', '6281585058128');       // Nomor admin
define('WA_ENABLED', true);                       // Aktifkan notifikasi
```

**Catatan:**

- Token bisa didapatkan dari dashboard Fonnte
- Nomor admin gunakan format internasional (628xxx)
- Set `WA_ENABLED = false` saat development untuk disable notifikasi

---

### 3. Application Configuration

**File:** `config/app.php`

File ini **SUDAH ADA** dan perlu disesuaikan:

```php
// Sesuaikan dengan environment Anda
define('PROJECT_FOLDER', 'siprada3');  // Nama folder di hosting
define('BASE_URL', 'https://bappeda.jatengprov.go.id/siprada3');  // URL lengkap
```

**Localhost:**

```php
define('PROJECT_FOLDER', 'sim_peminjaman_bappeda');
define('BASE_URL', 'http://localhost/sim_peminjaman_bappeda');
```

**Production:**

```php
define('PROJECT_FOLDER', 'siprada3');
define('BASE_URL', 'https://bappeda.jatengprov.go.id/siprada3');
```

---

## 📁 Struktur Folder yang Dijaga

Beberapa folder di-ignore oleh Git tapi strukturnya dijaga dengan `.gitkeep`:

```
assets/
├── files/              # Upload file dokumen
│   └── .gitkeep
└── images/
    └── rooms/          # Upload foto ruangan
        └── .gitkeep
```

---

## ✅ Checklist Setup

- [ ] Copy `config/database.example.php` → `config/database.php`
- [ ] Edit `config/database.php` dengan kredensial database
- [ ] Copy `config/whatsapp.example.php` → `config/whatsapp.php`
- [ ] Edit `config/whatsapp.php` dengan token Fonnte
- [ ] Edit `config/app.php` sesuai environment (localhost/production)
- [ ] Pastikan folder `assets/files/` dan `assets/images/rooms/` ada
- [ ] Import database dari file SQL (jika ada)
- [ ] Test koneksi database
- [ ] Test notifikasi WhatsApp

---

## 🔒 Keamanan

**PENTING!** File-file berikut **TIDAK BOLEH** di-commit ke Git:

- ❌ `config/database.php` - Berisi kredensial database
- ❌ `config/whatsapp.php` - Berisi API token
- ❌ `assets/files/*` - File upload user
- ❌ `assets/images/rooms/*` - Foto ruangan

File-file tersebut sudah ada di `.gitignore`.

---

## 🐛 Troubleshooting

### Database Connection Failed

```
Error: Koneksi Database Gagal
```

**Solusi:**

1. Cek kredensial di `config/database.php`
2. Pastikan database sudah dibuat
3. Pastikan MySQL/MariaDB sudah running
4. Test koneksi dengan tool seperti phpMyAdmin

### WhatsApp Notification Failed

```
Error: WhatsApp notification failed
```

**Solusi:**

1. Cek token di `config/whatsapp.php`
2. Pastikan token masih valid di dashboard Fonnte
3. Cek nomor admin sudah benar
4. Set `WA_ENABLED = false` untuk disable sementara

### File Upload Error

```
Error: Failed to upload file
```

**Solusi:**

1. Pastikan folder `assets/files/` dan `assets/images/rooms/` ada
2. Cek permission folder (755 atau 777)
3. Cek size limit di `php.ini` (`upload_max_filesize`, `post_max_size`)

---

## 📞 Support

Jika ada masalah, hubungi:

- Email: admin@bappeda.jatengprov.go.id
- WhatsApp: 081585058128
