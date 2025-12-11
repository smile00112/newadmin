<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Политика конфиденциальности - {{ config('app.name') }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #1a1a1a;
            background: #ffffff;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
            padding: 40px 20px;
        }

        header {
            background: #ffffff;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            padding: 20px 0;
            margin-bottom: 40px;
        }

        nav {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-size: 24px;
            font-weight: 700;
            color: #2563eb;
            text-decoration: none;
        }

        .back-link {
            color: #4b5563;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s;
        }

        .back-link:hover {
            color: #2563eb;
        }

        h1 {
            font-size: 36px;
            font-weight: 700;
            margin-bottom: 30px;
            color: #1a1a1a;
        }

        h2 {
            font-size: 24px;
            font-weight: 600;
            margin-top: 40px;
            margin-bottom: 20px;
            color: #1a1a1a;
        }

        p {
            margin-bottom: 20px;
            color: #4b5563;
        }

        ul {
            margin-left: 30px;
            margin-bottom: 20px;
        }

        li {
            margin-bottom: 10px;
            color: #4b5563;
        }

        footer {
            background: #1a1a1a;
            color: white;
            padding: 40px 0;
            text-align: center;
            margin-top: 60px;
        }

        .footer-links {
            display: flex;
            justify-content: center;
            gap: 30px;
            flex-wrap: wrap;
            margin-bottom: 20px;
        }

        .footer-links a {
            color: white;
            text-decoration: none;
            transition: opacity 0.3s;
        }

        .footer-links a:hover {
            opacity: 0.8;
        }
    </style>
</head>
<body>
    <header>
        <nav>
            <a href="{{ route('newsletters.landing.index') }}" class="logo">📧 {{ config('app.name') }}</a>
            <a href="{{ route('newsletters.landing.index') }}" class="back-link">← Вернуться на главную</a>
        </nav>
    </header>

    <div class="container">
        <h1>Политика конфиденциальности</h1>
        
        <p>Настоящая политика конфиденциальности описывает, как {{ config('app.name') }} собирает, использует и защищает персональные данные пользователей.</p>

        <h2>1. Сбор персональных данных</h2>
        <p>Мы собираем следующие персональные данные:</p>
        <ul>
            <li>Имя и контактная информация (email, телефон)</li>
            <li>Информация об использовании сервиса</li>
            <li>Технические данные (IP-адрес, тип браузера, устройство)</li>
        </ul>

        <h2>2. Использование данных</h2>
        <p>Персональные данные используются для:</p>
        <ul>
            <li>Предоставления и улучшения услуг</li>
            <li>Обработки заявок и регистраций</li>
            <li>Отправки уведомлений и важной информации</li>
            <li>Обеспечения безопасности сервиса</li>
        </ul>

        <h2>3. Защита данных</h2>
        <p>Мы применяем современные методы защиты данных, включая шифрование и безопасные протоколы передачи данных. Доступ к персональным данным имеют только уполномоченные сотрудники.</p>

        <h2>4. Передача данных третьим лицам</h2>
        <p>Мы не передаем персональные данные третьим лицам, за исключением случаев, когда это необходимо для предоставления услуг или требуется по закону.</p>

        <h2>5. Права пользователей</h2>
        <p>Пользователи имеют право:</p>
        <ul>
            <li>Получать информацию о своих персональных данных</li>
            <li>Требовать исправления неточных данных</li>
            <li>Требовать удаления персональных данных</li>
            <li>Отозвать согласие на обработку данных</li>
        </ul>

        <h2>6. Cookies</h2>
        <p>Мы используем cookies для улучшения работы сайта и анализа использования сервиса. Вы можете отключить cookies в настройках браузера.</p>

        <h2>7. Изменения в политике</h2>
        <p>Мы оставляем за собой право вносить изменения в настоящую политику конфиденциальности. Изменения вступают в силу после публикации на сайте.</p>

        <h2>8. Контакты</h2>
        <p>По вопросам обработки персональных данных обращайтесь в службу поддержки через форму обратной связи на сайте.</p>

        <p><strong>Дата последнего обновления:</strong> {{ date('d.m.Y') }}</p>
    </div>

    <footer>
        <div class="footer-links">
            <a href="{{ route('newsletters.landing.payment-terms') }}">Условия оплаты</a>
            <a href="{{ route('newsletters.landing.privacy-policy') }}">Политика конфиденциальности</a>
            <a href="{{ route('newsletters.landing.offer') }}">Оферта</a>
        </div>
        <p>&copy; 2025 {{ config('app.name') }}. Все права защищены.</p>
    </footer>
</body>
</html>




