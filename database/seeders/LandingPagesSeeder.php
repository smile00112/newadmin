<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Webkul\CMS\Repositories\PageRepository;
use Webkul\Core\Facades\Core;

class LandingPagesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $pageRepository = app(PageRepository::class);
        $channels = Core::getAllChannels();
        $channelIds = $channels->pluck('id')->toArray();

        // Главная страница
        $homePageContent = $this->getHomePageContent();
        $this->createOrUpdatePage(
            $pageRepository,
            'home',
            'Главная страница',
            $homePageContent,
            'Главная страница - Сервис рассылки',
            'Профессиональный сервис рассылки через Email, WhatsApp и Telegram',
            'рассылка, email, whatsapp, telegram, маркетинг',
            $channelIds
        );

        // Условия оплаты
        $paymentTermsContent = $this->getPaymentTermsContent();
        $this->createOrUpdatePage(
            $pageRepository,
            'payment-terms',
            'Условия оплаты',
            $paymentTermsContent,
            'Условия оплаты - ' . config('app.name'),
            'Условия оплаты услуг сервиса рассылки',
            'оплата, условия, тарифы',
            $channelIds
        );

        // Политика конфиденциальности
        $privacyPolicyContent = $this->getPrivacyPolicyContent();
        $this->createOrUpdatePage(
            $pageRepository,
            'privacy-policy',
            'Политика конфиденциальности',
            $privacyPolicyContent,
            'Политика конфиденциальности - ' . config('app.name'),
            'Политика конфиденциальности сервиса рассылки',
            'политика, конфиденциальность, защита данных',
            $channelIds
        );

        $privacyPolicyContent = $this->getOfertaContent();
        $this->createOrUpdatePage(
            $pageRepository,
            'oferta',
            'Оферта',
            $privacyPolicyContent,
            'Оферта - ' . config('app.name'),
            'Оферта сервиса рассылки',
            'Оферта',
            $channelIds
        );
    }

    /**
     * Create or update CMS page
     */
    private function createOrUpdatePage(
        PageRepository $pageRepository,
        string $urlKey,
        string $pageTitle,
        string $htmlContent,
        string $metaTitle,
        string $metaDescription,
        string $metaKeywords,
        array $channelIds
    ): void {
        // Check if page already exists
        $existingPage = $pageRepository->findByUrlKey($urlKey);

        $locale = app()->getLocale();

        if ($existingPage) {
            // Update existing page - format with locale key
            $data = [
                $locale => [
                    'page_title' => $pageTitle,
                    'url_key' => $urlKey,
                    'html_content' => $htmlContent,
                    'meta_title' => $metaTitle,
                    'meta_description' => $metaDescription,
                    'meta_keywords' => $metaKeywords,
                ],
                'channels' => $channelIds,
                'locale' => $locale,
            ];

            $pageRepository->update($data, $existingPage->id);
        } else {
            // Create new page - format without locale keys (repository handles all locales)
            // The repository expects data in format: ['page_title' => ..., 'channels' => [...]]
            // It will automatically create translations for all locales
            $data = [
                'page_title' => $pageTitle,
                'url_key' => $urlKey,
                'html_content' => $htmlContent,
                'meta_title' => $metaTitle,
                'meta_description' => $metaDescription,
                'meta_keywords' => $metaKeywords,
                'channels' => $channelIds,
            ];

            $pageRepository->create($data);
        }
    }

    /**
     * Get home page content (without registration modal)
     */
    private function getHomePageContent(): string
    {
        return '

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <h1>Профессиональный сервис рассылки</h1>
            <p>Эффективные массовые рассылки через Email, WhatsApp и Telegram для вашего бизнеса</p>
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
                    <div class="feature-icon icon-eye"></div>
                    <h3>Аналитика и отчеты</h3>
                    <p>Детальная статистика по каждой рассылке: открытия, клики, конверсии и многое другое.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon icon-arrow-right"></div>
                    <h3>Высокая скорость</h3>
                    <p>Мгновенная доставка сообщений тысячам получателей одновременно.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon icon-filter"></div>
                    <h3>Таргетирование</h3>
                    <p>Сегментация аудитории и персонализация сообщений для максимальной эффективности.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon icon-tick"></div>
                    <h3>Безопасность</h3>
                    <p>Защита данных и соответствие всем требованиям безопасности и конфиденциальности.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon icon-product"></div>
                    <h3>Мультиканальность</h3>
                    <p>Рассылки через Email, WhatsApp, Telegram и другие популярные каналы связи.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon icon-email"></div>
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
                    <div class="benefit-icon ">⚡</div>
                    <h3>Быстрый старт</h3>
                    <p>Начните работу уже сегодня. Простая настройка за несколько минут.</p>
                </div>
                <div class="benefit-item">
                    <div class="benefit-icon">💼</div>
                    <h3>Для бизнеса</h3>
                    <p>Решения для компаний любого размера - от стартапов до корпораций.</p>
                </div>
                <div class="benefit-item">
                    <div class="benefit-icon icon-support"></div>
                    <h3>Поддержка 24/7</h3>
                    <p>Наша команда всегда готова помочь вам с любыми вопросами.</p>
                </div>
                <div class="benefit-item">
                    <div class="benefit-icon">📱</div>
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
                    <button class="btn-primary" style="width: 100%;" onclick="openModal(\'start\')">
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
                    <button class="btn-primary" style="width: 100%;" onclick="openModal(\'pro\')">
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
                    <button class="btn-primary" style="width: 100%;" onclick="openModal(\'corporate\')">
                        Выбрать тариф
                    </button>
                </div>
            </div>
        </div>
    </section>

    ';
    }

    /**
     * Get payment terms content (without header and footer)
     */
    private function getPaymentTermsContent(): string
    {
        return '
        <h1>Условия оплаты</h1>

        <p>Настоящие условия оплаты регулируют порядок оплаты услуг, предоставляемых сервисом ' . config('app.name') . '.</p>

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
        <p>По вопросам оплаты обращайтесь в службу поддержки через форму обратной связи на сайте.</p>';
    }

    /**
     * Get privacy policy content (without header and footer)
     */
    private function getPrivacyPolicyContent(): string
    {
        return '
        <h1>Политика конфиденциальности</h1>

        <p>Настоящая политика конфиденциальности описывает, как ' . config('app.name') . ' собирает, использует и защищает персональные данные пользователей.</p>

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

        <p><strong>Дата последнего обновления:</strong> ' . date('d.m.Y') . '</p>';
    }

    /**
     * Get oferta (without header and footer)
     */
    private function getOfertaContent(): string
    {
        return '
        <h1>Политика конфиденциальности</h1>

        <div class="wrapper">
            <h1>ЛИЦЕНЗИОННАЯ ПУБЛИЧНАЯ ОФЕРТА</h1>
            <p>на предоставление права использования программы TargetX</p>

            <h2 class="section-title">1. Общие положения</h2>
            <p>1.1. Настоящая Лицензионная оферта (далее — «Оферта») является официальным предложением индивидуального предпринимателя Филиной Анастасии Константиновны, ИНН 222409994314, ОГРИП 325220200129143 (далее — «Исполнитель», «Правообладатель»), адресованным любому дееспособному лицу (далее — «Клиент», «Лицензиат») о заключении договора предоставления неисключительной лицензии на использование программы TargetX.</p>
            <p>1.2. Акцептом Оферты является: оплата тарифа и/или вход в личный кабинет, и/или использование функционала программы.</p>
            <p>1.3. Акцепт означает заключение Лицензионного договора в порядке ст. 437–438 Гражданского кодекса Российской Федерации.</p>

            <h2 class="section-title">2. Предмет договора (Лицензии)</h2>
            <p>2.1. Исполнитель предоставляет Клиенту простую (неисключительную), возмездную, отзывную лицензию на использование программы TargetX.</p>
            <p>2.2. Программа доступна по адресам:</p>
          <p>— веб-платформа:<br>
            <a href="https://newsletters.sinicaxo.store/admin" target="_blank">
                https://newsletters.sinicaxo.store/admin
            </a>
        </p>

        <p>— Telegram-бот:<br>
            <a href="https://t.me/targetx24_bot" target="_blank">
                https://t.me/targetx24_bot
            </a>
        </p>
            <p>2.3. Лицензия предоставляет Клиенту право использования функционала программы, включая: создание и управление рассылками, загрузку контактных баз, аналитику, прогрев номеров, автоматизацию коммуникаций и иные доступные функции.</p>
            <p>2.4. Исполнитель не оказывает услуги; Клиент приобретает право использования программы (лицензию).</p>
            <p>2.5. Лицензия предоставляется на срок действия оплаченного тарифа. По окончании срока доступ и лицензия прекращают действовать автоматически.</p>

            <h2 class="section-title">3. Ограничения использования программы</h2>
            <p>3.1. Клиенту запрещается:</p>
            <p>— копировать, модифицировать, декомпилировать программу;</p>
            <p>— пытаться получить исходный код программы;</p>
            <p>— передавать доступ третьим лицам;</p>
            <p>— сдавать доступ в аренду, субаренду, перепродавать или иным образом уступать права использования;</p>
            <p>— использовать программу для создания конкурирующих решений;</p>
            <p>— осуществлять вмешательство в работу системы, атаки, взлом или обход технических ограничений.</p>
            <p>3.2. Исполнитель имеет право блокировать доступ к программе без возврата средств при нарушении Клиентом условий Оферты и/или указанных ограничений.</p>

            <h2 class="section-title">4. Ответственность сторон</h2>
            <p>4.1. Клиент полностью и самостоятельно отвечает за законность баз, содержание сообщений, соблюдение ФЗ-152 и ФЗ «О рекламе», а также наличие согласий адресатов.</p>
            <p>4.2. Исполнитель не несёт ответственности за блокировки аккаунтов, незаконные действия Клиента, последствия рассылок, корректность данных.</p>
            <p>4.3. Программа предоставляется «как есть» (AS IS).</p>

            <h2 class="section-title">5. Стоимость и порядок оплаты</h2>
            <p>100% предоплата. Средства не возвращаются, кроме случаев, предусмотренных законом.</p>
            <p>Услуги считаются принятыми, если в течение 7 дней нет претензий.</p>

            <h2 class="section-title">6. Порядок предоставления доступа</h2>
            <p>Доступ предоставляется после оплаты.</p>
            <p>Переписка с доступами удаляется в течение 12 часов.</p>

            <h2 class="section-title">7. Политика обработки персональных данных</h2>
            <p>Исполнитель обрабатывает только данные Клиента. Данные получателей рассылок не обрабатываются.</p>

            <h2 class="section-title">8. Форс-мажор</h2>
            <p>Стороны освобождаются от ответственности при обстоятельствах непреодолимой силы.</p>

            <h2 class="section-title">9. Разрешение споров</h2>
            <p>Досудебный порядок обязателен. Суд — по месту регистрации Исполнителя.</p>

            <h2 class="section-title">10. Реквизиты</h2>
            <p>ИП Филина Анастасия Константиновна<br>
            ИНН 222409994314<br>
            ОГРИП 325220200129143<br>
            Адрес: 656010, Алтайский край, г Барнаул, ул Эмилии Алексеевой, д. 5, к. 1, кв. 113<br>
            Email: support@targetx.su</p>
        </div>
         ' . date('d.m.Y') . '</p>';
    }
}
