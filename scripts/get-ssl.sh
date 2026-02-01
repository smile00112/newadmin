#!/bin/bash

set -e

DOMAIN=$1
EMAIL=$2

if [ -z "$DOMAIN" ] || [ -z "$EMAIL" ]; then
    echo "Использование: ./scripts/get-ssl.sh domain.com email@example.com"
    exit 1
fi

echo "Получение SSL сертификата для домена: $DOMAIN"
echo "Email: $EMAIL"
echo ""

# Создание необходимых директорий
mkdir -p docker/nginx/ssl/live/$DOMAIN
mkdir -p docker/certbot/www

# Остановка Nginx для получения сертификата
echo "Остановка Nginx..."
docker compose -f docker-compose.prod.yml stop nginx || true

# Запуск временного веб-сервера для проверки домена
echo "Запуск временного веб-сервера..."
docker compose -f docker-compose.prod.yml run --rm -d --name certbot-temp \
    -p 80:80 \
    nginx:alpine \
    sh -c "echo 'Certbot verification' > /usr/share/nginx/html/index.html && nginx -g 'daemon off;'" || true

sleep 5

# Получение сертификата
echo "Получение SSL сертификата от Let's Encrypt..."
docker compose -f docker-compose.prod.yml run --rm certbot certonly \
    --webroot \
    --webroot-path=/var/www/certbot \
    --email $EMAIL \
    --agree-tos \
    --no-eff-email \
    --force-renewal \
    -d $DOMAIN \
    -d www.$DOMAIN || {
    echo "Ошибка при получении сертификата. Проверьте:"
    echo "1. Домен указывает на IP этого сервера"
    echo "2. Порт 80 открыт в firewall"
    docker compose -f docker-compose.prod.yml stop certbot-temp || true
    exit 1
}

# Остановка временного сервера
docker compose -f docker-compose.prod.yml stop certbot-temp || true
docker compose -f docker-compose.prod.yml rm -f certbot-temp || true

# Обновление конфигурации Nginx
echo "Обновление конфигурации Nginx..."
if [ -f docker/nginx/ssl.conf ]; then
    sed -i.bak "s/\${DOMAIN:-localhost}/$DOMAIN/g" docker/nginx/ssl.conf
    echo "Конфигурация обновлена"
fi

# Обновление .env файла
if [ -f .env ]; then
    if grep -q "^DOMAIN=" .env; then
        sed -i.bak "s/^DOMAIN=.*/DOMAIN=$DOMAIN/" .env
    else
        echo "DOMAIN=$DOMAIN" >> .env
    fi
    echo ".env файл обновлен"
fi

# Запуск Nginx
echo "Запуск Nginx..."
docker compose -f docker-compose.prod.yml up -d nginx

echo ""
echo "=========================================="
echo "  SSL сертификат успешно получен!"
echo "=========================================="
echo ""
echo "Сертификат находится в: docker/nginx/ssl/live/$DOMAIN/"
echo "Проверьте работу сайта: https://$DOMAIN"
echo ""
