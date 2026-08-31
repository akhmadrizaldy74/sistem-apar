#!/bin/bash
set -e

# Salin .env dari .env.example jika belum ada
if [ ! -f /var/www/html/.env ]; then
    echo "[Entrypoint] Membuat file .env dari .env.example..."
    cp /var/www/html/.env.example /var/www/html/.env
fi

# Pastikan folder permissions storage & cache dapat ditulis oleh Apache
mkdir -p /var/www/html/storage/framework/sessions \
         /var/www/html/storage/framework/views \
         /var/www/html/storage/framework/cache \
         /var/www/html/storage/logs \
         /var/www/html/bootstrap/cache

chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache 2>/dev/null || true
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache 2>/dev/null || true

# Jika folder vendor belum ada, jalankan composer install otomatis
if [ ! -d /var/www/html/vendor ] || [ ! -f /var/www/html/vendor/autoload.php ]; then
    echo "[Entrypoint] Menginstall dependency Composer..."
    composer install --no-interaction --prefer-dist --optimize-autoloader
fi

# Generate APP_KEY jika belum terisi di .env
if ! grep -q "^APP_KEY=base64:" /var/www/html/.env; then
    echo "[Entrypoint] Membuat APP_KEY Laravel..."
    php artisan key:generate --force
fi

# Buat storage link otomatis jika belum ada
if [ ! -L /var/www/html/public/storage ] && [ ! -d /var/www/html/public/storage ]; then
    echo "[Entrypoint] Membuat symlink storage..."
    php artisan storage:link 2>/dev/null || true
fi

# Jalankan perintah utama container (Apache foreground)
exec "$@"
