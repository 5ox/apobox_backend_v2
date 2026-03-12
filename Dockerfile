FROM php:8.2-apache

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    libicu-dev \
    libsodium-dev \
    libfreetype6-dev \
    libjpeg-dev \
    zip \
    unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo_mysql \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        intl \
        soap \
        sockets \
        zip \
        opcache \
        sodium \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Enable Apache modules (mpm_prefork is already default in php:8.2-apache)
RUN a2enmod rewrite headers \
    && ls /etc/apache2/mods-enabled/mpm_* \
    && rm -f /etc/apache2/mods-enabled/mpm_event.* /etc/apache2/mods-enabled/mpm_worker.*

# Set Apache document root to Laravel public directory
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy composer files first for better layer caching
COPY composer.json composer.lock* ./

# Install dependencies (before artisan commands, which need vendor/)
RUN COMPOSER_MEMORY_LIMIT=-1 composer install --no-dev --optimize-autoloader --no-interaction --no-scripts

# Copy application files
COPY . .

# Ensure Laravel directories exist (empty dirs aren't tracked by git)
RUN mkdir -p bootstrap/cache storage/framework/{sessions,views,cache} storage/logs

# Generate app key and optimize autoloader (use sqlite to avoid DB connection during build)
RUN cp .env.example .env \
    && DB_CONNECTION=sqlite DB_DATABASE=:memory: php artisan key:generate \
    && composer dump-autoload --optimize

# Set permissions
RUN chown -R www-data:www-data storage bootstrap/cache

# PHP configuration
RUN echo "opcache.enable=1\n\
opcache.memory_consumption=128\n\
opcache.interned_strings_buffer=8\n\
opcache.max_accelerated_files=4000\n\
opcache.validate_timestamps=0\n\
upload_max_filesize=20M\n\
post_max_size=20M\n\
memory_limit=256M" > /usr/local/etc/php/conf.d/custom.ini

EXPOSE 80

# Copy startup script
COPY docker/start.sh /usr/local/bin/start.sh
RUN chmod +x /usr/local/bin/start.sh

CMD ["start.sh"]
