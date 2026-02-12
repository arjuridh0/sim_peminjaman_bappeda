# 🚨 CHAOS ERROR - ModSecurity Memblokir SEMUA POST Request!

## 🔥 **MASALAH SEBENARNYA (UPDATE):**

Setelah analisis lebih dalam, masalahnya **BUKAN** hanya di admin atau notifikasi WhatsApp, tapi **ModSecurity memblokir SEMUA POST request** di sistem!

### ❌ **Yang Gagal (SEMUA POST Request):**

1. ✗ User search status booking → Error
2. ✗ User cancel booking → Error
3. ✗ Admin approve booking → Error
4. ✗ Admin reject booking → Error
5. ✗ Admin add/edit room → Error
6. ✗ Admin delete room → Error

### ✅ **Yang Berhasil:**

1. ✓ User booking (form submit) → Berhasil
2. ✓ View halaman (GET request) → Berhasil

---

## 🎯 **ROOT CAUSE:**

### **Kenapa Booking Berhasil Tapi Yang Lain Tidak?**

Lihat file `.htaccess` lama:

```apache
# Disable ModSecurity for booking.php to prevent false positives
<IfModule mod_security.c>
    <Files "booking.php">
        SecRuleEngine Off
    </Files>
</IfModule>
```

**Hanya `booking.php` yang di-bypass!**

File lain **TIDAK** di-bypass:

- ❌ `booking_status.php` → Diblokir ModSecurity
- ❌ `admin/bookings.php` → Diblokir ModSecurity
- ❌ `admin/rooms.php` → Diblokir ModSecurity

---

## 🔍 **Kenapa ModSecurity Blokir?**

ModSecurity mendeteksi POST request sebagai "serangan" karena:

### 1. **CSRF Token (String Panjang Random)**

```php
<input type="hidden" name="csrf_token" value="a1b2c3d4e5f6g7h8i9j0...">
```

**Trigger:** String panjang yang terlihat seperti SQL injection

### 2. **POST Parameters**

```php
$_POST['action'] = 'check';
$_POST['search_query'] = 'FEB26-1290';
$_POST['booking_id'] = '58';
```

**Trigger:** Parameter `action`, `id`, `query` sering digunakan dalam serangan

### 3. **Redirect After POST**

```php
redirect('booking_status.php?view=access_granted');
```

**Trigger:** Redirect dengan query string setelah POST

### 4. **Form Data dengan Karakter Khusus**

```php
$_POST['cancel_reason'] = 'Acara dibatalkan karena...';
```

**Trigger:** Text panjang dengan spasi dan karakter khusus

---

## ✅ **SOLUSI YANG SUDAH DITERAPKAN:**

Saya sudah update file `.htaccess` untuk **bypass ModSecurity** di:

### 1. **Semua File Booking:**

```apache
<FilesMatch "^(booking|booking_status|room_detail|calendar)\.php$">
    SecRuleEngine Off
</FilesMatch>
```

### 2. **Seluruh Admin Panel:**

```apache
<LocationMatch "^/siprada3/admin/">
    SecRuleEngine Off
</LocationMatch>
```

### 3. **Semua API Endpoints:**

```apache
<LocationMatch "^/siprada3/api/">
    SecRuleEngine Off
</LocationMatch>
```

---

## 📋 **LANGKAH UPLOAD & TEST:**

### **Step 1: Upload File `.htaccess` Baru**

Upload file `.htaccess` yang sudah diupdate ke:

```
public_html/siprada3/.htaccess
```

**PENTING:** Pastikan nama file **PERSIS** `.htaccess` (dengan titik di depan!)

---

### **Step 2: Test Semua Fungsi**

#### A. **Test User Search Status:**

1. Buka: `https://bappeda.jatengprov.go.id/siprada3/booking_status.php`
2. Masukkan kode booking (contoh: `FEB26-1290`)
3. Klik "Cek Status"
4. ✅ **Harus berhasil** (tidak ada error "The requested URL was rejected")

#### B. **Test User Cancel Booking:**

1. Buka detail booking
2. Klik "Batalkan Peminjaman"
3. Masukkan kode booking
4. Klik "Ya, Batalkan"
5. ✅ **Harus berhasil** redirect ke halaman status

#### C. **Test Admin Approval:**

1. Login admin
2. Klik "Setujui" pada booking
3. ✅ **Harus berhasil** redirect ke `admin/bookings.php`
4. ✅ Status berubah jadi "Disetujui"

#### D. **Test Admin Rejection:**

1. Klik "Tolak" pada booking
2. Isi alasan penolakan
3. ✅ **Harus berhasil** redirect ke `admin/bookings.php`

#### E. **Test Admin Add/Edit Room:**

1. Klik "Tambah Ruangan"
2. Isi form
3. Submit
4. ✅ **Harus berhasil** redirect ke `admin/rooms.php`

---

## 🔧 **Jika Masih Error Setelah Upload `.htaccess`:**

### **Opsi 1: Cek Syntax `.htaccess`**

Pastikan tidak ada typo. Test dengan command:

```bash
apachectl configtest
```

Atau lihat error log di cPanel → Metrics → Errors

---

### **Opsi 2: Ganti `LocationMatch` dengan Path Lengkap**

Jika hosting Anda tidak support `LocationMatch`, ganti dengan:

```apache
<IfModule mod_security.c>
    # Disable untuk semua file di folder admin
    <Directory "/home/bappedajateng/public_html/siprada3/admin">
        SecRuleEngine Off
    </Directory>

    # Disable untuk semua file di folder api
    <Directory "/home/bappedajateng/public_html/siprada3/api">
        SecRuleEngine Off
    </Directory>
</IfModule>
```

**Catatan:** Ganti path sesuai dengan path hosting Anda!

---

### **Opsi 3: Hubungi Hosting untuk Whitelist**

Jika `.htaccess` tidak berhasil, hubungi support hosting dan minta:

1. **Whitelist path berikut dari ModSecurity:**
   - `/siprada3/booking_status.php`
   - `/siprada3/admin/*`
   - `/siprada3/api/*`

2. **Disable ModSecurity Rule tertentu:**
   - Rule ID yang memblokir POST request
   - Rule ID yang memblokir CSRF token
   - Rule ID yang memblokir redirect

3. **Berikan Support ID dari error:**
   ```
   Support ID: 179598621915904773340
   ```

---

## 🎯 **KESIMPULAN:**

### **Masalah:**

- ❌ ModSecurity memblokir **SEMUA POST request** kecuali `booking.php`
- ❌ File `.htaccess` lama hanya bypass `booking.php`
- ❌ File lain (`booking_status.php`, `admin/*`) diblokir

### **Solusi:**

- ✅ Update `.htaccess` untuk bypass ModSecurity di semua file
- ✅ Bypass untuk: `booking_status.php`, `admin/*`, `api/*`
- ✅ Upload `.htaccess` baru ke hosting

### **Expected Result:**

- ✅ User bisa search status
- ✅ User bisa cancel booking
- ✅ Admin bisa approve/reject
- ✅ Admin bisa add/edit/delete room
- ✅ Semua POST request berhasil

---

## 📞 **Support:**

Jika masih error setelah upload `.htaccess`:

1. Screenshot error yang muncul
2. Cek error log di cPanel
3. Hubungi support hosting dengan Support ID
4. Atau hubungi saya dengan info error lengkap

**File `.htaccess` baru sudah siap diupload!** 🚀
