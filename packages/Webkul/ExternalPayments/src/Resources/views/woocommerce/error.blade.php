<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ошибка</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            background-color: #f5f5f5;
        }
        .container {
            background: white;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
            max-width: 500px;
        }
        .error-icon {
            color: #dc3545;
            font-size: 64px;
            margin-bottom: 20px;
        }
        h1 {
            color: #dc3545;
            margin-bottom: 20px;
        }
        .message {
            color: #666;
            margin-bottom: 30px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="error-icon">⚠</div>
        <h1>Произошла ошибка</h1>
        <div class="message">
            <p>{{ $message ?? 'Произошла неизвестная ошибка. Пожалуйста, свяжитесь с поддержкой.' }}</p>
        </div>
    </div>
</body>
</html>
