@echo off
title Microservices Runner - Laravel 13
echo ======================================================================
echo           MENJALANKAN 5 SERVICE ARSITEKTUR MICROSERVICES
echo ======================================================================
echo.
echo [Port 8001] Auth Service        (Database: db_auth)
echo [Port 8002] Kegiatan Service    (Database: db_kegiatan)
echo [Port 8003] Pendaftaran Service (Database: db_pendaftaran)
echo [Port 8004] Prestasi Service    (Database: db_prestasi)
echo [Port 8000] API Gateway / Web   (Frontend Portal)
echo.
echo Membuka masing-masing service di jendela CMD terpisah...
start "1. Auth Service (Port 8001)" cmd /k "title 1. Auth Service (Port 8001) && php artisan serve --port=8001"
start "2. Kegiatan Service (Port 8002)" cmd /k "title 2. Kegiatan Service (Port 8002) && php artisan serve --port=8002"
start "3. Pendaftaran Service (Port 8003)" cmd /k "title 3. Pendaftaran Service (Port 8003) && php artisan serve --port=8003"
start "4. Prestasi Service (Port 8004)" cmd /k "title 4. Prestasi Service (Port 8004) && php artisan serve --port=8004"
start "5. API Gateway Web (Port 8000)" cmd /k "title 5. API Gateway Web (Port 8000) && php artisan serve --port=8000"

echo.
echo [SUKSES] Kelima terminal service telah aktif!
echo Buka browser Anda di: http://localhost:8000
echo ======================================================================
pause

