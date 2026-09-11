FROM php:8.2-fpm-alpine

# Install system dependencies
RUN apk update && apk add --no-cache \
    nginx \
    curl \
    git \
    zip \
    unzip \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libzip-dev \
    oniguruma-dev \
    icu-dev \
    mariadb-client \
    && mkdir -p /run/nginx /var/log/nginx /var/lib/nginx/tmp

# Configure and install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo_mysql \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        zip \
        intl

# Install Composer
COPY --from=composer:2.7 /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY . /var/www/html

# Run composer install without scripts to prevent premature db calls during build
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-scripts

# Set permissions for storage and bootstrap/cache
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Copy Nginx configuration
COPY docker/nginx.conf /etc/nginx/http.d/default.conf

# Copy and setup entrypoint & worker
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
COPY docker/worker.sh /usr/local/bin/worker.sh
RUN chmod +x /usr/local/bin/entrypoint.sh /usr/local/bin/worker.sh

# Expose port 80
EXPOSE 80

# Run entrypoint
CMD ["/usr/local/bin/entrypoint.sh"]
