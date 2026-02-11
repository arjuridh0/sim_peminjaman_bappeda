# 🛡️ Troubleshooting ModSecurity / 403 Forbidden Error

## Masalah: Request Ditolak / 403 Forbidden saat Submit Booking

Jika Anda mengalami error **"403 Forbidden"** atau **"Request blocked by security rules"** saat submit form booking, ini disebabkan oleh **ModSecurity** di shared hosting yang salah mendeteksi request Anda sebagai serangan.

---

## ✅ Solusi 1: Update `.htaccess` (Sudah Diterapkan)

File `.htaccess` di root project sudah diupdate dengan rules berikut:

```apache
# Disable ModSecurity for booking.php
<IfModule mod_security.c>
    <Files "booking.php">
        SecRuleEngine Off
    </Files>
</IfModule>

# Disable ModSecurity for API endpoints
<IfModule mod_security.c>
    <FilesMatch "^(admin_update_booking_status|get_bookings)\.php$">
        SecRuleEngine Off
    </FilesMatch>
</IfModule>
```

**Upload file `.htaccess` ini ke hosting Anda.**

---

## ✅ Solusi 2: Disable ModSecurity via cPanel (Jika Solusi 1 Gagal)

Jika `.htaccess` tidak bekerja, disable ModSecurity langsung dari cPanel:

### **Untuk InfinityFree / VistaPanel:**

1. Login ke VistaPanel
2. Cari menu **"ModSecurity"** atau **"Security"**
3. **Disable ModSecurity** untuk domain Anda
4. Save dan test lagi

### **Untuk cPanel Standard:**

1. Login ke cPanel
2. Cari **"ModSecurity"** di menu Security
3. Klik **"Disable"** atau pilih domain dan disable
4. Save changes

---

## ✅ Solusi 3: Whitelist Specific Rules (Advanced)

Jika hosting tidak mengizinkan disable ModSecurity sepenuhnya, Anda bisa whitelist rule tertentu.

### **Cara Cek Rule ID yang Memblokir:**

1. Buka **Error Log** di cPanel (Menu: Metrics > Errors)
2. Cari error terbaru saat submit booking
3. Lihat **Rule ID** yang memblokir (contoh: `950001`, `981173`, dll)

### **Tambahkan ke `.htaccess`:**

```apache
<IfModule mod_security.c>
    # Whitelist specific ModSecurity rules
    SecRuleRemoveById 950001
    SecRuleRemoveById 981173
    SecRuleRemoveById 981176
</IfModule>
```

Ganti `950001` dengan Rule ID yang Anda temukan di error log.

---

## ✅ Solusi 4: Hubungi Support Hosting

Jika semua solusi di atas gagal, hubungi support hosting Anda dan minta:

**Template Email:**

```
Subject: Request to Disable ModSecurity for Booking Form

Hi Support Team,

I'm experiencing 403 Forbidden errors when submitting a booking form on my website.
The error is caused by ModSecurity false positive detection.

Could you please:
1. Disable ModSecurity for file: booking.php
2. Or whitelist my domain from ModSecurity rules

Domain: [your-domain.com]
File affected: booking.php

Thank you!
```

---

## 🧪 Testing

Setelah menerapkan salah satu solusi di atas:

1. **Clear browser cache** (Ctrl + Shift + Delete)
2. **Test submit booking** dari halaman depan
3. Jika masih error, cek **Error Log** di cPanel untuk detail

---

## 📝 Catatan Penting

- **ModSecurity** adalah firewall yang bagus untuk keamanan, tapi sering **false positive** pada form POST yang kompleks
- **Disable hanya untuk file tertentu** (booking.php) lebih aman daripada disable semuanya
- Jika hosting tidak support `.htaccess` rules, gunakan **Solusi 2** (disable via panel)

---

## 🆘 Masih Gagal?

Jika semua solusi gagal, kemungkinan:

1. **Hosting terlalu ketat** - Pertimbangkan pindah hosting yang lebih fleksibel
2. **IP Anda di-block** - Coba dari jaringan/IP berbeda
3. **Firewall lain aktif** - Cek dengan support hosting

**Hosting yang Recommended:**

- Niagahoster (Indonesia)
- Hostinger
- DigitalOcean (VPS)
- Cloudways

Hosting-hosting ini lebih fleksibel dengan ModSecurity settings.
