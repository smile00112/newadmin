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
    $PHPIZE_DEPS

# Установка PHP расширений (только те, которые действительно нужно устанавливать)
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

# Установка Redis расширения через PECL
RUN pecl install redis \
    && docker-php-ext-enable redis

# Установка Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Настройка рабочей директории
WORKDIR /var/www/html

# Копирование файлов проекта
COPY . .

# Установка зависимостей Composer (с dev зависимостями для установки RoadRunner)
RUN composer install --optimize-autoloader --no-interaction --prefer-dist

# Настройка прав доступа
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && chmod -R 775 /var/www/html/storage \
    && chmod -R 775 /var/www/html/bootstrap/cache

# Копирование PHP конфигурации (если есть)
# COPY docker/php/php.ini /usr/local/etc/php/php.ini
# COPY docker/php/php-cli.ini /usr/local/etc/php/php-cli.ini

# Копирование конфигурации RoadRunner (если есть)
# COPY .rr.yaml /var/www/html/.rr.yaml

# Создание entrypoint скрипта
COPY docker/php/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Expose порт для RoadRunner
EXPOSE 8000

# Healthcheck
HEALTHCHECK --interval=30s --timeout=3s --start-period=40s --retries=3 \
    CMD php artisan octane:status || wget --quiet --tries=1 --spider http://localhost:8000 || exit 1

# Запуск RoadRunner через Octane
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["php", "artisan", "octane:start", "--server=roadrunner", "--host=0.0.0.0", "--port=8000"]
