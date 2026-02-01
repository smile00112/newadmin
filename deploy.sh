#!/bin/bash

set -e

echo "=========================================="
echo "  Bagisto Docker Deployment Script"
echo "=========================================="
echo ""

# Цвета для вывода
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Функция для вывода сообщений
info() {
    echo -e "${GREEN}[INFO]${NC} $1"
}

warn() {
    echo -e "${YELLOW}[WARN]${NC} $1"
}

error() {
    echo -e "${RED}[ERROR]${NC} $1"
    exit 1
}

# Проверка зависимостей
info "Проверка зависимостей..."

if ! command -v docker &> /dev/null; then
    error "Docker не установлен. Установите Docker и повторите попытку."
fi

if ! command -v docker-compose &> /dev/null && ! docker compose version &> /dev/null; then
    error "Docker Compose не установлен. Установите Docker Compose и повторите попытку."
fi

info "Docker и Docker Compose установлены ✓"

# Проверка .env файла
if [ ! -f .env ]; then
    warn ".env файл не найден. Создание из .env.example..."
    if [ -f .env.example ]; then
        cp .env.example .env
        info ".env файл создан из .env.example"
        warn "ВАЖНО: Отредактируйте .env файл перед продолжением!"
        read -p "Нажмите Enter после редактирования .env файла..."
    else
        error ".env.example файл не найден. Создайте .env файл вручную."
    fi
fi

# Загрузка переменных из .env
source .env

# Проверка обязательных переменных
if [ -z "$APP_KEY" ] || [ "$APP_KEY" == "" ]; then
    warn "APP_KEY не установлен. Генерация нового ключа..."
    # Ключ будет сгенерирован в контейнере
fi

# Создание необходимых директорий
info "Создание необходимых директорий..."
mkdir -p docker/nginx/ssl
mkdir -p docker/nginx/ssl-logs
mkdir -p docker/nginx/logs
mkdir -p docker/certbot/www
mkdir -p storage/logs
mkdir -p storage/framework/cache
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p bootstrap/cache

# Установка прав доступа
info "Установка прав доступа..."
chmod -R 775 storage bootstrap/cache
chown -R $USER:$USER storage bootstrap/cache 2>/dev/null || true

# Сборка Docker образов
info "Сборка Docker образов..."
docker-compose -f docker-compose.prod.yml build --no-cache

# Запуск контейнеров
info "Запуск контейнеров..."
docker-compose -f docker-compose.prod.yml up -d mysql redis

# Ожидание готовности MySQL
info "Ожидание готовности MySQL..."
sleep 10
until docker-compose -f docker-compose.prod.yml exec -T mysql mysqladmin ping -h localhost --silent; do
    echo "Ожидание MySQL..."
    sleep 2
done
info "MySQL готов ✓"

# Ожидание готовности Redis
info "Ожидание готовности Redis..."
until docker-compose -f docker-compose.prod.yml exec -T redis redis-cli ping | grep -q PONG; do
    echo "Ожидание Redis..."
    sleep 2
done
info "Redis готов ✓"

# Генерация APP_KEY если нужно
if [ -z "$APP_KEY" ] || [ "$APP_KEY" == "" ]; then
    info "Генерация APP_KEY..."
    docker-compose -f docker-compose.prod.yml run --rm app php artisan key:generate
fi

# Запуск миграций
info "Запуск миграций базы данных..."
docker-compose -f docker-compose.prod.yml run --rm -e RUN_MIGRATIONS=true app php artisan migrate --force

# Оптимизация Laravel
info "Оптимизация Laravel для production..."
docker-compose -f docker-compose.prod.yml run --rm app php artisan config:cache
docker-compose -f docker-compose.prod.yml run --rm app php artisan route:cache
docker-compose -f docker-compose.prod.yml run --rm app php artisan view:cache
docker-compose -f docker-compose.prod.yml run --rm app php artisan event:cache

# Запуск всех сервисов
info "Запуск всех сервисов..."
docker-compose -f docker-compose.prod.yml up -d

# Ожидание готовности приложения
info "Ожидание готовности приложения..."
sleep 15

# Проверка статуса
info "Проверка статуса сервисов..."
docker-compose -f docker-compose.prod.yml ps

# Информация о SSL
echo ""
warn "=========================================="
warn "  ВАЖНО: Настройка SSL сертификата"
warn "=========================================="
echo ""
info "Для получения SSL сертификата выполните:"
echo "  ./scripts/get-ssl.sh your-domain.com your-email@example.com"
echo ""
info "Или настройте SSL вручную, следуя инструкциям в DOCKER_DEPLOYMENT.md"
echo ""

# Финальная информация
echo ""
info "=========================================="
info "  Развертывание завершено!"
info "=========================================="
echo ""
info "Приложение доступно по адресу: ${APP_URL:-http://localhost}"
info "Для просмотра логов: docker-compose -f docker-compose.prod.yml logs -f"
info "Для остановки: docker-compose -f docker-compose.prod.yml down"
echo ""
