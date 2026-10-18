@echo off
echo.
echo  ====================================================
echo    KPR Beta - Laravel Development Server
echo  ====================================================
echo.
echo  Akses Lokal (PC ini) : http://localhost:8000/
echo  Akses Perangkat Lain : http://192.168.1.63:8000/
echo.
echo  Menjalankan server di semua interface (0.0.0.0:8000)...
echo.
C:\xampp\php\php.exe artisan serve --host=0.0.0.0 --port=8000
