<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Сервис рассылки - Профессиональные решения для массовых рассылок</title>
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
            font-size: 28px;
            margin-bottom: 20px;
            color: #2563eb;
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
<body>
    <!-- Header -->
    <header>
        <nav class="container">
            <div class="logo">📧 MailingService</div>
            <ul class="nav-links">
                <li><a href="#features">Возможности</a></li>
                <li><a href="#benefits">Преимущества</a></li>
                <li><a href="#pricing">Тарифы</a></li>
            </ul>
            <button class="btn-primary" onclick="openModal()">Начать</button>
        </nav>
    </header>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <h1>Профессиональный сервис рассылки</h1>
            <p>Эффективные массовые рассылки для вашего бизнеса</p>
            <button class="btn-primary" onclick="openModal()" style="font-size: 18px; padding: 16px 32px;">
                Получить доступ
            </button>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features" id="features">
        <div class="container">
            <h2 class="section-title">Возможности платформы</h2>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">📊</div>
                    <h3>Аналитика и отчеты</h3>
                    <p>Детальная статистика по каждой рассылке: открытия, клики, конверсии и многое другое.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">⚡</div>
                    <h3>Высокая скорость</h3>
                    <p>Мгновенная доставка сообщений тысячам получателей одновременно.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">🎯</div>
                    <h3>Таргетирование</h3>
                    <p>Сегментация аудитории и персонализация сообщений для максимальной эффективности.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">🔒</div>
                    <h3>Безопасность</h3>
                    <p>Защита данных и соответствие всем требованиям безопасности и конфиденциальности.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">📱</div>
                    <h3>Мультиканальность</h3>
                    <p>Рассылки через Email, SMS, WhatsApp и другие популярные каналы связи.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">🤖</div>
                    <h3>Автоматизация</h3>
                    <p>Настройка автоматических рассылок по триггерам и событиям.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Benefits Section -->
    <section class="benefits" id="benefits">
        <div class="container">
            <h2 class="section-title">Почему выбирают нас</h2>
            <div class="benefits-grid">
                <div class="benefit-item">
                    <div style="font-size: 48px; margin-bottom: 15px;">🚀</div>
                    <h3>Быстрый старт</h3>
                    <p>Начните работу уже сегодня. Простая настройка за несколько минут.</p>
                </div>
                <div class="benefit-item">
                    <div style="font-size: 48px; margin-bottom: 15px;">💼</div>
                    <h3>Для бизнеса</h3>
                    <p>Решения для компаний любого размера - от стартапов до корпораций.</p>
                </div>
                <div class="benefit-item">
                    <div style="font-size: 48px; margin-bottom: 15px;">🎓</div>
                    <h3>Поддержка 24/7</h3>
                    <p>Наша команда всегда готова помочь вам с любыми вопросами.</p>
                </div>
                <div class="benefit-item">
                    <div style="font-size: 48px; margin-bottom: 15px;">📈</div>
                    <h3>Масштабируемость</h3>
                    <p>Растите вместе с нами. Платформа легко масштабируется под ваши нужды.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Pricing Section -->
    <section class="pricing" id="pricing">
        <div class="container">
            <h2 class="section-title">Выберите тариф</h2>
            <div class="pricing-grid">
                <div class="pricing-card">
                    <div class="pricing-plan">Start</div>
                    <div class="pricing-price">4 000<span> ₽/мес</span></div>
                    <ul class="pricing-features">
                        <li>До 10 000 сообщений</li>
                        <li>Базовые шаблоны</li>
                        <li>Email поддержка</li>
                        <li>Базовая аналитика</li>
                    </ul>
                    <button class="btn-primary" style="width: 100%;" onclick="openModal('start')">
                        Выбрать тариф
                    </button>
                </div>
                <div class="pricing-card featured">
                    <div class="pricing-plan">Pro</div>
                    <div class="pricing-price">8 000<span> ₽/мес</span></div>
                    <ul class="pricing-features">
                        <li>До 50 000 сообщений</li>
                        <li>Все шаблоны</li>
                        <li>Приоритетная поддержка</li>
                        <li>Расширенная аналитика</li>
                        <li>Автоматизация</li>
                    </ul>
                    <button class="btn-primary" style="width: 100%;" onclick="openModal('pro')">
                        Выбрать тариф
                    </button>
                </div>
                <div class="pricing-card">
                    <div class="pricing-plan">Corporate</div>
                    <div class="pricing-price">20 000<span> ₽/мес</span></div>
                    <ul class="pricing-features">
                        <li>Неограниченно сообщений</li>
                        <li>Все возможности</li>
                        <li>Персональный менеджер</li>
                        <li>Полная аналитика</li>
                        <li>API доступ</li>
                        <li>Кастомные интеграции</li>
                    </ul>
                    <button class="btn-primary" style="width: 100%;" onclick="openModal('corporate')">
                        Выбрать тариф
                    </button>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="container">
            <p>&copy; 2025 MailingService. Все права защищены.</p>
        </div>
    </footer>

    <!-- Modal -->
    <div id="registrationModal" class="modal">
        <div class="modal-content">
            <button class="modal-close" onclick="closeModal()">&times;</button>
            <h2 class="modal-title">Заявка на регистрацию</h2>
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
                    <input type="tel" id="phone" name="phone" required>
                    <div class="error" id="phoneError"></div>
                </div>

                <button type="submit" class="btn-primary" style="width: 100%;">
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
        }

        function clearErrors() {
            document.querySelectorAll('.error').forEach(el => el.textContent = '');
            document.querySelectorAll('input').forEach(el => el.style.borderColor = '#e5e7eb');
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

            const formData = new FormData(form);
            const data = Object.fromEntries(formData);

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
                    showAlert(result.message, 'success');
                    form.reset();
                    setTimeout(() => {
                        closeModal();
                    }, 2000);
                } else {
                    if (result.errors) {
                        Object.keys(result.errors).forEach(field => {
                            const errorElement = document.getElementById(field + 'Error');
                            const inputElement = document.getElementById(field);
                            if (errorElement) {
                                errorElement.textContent = result.errors[field][0];
                            }
                            if (inputElement) {
                                inputElement.style.borderColor = '#ef4444';
                            }
                        });
                    } else {
                        showAlert(result.message || 'Произошла ошибка', 'error');
                    }
                }
            } catch (error) {
                showAlert('Произошла ошибка при отправке формы. Пожалуйста, попробуйте позже.', 'error');
            }
        });

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
    </script>
</body>
</html>

