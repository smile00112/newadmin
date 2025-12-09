<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Условия оплаты - MailingService</title>
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
            <a href="{{ route('newsletters.landing.index') }}" class="logo">📧 MailingService</a>
            <a href="{{ route('newsletters.landing.index') }}" class="back-link">← Вернуться на главную</a>
        </nav>
    </header>

    <div class="container">
        <h1>Условия оплаты</h1>
        
        <p>Настоящие условия оплаты регулируют порядок оплаты услуг, предоставляемых сервисом MailingService.</p>

        <h2>1. Способы оплаты</h2>
        <p>Оплата услуг может производиться следующими способами:</p>
        <ul>
            <li>Банковской картой (Visa, MasterCard, МИР)</li>
            <li>Банковским переводом</li>
            <li>Электронными платежными системами</li>
        </ul>

        <h2>2. Стоимость услуг</h2>
        <p>Стоимость услуг указана на главной странице сайта и может быть изменена в любое время. Изменения вступают в силу после публикации на сайте.</p>

        <h2>3. Порядок оплаты</h2>
        <ul>
            <li>Оплата производится в соответствии с выбранным тарифным планом</li>
            <li>Оплата может быть произведена единовременно за весь период или по подписке</li>
            <li>После успешной оплаты услуги активируются автоматически</li>
        </ul>

        <h2>4. Возврат средств</h2>
        <p>Возврат средств возможен в течение 14 дней с момента оплаты при условии, что услуги не были использованы. Возврат производится тем же способом, которым была произведена оплата.</p>

        <h2>5. Налоги</h2>
        <p>Все цены указаны с учетом НДС, если применимо. Клиент несет ответственность за уплату любых налогов, связанных с использованием услуг.</p>

        <h2>6. Изменение тарифов</h2>
        <p>Мы оставляем за собой право изменять тарифы. Изменения не распространяются на уже оплаченные периоды.</p>

        <h2>7. Контакты</h2>
        <p>По вопросам оплаты обращайтесь в службу поддержки через форму обратной связи на сайте.</p>
    </div>

    <footer>
        <div class="footer-links">
            <a href="{{ route('newsletters.landing.payment-terms') }}">Условия оплаты</a>
            <a href="{{ route('newsletters.landing.privacy-policy') }}">Политика конфиденциальности</a>
        </div>
        <p>&copy; 2025 MailingService. Все права защищены.</p>
    </footer>
</body>
</html>




