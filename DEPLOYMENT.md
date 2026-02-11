# 🚀 Panduan Deployment (Production) - SIM BAPPEDA

Panduan ini ditujukan untuk deploy aplikasi ke server production (VPS/Hosting) menggunakan OS Linux (Ubuntu/Debian) dan Web Server Apache.

---

## 📋 Checklist Persiapan

- [ ] Domain / Subdomain sudah diarahkan ke IP Server
- [ ] Akses SSH ke Server (Root / Sudo access)
- [ ] SSL Certificate (HTTPS)

---

## 🛠️ Langkah 1: Persiapan Server

Pastikan server sudah terinstall LAMP Stack (Linux, Apache, MySQL, PHP).

```bash
# 1. Update Server
sudo apt update && sudo apt upgrade -y

# 2. Install Apache, MySQL, PHP & Extensions
sudo apt install apache2 mysql-server php php-mysql php-curl php-json php-mbstring php-xml php-zip libapache2-mod-php -y

# 3. Aktifkan Module Rewrite Apache
sudo a2enmod rewrite
sudo systemctl restart apache2
```

---

## 📂 Langkah 2: Upload Source Code

Upload file project ke folder `/var/www/`.

```bash
# Contoh menggunakan git (jika ada repo)
cd /var/www/
git clone https://github.com/username/sim_peminjaman_bappeda.git

# Atau upload manual via SCP/SFTP lalu ekstrak
unzip sim_peminjaman_bappeda.zip -d /var/www/
```

### Atur Permission Folder

Permission harus **0755** untuk folder dan **0644** untuk file. Folder upload butuh akses write owner (www-data).

```bash
# Ubah owner ke www-data (user apache)
sudo chown -R www-data:www-data /var/www/sim_peminjaman_bappeda

# Set permission dasar
sudo find /var/www/sim_peminjaman_bappeda -type d -exec chmod 755 {} \;
sudo find /var/www/sim_peminjaman_bappeda -type f -exec chmod 644 {} \;

# Pastikan folder upload bisa ditulis
sudo chmod 755 /var/www/sim_peminjaman_bappeda/assets/images
sudo chmod 755 /var/www/sim_peminjaman_bappeda/assets/files
```

---

## 💾 Langkah 3: Setup Database

1.  Login ke MySQL:

    ```bash
    sudo mysql -u root -p
    ```

2.  Buat Database & User:

    ```sql
    CREATE DATABASE bappeda_ruangan;
    CREATE USER 'bappeda_user'@'localhost' IDENTIFIED BY 'password_kuat_anda';
    GRANT ALL PRIVILEGES ON bappeda_ruangan.* TO 'bappeda_user'@'localhost';
    FLUSH PRIVILEGES;
    EXIT;
    ```

3.  Import Database:
    ```bash
    mysql -u bappeda_user -p bappeda_ruangan < /var/www/sim_peminjaman_bappeda/config/bappeda_ruangan.sql
    ```

---

## ⚙️ Langkah 4: Konfigurasi Aplikasi

Edit file `config/database.php` dan `config/whatsapp.php` di server.

```bash
nano /var/www/sim_peminjaman_bappeda/config/database.php
```

Sesuaikan dengan user database production yang baru dibuat.

```bash
nano /var/www/sim_peminjaman_bappeda/config/whatsapp.php
```

Sesuaikan API Token Fonnte dan nomor admin WhatsApp.

---

## 🌐 Langkah 5: Konfigurasi Apache Virtual Host

Buat file konfigurasi vhost baru:

```bash
sudo nano /etc/apache2/sites-available/bappeda.conf
```

Isi dengan konfigurasi berikut:

```apache
<VirtualHost *:80>
    ServerName booking.bappeda.jatengprov.go.id
    DocumentRoot /var/www/sim_peminjaman_bappeda

    <Directory /var/www/sim_peminjaman_bappeda>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/bappeda-error.log
    CustomLog ${APACHE_LOG_DIR}/bappeda-access.log combined
</VirtualHost>
```

Aktifkan site dan reload Apache:

```bash
sudo a2ensite bappeda.conf
sudo systemctl reload apache2
```

---

## 🔒 Langkah 6: Setup HTTPS (SSL)

Gunakan Certbot (Let's Encrypt) untuk SSL gratis.

```bash
sudo apt install certbot python3-certbot-apache -y
sudo certbot --apache -d booking.bappeda.jatengprov.go.id
```

Ikuti instruksi di layar. Pilih opsi **Redirect HTTP to HTTPS**.

---

## ✅ Langkah 7: Final Check

1.  Buka domain di browser.
2.  Coba login admin.
3.  Test upload gambar (pastikan permission benar).
4.  Test kirim notifikasi WhatsApp booking.

---

## 🛡️ Tips Keamanan Tambahan

1.  **Matikan Error Display**:
    Pastikan di `php.ini` production:

    ```ini
    display_errors = Off
    log_errors = On
    ```

2.  **Backup Rutin**:
    Buat cronjob untuk backup database setiap hari.

    ```bash
    0 2 * * * mysqldump -u bappeda_user -p'password' bappeda_ruangan > /backup/db_$(date +\%F).sql
    ```

3.  **Firewall (UFW)**:
    Pastikan hanya port penting yang terbuka.
    ```bash
    sudo ufw allow ssh
    sudo ufw allow http
    sudo ufw allow https
    sudo ufw enable
    ```

**Deployment Selesai!** 🚀
