# Быстрый старт с Docker и RoadRunner

## Быстрое развертывание

### 1. Подготовка

```bash
# Скопируйте .env.example в .env и настройте переменные
cp .env.example .env
nano .env  # Отредактируйте необходимые переменные
```

### 2. Развертывание

```bash
# Автоматическое развертывание
chmod +x deploy.sh
./deploy.sh
```

### 3. Получение SSL сертификата

```bash
# После настройки DNS
chmod +x scripts/get-ssl.sh
./scripts/get-ssl.sh your-domain.com your-email@example.com
```

### 4. Обновление проекта

```bash
chmod +x update.sh
./update.sh
```

## Основные команды

```bash
# Просмотр статуса
docker compose -f docker-compose.prod.yml ps

# Просмотр логов
docker compose -f docker-compose.prod.yml logs -f

# Остановка
docker compose -f docker-compose.prod.yml stop

# Запуск
docker compose -f docker-compose.prod.yml start

# Перезапуск RoadRunner
docker compose -f docker-compose.prod.yml exec app php artisan octane:reload
```

## Структура проекта

```
.
├── Dockerfile                 # Production образ с RoadRunner
├── docker-compose.prod.yml    # Production конфигурация
├── .rr.yaml                   # Конфигурация RoadRunner
├── deploy.sh                  # Скрипт развертывания
├── update.sh                  # Скрипт обновления
├── docker/
│   ├── nginx/                 # Конфигурации Nginx
│   ├── php/                   # PHP конфигурации
│   └── mysql/                 # MySQL инициализация
└── scripts/
    └── get-ssl.sh             # Получение SSL сертификата
```

## Документация

Полная документация доступна в файле [DOCKER_DEPLOYMENT.md](DOCKER_DEPLOYMENT.md)
