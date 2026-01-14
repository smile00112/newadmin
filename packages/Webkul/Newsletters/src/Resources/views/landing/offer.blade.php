<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Оферта - {{ config('app.name') }}</title>
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
            font-size: 16px;
            line-height: 1.8;
        }

        .intro-text {
            font-size: 18px;
            color: #374151;
            background: #f9fafb;
            padding: 30px;
            border-radius: 12px;
            border-left: 4px solid #2563eb;
            margin-bottom: 40px;
            line-height: 1.8;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin: 30px 0;
        }

        .feature-card {
            background: #f9fafb;
            padding: 25px;
            border-radius: 12px;
            border: 2px solid #e5e7eb;
            transition: all 0.3s;
        }

        .feature-card:hover {
            border-color: #2563eb;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.1);
        }

        .feature-card h3 {
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 10px;
            color: #1a1a1a;
        }

        .feature-card p {
            margin: 0;
            color: #6b7280;
            font-size: 14px;
        }

        .highlight-box {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 12px;
            margin: 30px 0;
        }

        .highlight-box h3 {
            font-size: 22px;
            margin-bottom: 15px;
            color: white;
        }

        .highlight-box p {
            color: rgba(255, 255, 255, 0.95);
            margin-bottom: 10px;
        }

        .highlight-box p:last-child {
            margin-bottom: 0;
        }

        ul {
            margin-left: 30px;
            margin-bottom: 20px;
        }

        li {
            margin-bottom: 10px;
            color: #4b5563;
            line-height: 1.8;
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
            <a href="/" class="logo">📧 {{ config('app.name') }}</a>
            <a href="/" class="back-link">← Вернуться на главную</a>
        </nav>
    </header>

    <div class="container">
        <h1>Публичная оферта</h1>
        
        <div class="intro-text">
            <strong>{{ config('app.name') }}</strong> — программный продукт (SaaS). Каждый партнёр, включая саму платформу, самостоятельно продаёт лицензии {{ config('app.name') }} конечным клиентам, принимает оплату на свой расчётный счёт через собственный эквайринг и несёт обязательства перед клиентом. {{ config('app.name') }} не принимает участие в расчётах между партнёрами и клиентами и не осуществляет распределение денежных средств.
        </div>

        <h2>Модель работы платформы</h2>
        <p>Платформа {{ config('app.name') }} построена на принципах независимой работы каждого партнёра. Это обеспечивает прозрачность и соответствие требованиям финансового законодательства.</p>

        <div class="features-grid">
            <div class="feature-card">
                <h3>Независимые мерчанты</h3>
                <p>Каждый партнёр работает как самостоятельный мерчант со своим расчётным счётом и эквайрингом</p>
            </div>
            <div class="feature-card">
                <h3>Единый софт</h3>
                <p>Все партнёры используют единое программное обеспечение {{ config('app.name') }}</p>
            </div>
            <div class="feature-card">
                <h3>Нет группового оборота</h3>
                <p>Каждый партнёр ведёт свою финансовую деятельность независимо</p>
            </div>
            <div class="feature-card">
                <h3>Нет общего кошелька</h3>
                <p>Все расчёты осуществляются напрямую между партнёром и клиентом</p>
            </div>
            <div class="feature-card">
                <h3>Нет оператора денежных средств</h3>
                <p>{{ config('app.name') }} не является оператором и не участвует в финансовых операциях</p>
            </div>
        </div>

        <div class="highlight-box">
            <h3>Как это работает для банков</h3>
            <p><strong>20 независимых мерчантов</strong> — каждый партнёр работает как отдельный бизнес</p>
            <p><strong>Один софт</strong> — все используют единую платформу {{ config('app.name') }}</p>
            <p><strong>Нет группового оборота</strong> — отсутствует объединение финансовых потоков</p>
            <p><strong>Нет общего кошелька</strong> — каждый партнёр имеет свой расчётный счёт</p>
            <p><strong>Нет оператора денежных средств</strong> — {{ config('app.name') }} не обрабатывает платежи</p>
        </div>

        <h2>Обязательства сторон</h2>
        <p>Каждый партнёр самостоятельно:</p>
        <ul>
            <li>Продаёт лицензии на использование платформы {{ config('app.name') }}</li>
            <li>Принимает оплату от клиентов на свой расчётный счёт</li>
            <li>Организует собственный эквайринг для приёма платежей</li>
            <li>Несёт полную ответственность перед своими клиентами</li>
            <li>Обеспечивает соблюдение всех требований законодательства</li>
        </ul>

        <h2>Роль платформы {{ config('app.name') }}</h2>
        <p>{{ config('app.name') }} предоставляет исключительно программное обеспечение и техническую инфраструктуру. Платформа не участвует в финансовых операциях между партнёрами и их клиентами, не обрабатывает платежи и не распределяет денежные средства.</p>

        <p><strong>Дата последнего обновления:</strong> {{ date('d.m.Y') }}</p>
    </div>

    <footer>
        <div class="footer-links">
            <a href="/">Условия оплаты</a>
            <a href="/">Политика конфиденциальности</a>
            <a href="{{ route('shop.cms.page', 'oferta') }}">Оферта</a>
        </div>
        <p>&copy; 2025 {{ config('app.name') }}. Все права защищены.</p>
    </footer>
</body>
</html>

