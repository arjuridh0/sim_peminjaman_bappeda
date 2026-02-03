# 🏛️ Sistem Informasi Peminjaman Ruangan BAPPEDA

**SIM Peminjaman Ruangan** adalah aplikasi berbasis web untuk mengelola jadwal, peminjaman, dan persetujuan penggunaan ruangan rapat di BAPPEDA Provinsi Jawa Tengah.

Sistem ini dirancang untuk mempermudah pegawai dalam melihat ketersediaan ruangan secara real-time, mengajukan peminjaman, dan mempermudah admin dalam mengelola jadwal.

![Bappeda Banner](assets/images/logo-bappeda.png)

---

## ✨ Fitur Utama

### 👥 Untuk Pengguna (Pegawai)

- **Cek Jadwal Real-time**: Kalender interaktif untuk melihat slot kosong.
- **Smart Search**: Cari ruangan berdasarkan kapasitas dan waktu.
- **Booking Online**: Pengajuan peminjaman cepat lewat form / kalender.
- **Notifikasi Email**: Otomatis terima kode booking & QR Code via email.
- **Pantau Status**: Cek status pengajuan (Menunggu/Disetujui/Ditolak).

### 🛡️ Untuk Admin

- **Dashboard Analitik**: Statistik penggunaan ruangan & tren bulanan.
- **Approval System**: Setujui atau tolak pengajuan dengan alasan + notifikasi email.
- **Manajemen Ruangan**: Tambah/Edit/Hapus data ruangan & fasilitas.
- **Lobby Display**: Mode tampilan TV untuk dipasang di layar lobi gedung.

---

## 🛠️ Teknologi yang Digunakan

- **Backend**: PHP Native (Structure MVC Clean)
- **Database**: MySQL 5.7+
- **Frontend**: Tailwind CSS, Vanilla JS
- **Libraries**:
  - **FullCalendar**: Tampilan jadwal interaktif.
  - **PHPMailer**: Pengiriman notifikasi email.
  - **TCPDF**: Export laporan/bukti booking.
  - **SweetAlert2**: Notifikasi popup modern.

---

## 🚀 Memulai (Getting Started)

Untuk panduan instalasi dan menjalankan project ini di komputer Anda (Localhost), silakan baca panduan lengkap di:

👉 **[SETUP.md](SETUP.md)**

Panduan tersebut mencakup:

1.  Persiapan Database (Import SQL).
2.  Konfigurasi Koneksi (`config/database.php`).
3.  Konfigurasi Email Gmail (`config/email.php`).

---

## 🌐 Deployment (Production)

Jika Anda ingin mengonlinekan sistem ini ke server / VPS, silakan ikuti panduan deployment di:

👉 **[DEPLOYMENT.md](DEPLOYMENT.md)**

---

## 📂 Struktur Folder

```
sim_peminjaman_bappeda/
├── admin/                  # Halaman & logika Admin Panel
├── api/                    # Endpoint JSON untuk AJAX (Calendar, Search, dll)
├── assets/                 # File statis (Images, Uploads PDF)
├── config/                 # Konfigurasi Database & Email
├── includes/               # Fungsi inti (Auth, Helper, Database Wrapper)
├── vendor/                 # Library pihak ketiga (Composer)
├── booking.php             # Form booking user
├── calendar.php            # Halaman kalender publik
├── index.php               # Halaman utama (Beranda)
└── ...
```

---

## 🔐 Akun Default

### Admin Panel

- **URL**: `/admin`
- **Username**: `bappedajateng`
- **Password**: `bappeda2026`

---

**Versi**: 2.0  
**Status**: Ready for Production ✅
