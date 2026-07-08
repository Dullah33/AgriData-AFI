#!/bin/bash
set -e

# Railway memberi nilai $PORT secara dinamis saat container dijalankan.
# Apache tidak bisa langsung baca env var di config file, jadi kita
# ganti placeholder ${PORT} dengan nilai asli sebelum Apache start.
: "${PORT:=80}"

sed -i "s/\${PORT}/${PORT}/g" /etc/apache2/sites-available/000-default.conf
sed -i "s/Listen 80/Listen ${PORT}/g" /etc/apache2/ports.conf

# Cache config Laravel untuk production (aman diulang setiap deploy)
php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true

# Jalankan migration otomatis setiap deploy baru
php artisan migrate --force || true

exec apache2-foreground
