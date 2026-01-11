<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $page->meta_title ?? $page->page_title ?? config('app.name') }}</title>
    
    @if($page->meta_description)
    <meta name="description" content="{{ $page->meta_description }}">
    @endif
    
    @if($page->meta_keywords)
    <meta name="keywords" content="{{ $page->meta_keywords }}">
    @endif

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

        /* Стили для информационных страниц */
        body.cms-info-page ul {
            padding-left: 40px;
        }
    </style>
</head>
<body class="cms-info-page">
    <header>
        <nav>
            <a href="/" class="logo">📧 {{ config('app.name') }}</a>
            <a href="/" class="back-link">← Вернуться на главную</a>
        </nav>
    </header>

    <div class="container">
        {!! $page->html_content !!}
    </div>

    <footer>
        <div class="footer-links">
            <a href="/">Условия оплаты</a>
            <a href="/">Политика конфиденциальности</a>
            <a href="/">Оферта</a>
        </div>
        <p>&copy; 2025 {{ config('app.name') }}. Все права защищены.</p>
    </footer>
</body>
</html>
