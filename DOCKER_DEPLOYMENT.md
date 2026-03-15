# Инструкция по развертыванию проекта на Docker с RoadRunner

Полная инструкция по развертыванию проекта Bagisto на production сервере с использованием Docker, RoadRunner (Laravel Octane), MySQL, Redis и Nginx.

## Содержание

1. [Требования к системе](#требования-к-системе)
2. [Установка Docker и Docker Compose](#установка-docker-и-docker-compose)
3. [Настройка переменных окружения](#настройка-переменных-окружения)
4. [Первоначальное развертывание](#первоначальное-развертывание)
5. [Настройка RoadRunner](#настройка-roadrunner)
6. [Настройка Laravel Reverb](#настройка-laravel-reverb)
7. [Настройка домена и DNS](#настройка-домена-и-dns)
8. [Получение SSL сертификата](#получение-ssl-сертификата)
9. [Обновление проекта из Git](#обновление-проекта-из-git)
10. [Управление сервисами](#управление-сервисами)
11. [Мониторинг RoadRunner](#мониторинг-roadrunner)
12. [Резервное копирование](#резервное-копирование)
13. [Troubleshooting](#troubleshooting)

---

## Требования к системе

### Минимальные требования:

- **ОС**: Ubuntu 20.04 LTS или выше, Debian 11 или выше
- **RAM**: 2 GB (рекомендуется 4 GB)
- **CPU**: 2 ядра (рекомендуется 4 ядра)
- **Диск**: 20 GB свободного места
- **Docker**: версия 20.10 или выше
- **Docker Compose**: версия 2.0 или выше

### Рекомендуемые требования для production:

- **RAM**: 8 GB или больше
- **CPU**: 4 ядра или больше
- **Диск**: SSD с 50+ GB свободного места

---

## Установка Docker и Docker Compose

### Ubuntu/Debian:

```bash
# Обновление системы
sudo apt update
sudo apt upgrade -y

# Установка зависимостей
sudo apt install -y apt-transport-https ca-certificates curl gnupg lsb-release

# Добавление официального GPG ключа Docker
curl -fsSL https://download.docker.com/linux/ubuntu/gpg | sudo gpg --dearmor -o /usr/share/keyrings/docker-archive-keyring.gpg

# Добавление репозитория Docker
echo "deb [arch=$(dpkg --print-architecture) signed-by=/usr/share/keyrings/docker-archive-keyring.gpg] https://download.docker.com/linux/ubuntu $(lsb_release -cs) stable" | sudo tee /etc/apt/sources.list.d/docker.list > /dev/null

# Установка Docker
sudo apt update
sudo apt install -y docker-ce docker-ce-cli containerd.io docker-compose-plugin

# Проверка установки
docker --version
docker compose version

# Добавление пользователя в группу docker (опционально, для запуска без sudo)
sudo usermod -aG docker $USER
newgrp docker
```

---

## Настройка переменных окружения

1. Скопируйте файл `.env.example` в `.env`:

```bash
cp .env.example .env
```

2. Откройте файл `.env` и настройте следующие переменные:

```env
# Приложение
APP_NAME="Bagisto"
APP_ENV=production
APP_KEY=                    # Будет сгенерирован автоматически
APP_DEBUG=false
APP_URL=https://your-domain.com

# База данных
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=bagisto
DB_USERNAME=bagisto_user
DB_PASSWORD=your_secure_password
DB_ROOT_PASSWORD=your_root_password

# Redis
REDIS_HOST=redis
REDIS_PASSWORD=
REDIS_PORT=6379

# Кеш и сессии
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

# Octane (RoadRunner)
OCTANE_SERVER=roadrunner

# Laravel Reverb (WebSocket сервер)
BROADCAST_DRIVER=reverb
REVERB_APP_ID=my-app-id
REVERB_APP_KEY=my-app-key
REVERB_APP_SECRET=my-app-secret
REVERB_HOST=0.0.0.0
REVERB_PORT=8080
REVERB_SCHEME=http

# Для мобильного приложения (публичные настройки)
VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
VITE_REVERB_HOST="your-domain.com"
VITE_REVERB_PORT="443"
VITE_REVERB_SCHEME="https"

# Домен для SSL
DOMAIN=your-domain.com
```

**Важно**: 
- Используйте сильные пароли для базы данных
- Установите `APP_DEBUG=false` для production
- Укажите правильный `APP_URL` с протоколом HTTPS

---

## Первоначальное развертывание

### Автоматическое развертывание:

Используйте скрипт `deploy.sh` для автоматического развертывания:

```bash
chmod +x deploy.sh
./deploy.sh
```

Скрипт выполнит:
- Проверку зависимостей
- Создание необходимых директорий
- Сборку Docker образов
- Запуск контейнеров
- Генерацию APP_KEY
- Запуск миграций
- Оптимизацию Laravel

### Ручное развертывание:

```bash
# 1. Создание необходимых директорий
mkdir -p docker/nginx/ssl docker/nginx/logs docker/certbot/www
mkdir -p storage/logs storage/framework/{cache,sessions,views} bootstrap/cache

# 2. Установка прав доступа
chmod -R 775 storage bootstrap/cache

# 3. Сборка образов
docker compose -f docker-compose.prod.yml build

# 4. Запуск сервисов
docker compose -f docker-compose.prod.yml up -d mysql redis

# 5. Ожидание готовности сервисов (30 секунд)
sleep 30

# 6. Генерация APP_KEY
docker compose -f docker-compose.prod.yml run --rm app php artisan key:generate

# 7. Установка Laravel Reverb (если еще не установлен)
docker compose -f docker-compose.prod.yml run --rm app composer require laravel/reverb
docker compose -f docker-compose.prod.yml run --rm app php artisan reverb:install

# 8. Запуск миграций
docker compose -f docker-compose.prod.yml run --rm -e RUN_MIGRATIONS=true app php artisan migrate --force

# 9. Оптимизация Laravel
docker compose -f docker-compose.prod.yml run --rm app php artisan config:cache
docker compose -f docker-compose.prod.yml run --rm app php artisan route:cache
docker compose -f docker-compose.prod.yml run --rm app php artisan view:cache

# 10. Запуск всех сервисов
docker compose -f docker-compose.prod.yml up -d
```

---

## Настройка RoadRunner

RoadRunner уже настроен через файл `.rr.yaml`. Основные параметры:

### Конфигурация воркеров:

По умолчанию настроено 4 воркера. Для изменения отредактируйте `.rr.yaml`:

```yaml
http:
  pool:
    num_workers: 4  # Измените на нужное количество (рекомендуется: количество CPU ядер)
```

### Проверка статуса RoadRunner:

```bash
docker compose -f docker-compose.prod.yml exec app php artisan octane:status
```

### Перезагрузка RoadRunner (graceful reload):

```bash
docker compose -f docker-compose.prod.yml exec app php artisan octane:reload
```

### Остановка RoadRunner:

```bash
docker compose -f docker-compose.prod.yml exec app php artisan octane:stop
```

---

## Настройка Laravel Reverb

Laravel Reverb - это официальный WebSocket сервер от Laravel для трансляции событий в реальном времени. Он используется для отправки уведомлений в мобильные приложения и веб-интерфейсы.

### Установка Reverb:

Reverb должен быть установлен перед первым запуском. Если он еще не установлен, выполните:

```bash
# Установка пакета
docker compose -f docker-compose.prod.yml run --rm app composer require laravel/reverb

# Установка конфигурации
docker compose -f docker-compose.prod.yml run --rm app php artisan reverb:install
```

**Примечание**: После установки Reverb убедитесь, что файл `config/reverb.php` создан и содержит правильные настройки.

### Конфигурация Reverb:

Reverb уже настроен в `docker-compose.prod.yml` и запускается автоматически. Основные параметры:

- **Порт**: 8080 (внутри Docker сети)
- **Доступ**: через Nginx proxy на `/app/`
- **Протокол**: WebSocket (ws://) или Secure WebSocket (wss://)

### Настройка переменных окружения:

В файле `.env` настройте следующие переменные:

```env
BROADCAST_DRIVER=reverb
REVERB_APP_ID=my-app-id
REVERB_APP_KEY=my-app-key
REVERB_APP_SECRET=my-app-secret
REVERB_HOST=0.0.0.0
REVERB_PORT=8080
REVERB_SCHEME=http

# Публичные настройки для клиентов (мобильное приложение)
VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
VITE_REVERB_HOST="your-domain.com"
VITE_REVERB_PORT="443"
VITE_REVERB_SCHEME="https"
```

**Важно**: 
- `REVERB_APP_ID`, `REVERB_APP_KEY` и `REVERB_APP_SECRET` должны быть уникальными и безопасными
- Для production используйте HTTPS (`REVERB_SCHEME=https`, `VITE_REVERB_SCHEME=https`)
- `VITE_REVERB_HOST` должен указывать на ваш домен

### Проверка статуса Reverb:

```bash
# Проверка статуса контейнера
docker compose -f docker-compose.prod.yml ps reverb

# Проверка доступности порта
docker compose -f docker-compose.prod.yml exec reverb nc -z localhost 8080
```

### Просмотр логов Reverb:

```bash
docker compose -f docker-compose.prod.yml logs -f reverb
```

### Перезапуск Reverb:

```bash
docker compose -f docker-compose.prod.yml restart reverb
```

### Адреса подключения для клиентов:

#### Для веб-приложения (через Nginx):

```javascript
wsHost: 'your-domain.com'
wsPort: 443
wssPort: 443
wsPath: '/app'
```

Полный URL: `wss://your-domain.com/app/`

#### Для мобильного приложения:

**React Native / Flutter:**
```javascript
import Echo from 'laravel-echo';
import Pusher from 'pusher-js/react-native';

const echo = new Echo({
  broadcaster: 'pusher',
  key: 'my-app-key',  // REVERB_APP_KEY из .env
  wsHost: 'your-domain.com',
  wsPort: 443,
  wssPort: 443,
  wsPath: '/app',
  forceTLS: true,
  enabledTransports: ['ws', 'wss'],
});

// Подписка на канал уведомлений
echo.channel('notification')
  .listen('.create-notification', (e) => {
    console.log('New order created:', e);
  })
  .listen('.update-notification', (e) => {
    console.log('Order updated:', e);
  });
```

**Нативный iOS (Swift):**
```swift
import PusherSwift

let options = PusherClientOptions(
    host: .host("your-domain.com"),
    port: 443,
    encrypted: true,
    authMethod: .endpoint(authEndpoint: "https://your-domain.com/broadcasting/auth")
)

let pusher = Pusher(
    key: "my-app-key",
    options: options
)

pusher.connect()

let channel = pusher.subscribe("notification")
channel.bind(eventName: "create-notification") { (event: PusherEvent) in
    print("New order: \(event.data ?? "")")
}
```

**Нативный Android (Kotlin):**
```kotlin
val options = PusherOptions()
options.setHost("your-domain.com")
options.setPort(443)
options.setEncrypted(true)

val pusher = Pusher("my-app-key", options)
pusher.connect()

val channel = pusher.subscribe("notification")
channel.bind("create-notification") { event ->
    Log.d("Notification", "New order: ${event.data}")
}
```

### Настройка Nginx:

Nginx уже настроен для проксирования WebSocket соединений к Reverb через путь `/app/`. Конфигурация находится в `docker/nginx/default.conf`.

### Проверка работы Reverb:

1. **Проверьте статус контейнера:**
```bash
docker compose -f docker-compose.prod.yml ps reverb
```

2. **Проверьте логи:**
```bash
docker compose -f docker-compose.prod.yml logs reverb
```

3. **Проверьте подключение через браузер:**
Откройте DevTools → Network → WS и попробуйте подключиться к `wss://your-domain.com/app/`

### Troubleshooting Reverb:

**Проблема: Reverb не запускается**

```bash
# Проверьте логи
docker compose -f docker-compose.prod.yml logs reverb

# Проверьте переменные окружения
docker compose -f docker-compose.prod.yml exec reverb env | grep REVERB

# Перезапустите Reverb
docker compose -f docker-compose.prod.yml restart reverb
```

**Проблема: WebSocket соединение не устанавливается**

- Убедитесь, что Nginx правильно проксирует запросы к Reverb
- Проверьте, что используется HTTPS (wss://) для production
- Проверьте файрвол и убедитесь, что порты открыты
- Проверьте логи Nginx: `docker compose -f docker-compose.prod.yml logs nginx`

**Проблема: События не транслируются**

- Убедитесь, что `BROADCAST_DRIVER=reverb` в `.env`
- Проверьте, что очередь `broadcastable` обрабатывается: `docker compose -f docker-compose.prod.yml logs queue`
- Убедитесь, что события реализуют интерфейс `ShouldBroadcast`

---

## Настройка домена и DNS

1. **Настройте DNS записи** на вашем DNS провайдере:

```
A запись: your-domain.com -> IP_адрес_сервера
A запись: www.your-domain.com -> IP_адрес_сервера
```

2. **Обновите `.env` файл**:

```env
APP_URL=https://your-domain.com
DOMAIN=your-domain.com
```

3. **Обновите конфигурацию Nginx** (если нужно изменить домен):

Отредактируйте `docker/nginx/default.conf` и замените `server_name _;` на `server_name your-domain.com www.your-domain.com;`

4. **Перезапустите Nginx**:

```bash
docker compose -f docker-compose.prod.yml restart nginx
```

---

## Получение SSL сертификата

### Автоматическое получение через Let's Encrypt:

1. **Создайте скрипт для получения сертификата** (`scripts/get-ssl.sh`):

```bash
#!/bin/bash
DOMAIN=$1
EMAIL=$2

if [ -z "$DOMAIN" ] || [ -z "$EMAIL" ]; then
    echo "Использование: ./scripts/get-ssl.sh domain.com email@example.com"
    exit 1
fi

# Остановка Nginx для получения сертификата
docker compose -f docker-compose.prod.yml stop nginx

# Получение сертификата
docker compose -f docker-compose.prod.yml run --rm certbot certonly \
    --webroot \
    --webroot-path=/var/www/certbot \
    --email $EMAIL \
    --agree-tos \
    --no-eff-email \
    -d $DOMAIN \
    -d www.$DOMAIN

# Обновление конфигурации Nginx
sed -i "s/\${DOMAIN:-localhost}/$DOMAIN/g" docker/nginx/ssl.conf

# Запуск Nginx
docker compose -f docker-compose.prod.yml start nginx
```

2. **Выполните скрипт**:

```bash
chmod +x scripts/get-ssl.sh
./scripts/get-ssl.sh your-domain.com your-email@example.com
```

### Ручная установка сертификатов:

1. Скопируйте сертификаты в `docker/nginx/ssl/live/your-domain.com/`:

```bash
mkdir -p docker/nginx/ssl/live/your-domain.com
cp fullchain.pem docker/nginx/ssl/live/your-domain.com/
cp privkey.pem docker/nginx/ssl/live/your-domain.com/
cp chain.pem docker/nginx/ssl/live/your-domain.com/
```

2. Обновите `docker/nginx/ssl.conf`:

```nginx
ssl_certificate /etc/nginx/ssl/live/your-domain.com/fullchain.pem;
ssl_certificate_key /etc/nginx/ssl/live/your-domain.com/privkey.pem;
```

3. Перезапустите Nginx:

```bash
docker compose -f docker-compose.prod.yml restart nginx
```

### Автоматическое обновление сертификатов:

Certbot автоматически обновляет сертификаты каждые 12 часов через контейнер `certbot`.

---

## Обновление проекта из Git

### Автоматическое обновление:

Используйте скрипт `update.sh`:

```bash
chmod +x update.sh
./update.sh
```

Скрипт выполнит:
- Получение обновлений из Git
- Обновление зависимостей
- Запуск миграций
- Очистку кеша
- Graceful reload RoadRunner
- Перезапуск сервисов

### Ручное обновление:

```bash
# 1. Получение обновлений
git pull origin main

# 2. Обновление зависимостей
docker compose -f docker-compose.prod.yml run --rm app composer install --no-dev --optimize-autoloader

# 3. Запуск миграций
docker compose -f docker-compose.prod.yml run --rm app php artisan migrate --force

# 4. Очистка кеша
docker compose -f docker-compose.prod.yml run --rm app php artisan cache:clear
docker compose -f docker-compose.prod.yml run --rm app php artisan config:clear
docker compose -f docker-compose.prod.yml run --rm app php artisan route:clear
docker compose -f docker-compose.prod.yml run --rm app php artisan view:clear

# 5. Оптимизация
docker compose -f docker-compose.prod.yml run --rm app php artisan config:cache
docker compose -f docker-compose.prod.yml run --rm app php artisan route:cache
docker compose -f docker-compose.prod.yml run --rm app php artisan view:cache

# 6. Graceful reload RoadRunner
docker compose -f docker-compose.prod.yml exec app php artisan octane:reload

# 7. Перезапуск queue worker
docker compose -f docker-compose.prod.yml restart queue

# 8. Перезапуск Reverb (если нужно)
docker compose -f docker-compose.prod.yml restart reverb
```

---

## Управление сервисами

### Просмотр статуса:

```bash
docker compose -f docker-compose.prod.yml ps
```

### Просмотр логов:

```bash
# Все сервисы
docker compose -f docker-compose.prod.yml logs -f

# Конкретный сервис
docker compose -f docker-compose.prod.yml logs -f app
docker compose -f docker-compose.prod.yml logs -f nginx
docker compose -f docker-compose.prod.yml logs -f mysql
docker compose -f docker-compose.prod.yml logs -f redis
docker compose -f docker-compose.prod.yml logs -f queue
docker compose -f docker-compose.prod.yml logs -f reverb
```

### Остановка сервисов:

```bash
docker compose -f docker-compose.prod.yml stop
```

### Запуск сервисов:

```bash
docker compose -f docker-compose.prod.yml start
```

### Перезапуск сервисов:

```bash
# Все сервисы
docker compose -f docker-compose.prod.yml restart

# Конкретный сервис
docker compose -f docker-compose.prod.yml restart app
docker compose -f docker-compose.prod.yml restart nginx
```

### Остановка и удаление контейнеров:

```bash
docker compose -f docker-compose.prod.yml down
```

### Остановка и удаление с данными:

```bash
# ВНИМАНИЕ: Это удалит все данные из базы данных!
docker compose -f docker-compose.prod.yml down -v
```

---

## Мониторинг RoadRunner

### Статус Octane:

```bash
docker compose -f docker-compose.prod.yml exec app php artisan octane:status
```

### Метрики RoadRunner:

RoadRunner предоставляет метрики на порту 2112. Для доступа к метрикам:

```bash
# Внутри контейнера
docker compose -f docker-compose.prod.yml exec app wget -qO- http://localhost:2112/metrics
```

### Мониторинг производительности:

```bash
# Использование ресурсов
docker stats

# Логи приложения
docker compose -f docker-compose.prod.yml logs -f app | grep -i error
```

---

## Резервное копирование

### Резервное копирование базы данных:

```bash
# Создание бэкапа
docker compose -f docker-compose.prod.yml exec mysql mysqldump -u root -p${DB_ROOT_PASSWORD} ${DB_DATABASE} > backup_$(date +%Y%m%d_%H%M%S).sql

# Восстановление из бэкапа
docker compose -f docker-compose.prod.yml exec -T mysql mysql -u root -p${DB_ROOT_PASSWORD} ${DB_DATABASE} < backup.sql
```

### Резервное копирование файлов:

```bash
# Создание архива
tar -czf storage_backup_$(date +%Y%m%d_%H%M%S).tar.gz storage/

# Восстановление
tar -xzf storage_backup_YYYYMMDD_HHMMSS.tar.gz
```

### Автоматическое резервное копирование:

Создайте cron задачу для автоматического бэкапа:

```bash
# Добавьте в crontab (crontab -e)
0 2 * * * cd /path/to/project && docker compose -f docker-compose.prod.yml exec -T mysql mysqldump -u root -p${DB_ROOT_PASSWORD} ${DB_DATABASE} > /backups/db_$(date +\%Y\%m\%d).sql
```

---

## Troubleshooting

### Проблема: Контейнеры не запускаются

**Решение:**
```bash
# Проверьте логи
docker compose -f docker-compose.prod.yml logs

# Проверьте статус
docker compose -f docker-compose.prod.yml ps

# Пересоберите образы
docker compose -f docker-compose.prod.yml build --no-cache
```

### Проблема: Ошибка подключения к базе данных

**Решение:**
```bash
# Проверьте, запущен ли MySQL
docker compose -f docker-compose.prod.yml ps mysql

# Проверьте логи MySQL
docker compose -f docker-compose.prod.yml logs mysql

# Проверьте переменные окружения
docker compose -f docker-compose.prod.yml exec app env | grep DB_
```

### Проблема: RoadRunner не запускается

**Решение:**
```bash
# Проверьте конфигурацию
docker compose -f docker-compose.prod.yml exec app php artisan octane:status

# Проверьте логи
docker compose -f docker-compose.prod.yml logs app

# Перезапустите RoadRunner
docker compose -f docker-compose.prod.yml exec app php artisan octane:reload
```

### Проблема: SSL сертификат не работает

**Решение:**
```bash
# Проверьте наличие сертификатов
ls -la docker/nginx/ssl/live/your-domain.com/

# Проверьте конфигурацию Nginx
docker compose -f docker-compose.prod.yml exec nginx nginx -t

# Перезапустите Nginx
docker compose -f docker-compose.prod.yml restart nginx
```

### Проблема: Высокое использование памяти

**Решение:**
- Уменьшите количество воркеров RoadRunner в `.rr.yaml`
- Проверьте на утечки памяти в коде
- Увеличьте лимит памяти в `docker-compose.prod.yml`

### Проблема: Медленная работа приложения

**Решение:**
- Проверьте настройки кеширования (Redis)
- Убедитесь, что Opcache включен
- Проверьте оптимизацию Laravel (config:cache, route:cache)
- Увеличьте количество воркеров RoadRunner

### Проблема: Reverb не работает или события не транслируются

**Решение:**
```bash
# Проверьте статус Reverb
docker compose -f docker-compose.prod.yml ps reverb

# Проверьте логи Reverb
docker compose -f docker-compose.prod.yml logs reverb

# Проверьте, что BROADCAST_DRIVER=reverb в .env
docker compose -f docker-compose.prod.yml exec app env | grep BROADCAST_DRIVER

# Проверьте очередь broadcastable
docker compose -f docker-compose.prod.yml logs queue | grep broadcastable

# Перезапустите Reverb
docker compose -f docker-compose.prod.yml restart reverb
```

---

## Дополнительные ресурсы

- [Документация Laravel Octane](https://laravel.com/docs/octane)
- [Документация Laravel Reverb](https://laravel.com/docs/reverb)
- [Документация RoadRunner](https://roadrunner.dev/docs)
- [Документация Docker](https://docs.docker.com/)
- [Документация Nginx](https://nginx.org/en/docs/)

---

## Поддержка

При возникновении проблем:
1. Проверьте логи: `docker compose -f docker-compose.prod.yml logs`
2. Проверьте статус сервисов: `docker compose -f docker-compose.prod.yml ps`
3. Обратитесь к разделу Troubleshooting выше

---

**Последнее обновление**: 2024
