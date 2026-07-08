FROM php:8.4-apache

# Install dependency sistem + library yang dibutuhkan ekstensi gd (jpeg/freetype)
RUN apt-get update && apt-get install -y \
    git curl zip unzip \
    libpng-dev libjpeg62-turbo-dev libfreetype6-dev \
    libonig-dev libxml2-dev libzip-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_mysql mbstring exif pcntl bcmath gd zip \
    && a2enmod rewrite

# FIX: apt-get install di atas kadang memicu Apache mengaktifkan mpm_event
# selain mpm_prefork bawaan image ini. PHP (mod_php) hanya kompatibel dengan
# mpm_prefork, jadi MPM lain wajib dimatikan secara eksplisit di sini.
RUN a2dismod mpm_event mpm_worker 2>/dev/null; \
    a2enmod mpm_prefork

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy source code
COPY . .

# Install dependency PHP (production, tanpa dev dependency)
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Apache document root diarahkan ke /public (folder Laravel)
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
COPY docker/apache/000-default.conf /etc/apache2/sites-available/000-default.conf

# Permission storage & cache Laravel
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Railway inject $PORT secara dinamis saat runtime — Apache perlu listen di port itu
COPY docker/apache/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

EXPOSE 80

ENTRYPOINT ["/entrypoint.sh"]
