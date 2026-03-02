<?php

namespace Webkul\Admin\Services;

use OpenAI;
use Webkul\Product\Repositories\ProductRepository;
use Webkul\Product\Repositories\ProductAttributeValueRepository;
use Webkul\Category\Repositories\CategoryRepository;
use Webkul\Attribute\Repositories\AttributeRepository;
use Webkul\Customer\Repositories\CustomerRepository;
use Webkul\Sales\Repositories\OrderRepository;
use Webkul\CMS\Repositories\PageRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AIAssistantService
{
    /**
     * Available tools for AI
     */
    protected array $tools = [];

    /**
     * System prompt for the AI
     */
    protected string $systemPrompt = '';

    /**
     * OpenAI client
     */
    protected $client = null;

    /**
     * AI model to use
     */
    protected string $model = 'gpt-4o-mini';

    public function __construct(
        protected ProductRepository $productRepository,
        protected ProductAttributeValueRepository $attributeValueRepository,
        protected CategoryRepository $categoryRepository,
        protected AttributeRepository $attributeRepository,
        protected CustomerRepository $customerRepository,
        protected OrderRepository $orderRepository,
        protected PageRepository $pageRepository
    ) {
        $this->initializeClient();
        $this->initializeTools();
        $this->initializeSystemPrompt();
    }

    /**
     * Initialize OpenAI client with settings from store config
     */
    protected function initializeClient(): void
    {
        // First try to get API key from AI Assistant settings
        $apiKey = core()->getConfigData('general.magic_ai.ai_assistant.api_key');
        
        // Fallback to Magic AI general settings
        if (empty($apiKey)) {
            $apiKey = core()->getConfigData('general.magic_ai.settings.api_key');
        }
        
        // Fallback to .env
        if (empty($apiKey)) {
            $apiKey = config('openai.api_key');
        }

        if (empty($apiKey)) {
            throw new \Exception('OpenAI API ключ не настроен. Перейдите в Настройки → Magic AI → AI Ассистент и укажите API ключ.');
        }

        // Get model from AI Assistant settings
        $model = core()->getConfigData('general.magic_ai.ai_assistant.model');
        if (!empty($model)) {
            $this->model = $model;
        }

        // Get organization from store settings
        $organization = core()->getConfigData('general.magic_ai.settings.organization');

        $this->client = OpenAI::client($apiKey, $organization ?: null);
    }

    /**
     * Check if AI Assistant is enabled
     */
    public function isEnabled(): bool
    {
        return (bool) core()->getConfigData('general.magic_ai.ai_assistant.enabled');
    }

    /**
     * Initialize system prompt
     */
    protected function initializeSystemPrompt(): void
    {
        $basePrompt = <<<PROMPT
Ты - AI-ассистент для полного управления интернет-магазином на базе Bagisto. Ты помогаешь пользователям управлять всеми аспектами магазина.

## ТВОИ ВОЗМОЖНОСТИ:

### 📦 ТОВАРЫ:
- Просмотр, поиск и фильтрация товаров
- Изменение цен, названий, описаний
- Управление статусом (активен/неактивен)
- Просмотр информации о товаре

### 📁 КАТЕГОРИИ:
- Просмотр списка категорий
- Создание новых категорий
- Изменение названий и описаний категорий
- Изменение статуса категорий

### 👥 КЛИЕНТЫ:
- Просмотр списка клиентов
- Поиск клиентов по email или имени
- Просмотр информации о клиенте
- Блокировка/разблокировка клиентов

### 🛒 ЗАКАЗЫ:
- Просмотр списка заказов
- Поиск заказов по номеру или email клиента
- Просмотр деталей заказа
- Изменение статуса заказа

### 📄 CMS СТРАНИЦЫ:
- Просмотр списка страниц
- Создание новых страниц
- Редактирование содержимого страниц
- Изменение статуса страниц

### 📊 СТАТИСТИКА:
- Общая статистика магазина
- Статистика продаж
- Топ товаров

## ПРАВИЛА:
- Отвечай на русском языке
- Будь кратким и по делу
- Если нужно уточнить информацию - спрашивай
- После выполнения действия сообщай о результате
- При ошибках объясняй что пошло не так
- Используй форматирование для читаемости
- ВСЕГДА добавляй ссылки на страницы админки когда это уместно
- Ссылки форматируй как markdown: [текст](url)

## КРИТИЧЕСКИ ВАЖНО - ВЫПОЛНЕНИЕ ДЕЙСТВИЙ:
- Когда пользователь просит ИЗМЕНИТЬ, ОБНОВИТЬ, ПОМЕНЯТЬ что-то - ты ОБЯЗАН вызвать соответствующую функцию!
- НЕ ПРИТВОРЯЙСЯ что выполнил действие! Ты должен РЕАЛЬНО вызвать функцию!
- Для изменения статуса заказа - вызови update_order_status для КАЖДОГО заказа отдельно
- Для изменения товара - вызови update_product
- Для изменения категории - вызови update_category
- Если нужно изменить несколько записей - вызови функцию несколько раз, для каждой записи
- Сначала вызови функцию, потом сообщи о результате на основе ответа функции
- Если функция вернула success: false - значит действие НЕ выполнено, сообщи об ошибке

## ФОРМАТЫ ДАННЫХ:
- ID - числовой идентификатор (например: 1, 2, 19)
- Цена - число в рублях (например: 3500, 9000.50)
- Статус: true = активен, false = неактивен
- Дата: формат дд.мм.гггг

## ССЫЛКИ НА СТРАНИЦЫ АДМИНКИ:
ВАЖНО: Используй ТОЛЬКО относительные ссылки без домена! НЕ добавляй https://... или домен сайта!
Правильно: [Товар](/admin/catalog/products/edit/4)
Неправильно: [Товар](https://example.com/admin/catalog/products/edit/4)

Когда упоминаешь сущности, добавляй ссылки в формате markdown [текст](относительный_путь):
- Товар: [Название товара](/admin/catalog/products/edit/ID)
- Категория: [Название категории](/admin/catalog/categories/edit/ID)
- Заказ: [#Номер заказа](/admin/sales/orders/view/ID)
- Клиент: [Имя клиента](/admin/customers/customers/view/ID)
- CMS страница: [Название страницы](/admin/cms/edit/ID)
- Настройки Magic AI: [Настройки AI](/admin/configuration/general/magic_ai)
- Настройки магазина: [Общие настройки](/admin/configuration/general/general)
- Все товары: [Каталог товаров](/admin/catalog/products)
- Все заказы: [Список заказов](/admin/sales/orders)
- Все клиенты: [Список клиентов](/admin/customers/customers)
- Все категории: [Категории](/admin/catalog/categories)

Бери URL из поля "url" в данных, которые возвращают функции. Там уже правильные относительные пути.
PROMPT;

        // Add custom instructions from settings
        $customInstructions = core()->getConfigData('general.magic_ai.ai_assistant.instructions');
        if (!empty($customInstructions)) {
            $basePrompt .= "\n\n## ДОПОЛНИТЕЛЬНЫЕ ИНСТРУКЦИИ ОТ АДМИНИСТРАТОРА:\n" . $customInstructions;
        }

        $this->systemPrompt = $basePrompt;
    }

    /**
     * Initialize available tools
     */
    protected function initializeTools(): void
    {
        $this->tools = [
            // ==================== PRODUCTS ====================
            [
                'type' => 'function',
                'function' => [
                    'name' => 'list_products',
                    'description' => 'Получить список товаров с фильтрацией',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'min_price' => ['type' => 'number', 'description' => 'Минимальная цена'],
                            'max_price' => ['type' => 'number', 'description' => 'Максимальная цена'],
                            'status' => ['type' => 'boolean', 'description' => 'Фильтр по статусу'],
                            'category_id' => ['type' => 'integer', 'description' => 'ID категории'],
                            'limit' => ['type' => 'integer', 'description' => 'Количество товаров (по умолчанию 10)']
                        ],
                        'required' => []
                    ]
                ]
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'search_products',
                    'description' => 'Поиск товаров по названию или SKU',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'query' => ['type' => 'string', 'description' => 'Поисковый запрос'],
                            'limit' => ['type' => 'integer', 'description' => 'Максимум результатов']
                        ],
                        'required' => ['query']
                    ]
                ]
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'get_product_info',
                    'description' => 'Получить полную информацию о товаре',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'product_id' => ['type' => 'integer', 'description' => 'ID товара']
                        ],
                        'required' => ['product_id']
                    ]
                ]
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'update_product',
                    'description' => 'Обновить данные товара (цену, название, описание, статус)',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'product_id' => ['type' => 'integer', 'description' => 'ID товара'],
                            'price' => ['type' => 'number', 'description' => 'Новая цена'],
                            'name' => ['type' => 'string', 'description' => 'Новое название'],
                            'short_description' => ['type' => 'string', 'description' => 'Краткое описание'],
                            'description' => ['type' => 'string', 'description' => 'Полное описание'],
                            'status' => ['type' => 'boolean', 'description' => 'Статус активности']
                        ],
                        'required' => ['product_id']
                    ]
                ]
            ],

            // ==================== CATEGORIES ====================
            [
                'type' => 'function',
                'function' => [
                    'name' => 'list_categories',
                    'description' => 'Получить список категорий',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'parent_id' => ['type' => 'integer', 'description' => 'ID родительской категории'],
                            'status' => ['type' => 'boolean', 'description' => 'Фильтр по статусу']
                        ],
                        'required' => []
                    ]
                ]
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'get_category_info',
                    'description' => 'Получить информацию о категории',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'category_id' => ['type' => 'integer', 'description' => 'ID категории']
                        ],
                        'required' => ['category_id']
                    ]
                ]
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'update_category',
                    'description' => 'Обновить данные категории',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'category_id' => ['type' => 'integer', 'description' => 'ID категории'],
                            'name' => ['type' => 'string', 'description' => 'Новое название'],
                            'description' => ['type' => 'string', 'description' => 'Описание'],
                            'status' => ['type' => 'boolean', 'description' => 'Статус']
                        ],
                        'required' => ['category_id']
                    ]
                ]
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'create_category',
                    'description' => 'Создать новую категорию',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'name' => ['type' => 'string', 'description' => 'Название категории'],
                            'parent_id' => ['type' => 'integer', 'description' => 'ID родительской категории (1 = корневая)'],
                            'description' => ['type' => 'string', 'description' => 'Описание категории'],
                            'status' => ['type' => 'boolean', 'description' => 'Активна ли категория']
                        ],
                        'required' => ['name']
                    ]
                ]
            ],

            // ==================== CUSTOMERS ====================
            [
                'type' => 'function',
                'function' => [
                    'name' => 'list_customers',
                    'description' => 'Получить список клиентов',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'status' => ['type' => 'boolean', 'description' => 'Фильтр по статусу'],
                            'limit' => ['type' => 'integer', 'description' => 'Количество']
                        ],
                        'required' => []
                    ]
                ]
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'search_customers',
                    'description' => 'Поиск клиентов по email или имени',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'query' => ['type' => 'string', 'description' => 'Email или имя для поиска'],
                            'limit' => ['type' => 'integer', 'description' => 'Максимум результатов']
                        ],
                        'required' => ['query']
                    ]
                ]
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'get_customer_info',
                    'description' => 'Получить информацию о клиенте',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'customer_id' => ['type' => 'integer', 'description' => 'ID клиента']
                        ],
                        'required' => ['customer_id']
                    ]
                ]
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'update_customer_status',
                    'description' => 'Заблокировать или разблокировать клиента',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'customer_id' => ['type' => 'integer', 'description' => 'ID клиента'],
                            'status' => ['type' => 'boolean', 'description' => 'true = активен, false = заблокирован']
                        ],
                        'required' => ['customer_id', 'status']
                    ]
                ]
            ],

            // ==================== ORDERS ====================
            [
                'type' => 'function',
                'function' => [
                    'name' => 'list_orders',
                    'description' => 'Получить список заказов',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'status' => ['type' => 'string', 'description' => 'Статус заказа (pending, processing, completed, canceled)'],
                            'limit' => ['type' => 'integer', 'description' => 'Количество'],
                            'customer_id' => ['type' => 'integer', 'description' => 'ID клиента']
                        ],
                        'required' => []
                    ]
                ]
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'get_order_info',
                    'description' => 'Получить детальную информацию о заказе',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'order_id' => ['type' => 'integer', 'description' => 'ID заказа']
                        ],
                        'required' => ['order_id']
                    ]
                ]
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'search_orders',
                    'description' => 'Поиск заказов по номеру или email клиента',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'query' => ['type' => 'string', 'description' => 'Номер заказа или email']
                        ],
                        'required' => ['query']
                    ]
                ]
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'update_order_status',
                    'description' => 'Изменить статус заказа',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'order_id' => ['type' => 'integer', 'description' => 'ID заказа'],
                            'status' => ['type' => 'string', 'description' => 'Новый статус (pending, processing, completed, canceled)']
                        ],
                        'required' => ['order_id', 'status']
                    ]
                ]
            ],

            // ==================== CMS PAGES ====================
            [
                'type' => 'function',
                'function' => [
                    'name' => 'list_cms_pages',
                    'description' => 'Получить список CMS страниц',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'limit' => ['type' => 'integer', 'description' => 'Количество']
                        ],
                        'required' => []
                    ]
                ]
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'get_cms_page',
                    'description' => 'Получить содержимое CMS страницы',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'page_id' => ['type' => 'integer', 'description' => 'ID страницы']
                        ],
                        'required' => ['page_id']
                    ]
                ]
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'update_cms_page',
                    'description' => 'Обновить CMS страницу',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'page_id' => ['type' => 'integer', 'description' => 'ID страницы'],
                            'title' => ['type' => 'string', 'description' => 'Заголовок'],
                            'content' => ['type' => 'string', 'description' => 'HTML содержимое']
                        ],
                        'required' => ['page_id']
                    ]
                ]
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'create_cms_page',
                    'description' => 'Создать новую CMS страницу',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'title' => ['type' => 'string', 'description' => 'Заголовок страницы'],
                            'url_key' => ['type' => 'string', 'description' => 'URL ключ (латиницей)'],
                            'content' => ['type' => 'string', 'description' => 'HTML содержимое']
                        ],
                        'required' => ['title', 'url_key']
                    ]
                ]
            ],

            // ==================== STATISTICS ====================
            [
                'type' => 'function',
                'function' => [
                    'name' => 'get_store_statistics',
                    'description' => 'Получить общую статистику магазина',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => (object)[],
                        'required' => []
                    ]
                ]
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'get_sales_statistics',
                    'description' => 'Получить статистику продаж за период',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'days' => ['type' => 'integer', 'description' => 'За сколько дней (по умолчанию 30)']
                        ],
                        'required' => []
                    ]
                ]
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'get_top_products',
                    'description' => 'Получить топ продаваемых товаров',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'limit' => ['type' => 'integer', 'description' => 'Количество (по умолчанию 10)']
                        ],
                        'required' => []
                    ]
                ]
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'get_most_expensive_product',
                    'description' => 'Получить самый дорогой товар в магазине',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => (object)[],
                        'required' => []
                    ]
                ]
            ],
        ];
    }

    /**
     * Process user message
     */
    public function processMessage(string $message, array $context = []): array
    {
        if (!$this->isEnabled()) {
            return [
                'message' => 'AI Ассистент отключён. Включите его в настройках: Конфигурация → Magic AI → AI Ассистент.',
                'actions' => []
            ];
        }

        $messages = [
            ['role' => 'system', 'content' => $this->systemPrompt],
            ['role' => 'user', 'content' => $message]
        ];

        if (!empty($context['history'])) {
            array_splice($messages, 1, 0, $context['history']);
        }

        $response = $this->client->chat()->create([
            'model' => $this->model,
            'messages' => $messages,
            'tools' => $this->tools,
            'tool_choice' => 'auto',
            'max_tokens' => 1500,
            'temperature' => 0.7,
        ]);

        $assistantMessage = $response->choices[0]->message;
        $actions = [];

        if (!empty($assistantMessage->toolCalls)) {
            $toolResults = [];
            
            foreach ($assistantMessage->toolCalls as $toolCall) {
                $functionName = $toolCall->function->name;
                $arguments = json_decode($toolCall->function->arguments, true) ?? [];
                
                Log::info("AI Assistant calling: {$functionName}", $arguments);
                
                $result = $this->executeFunction($functionName, $arguments);
                $actions[] = [
                    'function' => $functionName,
                    'arguments' => $arguments,
                    'result' => $result
                ];
                
                $toolResults[] = [
                    'role' => 'tool',
                    'tool_call_id' => $toolCall->id,
                    'content' => json_encode($result, JSON_UNESCAPED_UNICODE)
                ];
            }

            $messages[] = $assistantMessage->toArray();
            $messages = array_merge($messages, $toolResults);

            $finalResponse = $this->client->chat()->create([
                'model' => $this->model,
                'messages' => $messages,
                'max_tokens' => 1000,
                'temperature' => 0.7,
            ]);

            return [
                'message' => $finalResponse->choices[0]->message->content,
                'actions' => $actions
            ];
        }

        return [
            'message' => $assistantMessage->content,
            'actions' => []
        ];
    }

    /**
     * Execute a tool function
     */
    protected function executeFunction(string $name, array $args): array
    {
        return match($name) {
            // Products
            'list_products' => $this->listProducts($args),
            'search_products' => $this->searchProducts($args['query'], $args['limit'] ?? 10),
            'get_product_info' => $this->getProductInfo($args['product_id']),
            'update_product' => $this->updateProduct($args),
            
            // Categories
            'list_categories' => $this->listCategories($args),
            'get_category_info' => $this->getCategoryInfo($args['category_id']),
            'update_category' => $this->updateCategory($args),
            'create_category' => $this->createCategory($args),
            
            // Customers
            'list_customers' => $this->listCustomers($args),
            'search_customers' => $this->searchCustomers($args['query'], $args['limit'] ?? 10),
            'get_customer_info' => $this->getCustomerInfo($args['customer_id']),
            'update_customer_status' => $this->updateCustomerStatus($args['customer_id'], $args['status']),
            
            // Orders
            'list_orders' => $this->listOrders($args),
            'get_order_info' => $this->getOrderInfo($args['order_id']),
            'search_orders' => $this->searchOrders($args['query']),
            'update_order_status' => $this->updateOrderStatus($args['order_id'], $args['status']),
            
            // CMS Pages
            'list_cms_pages' => $this->listCmsPages($args),
            'get_cms_page' => $this->getCmsPage($args['page_id']),
            'update_cms_page' => $this->updateCmsPage($args),
            'create_cms_page' => $this->createCmsPage($args),
            
            // Statistics
            'get_store_statistics' => $this->getStoreStatistics(),
            'get_sales_statistics' => $this->getSalesStatistics($args['days'] ?? 30),
            'get_top_products' => $this->getTopProducts($args['limit'] ?? 10),
            'get_most_expensive_product' => $this->getMostExpensiveProduct(),
            
            default => ['success' => false, 'error' => 'Неизвестная функция: ' . $name]
        };
    }

    // ==================== PRODUCT METHODS ====================

    protected function listProducts(array $filters): array
    {
        try {
            $query = DB::table('product_flat')
                ->where('locale', config('app.locale', 'en'));

            if (isset($filters['min_price'])) {
                $query->where('price', '>=', $filters['min_price']);
            }
            if (isset($filters['max_price'])) {
                $query->where('price', '<=', $filters['max_price']);
            }
            if (isset($filters['status'])) {
                $query->where('status', $filters['status'] ? 1 : 0);
            }
            if (isset($filters['category_id'])) {
                $query->join('product_categories', 'product_flat.product_id', '=', 'product_categories.product_id')
                      ->where('product_categories.category_id', $filters['category_id']);
            }

            $products = $query
                ->select('product_flat.product_id', 'name', 'sku', 'price', 'status')
                ->limit($filters['limit'] ?? 10)
                ->get();

            return [
                'success' => true,
                'count' => $products->count(),
                'all_products_url' => '/admin/catalog/products',
                'products' => $products->map(fn($p) => [
                    'id' => $p->product_id,
                    'name' => $p->name,
                    'sku' => $p->sku,
                    'price' => number_format($p->price, 0, '', ' ') . ' руб.',
                    'status' => $p->status ? '✅ активен' : '❌ неактивен',
                    'url' => '/admin/catalog/products/edit/' . $p->product_id
                ])->toArray()
            ];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    protected function searchProducts(string $query, int $limit = 10): array
    {
        try {
            $products = DB::table('product_flat')
                ->where('locale', config('app.locale', 'en'))
                ->where(fn($q) => $q->where('name', 'like', "%{$query}%")->orWhere('sku', 'like', "%{$query}%"))
                ->select('product_id', 'name', 'sku', 'price', 'status')
                ->limit($limit)
                ->get();

            return [
                'success' => true,
                'count' => $products->count(),
                'products' => $products->map(fn($p) => [
                    'id' => $p->product_id,
                    'name' => $p->name,
                    'sku' => $p->sku,
                    'price' => number_format($p->price, 0, '', ' ') . ' руб.',
                    'status' => $p->status ? '✅' : '❌',
                    'url' => '/admin/catalog/products/edit/' . $p->product_id
                ])->toArray()
            ];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    protected function getProductInfo(int $productId): array
    {
        try {
            $product = $this->productRepository->with(['images', 'categories'])->find($productId);
            if (!$product) {
                return ['success' => false, 'error' => "Товар #{$productId} не найден"];
            }

            $flat = DB::table('product_flat')
                ->where('product_id', $productId)
                ->where('locale', config('app.locale', 'en'))
                ->first();

            return [
                'success' => true,
                'product' => [
                    'id' => $product->id,
                    'sku' => $product->sku,
                    'type' => $product->type,
                    'name' => $flat->name ?? 'Без названия',
                    'price' => $flat->price ? number_format($flat->price, 0, '', ' ') . ' руб.' : 'Не указана',
                    'status' => $flat->status ? '✅ активен' : '❌ неактивен',
                    'short_description' => mb_substr($flat->short_description ?? '', 0, 200),
                    'categories' => $product->categories->pluck('name')->toArray(),
                    'images_count' => $product->images->count(),
                    'created_at' => $product->created_at->format('d.m.Y'),
                    'url' => '/admin/catalog/products/edit/' . $product->id
                ]
            ];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    protected function updateProduct(array $args): array
    {
        try {
            $product = $this->productRepository->find($args['product_id']);
            if (!$product) {
                return ['success' => false, 'error' => "Товар #{$args['product_id']} не найден"];
            }

            $updates = [];

            $fieldsMap = [
                'price' => 'price',
                'name' => 'name',
                'short_description' => 'short_description',
                'description' => 'description',
                'status' => 'status'
            ];

            foreach ($fieldsMap as $argKey => $attrCode) {
                if (isset($args[$argKey])) {
                    $attr = $this->attributeRepository->findOneByField('code', $attrCode);
                    if ($attr) {
                        $value = $args[$argKey];
                        if ($attrCode === 'status') {
                            $value = $value ? 1 : 0;
                        }
                        
                        DB::table('product_attribute_values')
                            ->where('product_id', $args['product_id'])
                            ->where('attribute_id', $attr->id)
                            ->update([$attr->column_name => $value]);
                        
                        $updates[] = $attrCode;
                    }
                }
            }

            app(\Webkul\Product\Helpers\Indexers\Flat::class)->refresh($product);

            return [
                'success' => true,
                'message' => "Товар #{$args['product_id']} обновлён. Изменено: " . implode(', ', $updates),
                'url' => '/admin/catalog/products/edit/' . $args['product_id']
            ];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // ==================== CATEGORY METHODS ====================

    protected function listCategories(array $filters): array
    {
        try {
            $locale = config('app.locale', 'en');
            
            $query = DB::table('categories')
                ->join('category_translations', function($join) use ($locale) {
                    $join->on('categories.id', '=', 'category_translations.category_id')
                         ->where('category_translations.locale', '=', $locale);
                })
                ->select('categories.id', 'categories.parent_id', 'categories.status', 'category_translations.name');

            if (isset($filters['parent_id'])) {
                $query->where('categories.parent_id', $filters['parent_id']);
            }
            if (isset($filters['status'])) {
                $query->where('categories.status', $filters['status'] ? 1 : 0);
            }

            $categories = $query->get();

            return [
                'success' => true,
                'count' => $categories->count(),
                'all_categories_url' => '/admin/catalog/categories',
                'categories' => $categories->map(fn($c) => [
                    'id' => $c->id,
                    'name' => $c->name,
                    'parent_id' => $c->parent_id,
                    'status' => $c->status ? '✅' : '❌',
                    'url' => '/admin/catalog/categories/edit/' . $c->id
                ])->toArray()
            ];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    protected function getCategoryInfo(int $categoryId): array
    {
        try {
            $category = $this->categoryRepository->find($categoryId);
            if (!$category) {
                return ['success' => false, 'error' => "Категория #{$categoryId} не найдена"];
            }

            $productsCount = DB::table('product_categories')
                ->where('category_id', $categoryId)
                ->count();

            return [
                'success' => true,
                'category' => [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'description' => mb_substr($category->description ?? '', 0, 200),
                    'status' => $category->status ? '✅ активна' : '❌ неактивна',
                    'parent_id' => $category->parent_id,
                    'products_count' => $productsCount,
                    'url' => '/admin/catalog/categories/edit/' . $category->id
                ]
            ];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    protected function updateCategory(array $args): array
    {
        try {
            $category = $this->categoryRepository->find($args['category_id']);
            if (!$category) {
                return ['success' => false, 'error' => "Категория #{$args['category_id']} не найдена"];
            }

            $updates = [];
            $locale = config('app.locale', 'en');

            if (isset($args['name'])) {
                DB::table('category_translations')
                    ->where('category_id', $args['category_id'])
                    ->where('locale', $locale)
                    ->update(['name' => $args['name']]);
                $updates[] = 'название';
            }

            if (isset($args['description'])) {
                DB::table('category_translations')
                    ->where('category_id', $args['category_id'])
                    ->where('locale', $locale)
                    ->update(['description' => $args['description']]);
                $updates[] = 'описание';
            }

            if (isset($args['status'])) {
                DB::table('categories')
                    ->where('id', $args['category_id'])
                    ->update(['status' => $args['status'] ? 1 : 0]);
                $updates[] = 'статус';
            }

            return [
                'success' => true,
                'message' => "Категория #{$args['category_id']} обновлена. Изменено: " . implode(', ', $updates),
                'url' => '/admin/catalog/categories/edit/' . $args['category_id']
            ];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    protected function createCategory(array $args): array
    {
        try {
            $locale = config('app.locale', 'en');
            $slug = \Str::slug($args['name']);
            
            // Check slug uniqueness
            $existingSlug = DB::table('category_translations')->where('slug', $slug)->exists();
            if ($existingSlug) {
                $slug = $slug . '-' . time();
            }

            $categoryId = DB::table('categories')->insertGetId([
                'parent_id' => $args['parent_id'] ?? 1,
                'position' => 0,
                'status' => isset($args['status']) ? ($args['status'] ? 1 : 0) : 1,
                'display_mode' => 'products_and_description',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('category_translations')->insert([
                'category_id' => $categoryId,
                'locale' => $locale,
                'name' => $args['name'],
                'slug' => $slug,
                'description' => $args['description'] ?? null,
            ]);

            return [
                'success' => true,
                'message' => "Категория '{$args['name']}' создана с ID #{$categoryId}",
                'url' => '/admin/catalog/categories/edit/' . $categoryId
            ];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // ==================== CUSTOMER METHODS ====================

    protected function listCustomers(array $filters): array
    {
        try {
            $query = DB::table('customers')
                ->select('id', 'first_name', 'last_name', 'email', 'status', 'created_at');

            if (isset($filters['status'])) {
                $query->where('status', $filters['status'] ? 1 : 0);
            }

            $customers = $query->orderBy('created_at', 'desc')
                ->limit($filters['limit'] ?? 20)
                ->get();

            return [
                'success' => true,
                'count' => $customers->count(),
                'all_customers_url' => '/admin/customers/customers',
                'customers' => $customers->map(fn($c) => [
                    'id' => $c->id,
                    'name' => trim($c->first_name . ' ' . $c->last_name),
                    'email' => $c->email,
                    'status' => $c->status ? '✅ активен' : '🚫 заблокирован',
                    'registered' => date('d.m.Y', strtotime($c->created_at)),
                    'url' => '/admin/customers/customers/view/' . $c->id
                ])->toArray()
            ];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    protected function searchCustomers(string $query, int $limit = 10): array
    {
        try {
            $customers = DB::table('customers')
                ->where(fn($q) => 
                    $q->where('email', 'like', "%{$query}%")
                      ->orWhere('first_name', 'like', "%{$query}%")
                      ->orWhere('last_name', 'like', "%{$query}%")
                )
                ->select('id', 'first_name', 'last_name', 'email', 'status')
                ->limit($limit)
                ->get();

            return [
                'success' => true,
                'count' => $customers->count(),
                'customers' => $customers->map(fn($c) => [
                    'id' => $c->id,
                    'name' => trim($c->first_name . ' ' . $c->last_name),
                    'email' => $c->email,
                    'status' => $c->status ? '✅' : '🚫',
                    'url' => '/admin/customers/customers/view/' . $c->id
                ])->toArray()
            ];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    protected function getCustomerInfo(int $customerId): array
    {
        try {
            $customer = $this->customerRepository->find($customerId);
            if (!$customer) {
                return ['success' => false, 'error' => "Клиент #{$customerId} не найден"];
            }

            $ordersCount = DB::table('orders')->where('customer_id', $customerId)->count();
            $totalSpent = DB::table('orders')
                ->where('customer_id', $customerId)
                ->where('status', '!=', 'canceled')
                ->sum('grand_total');

            return [
                'success' => true,
                'customer' => [
                    'id' => $customer->id,
                    'name' => $customer->name,
                    'email' => $customer->email,
                    'phone' => $customer->phone ?? 'Не указан',
                    'status' => $customer->status ? '✅ активен' : '🚫 заблокирован',
                    'orders_count' => $ordersCount,
                    'total_spent' => number_format($totalSpent, 0, '', ' ') . ' руб.',
                    'registered' => $customer->created_at->format('d.m.Y'),
                    'url' => '/admin/customers/customers/view/' . $customer->id
                ]
            ];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    protected function updateCustomerStatus(int $customerId, bool $status): array
    {
        try {
            $customer = $this->customerRepository->find($customerId);
            if (!$customer) {
                return ['success' => false, 'error' => "Клиент #{$customerId} не найден"];
            }

            DB::table('customers')
                ->where('id', $customerId)
                ->update(['status' => $status ? 1 : 0]);

            $statusText = $status ? 'разблокирован' : 'заблокирован';
            return [
                'success' => true,
                'message' => "Клиент {$customer->name} (#{$customerId}) {$statusText}",
                'url' => '/admin/customers/customers/view/' . $customerId
            ];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // ==================== ORDER METHODS ====================

    protected function listOrders(array $filters): array
    {
        try {
            $query = DB::table('orders')
                ->select('id', 'increment_id', 'customer_email', 'status', 'grand_total', 'created_at');

            if (isset($filters['status'])) {
                $query->where('status', $filters['status']);
            }
            if (isset($filters['customer_id'])) {
                $query->where('customer_id', $filters['customer_id']);
            }

            $orders = $query->orderBy('created_at', 'desc')
                ->limit($filters['limit'] ?? 20)
                ->get();

            $statusLabels = [
                'pending' => '⏳ Ожидает',
                'processing' => '🔄 В обработке',
                'completed' => '✅ Завершён',
                'canceled' => '❌ Отменён',
                'closed' => '📦 Закрыт'
            ];

            return [
                'success' => true,
                'count' => $orders->count(),
                'all_orders_url' => '/admin/sales/orders',
                'orders' => $orders->map(fn($o) => [
                    'id' => $o->id,
                    'number' => '#' . $o->increment_id,
                    'email' => $o->customer_email,
                    'status' => $statusLabels[$o->status] ?? $o->status,
                    'total' => number_format($o->grand_total, 0, '', ' ') . ' руб.',
                    'date' => date('d.m.Y H:i', strtotime($o->created_at)),
                    'url' => '/admin/sales/orders/view/' . $o->id
                ])->toArray()
            ];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    protected function getOrderInfo(int $orderId): array
    {
        try {
            $order = $this->orderRepository->with(['items', 'addresses'])->find($orderId);
            if (!$order) {
                return ['success' => false, 'error' => "Заказ #{$orderId} не найден"];
            }

            $statusLabels = [
                'pending' => '⏳ Ожидает',
                'processing' => '🔄 В обработке', 
                'completed' => '✅ Завершён',
                'canceled' => '❌ Отменён',
                'closed' => '📦 Закрыт'
            ];

            return [
                'success' => true,
                'order' => [
                    'id' => $order->id,
                    'number' => '#' . $order->increment_id,
                    'status' => $statusLabels[$order->status] ?? $order->status,
                    'customer_name' => $order->customer_first_name . ' ' . $order->customer_last_name,
                    'customer_email' => $order->customer_email,
                    'items_count' => $order->items->count(),
                    'items' => $order->items->map(fn($i) => [
                        'name' => $i->name,
                        'qty' => $i->qty_ordered,
                        'price' => number_format($i->price, 0, '', ' ') . ' руб.'
                    ])->toArray(),
                    'subtotal' => number_format($order->sub_total, 0, '', ' ') . ' руб.',
                    'shipping' => number_format($order->shipping_amount, 0, '', ' ') . ' руб.',
                    'total' => number_format($order->grand_total, 0, '', ' ') . ' руб.',
                    'payment_method' => $order->payment?->method_title ?? 'Не указан',
                    'shipping_method' => $order->shipping_title ?? 'Не указан',
                    'created_at' => $order->created_at->format('d.m.Y H:i'),
                    'url' => '/admin/sales/orders/view/' . $order->id
                ]
            ];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    protected function searchOrders(string $query): array
    {
        try {
            $orders = DB::table('orders')
                ->where(fn($q) => 
                    $q->where('increment_id', 'like', "%{$query}%")
                      ->orWhere('customer_email', 'like', "%{$query}%")
                )
                ->select('id', 'increment_id', 'customer_email', 'status', 'grand_total')
                ->limit(10)
                ->get();

            return [
                'success' => true,
                'count' => $orders->count(),
                'orders' => $orders->map(fn($o) => [
                    'id' => $o->id,
                    'number' => '#' . $o->increment_id,
                    'email' => $o->customer_email,
                    'status' => $o->status,
                    'total' => number_format($o->grand_total, 0, '', ' ') . ' руб.',
                    'url' => '/admin/sales/orders/view/' . $o->id
                ])->toArray()
            ];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    protected function updateOrderStatus(int $orderId, string $status): array
    {
        try {
            Log::info('AI Assistant: updateOrderStatus called', ['order_id' => $orderId, 'status' => $status]);
            
            $order = $this->orderRepository->find($orderId);
            if (!$order) {
                Log::warning('AI Assistant: Order not found', ['order_id' => $orderId]);
                return ['success' => false, 'error' => "Заказ #{$orderId} не найден"];
            }

            $allowedStatuses = ['pending', 'processing', 'completed', 'canceled', 'closed'];
            if (!in_array($status, $allowedStatuses)) {
                return ['success' => false, 'error' => "Недопустимый статус. Доступны: " . implode(', ', $allowedStatuses)];
            }

            $oldStatus = $order->status;
            
            // Use OrderRepository method to properly update status with events
            $this->orderRepository->updateOrderStatus($order, $status);
            
            // Reload order to verify change
            $order->refresh();
            
            Log::info('AI Assistant: Order status updated', [
                'order_id' => $orderId,
                'old_status' => $oldStatus,
                'new_status' => $order->status,
                'requested_status' => $status
            ]);

            return [
                'success' => true,
                'message' => "Статус заказа #{$order->increment_id} изменён с '{$oldStatus}' на '{$order->status}'",
                'url' => '/admin/sales/orders/view/' . $orderId
            ];
        } catch (\Exception $e) {
            Log::error('AI Assistant: updateOrderStatus error', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // ==================== CMS PAGES METHODS ====================

    protected function listCmsPages(array $filters): array
    {
        try {
            $locale = config('app.locale', 'en');
            
            $pages = DB::table('cms_pages')
                ->join('cms_page_translations', function($join) use ($locale) {
                    $join->on('cms_pages.id', '=', 'cms_page_translations.cms_page_id')
                         ->where('cms_page_translations.locale', '=', $locale);
                })
                ->select('cms_pages.id', 'cms_page_translations.page_title', 'cms_page_translations.url_key')
                ->limit($filters['limit'] ?? 20)
                ->get();

            return [
                'success' => true,
                'count' => $pages->count(),
                'pages' => $pages->map(fn($p) => [
                    'id' => $p->id,
                    'title' => $p->page_title,
                    'url' => '/' . $p->url_key,
                    'edit_url' => '/admin/cms/edit/' . $p->id
                ])->toArray()
            ];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    protected function getCmsPage(int $pageId): array
    {
        try {
            $page = $this->pageRepository->find($pageId);
            if (!$page) {
                return ['success' => false, 'error' => "Страница #{$pageId} не найдена"];
            }

            return [
                'success' => true,
                'page' => [
                    'id' => $page->id,
                    'title' => $page->page_title,
                    'url_key' => $page->url_key,
                    'content' => mb_substr(strip_tags($page->html_content ?? ''), 0, 500),
                    'meta_title' => $page->meta_title,
                    'meta_description' => $page->meta_description,
                    'edit_url' => '/admin/cms/edit/' . $page->id
                ]
            ];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    protected function updateCmsPage(array $args): array
    {
        try {
            $page = $this->pageRepository->find($args['page_id']);
            if (!$page) {
                return ['success' => false, 'error' => "Страница #{$args['page_id']} не найдена"];
            }

            $locale = config('app.locale', 'en');
            $updates = [];

            if (isset($args['title'])) {
                DB::table('cms_page_translations')
                    ->where('cms_page_id', $args['page_id'])
                    ->where('locale', $locale)
                    ->update(['page_title' => $args['title']]);
                $updates[] = 'заголовок';
            }

            if (isset($args['content'])) {
                DB::table('cms_page_translations')
                    ->where('cms_page_id', $args['page_id'])
                    ->where('locale', $locale)
                    ->update(['html_content' => $args['content']]);
                $updates[] = 'содержимое';
            }

            return [
                'success' => true,
                'message' => "Страница #{$args['page_id']} обновлена. Изменено: " . implode(', ', $updates),
                'url' => '/admin/cms/edit/' . $args['page_id']
            ];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    protected function createCmsPage(array $args): array
    {
        try {
            $locale = config('app.locale', 'en');

            $pageId = DB::table('cms_pages')->insertGetId([
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('cms_page_translations')->insert([
                'cms_page_id' => $pageId,
                'locale' => $locale,
                'page_title' => $args['title'],
                'url_key' => $args['url_key'],
                'html_content' => $args['content'] ?? '',
            ]);

            // Attach to channel
            $channelId = core()->getCurrentChannel()->id ?? 1;
            DB::table('cms_page_channels')->insert([
                'cms_page_id' => $pageId,
                'channel_id' => $channelId,
            ]);

            return [
                'success' => true,
                'message' => "Страница '{$args['title']}' создана с ID #{$pageId}. URL: /{$args['url_key']}",
                'edit_url' => '/admin/cms/edit/' . $pageId
            ];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // ==================== STATISTICS METHODS ====================

    protected function getStoreStatistics(): array
    {
        try {
            $productsCount = DB::table('products')->count();
            $activeProducts = DB::table('product_flat')
                ->where('locale', config('app.locale', 'en'))
                ->where('status', 1)
                ->count();
            $categoriesCount = DB::table('categories')->count();
            $customersCount = DB::table('customers')->count();
            $ordersCount = DB::table('orders')->count();
            $totalRevenue = DB::table('orders')
                ->where('status', '!=', 'canceled')
                ->sum('grand_total');

            return [
                'success' => true,
                'statistics' => [
                    '📦 Всего товаров' => $productsCount,
                    '✅ Активных товаров' => $activeProducts,
                    '📁 Категорий' => $categoriesCount,
                    '👥 Клиентов' => $customersCount,
                    '🛒 Заказов' => $ordersCount,
                    '💰 Общая выручка' => number_format($totalRevenue, 0, '', ' ') . ' руб.'
                ],
                'links' => [
                    'products' => '/admin/catalog/products',
                    'categories' => '/admin/catalog/categories',
                    'customers' => '/admin/customers/customers',
                    'orders' => '/admin/sales/orders',
                    'settings' => '/admin/configuration'
                ]
            ];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    protected function getSalesStatistics(int $days = 30): array
    {
        try {
            $startDate = now()->subDays($days);
            
            $stats = DB::table('orders')
                ->where('created_at', '>=', $startDate)
                ->where('status', '!=', 'canceled')
                ->selectRaw('COUNT(*) as orders_count, SUM(grand_total) as total_revenue, AVG(grand_total) as avg_order')
                ->first();

            $ordersCount = $stats->orders_count ?? 0;
            $totalRevenue = $stats->total_revenue ?? 0;
            $avgOrder = $stats->avg_order ?? 0;

            return [
                'success' => true,
                'period' => "Последние {$days} дней",
                'statistics' => [
                    '🛒 Заказов' => $ordersCount,
                    '💰 Выручка' => number_format($totalRevenue, 0, '', ' ') . ' руб.',
                    '📊 Средний чек' => number_format($avgOrder, 0, '', ' ') . ' руб.',
                    '📈 Заказов в день' => round($ordersCount / $days, 1)
                ]
            ];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    protected function getTopProducts(int $limit = 10): array
    {
        try {
            // Check if there are any orders first
            $hasOrders = DB::table('order_items')->exists();
            
            if (!$hasOrders) {
                // No orders - try to get most expensive products instead
                $products = DB::table('product_flat')
                    ->where('locale', config('app.locale', 'en'))
                    ->where('status', 1)
                    ->whereNotNull('price')
                    ->orderByDesc('price')
                    ->limit($limit)
                    ->select('product_id', 'name', 'price')
                    ->get();

                if ($products->isEmpty()) {
                    return [
                        'success' => true,
                        'message' => 'Нет данных о продажах. Показаны самые дорогие товары.',
                        'top_products' => [],
                        'all_products_url' => '/admin/catalog/products'
                    ];
                }

                return [
                    'success' => true,
                    'message' => 'Нет данных о продажах. Показаны самые дорогие товары:',
                    'top_products' => $products->map(fn($p, $i) => [
                        'rank' => $i + 1,
                        'id' => $p->product_id,
                        'name' => $p->name,
                        'price' => number_format($p->price, 0, '', ' ') . ' руб.',
                        'url' => '/admin/catalog/products/edit/' . $p->product_id
                    ])->toArray()
                ];
            }

            $topProducts = DB::table('order_items')
                ->join('orders', 'order_items.order_id', '=', 'orders.id')
                ->where('orders.status', '!=', 'canceled')
                ->groupBy('order_items.product_id')
                ->selectRaw('order_items.product_id, MAX(order_items.name) as name, SUM(order_items.qty_ordered) as total_qty, SUM(order_items.total) as total_revenue')
                ->orderByDesc('total_qty')
                ->limit($limit)
                ->get();

            return [
                'success' => true,
                'top_products' => $topProducts->map(fn($p, $i) => [
                    'rank' => $i + 1,
                    'id' => $p->product_id,
                    'name' => $p->name,
                    'sold' => (int)$p->total_qty . ' шт.',
                    'revenue' => number_format($p->total_revenue, 0, '', ' ') . ' руб.',
                    'url' => '/admin/catalog/products/edit/' . $p->product_id
                ])->toArray()
            ];
        } catch (\Exception $e) {
            Log::error('get_top_products error: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    protected function getMostExpensiveProduct(): array
    {
        try {
            $product = DB::table('product_flat')
                ->where('locale', config('app.locale', 'en'))
                ->where('status', 1)
                ->whereNotNull('price')
                ->where('price', '>', 0)
                ->orderByDesc('price')
                ->first(['product_id', 'name', 'sku', 'price']);

            if (!$product) {
                return [
                    'success' => false,
                    'error' => 'Активных товаров с ценой не найдено',
                    'all_products_url' => '/admin/catalog/products'
                ];
            }

            return [
                'success' => true,
                'product' => [
                    'id' => $product->product_id,
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'price' => number_format($product->price, 0, '', ' ') . ' руб.',
                    'url' => '/admin/catalog/products/edit/' . $product->product_id
                ]
            ];
        } catch (\Exception $e) {
            Log::error('get_most_expensive_product error: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}
