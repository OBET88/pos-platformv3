@echo off
title Git Project Manager - PRO V2 SAFE
color 0A
cls
setlocal EnableDelayedExpansion

goto start

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
:: CHECK MAIN SAFETY
:: ==================================================
:check_main_safe
if "!BRANCH!"=="main" (
    echo.
    echo [WARNING] Kamu sedang di BRANCH MAIN!
    echo [INFO] Disarankan pakai feature branch dulu.
    echo.
)
exit /b

:: ==================================================
:: CHECK CHANGES
:: ==================================================
:has_changes
set "HAS_CHANGES="
for /f %%X in ('git status --porcelain 2^>nul ^| find /c /v ""') do (
    if %%X GTR 0 set "HAS_CHANGES=1"
)
exit /b

:: ==================================================
:: MENU
:: ==================================================
:menu
cls
call :detect_branch
call :check_main_safe

echo ========================================================
echo         GIT PROJECT MANAGER PRO V2 (SAFE MODE)
echo ========================================================
echo Branch Aktif : !BRANCH!
echo ========================================================
echo 1. Ambil Project dari Git (Clone)
echo 2. Hubungkan / Upload Project ke Git
echo 3. Simpan Perubahan + Kirim ke Git (Save & Upload)
echo 4. Ambil Update Terbaru dari Git
echo 5. Lihat Status File (Yang berubah)
echo 6. Buat Jalur Kerja Baru (Branch)
echo 7. Pindah Jalur Kerja
echo 8. Gabungkan ke Versi Utama (MAIN - HATI-HATI)
echo 9. Kembalikan ke Versi Aman (Rollback)
echo 10. Keluar
echo ========================================================

set /p pilih=Masukkan pilihan:

if "%pilih%"=="1" goto clone
if "%pilih%"=="2" goto init_repo
if "%pilih%"=="3" goto push_safe
if "%pilih%"=="4" goto pull_safe
if "%pilih%"=="5" goto status
if "%pilih%"=="6" goto create_branch
if "%pilih%"=="7" goto switch_branch
if "%pilih%"=="8" goto merge_main_safe
if "%pilih%"=="9" goto rollback_safe
if "%pilih%"=="10" exit

goto menu

:: ==================================================
:: CLONE
:: ==================================================
:clone
cls
set /p url=Masukkan URL Git:
git clone %url%
pause
goto menu

:: ==================================================
:: INIT REPO
:: ==================================================
:init_repo
cls
if not exist .git (
    git init
)

set /p url=Masukkan remote URL:
git remote add origin %url%

echo [OK] Repo connected
pause
goto menu

:: ==================================================
:: SAFE PUSH
:: ==================================================
:push_safe
cls
call :detect_branch
call :has_changes

if not defined HAS_CHANGES (
    echo [INFO] Tidak ada perubahan
    pause
    goto menu
)

echo ========================================================
echo BRANCH AKTIF: !BRANCH!
echo ========================================================

if "!BRANCH!"=="main" (
    echo [WARNING] Kamu push dari MAIN!
    choice /M "Yakin lanjut push ke main?"
    if errorlevel 2 goto menu
)

git add .
set /p msg=Commit message:
if "%msg%"=="" set msg=update

git commit -m "%msg%"
git push origin !BRANCH!

pause
goto menu

:: ==================================================
:: SAFE PULL (WITH FETCH FIRST)
:: ==================================================
:pull_safe
cls
call :detect_branch

echo [INFO] Fetching latest update...
git fetch origin

echo [INFO] Pulling branch: !BRANCH!
git pull origin !BRANCH!

pause
goto menu

:: ==================================================
:: STATUS
:: ==================================================
:status
cls
git status
pause
goto menu

:: ==================================================
:: CREATE BRANCH
:: ==================================================
:create_branch
cls
set /p bname=Nama branch baru:
git checkout -b %bname%
echo [OK] Branch dibuat
pause
goto menu

:: ==================================================
:: SWITCH BRANCH
:: ==================================================
:switch_branch
cls
git branch
set /p bname=Pilih branch:
git checkout %bname%
pause
goto menu

:: ==================================================
:: SAFE MERGE MAIN (PROTECTED)
:: ==================================================
:merge_main_safe
cls
call :detect_branch

echo ========================================================
echo MERGE KE MAIN (PROTECTED MODE)
echo ========================================================
echo Current Branch: !BRANCH!
echo ========================================================

if "!BRANCH!"=="main" (
    echo [ERROR] Kamu sudah di MAIN!
    pause
    goto menu
)

set /p confirm=Yakin merge "!BRANCH!" ke MAIN? (y/n):
if /i not "%confirm%"=="y" goto menu

git checkout main
git merge !BRANCH!

echo [OK] Merge selesai
pause
goto menu

:: ==================================================
:: ROLLBACK SAFE
:: ==================================================
:rollback_safe
cls
echo ========================================================
echo ROLLBACK KE REMOTE VERSION (DANGER MODE SAFE)
echo ========================================================

set /p confirm=Yakin reset local ke remote? (y/n):
if /i not "%confirm%"=="y" goto menu

git fetch origin
git reset --hard origin/!BRANCH!

echo [OK] Reset selesai
pause
goto menu


:start
call :menu
exit /b