# 🔍 FCM Токен не сохраняется - Чеклист для отладки

## ✅ Исправления которые я сделал:

### 1. ✅ Исправлен FcmTokenController
**Проблема:** Использовался `Auth::user()` вместо `Auth::guard('admin')->user()`

**Было:**
```php
$user = Auth::user();
```

**Стало:**
```php
$admin = Auth::guard('admin')->user();
```

### 2. ✅ Исправлен JavaScript код
**Проблема:** Неправильное получение CSRF токена

**Было:**
```javascript
'X-CSRF-TOKEN': csrfToken.content
```

**Стало:**
```javascript
'X-CSRF-TOKEN': csrfToken.getAttribute('content')
```

### 3. ✅ Добавлено логирование
- В контроллере: `\Log::info('FCM token saved', ...)`
- В JavaScript: `console.log('FCM: Sending token to server...')`

---

## 🧪 Как протестировать СЕЙЧАС:

### Шаг 1: Очистить все кэши
```bash
php artisan cache:clear
php artisan config:clear
php artisan view:clear
php artisan route:clear
```

### Шаг 2: Открыть браузер с чистого листа
1. Откройте браузер в **режиме инкогнито** (Ctrl+Shift+N)
2. Откройте **DevTools** (F12)
3. Перейдите во вкладку **Console**
4. Перейдите во вкладку **Network**

### Шаг 3: Войти в админ панель
```
http://your-domain/admin
```

### Шаг 4: Разрешить уведомления
Когда браузер спросит - нажмите **"Разрешить"**

### Шаг 5: Проверить консоль
Вы должны увидеть в консоли:
```
✅ FCM: Service Worker registered
FCM: Permission status: granted
✅ FCM Token obtained: xxxxx...
FCM: Sending token to server...
✅ FCM: Token saved to server successfully
Admin: {id: 1, name: "...", email: "..."}
```

### Шаг 6: Проверить Network
Во вкладке **Network** найдите запрос:
- **URL:** `/admin/fcm-token`
- **Method:** `POST`
- **Status:** `200`

Кликните на него и проверьте:
- **Request Payload:** должен содержать `fcm_token`
- **Response:** должен быть `{"success": true, "message": "...", "admin": {...}}`

### Шаг 7: Проверить базу данных

**Через Artisan Tinker:**
```bash
php artisan tinker
```

```php
// Получить текущего авторизованного админа
$admin = \Webkul\User\Models\Admin::find(1); // замените 1 на ваш ID

// Проверить fcm_token
echo $admin->fcm_token;

// Если NULL - токен не сохранился
// Если видите длинную строку - ✅ ВСЕ РАБОТАЕТ!
```

**Через SQL:**
```sql
SELECT id, name, email, fcm_token 
FROM admins 
WHERE id = 1; -- замените на ваш ID
```

### Шаг 8: Проверить логи Laravel
```bash
tail -f storage/logs/laravel.log
```

Вы должны увидеть:
```
[2025-10-27 20:xx:xx] local.INFO: FCM token saved {"admin_id":1,"admin_email":"...","token_preview":"xxxxx..."}
```

---

## 🐛 Возможные проблемы и решения

### Проблема 1: В консоли нет сообщений FCM

**Причина:** Скрипт не загрузился или ошибка инициализации

**Решение:**
1. Откройте DevTools → Console
2. Посмотрите на ошибки
3. Очистите кэш браузера: Ctrl+Shift+Delete
4. Перезагрузите страницу с очисткой: Ctrl+Shift+R

### Проблема 2: "FCM: CSRF token not found"

**Причина:** Meta-тег CSRF не найден

**Решение:**
1. Проверьте в исходном коде страницы наличие:
```html
<meta name="csrf-token" content="...">
```
2. Если нет - проверьте layout файл

### Проблема 3: "FCM: Failed to save token" или статус 401/419

**Причина:** Проблема с аутентификацией или CSRF

**Решение:**
1. Убедитесь что вы авторизованы в админ панели
2. Проверьте что сессия не истекла
3. Попробуйте перелогиниться

### Проблема 4: В Network запрос `/admin/fcm-token` вернул ошибку

**Проверьте Response:**

**Если 404:**
- Маршрут не зарегистрирован
- Решение: `php artisan route:clear`

**Если 419 (CSRF token mismatch):**
- Проблема с CSRF токеном
- Решение: Обновите страницу, очистите cookies

**Если 401 (Unauthorized):**
- Проблема с аутентификацией
- Решение: Перелогиньтесь в админ панель

**Если 500:**
- Ошибка на сервере
- Решение: Проверьте `storage/logs/laravel.log`

### Проблема 5: Токен получен, запрос успешен, но в БД NULL

**Причина:** Поле не в `$fillable` или проблема с моделью

**Проверьте:**
```bash
php artisan tinker
```

```php
$admin = \Webkul\User\Models\Admin::find(1);
$admin->fcm_token = 'test_token';
$admin->save();

// Проверить
$admin->refresh();
echo $admin->fcm_token; // Должно вывести 'test_token'
```

Если токен сохраняется вручную - проблема в контроллере.
Если нет - проблема с моделью или БД.

---

## 📊 Быстрая диагностика

Откройте консоль браузера и выполните:

```javascript
// 1. Проверить Service Worker
navigator.serviceWorker.getRegistrations().then(registrations => {
    console.log('Service Workers:', registrations.length);
});

// 2. Проверить разрешения
console.log('Notification permission:', Notification.permission);

// 3. Проверить CSRF токен
const csrf = document.querySelector('meta[name="csrf-token"]');
console.log('CSRF token exists:', !!csrf);
console.log('CSRF token value:', csrf ? csrf.getAttribute('content').substring(0, 20) + '...' : 'NOT FOUND');

// 4. Проверить Firebase
console.log('Firebase loaded:', typeof firebase !== 'undefined');
console.log('Firebase apps:', firebase.apps.length);
```

---

## ✅ Ожидаемый результат

После всех исправлений в консоли должно быть:

```
✅ FCM: Service Worker registered
FCM: Permission status: granted
✅ FCM Token obtained: cPXa8hGLBw2lAK...
FCM: Sending token to server...
✅ FCM: Token saved to server successfully
Admin: {id: 1, name: "Admin Name", email: "admin@example.com"}
```

И в базе данных:
```sql
SELECT fcm_token FROM admins WHERE id = 1;
-- Результат: длинная строка токена
```

---

## 🔄 Последовательность действий (Summary)

1. ✅ Исправлен контроллер (использует `Auth::guard('admin')`)
2. ✅ Исправлен JavaScript (правильное получение CSRF)
3. ✅ Добавлено логирование
4. ✅ Очищены кэши
5. 🧪 **ТЕСТИРОВАНИЕ:**
   - Открыть браузер в режиме инкогнито
   - Войти в админ панель
   - Проверить консоль
   - Проверить Network
   - Проверить БД

---

**Статус:** ✅ Исправления внесены, готово к тестированию

**Следующий шаг:** Откройте админ панель в режиме инкогнито и проверьте консоль браузера

