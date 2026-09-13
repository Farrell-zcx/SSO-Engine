# ============================================
# SSO Engine - PHP Backend (CodeIgniter 4)
# ============================================
FROM php:8.2-apache

# Install PHP extensions yang dibutuhkan CI4 + Redis
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    unzip \
    git \
    curl \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd mysqli pdo pdo_mysql zip intl \
    && a2enmod rewrite \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy composer files dulu (agar layer cache optimal)
COPY composer.json composer.lock* ./

# Install dependencies PHP (termasuk predis/predis untuk Redis)
RUN composer install --no-dev --optimize-autoloader --no-scripts

# Copy seluruh kode aplikasi
COPY . .

# Set permissions
RUN chown -R www-data:www-data /var/www/html/writable \
    && chmod -R 775 /var/www/html/writable

# Pastikan file keys (JWT RS256) punya permission yang benar jika ada
RUN if [ -f /var/www/html/keys/private.pem ]; then chmod 600 /var/www/html/keys/private.pem; fi && \
    if [ -f /var/www/html/keys/public.pem ]; then chmod 644 /var/www/html/keys/public.pem; fi

# Konfigurasi Apache: DocumentRoot ke folder public CI4
RUN sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/sites-available/000-default.conf \
    && sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/apache2.conf

# AllowOverride All untuk .htaccess CI4
RUN echo '<Directory /var/www/html/public>\n\
    AllowOverride All\n\
    Require all granted\n\
</Directory>' > /etc/apache2/conf-available/ci4.conf \
    && a2enconf ci4

EXPOSE 80

CMD ["apache2-foreground"]
