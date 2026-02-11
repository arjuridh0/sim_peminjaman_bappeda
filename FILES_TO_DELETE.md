# 🗑️ FILE DEBUGGING YANG HARUS DIHAPUS DI CPANEL

## File yang WAJIB dihapus sebelum/sesudah upload:

1. **debug.php** - Script debugging umum
2. **debug_user_wa.php** - Script test notifikasi WA user
3. **test_email.php** - Script test email
4. **test_wa.php** - Script test WhatsApp admin
5. **conflict.log** - Log file konflik (tidak diperlukan)

## Cara Hapus di cPanel File Manager:

1. Login ke cPanel
2. Buka **File Manager**
3. Navigate ke folder `public_html/` atau folder project Anda
4. Cari file-file di atas
5. Centang file → klik **Delete**

## PENTING - File Config (JANGAN HAPUS):

✅ **KEEP** (jangan dihapus):

- config/database.php
- config/email.php
- config/whatsapp.php

File-file config ini HARUS ada di hosting, tapi:

- Edit dulu sesuai credentials hosting
- Upload manual via FTP/File Manager
- JANGAN commit ke Git (sudah di .gitignore)

## File Dokumentasi (Opsional):

Boleh dihapus jika ingin menghemat space:

- README.md
- SETUP.md
- DEPLOYMENT.md
- HOSTING_GUIDE.md
- cleanup_for_deployment.bat (file Windows, tidak berguna di Linux hosting)

---

## Quick Checklist:

```
[ ] Hapus debug.php
[ ] Hapus debug_user_wa.php
[ ] Hapus test_email.php
[ ] Hapus test_wa.php
[ ] Hapus conflict.log
[ ] (Opsional) Hapus file dokumentasi .md
[ ] (Opsional) Hapus cleanup_for_deployment.bat
```

**Total file yang harus dihapus: 5 file wajib + 5 file opsional**
