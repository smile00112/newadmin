# Real-time Broadcast Implementation для Customer Numbers

## Обзор изменений

Реализована система real-time обновления страницы `mailing-lists/edit` при очистке параметра `incoming_message` в модели `CustomerNumber`.

## Что было сделано

### 1. **CustomerNumberController** - улучшения и новая логика

#### Добавлены импорты:
- `Illuminate\Validation\Rule` - для сложной валидации
- `Illuminate\Support\Facades\DB` - для транзакций БД
- `Illuminate\Support\Facades\Log` - для логирования
- `Webkul\Newsletters\Events\CustomerNumberMessageRead` - broadcast событие

#### Улучшенная валидация (методы `store` и `update`):
```php
'phone_number' => [
    'required',
    'string',
    'max:20',
    'regex:/^\+?[1-9]\d{1,14}$/',
    Rule::unique('newsletters_customer_numbers')
        ->where('mailing_list_id', $request->mailing_list_id)
        ->ignore($id ?? null),
]
```

#### Улучшенный метод `import()`:
- Транзакции БД для целостности данных
- Проверка дубликатов перед добавлением
- Детальное логирование ошибок
- Подсчет импортированных и пропущенных записей

#### Обновленный метод `getChatHistory()`:
```php
// Обнуление incoming_message при просмотре чата
if ($customerNumber->incoming_message) {
    $customerNumber->incoming_message = false;
    $customerNumber->save();
    
    // Broadcast события для real-time обновления UI
    broadcast(new CustomerNumberMessageRead($customerNumber));
    
    Log::info('Incoming message flag cleared for customer number', [...]);
}
```

#### Добавлены новые методы:
- `getChatHistory()` - получение истории чата (уже существовал, обновлен)
- `search()` - поиск номеров клиентов

### 2. **CustomerNumberMessageRead Event**

Создан новый broadcast event: `packages/Webkul/Newsletters/src/Events/CustomerNumberMessageRead.php`

**Основные характеристики:**
- Implements `ShouldBroadcast` для автоматической трансляции
- Транслируется на канал: `mailing-list.{mailingListId}`
- Событие: `customer-number.message-read`
- Данные события:
  ```php
  [
      'customer_number_id' => $customerNumber->id,
      'phone_number' => $customerNumber->phone_number,
      'name' => $customerNumber->name,
      'incoming_message' => false,
      'mailing_list_id' => $mailingListId,
      'timestamp' => now()->toISOString()
  ]
  ```

### 3. **Broadcast Channels**

Добавлены каналы в `routes/channels.php`:

```php
// Канал для обновлений mailing list
Broadcast::channel('mailing-list.{mailingListId}', function ($user, $mailingListId) {
    return true; // Все аутентифицированные пользователи
});

// Канал для статистики
Broadcast::channel('mailing-lists-stats', function ($user) {
    return true;
});
```

### 4. **Frontend - Real-time Updates**

В `packages/Webkul/Newsletters/src/Resources/views/admin/mailing-lists/edit.blade.php` добавлен JavaScript код для подписки на события:

```javascript
// WebSocket / Broadcast подключение
if (typeof window.Echo !== 'undefined') {
    window.Echo.channel('mailing-list.{{ $mailingList->id }}')
        .listen('.customer-number.message-read', (event) => {
            updateCustomerNumberRow(event);
        });
}
```

**Функция `updateCustomerNumberRow(event)`:**
- Находит строку таблицы по `customer_number_id`
- Удаляет желтый фон (`bg-yellow-50`, `border-yellow-300`)
- Удаляет badge "Входящее сообщение"
- Обновляет скрытое поле `incoming_message` на `0`
- Показывает уведомление об обновлении

### 5. **Переводы**

Добавлены новые ключи переводов в:
- `packages/Webkul/Newsletters/src/Resources/lang/ru/app.php`
- `packages/Webkul/Newsletters/src/Resources/lang/en/app.php`

**Новые ключи:**
- `import-skipped` - "Пропущено: :count"
- `chat-history-failed` - "Не удалось загрузить историю чата"
- `search-failed` - "Ошибка поиска"
- `no-whatsapp-instance` - "У клиента нет WhatsApp инстанса"
- `delivered_title` - "Доставка"
- `viewed_title` - "Просмотр"
- И другие вспомогательные ключи

## Как это работает

### Сценарий использования:

1. **Пользователь A** находится на странице `mailing-lists/edit` и видит номер клиента с желтым фоном (incoming_message = true)

2. **Пользователь B** открывает чат с этим клиентом (метод `getChatHistory()`)

3. **Backend:**
   - Обнуляет `incoming_message` на `false`
   - Сохраняет изменения в БД
   - Отправляет broadcast событие `CustomerNumberMessageRead`

4. **Frontend (Пользователь A):**
   - Получает событие через WebSocket
   - Автоматически обновляет строку таблицы
   - Удаляет желтый фон и badge
   - Показывает уведомление

## Требования для работы

### 1. Настройка Broadcasting

В `.env` файле:

```env
BROADCAST_CONNECTION=pusher
# или
BROADCAST_CONNECTION=reverb

# Для Pusher:
PUSHER_APP_ID=your-app-id
PUSHER_APP_KEY=your-app-key
PUSHER_APP_SECRET=your-app-secret
PUSHER_APP_CLUSTER=mt1

# Для Reverb:
REVERB_APP_ID=your-app-id
REVERB_APP_KEY=your-app-key
REVERB_APP_SECRET=your-app-secret
REVERB_HOST=localhost
REVERB_PORT=8080
REVERB_SCHEME=http
```

### 2. Laravel Echo

Убедитесь, что Laravel Echo подключен в вашем приложении. В основном layout должно быть:

```javascript
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: import.meta.env.VITE_PUSHER_APP_KEY,
    cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
    forceTLS: true
});
```

### 3. Queue Worker

Запустите queue worker для обработки broadcast событий:

```bash
php artisan queue:work --queue=broadcastable
```

## Тестирование

### 1. Проверка broadcast канала:

```php
// В tinker или контроллере
use Webkul\Newsletters\Models\CustomerNumber;
use Webkul\Newsletters\Events\CustomerNumberMessageRead;

$customerNumber = CustomerNumber::find(1);
broadcast(new CustomerNumberMessageRead($customerNumber));
```

### 2. Проверка в браузере:

1. Откройте две вкладки браузера
2. В обеих откройте страницу редактирования одного mailing list
3. В консоли должно быть: "Subscribed to mailing-list.{id} channel"
4. Вызовите `getChatHistory()` для номера с `incoming_message = true`
5. Во второй вкладке должна обновиться строка таблицы

## Структура файлов

```
packages/Webkul/Newsletters/src/
├── Events/
│   ├── CustomerNumberMessageRead.php       # NEW - Broadcast событие
│   └── MailingListStatsUpdated.php
├── Http/Controllers/Admin/
│   └── CustomerNumberController.php        # UPDATED - Улучшения + broadcast
├── Resources/
│   ├── lang/
│   │   ├── en/app.php                      # UPDATED - Новые переводы
│   │   └── ru/app.php                      # UPDATED - Новые переводы
│   └── views/admin/mailing-lists/
│       └── edit.blade.php                  # UPDATED - WebSocket подписка
└── Models/
    └── CustomerNumber.php

routes/
└── channels.php                            # UPDATED - Новые broadcast каналы
```

## Логирование

Все события логируются:

```php
// При обнулении incoming_message
Log::info('Incoming message flag cleared for customer number', [
    'customer_number_id' => $customerNumber->id,
    'phone_number' => $customerNumber->phone_number,
]);

// При ошибках broadcast
Log::error('Failed to retrieve chat history', [
    'customer_number_id' => $request->customer_id,
    'error' => $e->getMessage(),
]);
```

## Возможные проблемы и решения

### Broadcast не работает:

1. **Проверьте настройки .env** - `BROADCAST_CONNECTION` должен быть не `null`
2. **Проверьте queue worker** - должен быть запущен
3. **Проверьте Laravel Echo** - должен быть инициализирован
4. **Проверьте консоль браузера** - должны быть логи подписки на канал

### События не приходят:

1. **Проверьте channels.php** - канал должен быть зарегистрирован
2. **Проверьте права доступа** - функция авторизации канала должна возвращать `true`
3. **Проверьте логи** - `storage/logs/laravel.log`

## Дополнительные улучшения

### Возможные расширения:

1. **Приватные каналы** - ограничить доступ по ролям пользователей
2. **Presence каналы** - показывать кто сейчас онлайн
3. **Дополнительные события** - delivered, viewed изменения
4. **Звуковые уведомления** - при получении нового incoming message
5. **Desktop notifications** - через Notification API браузера

## Автор

Реализовано: 2025-10-23

