# Build stage for Composer dependencies
FROM php:8.2-fpm AS builder

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    libicu-dev \
    && docker-php-ext-install zip

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/symfony

# Copy composer files first for better caching
COPY composer.json composer.lock symfony.lock ./

# Install dependencies (without dev dependencies for production)
RUN composer install --no-dev --no-scripts --no-interaction --prefer-dist --optimize-autoloader

# Copy the rest of the application
COPY . .

# Run post-install scripts
RUN composer dump-autoload --optimize --classmap-authoritative

# Production stage
FROM php:8.2-fpm

# Install production dependencies only
RUN apt-get update && apt-get install -y \
    libzip-dev \
    libicu-dev \
    && docker-php-ext-install pdo pdo_mysql zip intl opcache \
    && rm -rf /var/lib/apt/lists/*

# Copy composer from builder stage (useful for production maintenance)
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Configure PHP for production
RUN { \
    echo 'opcache.enable=1'; \
    echo 'opcache.memory_consumption=256'; \
    echo 'opcache.max_accelerated_files=20000'; \
    echo 'opcache.validate_timestamps=0'; \
    echo 'opcache.interned_strings_buffer=16'; \
    echo 'realpath_cache_size=4096K'; \
    echo 'realpath_cache_ttl=600'; \
} > /usr/local/etc/php/conf.d/opcache-prod.ini

WORKDIR /var/www/symfony

# Copy from builder stage
COPY --from=builder --chown=www-data:www-data /var/www/symfony /var/www/symfony

# Ensure proper permissions
RUN chown -R www-data:www-data /var/www/symfony

USER www-data

EXPOSE 9000

