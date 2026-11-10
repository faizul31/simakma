Write-Host "======================================================================" -ForegroundColor Cyan
Write-Host "          MENJALANKAN 5 SERVICE ARSITEKTUR MICROSERVICES              " -ForegroundColor Yellow
Write-Host "======================================================================" -ForegroundColor Cyan
Write-Host "[Port 8001] Auth Service        (Database: db_auth)" -ForegroundColor White
Write-Host "[Port 8002] Kegiatan Service    (Database: db_kegiatan)" -ForegroundColor White
Write-Host "[Port 8003] Pendaftaran Service (Database: db_pendaftaran)" -ForegroundColor White
Write-Host "[Port 8004] Prestasi Service    (Database: db_prestasi)" -ForegroundColor White
Write-Host "[Port 8000] API Gateway / Web   (Frontend Portal)" -ForegroundColor Green
Write-Host "======================================================================" -ForegroundColor Cyan

Start-Process powershell -ArgumentList "-NoExit", "-Command", "host.ui.RawUI.WindowTitle='1. Auth Service (Port 8001)'; php artisan serve --port=8001"
Start-Process powershell -ArgumentList "-NoExit", "-Command", "host.ui.RawUI.WindowTitle='2. Kegiatan Service (Port 8002)'; php artisan serve --port=8002"
Start-Process powershell -ArgumentList "-NoExit", "-Command", "host.ui.RawUI.WindowTitle='3. Pendaftaran Service (Port 8003)'; php artisan serve --port=8003"
Start-Process powershell -ArgumentList "-NoExit", "-Command", "host.ui.RawUI.WindowTitle='4. Prestasi Service (Port 8004)'; php artisan serve --port=8004"
Start-Process powershell -ArgumentList "-NoExit", "-Command", "host.ui.RawUI.WindowTitle='5. API Gateway Web (Port 8000)'; php artisan serve --port=8000"

Write-Host "Kelima service berhasil diluncurkan!" -ForegroundColor Green
Write-Host "Buka browser Anda di: http://localhost:8000" -ForegroundColor Cyan

