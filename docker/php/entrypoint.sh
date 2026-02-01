#!/bin/sh
set -e

# Функция проверки доступности порта
wait_for_port() {
    host=$1
    port=$2
    name=$3
    echo "Waiting for $name..."
    max_attempts=60
    attempt=0
    while [ $attempt -lt $max_attempts ]; do
        if nc -z "$host" "$port" 2>/dev/null; then
            echo "$name is ready!"
            return 0
        fi
        attempt=$((attempt + 1))
        sleep 1
    done
    echo "Warning: $name did not become ready after $max_attempts attempts"
}

# Ожидание готовности MySQL
wait_for_port mysql 3306 "MySQL"

# Ожидание готовности Redis
wait_for_port redis 6379 "Redis"

# Установка прав доступа
chown -R www-data:www-data /var/www/html
chmod -R 755 /var/www/html
chmod -R 775 /var/www/html/storage
chmod -R 775 /var/www/html/bootstrap/cache

# Оптимизация Laravel для production
if [ "$APP_ENV" = "production" ]; then
    echo "Optimizing Laravel for production..."
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    php artisan event:cache
fi

# Выполнение миграций (если нужно)
if [ "$RUN_MIGRATIONS" = "true" ]; then
    echo "Running migrations..."
    php artisan migrate --force
fi

# Запуск команды
exec "$@"
