@echo off
REM ========================================
REM CLEANUP SCRIPT - Persiapan Deployment
REM ========================================

echo.
echo ========================================
echo   CLEANUP SCRIPT - SIM Peminjaman
echo ========================================
echo.

REM Backup config files
echo [1/4] Backing up config files...
if not exist "..\config_backup" mkdir "..\config_backup"
copy "config\database.php" "..\config_backup\" >nul 2>&1
copy "config\email.php" "..\config_backup\" >nul 2>&1
copy "config\whatsapp.php" "..\config_backup\" >nul 2>&1
echo      Config files backed up to ..\config_backup\
echo.

REM Delete test/debug files
echo [2/4] Deleting test and debug files...
if exist "debug.php" (
    del "debug.php"
    echo      Deleted: debug.php
)
if exist "debug_user_wa.php" (
    del "debug_user_wa.php"
    echo      Deleted: debug_user_wa.php
)
if exist "test_email.php" (
    del "test_email.php"
    echo      Deleted: test_email.php
)
if exist "test_wa.php" (
    del "test_wa.php"
    echo      Deleted: test_wa.php
)
if exist "conflict.log" (
    del "conflict.log"
    echo      Deleted: conflict.log
)
echo.

REM Delete documentation (optional)
echo [3/4] Deleting documentation files (optional)...
set /p DELETE_DOCS="Delete README.md, SETUP.md, DEPLOYMENT.md, HOSTING_GUIDE.md? (Y/N): "
if /i "%DELETE_DOCS%"=="Y" (
    if exist "README.md" del "README.md"
    if exist "SETUP.md" del "SETUP.md"
    if exist "DEPLOYMENT.md" del "DEPLOYMENT.md"
    if exist "HOSTING_GUIDE.md" del "HOSTING_GUIDE.md"
    echo      Documentation files deleted.
) else (
    echo      Documentation files kept.
)
echo.

REM Summary
echo [4/4] Cleanup complete!
echo.
echo ========================================
echo   NEXT STEPS:
echo ========================================
echo 1. Upload project ke hosting (kecuali vendor/ dan config/)
echo 2. Di hosting, jalankan: composer install --no-dev
echo 3. Upload config files manual via FTP:
echo    - config/database.php (edit dulu dengan credentials hosting)
echo    - config/email.php
echo    - config/whatsapp.php
echo 4. Import database: config/bappeda_ruangan.sql
echo 5. Set permissions: chmod 755 assets/files/ assets/images/rooms/
echo.
echo Config backup tersimpan di: ..\config_backup\
echo ========================================
echo.
pause
