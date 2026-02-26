FROM php:8.3-cli-alpine AS base

# Установка системных зависимостей
RUN apk add --no-cache \
    git \
    curl \
    curl-dev \
    libpng-dev \
    libzip-dev \
    oniguruma-dev \
    icu-dev \
    libxml2-dev \
    freetype-dev \
    libjpeg-turbo-dev \
    unzip \
    bash \
    supervisor \
    netcat-openbsd \
    linux-headers \
    # Добавляем репозиторий для PHP 8.3 пакетов
    --repository http://dl-cdn.alpinelinux.org/alpine/edge/main/ \
    --repository http://dl-cdn.alpinelinux.org/alpine/edge/community/

# Установка PHP расширений
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
    pdo \
    pdo_mysql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    zip \
    intl \
    opcache \
    calendar \
    xml \
    dom \
    fileinfo

# Установка Redis через Alpine пакет
RUN apk add --no-cache \
    --repository http://dl-cdn.alpinelinux.org/alpine/edge/community/ \
    php83-pecl-redis \
    php83-pecl-igbinary \
    php83-pecl-msgpack

# Создаем символическую ссылку для redis.so
RUN ln -s /usr/lib/php83/modules/redis.so /usr/local/lib/php/extensions/no-debug-non-zts-20220829/redis.so \
    && echo "extension=redis.so" > /usr/local/etc/php/conf.d/docker-php-ext-redis.ini

# Установка Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY . .

# Установка зависимостей Composer
RUN composer install --no-scripts --no-autoloader --no-interaction --prefer-dist \
    && composer dump-autoload --optimize

# Настройка прав
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && chmod -R 775 /var/www/html/storage \
    && chmod -R 775 /var/www/html/bootstrap/cache

COPY docker/php/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 8000

HEALTHCHECK --interval=30s --timeout=3s --start-period=40s --retries=3 \
    CMD php artisan octane:status || wget --quiet --tries=1 --spider http://localhost:8000 || exit 1

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["php", "artisan", "octane:start", "--server=roadrunner", "--host=0.0.0.0", "--port=8000"]
