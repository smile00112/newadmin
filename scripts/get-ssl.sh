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
mkdir -p docker/certbot/www

# Остановка Nginx (освобождаем порт 80 для standalone certbot)
echo "Остановка Nginx..."
docker compose -f docker-compose.prod.yml stop nginx || true

# Создание необходимых директорий (только корень, НЕ live/$DOMAIN — certbot создаст сам)
mkdir -p docker/nginx/ssl

# Получение сертификата через standalone (certbot поднимает временный HTTP-сервер на порту 80)
echo "Получение SSL сертификата от Let's Encrypt (standalone)..."
docker run --rm \
    -v "$(pwd)/docker/nginx/ssl:/etc/letsencrypt" \
    -p 80:80 \
    certbot/certbot certonly --standalone \
    --email "$EMAIL" \
    --agree-tos \
    --no-eff-email \
    --force-renewal \
    -d "$DOMAIN" || {
    echo "Ошибка при получении сертификата. Проверьте:"
    echo "1. Домен указывает на IP этого сервера"
    echo "2. Порт 80 открыт в firewall"
    docker compose -f docker-compose.prod.yml start nginx || true
    exit 1
}

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
docker compose -f docker-compose.prod.yml start nginx

echo ""
echo "=========================================="
echo "  SSL сертификат успешно получен!"
echo "=========================================="
echo ""
echo "Сертификат находится в: docker/nginx/ssl/live/$DOMAIN/"
echo "Проверьте работу сайта: https://$DOMAIN"
echo ""
