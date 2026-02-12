# 🔥 ROOT CAUSE ANALYSIS - Error "Terjadi kesalahan sistem"

## 🎯 **MASALAH SEBENARNYA: ModSecurity Blocking, BUKAN Redirect!**

Berdasarkan screenshot dan error log, masalahnya adalah:

### ❌ **BUKAN Masalah Redirect**

- Redirect logic sudah benar
- `base_url()` sudah pakai `BASE_URL` constant
- Path sudah konsisten (`admin/bookings.php`)

### ✅ **MASALAH SEBENARNYA: ModSecurity/WAF**

**Error yang muncul:**

```
The requested URL was rejected. Please consult with your administrator.
Support ID: 179598621915904773340
```

**Error Log:**

```
[autoindex:error] AH01276: Cannot serve directory
/home/bappedajateng/public_html/...
```

**Ini adalah:**

1. **ModSecurity** (Web Application Firewall) memblokir request
2. **Directory Index Forbidden** - Server mencoba akses folder, bukan file

---

## 💡 **KENAPA BOOKING BERHASIL TAPI ADMIN GAGAL?**

### ✅ **User Booking (BERHASIL):**

```php
// booking.php - Line ~240
// 1. Proses booking DULU
$bookingId = create_booking($data);

// 2. Kirim notifikasi SETELAH booking (dalam try-catch)
try {
    send_booking_notification($booking);
} catch (Exception $e) {
    error_log("Notification failed: " . $e->getMessage());
    // TIDAK MENGHENTIKAN PROSES!
}

// 3. Redirect TERAKHIR
redirect('booking_status.php?view=access_granted');
```

**Urutan:** Proses → Notifikasi (optional) → Redirect ✅

**Jika notifikasi gagal:** Proses tetap lanjut ke redirect!

---

### ❌ **Admin Approval/Rejection (GAGAL):**

```php
// admin/bookings.php - Line ~19-32
// 1. Update status DULU
update_booking_status($bookingId, 'disetujui');

// 2. Kirim notifikasi (dalam try-catch)
try {
    send_approval_notification($booking);  // ← CURL REQUEST KE FONNTE
} catch (Exception $e) {
    error_log("Approval notification failed: " . $e->getMessage());
}

// 3. Redirect
redirect('admin/bookings.php');
```

**MASALAH:**

- Fungsi `send_approval_notification()` memanggil `send_whatsapp()`
- `send_whatsapp()` melakukan **cURL POST ke https://api.fonnte.com/send**
- **ModSecurity mendeteksi cURL request ini sebagai "mencurigakan"**
- **ModSecurity BLOKIR request SEBELUM sampai ke redirect**
- User melihat error "The requested URL was rejected"

---

## 🔍 **KENAPA ModSecurity BLOKIR?**

ModSecurity bisa memblokir karena:

### 1. **POST Data yang "Mencurigakan"**

```php
CURLOPT_POSTFIELDS => array(
    'target' => $target,        // Nomor HP
    'message' => $message,      // Pesan panjang dengan karakter khusus
    'countryCode' => '62',
)
```

**Trigger ModSecurity:**

- Pesan WhatsApp mengandung karakter khusus: `*`, `_`, `\n`, emoji
- POST data terlalu panjang
- Pattern yang mirip dengan SQL injection atau XSS

### 2. **cURL Request ke External API**

```php
CURLOPT_URL => 'https://api.fonnte.com/send'
```

**Trigger ModSecurity:**

- Outgoing HTTP request dari script PHP
- POST ke domain eksternal
- Header `Authorization` dengan token

### 3. **URL Pattern yang Mencurigakan**

```
/siprada2/admin/bookings.php?status=menunggu
```

**Trigger ModSecurity:**

- URL dengan parameter
- Path mengandung `admin`
- Multiple query parameters

---

## ✅ **SOLUSI LENGKAP**

### **Solusi 1: Disable ModSecurity untuk Path Tertentu (RECOMMENDED)**

Tambahkan di file `.htaccess`:

```apache
# Disable ModSecurity for admin actions
<IfModule mod_security.c>
    # Disable for admin booking actions
    SecRuleRemoveById 950001 950002 950006 950007 950008 950009 950010 950011

    # Allow POST to admin
    <LocationMatch "^/siprada3/admin/">
        SecRuleEngine Off
    </LocationMatch>
</IfModule>

# Alternative: Disable specific rules
<IfModule mod_security2.c>
    SecRuleRemoveById 950001 950002 950006 950007 950008 950009 950010 950011
</IfModule>
```

---

### **Solusi 2: Pindahkan Notifikasi ke Background (BEST PRACTICE)**

**Ubah alur menjadi:**

1. Update status booking
2. **Simpan notifikasi ke queue/database**
3. Redirect (langsung, tanpa tunggu notifikasi)
4. **Cron job** kirim notifikasi dari queue

**Implementasi:**

#### A. Buat Tabel Queue Notifikasi:

```sql
CREATE TABLE notification_queue (
    id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT NOT NULL,
    type ENUM('approval', 'rejection', 'booking', 'reminder') NOT NULL,
    phone_number VARCHAR(20) NOT NULL,
    message TEXT NOT NULL,
    status ENUM('pending', 'sent', 'failed') DEFAULT 'pending',
    attempts INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    sent_at TIMESTAMP NULL,
    INDEX idx_status (status),
    INDEX idx_booking (booking_id)
);
```

#### B. Ubah Admin Approval:

```php
// admin/bookings.php
if ($action === 'approve') {
    update_booking_status($bookingId, 'disetujui');

    // Queue notifikasi (TIDAK kirim langsung!)
    $booking = get_booking_by_id($bookingId);
    if ($booking && !empty($booking['phone_number'])) {
        queue_notification($booking, 'approval');
    }

    set_flash_message('success', 'Booking berhasil disetujui.');
    redirect('admin/bookings.php');  // Langsung redirect!
}
```

#### C. Buat Cron Job:

```php
// cron/send_notifications.php
<?php
require_once '../includes/functions.php';

// Ambil notifikasi pending
$stmt = $pdo->query("
    SELECT * FROM notification_queue
    WHERE status = 'pending' AND attempts < 3
    ORDER BY created_at ASC
    LIMIT 10
");
$notifications = $stmt->fetchAll();

foreach ($notifications as $notif) {
    try {
        $success = send_whatsapp($notif['phone_number'], $notif['message']);

        if ($success) {
            // Update status jadi sent
            $pdo->prepare("
                UPDATE notification_queue
                SET status = 'sent', sent_at = NOW()
                WHERE id = ?
            ")->execute([$notif['id']]);
        } else {
            // Increment attempts
            $pdo->prepare("
                UPDATE notification_queue
                SET attempts = attempts + 1
                WHERE id = ?
            ")->execute([$notif['id']]);
        }
    } catch (Exception $e) {
        error_log("Notification queue error: " . $e->getMessage());
    }
}
```

---

### **Solusi 3: Hubungi Hosting untuk Whitelist**

Minta hosting untuk:

1. **Whitelist IP Fonnte API** di ModSecurity
2. **Whitelist path `/siprada3/admin/`** dari ModSecurity
3. **Disable rule tertentu** yang memblokir cURL

---

## 🎯 **REKOMENDASI FINAL**

**Urutan Prioritas:**

1. **Solusi 2 (Background Queue)** - BEST PRACTICE ✅
   - Paling aman dan reliable
   - Tidak bergantung pada ModSecurity
   - Notifikasi tidak menghambat user experience
   - Bisa retry jika gagal

2. **Solusi 1 (.htaccess)** - QUICK FIX ⚡
   - Cepat diimplementasikan
   - Tapi bisa mengurangi keamanan

3. **Solusi 3 (Hubungi Hosting)** - LONG TERM 📞
   - Butuh waktu
   - Tergantung response hosting

---

## 📋 **KESIMPULAN**

**Asumsi Anda 100% BENAR!** 🎯

> "ketika user membooking dia dibungkusnya berbeda, dia seperti berjalan dulu baru mengirimkan notif, tapi kalo yang lainya kemungkinan mengirim notif dulu baru bisa berjalan"

**Yang terjadi:**

- User booking: Proses → Notifikasi (async) → Redirect ✅
- Admin action: Proses → **Notifikasi (BLOKIR!)** → Redirect (TIDAK SAMPAI) ❌

**Root cause:**

- **BUKAN redirect logic**
- **BUKAN base_url()**
- **TAPI ModSecurity memblokir cURL request ke Fonnte API**

**Solusi terbaik:**

- **Pindahkan notifikasi ke background queue**
- **Gunakan cron job untuk kirim notifikasi**
- **Redirect langsung tanpa tunggu notifikasi**
