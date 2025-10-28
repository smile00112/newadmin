<?php
/**
 * Тестовый скрипт для проверки сохранения FCM токена
 * 
 * Запуск: php test_fcm_token.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "\n🔍 Проверка FCM токена в базе данных\n";
echo str_repeat('=', 60) . "\n\n";

// Получить всех администраторов
$admins = \Webkul\User\Models\Admin::all();

if ($admins->isEmpty()) {
    echo "❌ Администраторы не найдены в базе данных\n\n";
    exit(1);
}

echo "Найдено администраторов: " . $admins->count() . "\n\n";

foreach ($admins as $admin) {
    echo "Администратор: {$admin->name} (ID: {$admin->id})\n";
    echo "  Email: {$admin->email}\n";
    echo "  FCM Token: ";
    
    if ($admin->fcm_token) {
        $tokenPreview = substr($admin->fcm_token, 0, 30) . '...';
        echo "✅ {$tokenPreview}\n";
        echo "  Длина токена: " . strlen($admin->fcm_token) . " символов\n";
    } else {
        echo "❌ НЕ УСТАНОВЛЕН (NULL)\n";
    }
    
    echo "\n";
}

echo str_repeat('=', 60) . "\n";
echo "\n💡 Инструкция:\n";
echo "1. Если токен NULL - войдите в админ панель и разрешите уведомления\n";
echo "2. Откройте консоль браузера (F12) и проверьте логи FCM\n";
echo "3. Запустите этот скрипт снова для проверки\n\n";

echo "📋 Проверка структуры таблицы admins:\n";
$columns = \DB::select("SHOW COLUMNS FROM admins WHERE Field = 'fcm_token'");

if (!empty($columns)) {
    echo "✅ Поле fcm_token существует в таблице admins\n";
    foreach ($columns as $column) {
        echo "  Тип: {$column->Type}\n";
        echo "  NULL: {$column->Null}\n";
        echo "  Default: {$column->Default}\n";
    }
} else {
    echo "❌ Поле fcm_token НЕ найдено в таблице admins\n";
    echo "   Выполните: php artisan migrate\n";
}

echo "\n";

