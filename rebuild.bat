@echo off
echo Menghentikan container esimko-app...
docker compose -f docker-compose.npm.yml down

echo Membangun ulang image Docker...
docker compose -f docker-compose.npm.yml build --no-cache app

echo Menjalankan kembali container...
docker compose -f docker-compose.npm.yml up -d

echo Proses rebuild selesai!
pause
