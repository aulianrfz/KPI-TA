FROM php:8.2-fpm

# Install dependencies
RUN apt-get update && apt-get install -y \
    git unzip libzip-dev libpng-dev libonig-dev libxml2-dev curl \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy app
COPY . .

# Set permission
RUN chown -R www-data:www-data storage bootstrap/cache

# Install dependencies
RUN composer install --optimize-autoloader --no-dev

# Cache config
RUN php artisan config:cache

# Expose socket for Cloud SQL
VOLUME ["/cloudsql"]

# Expose port
EXPOSE 8080

# Run Laravel using PHP built-in server (not ideal, but minimal)
CMD ["php", "-S", "0.0.0.0:8080", "-t", "public"]

