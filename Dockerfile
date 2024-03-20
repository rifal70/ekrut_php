# Menggunakan PHP 7.4 sebagai base image
FROM php:7.4

# Install PostgreSQL client and development libraries
RUN apt-get update && apt-get install -y libpq-dev

# Install PDO PostgreSQL extension
RUN docker-php-ext-install pgsql pdo pdo_pgsql

# Instal dependensi yang diperlukan untuk Composer
RUN apt-get update \
    && apt-get install -y zip unzip

# Download dan instal Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Verifikasi instalasi Composer
RUN composer --version

# Set working directory
WORKDIR /var/www/html

# Expose port 80
EXPOSE 80

# Perintah default untuk menjalankan aplikasi
CMD ["php", "-S", "0.0.0.0:80"]