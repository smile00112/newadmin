<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tochka Payment — {{ $type === 'success' ? 'Успех' : 'Ошибка' }}</title>
    <style>
        body { font-family: system-ui, sans-serif; max-width: 800px; margin: 2rem auto; padding: 0 1rem; }
        h1 { font-size: 1.5rem; margin-bottom: 1rem; }
        .success { color: #059669; }
        .fail { color: #dc2626; }
        pre { background: #f3f4f6; padding: 1rem; border-radius: 0.5rem; overflow-x: auto; font-size: 0.875rem; }
    </style>
</head>
<body>
    <h1 class="{{ $type === 'success' ? 'success' : 'fail' }}">
        {{ $type === 'success' ? 'Оплата прошла успешно' : 'Оплата не выполнена' }}
    </h1>
    <p>Данные из запроса (GET / POST):</p>
    <pre>{{ json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
</body>
</html>
