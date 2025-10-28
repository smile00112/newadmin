# FCM Push Уведомления - Инструкция по настройке

## 📋 Обзор

В админ панель Dolinger интегрирована система Firebase Cloud Messaging (FCM) для отправки push-уведомлений администраторам.

## 🔧 Настройка Firebase

### 1. Получение Service Account Key

1. Перейдите в [Firebase Console](https://console.firebase.google.com/)
2. Выберите ваш проект: **couriers-3473b**
3. Перейдите в **Project Settings** (⚙️) → **Service accounts**
4. Нажмите **Generate new private key**
5. Скачайте JSON файл

### 2. Установка credentials

1. Переименуйте скачанный файл в `firebase-credentials.json`
2. Поместите файл в корень проекта (рядом с `composer.json`)
3. Убедитесь что файл добавлен в `.gitignore`:

```
# Firebase credentials
firebase-credentials.json
```

### 3. Получение VAPID Key

1. В Firebase Console перейдите в **Project Settings** → **Cloud Messaging**
2. Во вкладке **Web Push certificates** найдите или создайте **Key pair**
3. Скопируйте ключ (формат: `xxxxxxxxxxxxxxxxxxxxxxxxxxxxx`)
4. Обновите `vapidKey` в файле `packages/Webkul/Admin/src/Resources/views/components/layouts/index.blade.php`:

```javascript
this.vapidKey = "ВАШ_VAPID_KEY_ЗДЕСЬ";
```

## 📁 Структура файлов

```
├── firebase-credentials.json          # Service Account (НЕ коммитить!)
├── firebase-credentials.json.example  # Пример структуры файла
├── public/
│   └── firebase-messaging-sw.js       # Service Worker для FCM
├── packages/Webkul/Admin/
│   ├── src/
│   │   ├── Services/
│   │   │   └── FcmNotificationService.php   # Сервис для отправки уведомлений
│   │   ├── Http/Controllers/User/
│   │   │   ├── FcmTokenController.php       # Сохранение FCM токенов
│   │   │   └── FcmNotificationController.php # Отправка тестовых уведомлений
│   │   ├── Resources/views/fcm/
│   │   │   └── test.blade.php               # Страница тестирования
│   │   └── Routes/
│   │       └── fcm-token-routes.php         # Маршруты FCM
│   └── ...
└── packages/Webkul/User/
    └── src/
        ├── Models/
        │   └── Admin.php                     # Модель с полем fcm_token
        └── Database/Migrations/
            └── 2025_10_22_084858_add_fcm_token_field_to_admins_table.php
```

## 🚀 Использование

### Автоматическая подписка

При входе в админ панель:
1. Браузер запросит разрешение на уведомления
2. После разрешения FCM токен автоматически сохранится в базе
3. Токен будет обновляться при каждом входе

### Тестовая страница

Откройте страницу тестирования:
```
http://your-domain/admin/fcm/test-page
```

На странице вы можете:
- ✅ Проверить статус FCM токена
- 📤 Отправить тестовое уведомление себе
- 📢 Отправить уведомление всем администраторам

### Программная отправка

#### Отправка одному пользователю:

```php
use Webkul\Admin\Services\FcmNotificationService;

$fcmService = app(FcmNotificationService::class);

$fcmService->sendToDevice(
    $admin->fcm_token,
    'Заголовок уведомления',
    'Текст уведомления',
    ['custom_data' => 'value']
);
```

#### Отправка всем администраторам:

```php
$fcmService = app(FcmNotificationService::class);

$fcmService->sendToAllAdmins(
    'Важное объявление',
    'Это сообщение увидят все администраторы',
    ['type' => 'announcement']
);
```

#### Отправка нескольким пользователям:

```php
$tokens = ['token1', 'token2', 'token3'];

$fcmService->sendToMultipleDevices(
    $tokens,
    'Групповое уведомление',
    'Сообщение для группы админов'
);
```

## 🔔 Примеры использования

### Уведомление о новом заказе:

```php
// В контроллере заказов
use Webkul\Admin\Services\FcmNotificationService;

public function store(Request $request)
{
    $order = $this->orderRepository->create($data);
    
    // Отправить уведомление всем админам
    $fcmService = app(FcmNotificationService::class);
    $fcmService->sendToAllAdmins(
        'Новый заказ #' . $order->id,
        'Клиент: ' . $order->customer_name . ', Сумма: ' . $order->total,
        [
            'type' => 'new_order',
            'order_id' => $order->id,
            'url' => route('admin.sales.orders.view', $order->id)
        ]
    );
    
    return response()->json(['success' => true]);
}
```

### Уведомление о новом сообщении в чате:

```php
// В обсервере CustomerNumber
use Webkul\Admin\Services\FcmNotificationService;

public function updated(CustomerNumber $customerNumber)
{
    if ($customerNumber->isDirty('incoming_message') && $customerNumber->incoming_message) {
        $fcmService = app(FcmNotificationService::class);
        
        $fcmService->sendToAllAdmins(
            '💬 Новое сообщение',
            'От: ' . $customerNumber->name . ' (' . $customerNumber->phone_number . ')',
            [
                'type' => 'new_message',
                'customer_id' => $customerNumber->id,
                'url' => route('admin.newsletters.messages.index')
            ]
        );
    }
}
```

## 🛠️ API Endpoints

### POST `/admin/fcm-token`
Сохранение FCM токена пользователя (автоматически)

**Request:**
```json
{
  "fcm_token": "xxxxxxxxxxxxx"
}
```

### GET `/admin/fcm/test-page`
Страница тестирования FCM уведомлений

### POST `/admin/fcm/send-test`
Отправка тестового уведомления текущему пользователю

**Request:**
```json
{
  "title": "Тестовый заголовок",
  "body": "Тестовое сообщение"
}
```

### POST `/admin/fcm/send-all`
Отправка уведомления всем администраторам

**Request:**
```json
{
  "title": "Заголовок",
  "body": "Текст сообщения"
}
```

## ❗ Важные замечания

1. **HTTPS обязателен** для Service Workers (кроме localhost)
2. Файл `firebase-credentials.json` **НЕ должен** попадать в git
3. Service Worker (`firebase-messaging-sw.js`) должен находиться в корне `public/`
4. VAPID Key уникален для каждого проекта Firebase
5. FCM токены могут изменяться, поэтому они обновляются при каждом входе

## 🐛 Troubleshooting

### Уведомления не приходят

1. Проверьте что `firebase-credentials.json` существует и валиден
2. Проверьте логи Laravel: `storage/logs/laravel.log`
3. Проверьте консоль браузера на ошибки Service Worker
4. Убедитесь что разрешения на уведомления выданы в браузере

### Service Worker не регистрируется

1. Проверьте что файл доступен: `http://your-domain/firebase-messaging-sw.js`
2. Проверьте что используется HTTPS (или localhost)
3. Очистите кэш браузера и Service Workers

### Токен не сохраняется

1. Проверьте что миграция выполнена: `php artisan migrate`
2. Проверьте что поле `fcm_token` есть в `$fillable` модели `Admin`
3. Проверьте сетевые запросы в DevTools

## 📊 База данных

Таблица `admins` содержит поле:
- `fcm_token` (VARCHAR 255, nullable) - FCM токен для push уведомлений

Миграция: `2025_10_22_084858_add_fcm_token_field_to_admins_table.php`

## 🔐 Безопасность

- Service Account Key хранится на сервере и НЕ доступен клиенту
- Все запросы на отправку уведомлений проходят через middleware `admin`
- FCM токены привязаны к конкретным пользователям
- Используется официальная библиотека `kreait/firebase-php`

## 📚 Дополнительные ресурсы

- [Firebase Cloud Messaging Documentation](https://firebase.google.com/docs/cloud-messaging)
- [kreait/firebase-php Library](https://github.com/kreait/firebase-php)
- [Service Workers API](https://developer.mozilla.org/en-US/docs/Web/API/Service_Worker_API)

