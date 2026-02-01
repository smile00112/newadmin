#!/bin/bash

set -e

echo "=========================================="
echo "  Bagisto Docker Update Script"
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

# Проверка Git
if [ ! -d .git ]; then
    error "Это не Git репозиторий. Обновление из Git невозможно."
fi

# Проверка изменений
if [ -n "$(git status --porcelain)" ]; then
    warn "Обнаружены незакоммиченные изменения. Продолжить? (y/n)"
    read -r response
    if [[ ! "$response" =~ ^[Yy]$ ]]; then
        info "Обновление отменено."
        exit 0
    fi
fi

# Получение текущей ветки
CURRENT_BRANCH=$(git rev-parse --abbrev-ref HEAD)
info "Текущая ветка: $CURRENT_BRANCH"

# Обновление кода из Git
info "Получение обновлений из Git..."
git fetch origin

# Проверка наличия обновлений
LOCAL=$(git rev-parse @)
REMOTE=$(git rev-parse @{u})

if [ "$LOCAL" = "$REMOTE" ]; then
    info "Нет новых обновлений. Приложение уже актуально."
    exit 0
fi

info "Обнаружены новые обновления. Начинаю обновление..."

# Переключение на последнюю версию
info "Обновление кода..."
git pull origin "$CURRENT_BRANCH"

# Обновление зависимостей Composer
info "Обновление зависимостей Composer..."
docker-compose -f docker-compose.prod.yml run --rm app composer install --no-dev --optimize-autoloader --no-interaction

# Обновление зависимостей NPM (если нужно)
if [ -f package.json ]; then
    info "Обновление зависимостей NPM..."
    docker-compose -f docker-compose.prod.yml run --rm app npm install --production
    docker-compose -f docker-compose.prod.yml run --rm app npm run build
fi

# Пересборка образов (если нужно)
info "Проверка изменений в Dockerfile..."
if git diff HEAD@{1} HEAD --name-only | grep -q -E "(Dockerfile|docker-compose.prod.yml|\.rr\.yaml)"; then
    warn "Обнаружены изменения в Docker конфигурации. Пересборка образов..."
    docker-compose -f docker-compose.prod.yml build
fi

# Запуск миграций
info "Запуск миграций базы данных..."
docker-compose -f docker-compose.prod.yml run --rm app php artisan migrate --force

# Очистка кеша
info "Очистка кеша..."
docker-compose -f docker-compose.prod.yml run --rm app php artisan cache:clear
docker-compose -f docker-compose.prod.yml run --rm app php artisan config:clear
docker-compose -f docker-compose.prod.yml run --rm app php artisan route:clear
docker-compose -f docker-compose.prod.yml run --rm app php artisan view:clear

# Оптимизация Laravel
info "Оптимизация Laravel для production..."
docker-compose -f docker-compose.prod.yml run --rm app php artisan config:cache
docker-compose -f docker-compose.prod.yml run --rm app php artisan route:cache
docker-compose -f docker-compose.prod.yml run --rm app php artisan view:cache
docker-compose -f docker-compose.prod.yml run --rm app php artisan event:cache

# Graceful reload RoadRunner
info "Перезагрузка RoadRunner (graceful reload)..."
docker-compose -f docker-compose.prod.yml exec app php artisan octane:reload || {
    warn "Не удалось выполнить graceful reload. Перезапуск контейнера..."
    docker-compose -f docker-compose.prod.yml restart app
}

# Перезапуск queue worker
info "Перезапуск queue worker..."
docker-compose -f docker-compose.prod.yml restart queue

# Перезапуск Nginx (если нужно)
if git diff HEAD@{1} HEAD --name-only | grep -q "docker/nginx/"; then
    info "Обнаружены изменения в конфигурации Nginx. Перезапуск..."
    docker-compose -f docker-compose.prod.yml exec nginx nginx -t
    docker-compose -f docker-compose.prod.yml exec nginx nginx -s reload
fi

# Проверка статуса
info "Проверка статуса сервисов..."
docker-compose -f docker-compose.prod.yml ps

echo ""
info "=========================================="
info "  Обновление завершено!"
info "=========================================="
echo ""
info "Все сервисы обновлены и перезапущены."
info "Для просмотра логов: docker-compose -f docker-compose.prod.yml logs -f"
echo ""
