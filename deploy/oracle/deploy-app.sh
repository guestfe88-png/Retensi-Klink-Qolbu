#!/bin/bash
# Deploy / update aplikasi Retensi (jalankan dari /var/www/retensi)
set -e

APP_DIR="/var/www/retensi"
cd "$APP_DIR"

echo "==> Maintenance mode..."
php artisan down --retry=60 || true

echo "==> Composer production..."
composer install --no-dev --optimize-autoloader --no-interaction

echo "==> Build asset frontend..."
npm ci --ignore-scripts 2>/dev/null || npm install --ignore-scripts
npm run build

echo "==> Migrasi database..."
php artisan migrate --force

echo "==> Cache config..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "==> Permission storage..."
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

echo "==> Selesai maintenance mode..."
php artisan up

echo "Deploy berhasil!"
