# 🚀 FCM Push Notifications - Быстрый старт

## ✅ Что уже сделано

1. ✅ Миграция добавлена (поле `fcm_token` в таблице `admins`)
2. ✅ FCM скрипты подключены в админ панели
3. ✅ Service Worker настроен (`public/firebase-messaging-sw.js`)
4. ✅ Контроллеры и сервисы созданы
5. ✅ Тестовая страница готова
6. ✅ Firebase credentials подготовлен (нужно заменить на реальный)

## 🔑 Что нужно сделать СЕЙЧАС

### 1. Получить правильный VAPID Key

**ВАЖНО!** Текущий VAPID ключ - это placeholder. Нужно получить реальный:

1. Откройте [Firebase Console](https://console.firebase.google.com/)
2. Выберите проект: **couriers-3473b**
3. Перейдите: **⚙️ Project Settings** → **Cloud Messaging**
4. Найдите раздел **Web Push certificates**
5. Если ключа нет - нажмите **Generate key pair**
6. Скопируйте **Key pair** (длинная строка вида: `BPGJluaMnJq6p5MXx...`)

### 2. Обновить VAPID Key в коде

Откройте файл:
```
packages/Webkul/Admin/src/Resources/views/components/layouts/index.blade.php
```

Найдите строку (около line 169):
```javascript
const VAPID_KEY = "BPGJluaMnJq6p5MXxYOYW52FCWPRgFl4V0LUCNVmjSjpPj1a5kD7zGz9_o_xPjmzQa-b0DhVJyP3F6SvY6AFZQY";
```

Замените на ваш реальный ключ:
```javascript
const VAPID_KEY = "ВАШ_РЕАЛЬНЫЙ_VAPID_KEY";
```

### 3. Проверить Service Account JSON

Файл `firebase-credentials.json` уже создан с реальными данными из вашего проекта.

**Убедитесь что он НЕ попадёт в git** (уже добавлен в `.gitignore`).

### 4. Протестировать

1. Откройте браузер и перейдите в админ панель
2. Разрешите уведомления когда браузер спросит
3. Откройте консоль разработчика (F12)
4. Должны увидеть:
   ```
   ✅ FCM: Service Worker registered
   FCM: Permission status: granted
   ✅ FCM Token obtained: xxxxx...
   ✅ FCM: Token saved to server
   ```

5. Перейдите на страницу тестирования:
   ```
   http://your-domain/admin/fcm/test-page
   ```

6. Нажмите **"Отправить себе"**

7. Должно прийти push-уведомление!

## 🐛 Возможные ошибки

### "messaging/token-subscribe-failed"
- ❌ Неправильный VAPID key
- ✅ Получите новый из Firebase Console

### "Service Worker registration failed"
- ❌ Файл `firebase-messaging-sw.js` не найден
- ✅ Проверьте что файл в `public/firebase-messaging-sw.js`

### "Failed to save token"
- ❌ Маршрут не найден или ошибка CSRF
- ✅ Проверьте что вы авторизованы в админ панели

### Firebase initialization error
- ❌ Неправильный `firebase-credentials.json`
- ✅ Скачайте новый Service Account Key из Firebase

## 📍 Полезные ссылки

- Тестовая страница: `http://your-domain/admin/fcm/test-page`
- Firebase Console: https://console.firebase.google.com/
- Полная документация: `FCM_SETUP_INSTRUCTIONS.md`

## 🎯 Что дальше?

После успешного тестирования вы можете:

1. **Интегрировать уведомления в бизнес-логику:**
   - Новые заказы
   - Новые сообщения в чате
   - Важные события

2. **Пример интеграции:**

```php
// В любом контроллере или обсервере
use Webkul\Admin\Services\FcmNotificationService;

$fcmService = app(FcmNotificationService::class);

// Отправить всем админам
$fcmService->sendToAllAdmins(
    '📦 Новый заказ!',
    'Заказ #12345 от клиента Иван Иванов',
    [
        'type' => 'new_order',
        'order_id' => 12345,
        'url' => route('admin.sales.orders.view', 12345)
    ]
);
```

## ✨ Готово!

FCM Push уведомления полностью настроены и готовы к использованию!

Нужна помощь? Проверьте логи:
- Laravel: `storage/logs/laravel.log`
- Browser Console: F12 → Console

