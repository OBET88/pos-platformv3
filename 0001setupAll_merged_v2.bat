@echo off
title Git Project Manager - PRO FINAL
color 0A
cls
setlocal EnableDelayedExpansion

:: ==================================================
:: CEK SOFTWARE WAJIB
:: ==================================================
echo ==================================================
echo      CEK SOFTWARE WAJIB: Git, PHP, Composer, NPM
echo ==================================================

call :cek_software "Git" "git --version" "https://git-scm.com/download/win"
call :cek_software "PHP" "php -v" "https://windows.php.net/download/"
call :cek_software "Composer" "composer --version" "https://getcomposer.org/Composer-Setup.exe"
call :cek_software "Node.js & NPM" "npm -v" "https://nodejs.org/"

echo ==================================================
echo Semua pengecekan selesai. Pastikan sudah OK.
pause
goto menu

:: ==================================================
:: CEK SOFTWARE FUNCTION
:: ==================================================
:cek_software
setlocal EnableDelayedExpansion
set "software=%~1"
set "command=%~2"
set "download_url=%~3"

echo Mengecek %software%...

del tmp_check.txt >nul 2>&1
cmd /c "!command!" > tmp_check.txt 2>&1

if errorlevel 1 (
    echo [X] %software% TIDAK TERDETEKSI!
    echo     Download: %download_url%
    start "" %download_url%
) else (
    set "foundLine="
    for /f "tokens=*" %%i in (tmp_check.txt) do (
        if not defined foundLine set "foundLine=%%i"
    )

    if defined foundLine (
        echo [OK] %software% terdeteksi: !foundLine!
    ) else (
        echo [OK] %software% terpasang
    )
)

del tmp_check.txt >nul 2>&1
endlocal
exit /b

:: ==================================================
:: DETECT BRANCH
:: ==================================================
:detect_branch
set "BRANCH=main"
for /f "tokens=*" %%B in ('git branch --show-current 2^>nul') do (
    if not "%%B"=="" set "BRANCH=%%B"
)
exit /b

:: ==================================================
:: CEK APAKAH ADA PERUBAHAN
:: ==================================================
:has_changes
set "HAS_CHANGES="
for /f %%X in ('git status --porcelain 2^>nul ^| find /c /v ""') do (
    if %%X GTR 0 set "HAS_CHANGES=1"
)
exit /b

:: ==================================================
:: CEK APAKAH REMOTE ORIGIN ADA
:: ==================================================
:has_remote
set "HAS_REMOTE="
git remote get-url origin >nul 2>&1
if not errorlevel 1 set "HAS_REMOTE=1"
exit /b

:: ==================================================
:: TAMPILKAN FILE YANG BERUBAH
:: ==================================================
:show_changed_files
setlocal EnableDelayedExpansion
set "FOUND_STATUS="

echo.
echo ========================================================
echo FILE YANG BERUBAH
echo ========================================================
for /f "delims=" %%F in ('git status --short 2^>nul') do (
    set "FOUND_STATUS=1"
    set "line=%%F"
    set "code=!line:~0,2!"
    set "file=!line:~3!"

    if "!code!"==" M" (
        color 0E
        echo [MODIFIED] !file!
    ) else if "!code!"=="M " (
        color 0E
        echo [STAGED-MODIFIED] !file!
    ) else if "!code!"=="MM" (
        color 0E
        echo [MODIFIED + STAGED] !file!
    ) else if "!code!"=="A " (
        color 0A
        echo [ADDED] !file!
    ) else if "!code!"=="AM" (
        color 0A
        echo [ADDED + MODIFIED] !file!
    ) else if "!code!"=="??" (
        color 0B
        echo [UNTRACKED] !file!
    ) else if "!code!"==" D" (
        color 0C
        echo [DELETED] !file!
    ) else if "!code!"=="D " (
        color 0C
        echo [STAGED-DELETED] !file!
    ) else if "!code!"=="R " (
        color 0D
        echo [RENAMED] !file!
    ) else (
        color 07
        echo [OTHER !code!] !file!
    )
)

if not defined FOUND_STATUS (
    color 0A
    echo [INFO] Tidak ada perubahan file.
)

color 0A
echo ========================================================
endlocal
exit /b

:: ==================================================
:: MENU
:: ==================================================
:menu
cls
color 0A
echo ========================================================
echo               GIT PROJECT MANAGER - MENU UTAMA
echo ========================================================
echo 1. [CLONE] Ambil project dari GitHub ke komputer ini
echo 2. [UPLOAD / HUBUNGKAN REPO] Upload project lokal ke GitHub
echo 3. [UPDATE: COMMIT DAN PUSH] Kirim perubahan ke GitHub
echo 4. [AMBIL UPDATE: PULL] Ambil perubahan terbaru dari GitHub
echo 5. [CEK STATUS] Lihat file yang berubah di lokal
echo 6. [CEK REMOTE] Lihat repo GitHub yang terhubung
echo 7. [KELUAR]
echo ========================================================
set /p pilihan=Masukkan pilihan (1-7): 

if "%pilihan%"=="1" goto clone
if "%pilihan%"=="2" goto upload_pertama
if "%pilihan%"=="3" goto update_project
if "%pilihan%"=="4" goto pull_project
if "%pilihan%"=="5" goto cek_status
if "%pilihan%"=="6" goto cek_remote
if "%pilihan%"=="7" exit

echo Pilihan tidak valid!
pause
goto menu

:: ==================================================
:: CLONE
:: ==================================================
:clone
cls
set /p clone_url=Masukkan URL repo GitHub: 

echo.
echo [INFO] Cloning project...
git clone %clone_url%

if errorlevel 1 (
    echo [FAILED] Clone gagal! Cek URL / internet / akses repo.
) else (
    echo [SUCCESS] Project berhasil di-clone.
)

pause
goto menu

:: ==================================================
:: UPLOAD / HUBUNGKAN REPO
:: ==================================================
:upload_pertama
cls
color 0A

if not exist ".git" (
    echo [INFO] Folder ini belum menjadi Git repository.
    echo [INFO] Menjalankan git init...
    git init
    if errorlevel 1 (
        echo [FAILED] git init gagal.
        pause
        goto menu
    )
)

call :has_remote

if defined HAS_REMOTE (
    echo ========================================================
    echo [INFO] Folder ini sudah Git repository dan SUDAH terhubung ke remote:
    git remote -v
    echo ========================================================
    echo.
    choice /C YNB /M "Y=Ganti URL origin, N=Gunakan origin lama, B=Batal"
    if errorlevel 3 goto menu
    if errorlevel 2 goto upload_lanjut_pakai_remote
    if errorlevel 1 goto ganti_remote
) else (
    echo [INFO] Folder Git belum terhubung ke GitHub.
    set /p remote_url=Masukkan URL GitHub:
    git remote add origin %remote_url%
    if errorlevel 1 (
        echo [FAILED] Gagal menambahkan remote origin.
        pause
        goto menu
    )
)

:upload_lanjut_pakai_remote
call :detect_branch

echo.
echo [INFO] Branch aktif: !BRANCH!
echo [INFO] Menambahkan semua file...
git add .

echo.
git diff --cached --quiet
if not errorlevel 1 (
    echo [INFO] Tidak ada file baru/perubahan untuk di-commit.
    echo [INFO] Tetap akan coba push branch !BRANCH! ke remote.
    goto upload_push
)

set /p commit_msg=Masukkan pesan commit awal: 
if "%commit_msg%"=="" set "commit_msg=Initial commit"

git commit -m "%commit_msg%"

:upload_push
echo.
echo [INFO] Push ke GitHub...
git push -u origin !BRANCH!
if not errorlevel 1 (
    echo [SUCCESS] Upload / sinkronisasi ke GitHub berhasil.
    pause
    goto menu
)

echo.
echo [WARNING] Push biasa gagal.
echo Kemungkinan:
echo - Remote sudah ada isi
echo - Branch berbeda
echo - Butuh login
echo.

choice /C PFB /M "P=Pull lalu merge, F=Force push, B=Batal"
if errorlevel 3 goto menu
if errorlevel 2 goto force_push_upload
if errorlevel 1 goto pull_merge_upload

:pull_merge_upload
echo.
echo [INFO] Menjalankan pull merge...
git pull origin !BRANCH! --allow-unrelated-histories
if errorlevel 1 (
    echo [FAILED] Pull merge gagal.
    echo [INFO] Cek conflict dengan: git status
    pause
    goto menu
)

echo.
echo [INFO] Menambahkan hasil merge...
git add .
git commit -m "Gabungkan repo lokal dan remote" >nul 2>&1

echo.
echo [INFO] Push ulang...
git push -u origin !BRANCH!
if errorlevel 1 (
    echo [FAILED] Push setelah merge masih gagal.
) else (
    echo [SUCCESS] Merge dan push berhasil.
)
pause
goto menu

:force_push_upload
echo.
echo [WARNING] Force push akan MENIMPA isi branch remote.
choice /C YN /M "Yakin lanjut force push?"
if errorlevel 2 goto menu

git push -u origin !BRANCH! --force
if errorlevel 1 (
    echo [FAILED] Force push gagal.
) else (
    echo [SUCCESS] Force push berhasil.
)
pause
goto menu

:ganti_remote
set /p remote_url_baru=Masukkan URL GitHub baru:
git remote remove origin >nul 2>&1
git remote add origin %remote_url_baru%
if errorlevel 1 (
    echo [FAILED] Gagal mengganti remote origin.
    pause
    goto menu
)
echo [SUCCESS] Remote origin berhasil diganti.
goto upload_lanjut_pakai_remote

:: ==================================================
:: UPDATE PROJECT
:: ==================================================
:update_project
cls

if not exist ".git" (
    echo [ERROR] Ini bukan Git repository!
    pause
    goto menu
)

call :has_remote
if not defined HAS_REMOTE (
    echo [ERROR] Remote origin belum ada.
    echo Gunakan menu 2 untuk hubungkan repo ke GitHub.
    pause
    goto menu
)

call :detect_branch
call :has_changes

echo ========================================================
echo BRANCH AKTIF: !BRANCH!
echo ========================================================

if not defined HAS_CHANGES (
    echo [INFO] Tidak ada perubahan untuk di-commit.
    echo.
    choice /C YN /M "Tetap mau push branch sekarang?"
    if errorlevel 2 goto menu
    git push origin !BRANCH!
    pause
    goto menu
)

call :show_changed_files

echo.
choice /C YN /M "Sync dulu dengan git pull sebelum commit?"
if errorlevel 2 goto skip_pull_update

echo.
echo [INFO] Sync dengan GitHub dulu...
git pull origin !BRANCH!
if errorlevel 1 (
    echo [WARNING] Pull gagal. Mungkin conflict / branch beda.
)

:skip_pull_update
echo.
set /p commit_msg=Masukkan pesan commit: 
if "%commit_msg%"=="" set "commit_msg=Update project"

git add .
git commit -m "%commit_msg%"

if errorlevel 1 (
    echo.
    echo [INFO] Tidak ada commit baru yang dibuat.
    pause
    goto menu
)

echo.
choice /C YN /M "Lanjut push ke GitHub sekarang?"
if errorlevel 2 (
    echo.
    echo [INFO] Push dibatalkan. Commit lokal sudah dibuat.
    pause
    goto menu
)

git push origin !BRANCH!
if not errorlevel 1 (
    echo.
    echo [SUCCESS] UPDATE BERHASIL DIKIRIM
    pause
    goto menu
)

echo.
echo [FAILED] PUSH GAGAL!
echo Kemungkinan:
echo - Remote berisi perubahan yang belum ada di lokal
echo - Login GitHub bermasalah
echo - Conflict file
echo.

choice /C PFB /M "P=Pull lalu merge, F=Force push, B=Batal"
if errorlevel 3 goto menu
if errorlevel 2 goto force_push_update
if errorlevel 1 goto pull_merge_update

:pull_merge_update
echo.
git pull origin !BRANCH! --allow-unrelated-histories
if errorlevel 1 (
    echo [FAILED] Pull merge gagal.
    echo [INFO] Cek file conflict dengan git status
    pause
    goto menu
)

git add .
git commit -m "Selesaikan merge sebelum push" >nul 2>&1
git push origin !BRANCH!

if errorlevel 1 (
    echo [FAILED] Push setelah merge gagal.
) else (
    echo [SUCCESS] Merge dan push berhasil.
)
pause
goto menu

:force_push_update
echo.
echo [WARNING] Force push akan menimpa isi remote.
choice /C YN /M "Yakin force push?"
if errorlevel 2 goto menu

git push origin !BRANCH! --force
if errorlevel 1 (
    echo [FAILED] Force push gagal.
) else (
    echo [SUCCESS] Force push berhasil.
)
pause
goto menu

:: ==================================================
:: PULL PROJECT
:: ==================================================
:pull_project
cls

if not exist ".git" (
    echo [ERROR] Ini bukan Git repository!
    pause
    goto menu
)

call :has_remote
if not defined HAS_REMOTE (
    echo [ERROR] Remote origin belum ada.
    pause
    goto menu
)

call :detect_branch

echo [INFO] Mengambil update dari GitHub (!BRANCH!)...
git pull origin !BRANCH!

if errorlevel 1 (
    echo [FAILED] Pull gagal!
    echo Kemungkinan conflict / koneksi / branch salah.
) else (
    echo [SUCCESS] Update berhasil diambil.
)

pause
goto menu

:: ==================================================
:: STATUS
:: ==================================================
:cek_status
cls

if not exist ".git" (
    echo [ERROR] Ini bukan Git repository!
    pause
    goto menu
)

call :show_changed_files
echo.
git status
pause
goto menu

:: ==================================================
:: CEK REMOTE
:: ==================================================
:cek_remote
cls

if not exist ".git" (
    echo [ERROR] Ini bukan Git repository!
    pause
    goto menu
)

call :has_remote
if defined HAS_REMOTE (
    echo ========================================================
    echo REMOTE YANG TERHUBUNG
    echo ========================================================
    git remote -v
    echo ========================================================
) else (
    echo [INFO] Belum ada remote origin yang terhubung.
)

pause
goto menu