<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $page->meta_title ?? $page->page_title ?? 'Сервис рассылки' }}</title>

    @if($page->meta_description)
    <meta name="description" content="{{ $page->meta_description }}">
    @endif

    @if($page->meta_keywords)
    <meta name="keywords" content="{{ $page->meta_keywords }}">
    @endif

    <link type="image/x-icon" href="https://dolinger_new_admin.test/themes/admin/default/build/assets/favicon-CiQV8jiw.ico" rel="shortcut icon" sizes="16x16">


    <style>
        /* Bagisto Shop Icon Font 33333333333*/
        @font-face {
            font-family: "bagisto-shop";
            src: url("{{ asset('themes/shop/default/build/assets/bagisto-shop-BHAKyv0r.woff') }}") format("woff");
            font-weight: normal;
            font-style: normal;
            font-display: swap;
        }

        [class^="icon-"],
        [class*=" icon-"] {
            font-family: "bagisto-shop" !important;
            speak: never;
            font-style: normal;
            font-weight: normal;
            font-variant: normal;
            text-transform: none;
            line-height: 1 !important;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        .icon-email:before { content: "\e916"; }
        .icon-eye:before { content: "\e918"; }
        .icon-arrow-right:before { content: "\e905"; }
        .icon-filter:before { content: "\e91b"; }
        .icon-tick:before { content: "\e93b"; }
        .icon-product:before { content: "\e92f"; }
        .icon-arrow-up:before { content: "\e906"; }
        .icon-users:before { content: "\e942"; }
        .icon-support:before { content: "\e93a"; }
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
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* Header */
        header {
            background: #ffffff;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            position: sticky;
            top: 0;
            z-index: 1000;
            padding: 15px 0;
        }

        nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 0;
        }

        .logo {
            font-size: 24px;
            font-weight: 700;
            color: #2563eb;
        }

        .nav-links {
            display: flex;
            gap: 30px;
            list-style: none;
        }

        .nav-links a {
            text-decoration: none;
            color: #4b5563;
            font-weight: 500;
            transition: color 0.3s;
        }

        .nav-links a:hover {
            color: #2563eb;
        }

        .btn-primary {
            background: #2563eb;
            color: white;
            padding: 12px 24px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            border: none;
            cursor: pointer;
            transition: background 0.3s;
        }

        .btn-primary:hover {
            background: #1d4ed8;
        }

        .btn-primary:disabled {
            background: #9ca3af;
            cursor: not-allowed;
            opacity: 0.7;
        }

        .btn-primary:disabled:hover {
            background: #9ca3af;
        }

        .btn-loading {
            position: relative;
            color: transparent;
        }

        .btn-loading::after {
            content: '';
            position: absolute;
            width: 20px;
            height: 20px;
            top: 50%;
            left: 50%;
            margin-left: -10px;
            margin-top: -10px;
            border: 3px solid rgba(255, 255, 255, 0.3);
            border-top-color: white;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Hero Section */
        .hero {
            padding: 100px 0;
            text-align: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .hero h1 {
            font-size: 56px;
            font-weight: 800;
            margin-bottom: 20px;
            line-height: 1.2;
        }

        .hero p {
            font-size: 20px;
            margin-bottom: 40px;
            opacity: 0.95;
        }

        /* Features Section */
        .features {
            padding: 80px 0;
            background: #f9fafb;
        }

        .section-title {
            text-align: center;
            font-size: 40px;
            font-weight: 700;
            margin-bottom: 60px;
            color: #1a1a1a;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 40px;
        }

        .feature-card {
            background: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }

        .feature-icon {
            width: 60px;
            height: 60px;
            background: #e0e7ff;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            margin-bottom: 20px;
            color: #2563eb;
        }

        .benefit-icon {
            font-size: 48px;
            margin-bottom: 15px;
            display: inline-block;
            font-size: 50px;
            line-height: normal;
        }

        .feature-card h3 {
            font-size: 24px;
            margin-bottom: 15px;
            color: #1a1a1a;
        }

        .feature-card p {
            color: #6b7280;
            line-height: 1.7;
        }

        /* Benefits Section */
        .benefits {
            padding: 80px 0;
        }

        .benefits-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 30px;
        }

        .benefit-item {
            text-align: center;
            padding: 30px;
        }

        .benefit-item h3 {
            font-size: 20px;
            margin: 15px 0 10px;
            color: #1a1a1a;
        }

        .benefit-item p {
            color: #6b7280;
        }

        /* Pricing Section */
        .pricing {
            padding: 80px 0;
            background: #f9fafb;
        }

        .pricing-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            max-width: 1000px;
            margin: 0 auto;
        }

        .pricing-card {
            background: white;
            border-radius: 16px;
            padding: 40px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            transition: transform 0.3s, box-shadow 0.3s;
            position: relative;
        }

        .pricing-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        }

        .pricing-card.featured {
            border: 2px solid #2563eb;
            transform: scale(1.05);
        }

        .pricing-card.featured::before {
            content: 'Популярный';
            position: absolute;
            top: -12px;
            left: 50%;
            transform: translateX(-50%);
            background: #2563eb;
            color: white;
            padding: 6px 20px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .pricing-plan {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 10px;
            color: #1a1a1a;
        }

        .pricing-price {
            font-size: 48px;
            font-weight: 800;
            color: #2563eb;
            margin: 20px 0;
        }

        .pricing-price span {
            font-size: 20px;
            color: #6b7280;
            font-weight: 400;
        }

        .pricing-features {
            list-style: none;
            margin: 30px 0;
        }

        .pricing-features li {
            padding: 10px 0;
            color: #4b5563;
            border-bottom: 1px solid #e5e7eb;
        }

        .pricing-features li:last-child {
            border-bottom: none;
        }

        .pricing-features li::before {
            content: '✓';
            color: #10b981;
            font-weight: bold;
            margin-right: 10px;
        }

        /* Footer */
        footer {
            background: #1a1a1a;
            color: white;
            padding: 40px 0;
            text-align: center;
        }

        /* Modal */
        .modal {
            display: none;
            position: fixed;
            z-index: 2000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            backdrop-filter: blur(4px);
        }

        .modal.active {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            background: white;
            border-radius: 16px;
            padding: 40px;
            max-width: 500px;
            width: 90%;
            position: relative;
            animation: slideIn 0.3s ease;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .modal-close {
            position: absolute;
            top: 20px;
            right: 20px;
            font-size: 28px;
            cursor: pointer;
            color: #6b7280;
            background: none;
            border: none;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .modal-close:hover {
            color: #1a1a1a;
        }

        .modal-title {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 10px;
            color: #1a1a1a;
        }

        .modal-subtitle {
            color: #6b7280;
            margin-bottom: 30px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #374151;
        }

        .form-group input {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s;
        }

        .form-group input:focus {
            outline: none;
            border-color: #2563eb;
        }

        .form-group .error {
            color: #ef4444;
            font-size: 14px;
            margin-top: 5px;
        }

        .alert {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .alert-success {
            background: #d1fae5;
            color: #065f46;
        }

        .alert-error {
            background: #fee2e2;
            color: #991b1b;
        }

        @media (max-width: 768px) {
            .hero h1 {
                font-size: 36px;
            }

            .hero p {
                font-size: 18px;
            }

            .nav-links {
                display: none;
            }

            .pricing-card.featured {
                transform: scale(1);
            }
        }
    </style>
</head>
<body class="cms-info-page">
    <!-- Header -->
    <header>
        <nav class="container">
            <div class="logo">
                <a href="/" class="flex-shrink-0">
                    <img src="/themes/admin/default/build/assets/logo-DVDU6gpe.svg" class="h-8 w-auto sm:h-10" id="logo-image" alt="DolingerAdmin">
                </a>
            </div>
            <ul class="nav-links">
                <li><a href="/#features">Возможности</a></li>
                <li><a href="/#benefits">Преимущества</a></li>
                <li><a href="/#pricing">Тарифы</a></li>
            </ul>
            <button class="btn-primary" onclick="openModal()">Начать</button>
        </nav>
    </header>

        <!-- CMS Content -->
        <div class="container">
            <div class="cms-content">
                {!! $page->html_content !!}
            </div>
        </div>

        <!-- Footer -->
        <footer>
            <div class="container">
                <div style="display: flex; justify-content: center; gap: 30px; flex-wrap: wrap; margin-bottom: 20px;">
                    <a href="{{ route('newsletters.landing.payment-terms', 'payment-terms') }}" style="color: white; text-decoration: none; transition: opacity 0.3s;" onmouseover="this.style.opacity='0.8'" onmouseout="this.style.opacity='1'">Условия оплаты</a>
                    <a href="{{ route('newsletters.landing.privacy-policy', 'privacy-policy') }}" style="color: white; text-decoration: none; transition: opacity 0.3s;" onmouseover="this.style.opacity='0.8'" onmouseout="this.style.opacity='1'">Политика конфиденциальности</a>
                    <a href="{{ route('newsletters.landing.oferta', 'oferta') }}" style="color: white; text-decoration: none; transition: opacity 0.3s;" onmouseover="this.style.opacity='0.8'" onmouseout="this.style.opacity='1'">Оферта</a>
                </div>
                <p>&copy; 2025 TargetX. Все права защищены.</p>
            </div>
        </footer>

    <!-- Registration Modal -->
    <div id="registrationModal" class="modal">
        <div class="modal-content">
            <button class="modal-close" onclick="closeModal()">&times;</button>
            <h2 class="modal-title">Заявка на регистрацию ..</h2>
            <p class="modal-subtitle">Заполните форму, и мы свяжемся с вами в ближайшее время</p>

            <div id="alertContainer"></div>

            <form id="registrationForm">
                <input type="hidden" id="selectedPlan" name="plan" value="">

                <div class="form-group">
                    <label for="name">Имя *</label>
                    <input type="text" id="name" name="name" required>
                    <div class="error" id="nameError"></div>
                </div>

                <div class="form-group">
                    <label for="email">Email *</label>
                    <input type="email" id="email" name="email" required>
                    <div class="error" id="emailError"></div>
                </div>

                <div class="form-group">
                    <label for="phone">Телефон *</label>
                    <input type="tel" id="phone" name="phone" placeholder="+7 (___) ___-__-__" required>
                    <div class="error" id="phoneError"></div>
                </div>

                <div class="form-group">
                    <label style="display: flex; align-items: flex-start; gap: 10px; font-weight: 400; cursor: pointer;">
                        <input type="checkbox" id="privacy_policy_accepted" name="privacy_policy_accepted" value="1" required style="width: auto; margin-top: 4px;">
                        <span>Я принимаю <a href="/" target="_blank" style="color: #2563eb; text-decoration: underline;">политику конфиденциальности</a> *</span>
                    </label>
                    <div class="error" id="privacy_policy_acceptedError"></div>
                </div>

                <button type="submit" id="submitBtn" class="btn-primary" style="width: 100%;">
                    Отправить заявку
                </button>
            </form>
        </div>
    </div>

    <script>
        const modal = document.getElementById('registrationModal');
        const form = document.getElementById('registrationForm');
        const selectedPlanInput = document.getElementById('selectedPlan');
        const alertContainer = document.getElementById('alertContainer');
        const submitBtn = document.getElementById('submitBtn');

        function openModal(plan = '') {
            selectedPlanInput.value = plan;
            modal.classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function closeModal() {
            modal.classList.remove('active');
            document.body.style.overflow = 'auto';
            form.reset();
            clearErrors();
            alertContainer.innerHTML = '';
            enableSubmitButton();
        }

        function disableSubmitButton() {
            submitBtn.disabled = true;
            submitBtn.classList.add('btn-loading');
            submitBtn.setAttribute('data-original-text', submitBtn.textContent);
            submitBtn.textContent = 'Отправка...';
        }

        function enableSubmitButton() {
            submitBtn.disabled = false;
            submitBtn.classList.remove('btn-loading');
            const originalText = submitBtn.getAttribute('data-original-text') || 'Отправить заявку';
            submitBtn.textContent = originalText;
            submitBtn.removeAttribute('data-original-text');
        }

        function clearErrors() {
            document.querySelectorAll('.error').forEach(el => el.textContent = '');
            document.querySelectorAll('input').forEach(el => {
                if (el.type === 'checkbox') {
                    el.style.outline = 'none';
                } else {
                    el.style.borderColor = '#e5e7eb';
                }
            });
        }

        function showAlert(message, type = 'success') {
            alertContainer.innerHTML = `<div class="alert alert-${type}">${message}</div>`;
            setTimeout(() => {
                alertContainer.innerHTML = '';
            }, 5000);
        }

        // Close modal on outside click
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                closeModal();
            }
        });

        // Form submission
        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            clearErrors();
            disableSubmitButton();

            const formData = new FormData(form);
            const data = Object.fromEntries(formData);

            // Очищаем телефон от форматирования перед отправкой (оставляем только цифры)
            if (data.phone) {
                data.phone = data.phone.replace(/\D/g, '');
                // Если начинается с 8, заменяем на 7
                if (data.phone.length > 0 && data.phone[0] === '8') {
                    data.phone = '7' + data.phone.substring(1);
                }
                // Если не начинается с 7, добавляем 7
                if (data.phone.length > 0 && data.phone[0] !== '7') {
                    data.phone = '7' + data.phone;
                }
                // Форматируем для отправки в формате +7XXXXXXXXXX
                data.phone = '+' + data.phone;
            }

            // Ensure checkbox value is properly set
            if (!data.privacy_policy_accepted) {
                data.privacy_policy_accepted = form.querySelector('#privacy_policy_accepted').checked ? '1' : '';
            }

            try {
                const response = await fetch('{{ route("newsletters.landing.register") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(data)
                });

                const result = await response.json();

                if (response.ok && result.success) {
                    console.log(result);

                    if (result.redirect_url) {
                        window.location.href = result.redirect_url;
                    } else {
                        showAlert(result.message, 'success');
                        setTimeout(() => {
                            form.reset();
                            closeModal();
                        }, 3000);
                    }
                } else {
                    enableSubmitButton();
                    if (result.errors) {
                        Object.keys(result.errors).forEach(field => {
                            const errorElement = document.getElementById(field + 'Error');
                            const inputElement = document.getElementById(field);
                            if (errorElement) {
                                errorElement.textContent = result.errors[field][0];
                            }
                            if (inputElement) {
                                if (inputElement.type === 'checkbox') {
                                    inputElement.style.outline = '2px solid #ef4444';
                                } else {
                                    inputElement.style.borderColor = '#ef4444';
                                }
                            }
                        });
                    } else {
                        showAlert(result.message || 'Произошла ошибка', 'error');
                    }
                }
            } catch (error) {
                enableSubmitButton();
                showAlert('Произошла ошибка при отправке формы. Пожалуйста, попробуйте позже.', 'error');
            }
        });

        // Phone mask
        const phoneInput = document.getElementById('phone');
        if (phoneInput) {
            phoneInput.addEventListener('input', function(e) {
                let value = e.target.value.replace(/\D/g, ''); // Удаляем все нецифровые символы

                // Если начинается не с 7 или 8, добавляем 7
                if (value.length > 0 && value[0] !== '7' && value[0] !== '8') {
                    value = '7' + value;
                }

                // Если начинается с 8, заменяем на 7
                if (value.length > 0 && value[0] === '8') {
                    value = '7' + value.substring(1);
                }

                // Ограничиваем длину до 11 цифр (7 + 10 цифр)
                if (value.length > 11) {
                    value = value.substring(0, 11);
                }

                // Форматируем номер
                let formattedValue = '';
                if (value.length > 0) {
                    formattedValue = '+7';
                    if (value.length > 1) {
                        formattedValue += ' (' + value.substring(1, 4);
                    }
                    if (value.length >= 4) {
                        formattedValue += ') ' + value.substring(4, 7);
                    }
                    if (value.length >= 7) {
                        formattedValue += '-' + value.substring(7, 9);
                    }
                    if (value.length >= 9) {
                        formattedValue += '-' + value.substring(9, 11);
                    }
                }

                e.target.value = formattedValue;
            });

            phoneInput.addEventListener('keydown', function(e) {
                // Разрешаем: backspace, delete, tab, escape, enter
                if ([8, 9, 27, 13, 46].indexOf(e.keyCode) !== -1 ||
                    // Разрешаем: Ctrl+A, Ctrl+C, Ctrl+V, Ctrl+X
                    (e.keyCode === 65 && e.ctrlKey === true) ||
                    (e.keyCode === 67 && e.ctrlKey === true) ||
                    (e.keyCode === 86 && e.ctrlKey === true) ||
                    (e.keyCode === 88 && e.ctrlKey === true) ||
                    // Разрешаем: home, end, left, right
                    (e.keyCode >= 35 && e.keyCode <= 39)) {
                    return;
                }
                // Запрещаем все, кроме цифр
                if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
                    e.preventDefault();
                }
            });

            phoneInput.addEventListener('paste', function(e) {
                e.preventDefault();
                const paste = (e.clipboardData || window.clipboardData).getData('text');
                let numbers = paste.replace(/\D/g, '');

                if (numbers.length > 0) {
                    // Если начинается с 8, заменяем на 7
                    if (numbers[0] === '8') {
                        numbers = '7' + numbers.substring(1);
                    }
                    // Если не начинается с 7, добавляем 7
                    if (numbers[0] !== '7') {
                        numbers = '7' + numbers;
                    }
                    // Ограничиваем до 11 цифр
                    numbers = numbers.substring(0, 11);

                    // Устанавливаем только цифры и триггерим событие input для форматирования
                    phoneInput.value = numbers;
                    phoneInput.dispatchEvent(new Event('input', { bubbles: true }));
                }
            });
        }

        // Smooth scroll
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Make openModal available globally
        window.openModal = openModal;
    </script>
</body>
</html>
