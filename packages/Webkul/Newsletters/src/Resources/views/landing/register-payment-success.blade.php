<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Оплата прошла успешно — TargetX</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #1a1a1a;
            background: #f9fafb;
        }
        .container { max-width: 560px; margin: 0 auto; padding: 40px 20px; }
        .card {
            background: #fff;
            border-radius: 16px;
            padding: 40px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            text-align: center;
        }
        .success-icon { font-size: 48px; margin-bottom: 16px; color: #059669; }
        h1 { font-size: 24px; font-weight: 700; margin-bottom: 16px; color: #1a1a1a; }
        .text { color: #4b5563; margin-bottom: 24px; }
        .btn-primary {
            display: inline-block;
            background: #2563eb;
            color: white;
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 600;
            text-decoration: none;
            transition: background 0.3s;
        }
        .btn-primary:hover { background: #1d4ed8; }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="success-icon">✓</div>
            <h1>Оплата прошла успешно</h1>
            <p class="text">
                На вашу почту отправлено письмо с данными для входа в систему. Проверьте папку «Спам», если письмо не пришло в течение нескольких минут.
            </p>
            <a href="{{ route('admin.session.create') }}" class="btn-primary">Войти в админ панель</a>
        </div>
    </div>
</body>
</html>
