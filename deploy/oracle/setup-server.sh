#!/bin/bash
# Setup server Ubuntu 22.04/24.04 di Oracle Cloud (jalankan sebagai root atau sudo)
set -e

export DEBIAN_FRONTEND=noninteractive

echo "==> Update sistem..."
apt-get update -y
apt-get upgrade -y

echo "==> Install paket dasar..."
apt-get install -y nginx mysql-server git unzip curl software-properties-common

echo "==> Install PHP 8.3..."
add-apt-repository ppa:ondrej/php -y
apt-get update -y
apt-get install -y \
    php8.3-fpm php8.3-cli php8.3-mysql php8.3-mbstring \
    php8.3-xml php8.3-curl php8.3-zip php8.3-bcmath php8.3-intl

echo "==> Install Composer..."
if ! command -v composer &>/dev/null; then
    curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
fi

echo "==> Install Node.js (untuk build asset)..."
if ! command -v node &>/dev/null; then
    curl -fsSL https://deb.nodesource.com/setup_20.x | bash -
    apt-get install -y nodejs
fi

echo "==> Konfigurasi MySQL..."
mysql -e "CREATE DATABASE IF NOT EXISTS retensirm CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -e "CREATE USER IF NOT EXISTS 'retensi'@'localhost' IDENTIFIED BY 'GANTI_PASSWORD_KUAT';"
mysql -e "GRANT ALL PRIVILEGES ON retensirm.* TO 'retensi'@'localhost';"
mysql -e "FLUSH PRIVILEGES;"

echo "==> Buat folder aplikasi..."
mkdir -p /var/www/retensi
chown -R www-data:www-data /var/www/retensi

echo "==> Nginx site..."
cp /var/www/retensi/deploy/oracle/nginx-retensi.conf /etc/nginx/sites-available/retensi 2>/dev/null || true
if [ -f /var/www/retensi/deploy/oracle/nginx-retensi.conf ]; then
    ln -sf /etc/nginx/sites-available/retensi /etc/nginx/sites-enabled/retensi
    rm -f /etc/nginx/sites-enabled/default
fi

echo "==> Cron Laravel scheduler..."
(crontab -l 2>/dev/null | grep -v "artisan schedule:run"; echo "* * * * * cd /var/www/retensi && php artisan schedule:run >> /dev/null 2>&1") | crontab -

systemctl enable nginx php8.3-fpm mysql
systemctl restart nginx php8.3-fpm mysql

echo ""
echo "Setup selesai!"
echo "1. Upload project ke /var/www/retensi"
echo "2. Jalankan: bash deploy/oracle/deploy-app.sh"
echo "3. Buka port 80 di Oracle Security List (Networking -> VCN -> Security List)"
