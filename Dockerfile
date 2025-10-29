# File: ai-php-api/Dockerfile

# Base image with PHP 8.3 and FPM on a small Alpine Linux
FROM php:8.3-fpm-alpine

# Set working directory
WORKDIR /var/www

# Install system dependencies
RUN apk add --no-cache \
    curl \
    libzip-dev \
    libpng-dev \
    unzip \
    zip \
    # Install necessary PHP extensions for Laravel
    && docker-php-ext-install pdo pdo_mysql bcmath zip

# Get the latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy existing application directory contents
COPY . /var/www

# Copy composer.json and composer.lock first to leverage Docker cache
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-scripts

# Copy the rest of the application code
COPY . .

# Set permissions for Laravel storage and cache
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

# Expose port 9000 and start php-fpm server
EXPOSE 9000
CMD ["php-fpm"]