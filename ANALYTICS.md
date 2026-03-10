# Analytics System — Architecture Documentation

> Полная система аналитики для фуд-платформы (app / kiosk / cashier)

---

## Table of Contents
1. [Обзор](#overview)
2. [Структура файлов](#file-structure)
3. [Database Schema](#database)
4. [Event Tracking Schema](#events)
5. [KPI Формулы](#kpi-formulas)
6. [API Endpoints](#api)
7. [Dashboard Views](#dashboards)
8. [Deployment](#deployment)

---

## <a name="overview"></a>1. Обзор архитектуры

```
┌──────────────────────────────────────────────────────────────┐
│                    Frontend (Blade + Vue.js)                 │
│  Executive │ Daily │ Product │ Operations │ Menu │ Channels  │
└──────┬──────┬──────┬──────┬──────┬──────┬──────┬─────────────┘
       │      │      │      │      │      │      │
       ▼      ▼      ▼      ▼      ▼      ▼      ▼
┌──────────────────────────────────────────────────────────────┐
│              AnalyticsDashboardController                     │
│         (6 page routes + 6 JSON stats endpoints)             │
└──────────────────────┬───────────────────────────────────────┘
                       │
       ┌───────────────┼───────────────┐
       ▼               ▼               ▼
┌─────────────┐ ┌─────────────┐ ┌──────────────┐
│ NorthStar   │ │ Funnel      │ │ Operations   │
│ Service     │ │ Retention   │ │ Service      │
│             │ │ Service     │ │              │
├─────────────┤ ├─────────────┤ ├──────────────┤
│ Menu        │ │ Visit       │ │ Payments     │
│ Analytics   │ │ Behavior    │ │ Channels     │
│ Service     │ │ Service     │ │ Service      │
└──────┬──────┘ └──────┬──────┘ └──────┬───────┘
       │               │               │
       ▼               ▼               ▼
┌──────────────────────────────────────────────────────────────┐
│                    MySQL Database                             │
│  orders │ analytics_events │ analytics_sessions │             │
│  analytics_order_timestamps │ analytics_daily_kpi │ ...      │
└──────────────────────────────────────────────────────────────┘
       ▲
       │  Event Listeners
┌──────┴──────────────────────────────────────────┐
│  AnalyticsOrderListener                          │
│  checkout.order.save.after                       │
│  sales.order.update-status.after                 │
│  sales.order.cancel.after                        │
└──────────────────────────────────────────────────┘
```

---

## <a name="file-structure"></a>2. Структура файлов

```
app/
├── Console/Commands/
│   └── AggregateDailyKpi.php          # Artisan: analytics:aggregate-daily
├── Http/Controllers/Admin/Analytics/
│   ├── AnalyticsDashboardController.php  # 6 pages + 6 stats
│   └── AnalyticsEventController.php      # API event ingestion
├── Listeners/Order/
│   └── AnalyticsOrderListener.php        # Order event → timestamps
├── Models/Analytics/
│   ├── AnalyticsDailyKpi.php
│   ├── AnalyticsEvent.php
│   ├── AnalyticsIncident.php
│   ├── AnalyticsKioskStatus.php
│   ├── AnalyticsLocation.php
│   ├── AnalyticsOrderTimestamp.php
│   ├── AnalyticsPaymentAttempt.php
│   └── AnalyticsSession.php
├── Services/Analytics/
│   ├── BaseAnalyticsService.php          # Общий: dateRange, filters, percentChange
│   ├── DailyKpiAggregator.php            # Агрегация в daily_kpi
│   ├── EventTracker.php                  # Трекинг событий
│   ├── FunnelRetentionService.php        # DAU/WAU/MAU, конверсия, когорты
│   ├── MenuAnalyticsService.php          # Блюда, ингредиенты, dead items
│   ├── NorthStarService.php              # GMV, AOV, SLA, repeat rate
│   ├── OperationsService.php             # Stage times, качество, heatmap
│   ├── PaymentsChannelsService.php       # Оплаты, каналы, NPS, жалобы
│   └── VisitBehaviorService.php          # Order mix по визитам, AOV by visit
├── Providers/
│   └── AppServiceProvider.php            # Регистрация event listeners

database/migrations/
├── 2026_03_10_000001_create_analytics_events_table.php
├── 2026_03_10_000002_create_analytics_sessions_table.php
├── 2026_03_10_000003_create_analytics_locations_table.php
├── 2026_03_10_000004_create_analytics_order_timestamps_table.php
├── 2026_03_10_000005_create_analytics_payment_attempts_table.php
├── 2026_03_10_000006_create_analytics_incidents_table.php
├── 2026_03_10_000007_create_analytics_kiosk_status_table.php
└── 2026_03_10_000008_create_analytics_daily_kpi_table.php

packages/Webkul/Admin/src/
├── Config/menu.php                       # +7 menu items (analytics.*)
├── Routes/analytics-routes.php           # 12 routes
└── Resources/views/analytics/
    ├── partials/filters.blade.php        # Shared date+channel filters
    ├── executive.blade.php               # Section A: North Star KPIs
    ├── daily.blade.php                   # Section K: Daily management
    ├── product.blade.php                 # Sections B-D: Funnel, retention, visits
    ├── operations.blade.php              # Sections G-H: Stage times, quality, payments
    ├── menu.blade.php                    # Section E: Menu & ingredients
    └── channels.blade.php               # Section I: Channels & format

routes/web.php                            # +3 API event ingestion routes
```

---

## <a name="database"></a>3. Database Schema

### analytics_events
Сырые события для воронки и поведенческого анализа.

| Column       | Type        | Description                        |
|-------------|-------------|-------------------------------------|
| id          | bigint PK   | Auto-increment                     |
| event_name  | varchar(100)| `page_view`, `add_to_cart`, `order_created`, etc. |
| customer_id | bigint NULL  | FK → customers                     |
| session_id  | varchar(64) | UUID сессии                        |
| order_id    | bigint NULL  | FK → orders                        |
| channel     | varchar(50) | `app`, `kiosk`, `cashier`          |
| location_id | bigint NULL  | FK → analytics_locations           |
| device_type | varchar(30) | `ios`, `android`, `kiosk`, `web`   |
| properties  | json        | Свободные доп. данные              |
| created_at  | timestamp   | Индексирован                       |

**Indexes:** `(event_name, created_at)`, `(session_id)`, `(customer_id, created_at)`

### analytics_sessions
Один ряд на сессию, для подсчёта DAU/WAU/MAU и конверсии.

| Column         | Type        | Description            |
|---------------|-------------|-------------------------|
| id            | bigint PK   |                        |
| session_id    | varchar(64) | UNIQUE                 |
| customer_id   | bigint NULL  |                        |
| channel       | varchar(50) |                        |
| visit_number  | int (def 1) | Порядковый визит       |
| is_first_session | boolean  | Первый визит?          |
| has_order     | boolean     | Закончился заказом?    |
| page_views    | int (def 0) |                        |
| events_count  | int (def 0) |                        |
| started_at    | timestamp   |                        |
| ended_at      | timestamp   |                        |

### analytics_locations
Справочник точек.

| Column | Type        |
|--------|-------------|
| id     | bigint PK   |
| name   | varchar(255)|
| code   | varchar(50) UNIQUE |
| zone   | varchar(100)|
| address| text NULL    |
| is_active | boolean  |

### analytics_order_timestamps
Таймлайн статусов для расчёта stage times и SLA.

| Column        | Type        | Description                    |
|--------------|-------------|--------------------------------|
| id           | bigint PK   |                                |
| order_id     | bigint UNIQUE| FK → orders                   |
| channel      | varchar(50) |                                |
| location_id  | bigint NULL  |                                |
| order_type   | varchar(30) | `dine_in`, `takeaway`          |
| created_at   | timestamp   | Заказ создан                   |
| accepted_at  | timestamp NULL | `processing`                |
| preparing_at | timestamp NULL | `preparing`                 |
| ready_at     | timestamp NULL | `ready`                     |
| served_at    | timestamp NULL | Выдано                      |
| completed_at | timestamp NULL | `completed`                 |
| cancelled_at | timestamp NULL | `canceled`                  |
| within_sla   | boolean NULL| Уложился в SLA?               |
| sla_seconds  | int NULL    | Порог SLA                      |
| total_seconds| int NULL    | Общее время обработки          |

**Model accessors:** `accept_duration`, `prepare_duration`, `handoff_duration` (в секундах)

### analytics_payment_attempts
Каждая попытка оплаты.

| Column         | Type        |
|---------------|-------------|
| id            | bigint PK   |
| order_id      | bigint NULL  |
| payment_method| varchar(50) |
| status        | enum: initiated, success, failed, timeout, cancelled |
| fail_reason   | varchar(255) NULL |
| duration_seconds | decimal(8,2) NULL |
| amount        | decimal(12,2) NULL |

### analytics_incidents
Жалобы, возвраты, фидбэк.

| Column         | Type        |
|---------------|-------------|
| id            | bigint PK   |
| order_id      | bigint NULL  |
| customer_id   | bigint NULL  |
| type          | enum: complaint, incorrect_order, refund, cancel, feedback |
| description   | text NULL    |
| rating        | tinyint 1-5 NULL |
| feedback_theme| varchar(100) NULL |
| status        | varchar(30) def 'open' |
| resolved_at   | timestamp NULL |

### analytics_kiosk_status
Heartbeat статус киосков.

| Column            | Type        |
|------------------|-------------|
| id               | bigint PK   |
| kiosk_code       | varchar(50) |
| location_id      | bigint NULL  |
| status           | enum: online, offline, degraded |
| last_heartbeat_at| timestamp NULL |
| uptime_seconds   | int def 0   |
| downtime_seconds | int def 0   |
| meta             | json NULL    |

**Model accessor:** `uptime_percent`

### analytics_daily_kpi
Пре-агрегированный ежедневный снимок (~35 метрик).

| Column | Type | Description |
|--------|------|-------------|
| date   | date | Composit UNIQUE с channel + location_id |
| channel | varchar(50) NULL | |
| location_id | bigint NULL | |
| total_orders | int | |
| total_revenue | decimal(14,2) | |
| online_orders / online_revenue | int / decimal | |
| aov | decimal(10,2) | |
| unique_customers | int | |
| new_customers | int | |
| repeat_customers | int | |
| repeat_rate | decimal(5,2) | |
| avg_ready_seconds | decimal(8,2) | |
| orders_within_sla | int | |
| sla_rate | decimal(5,2) | |
| avg_accept_seconds | decimal(8,2) | |
| avg_prepare_seconds | decimal(8,2) | |
| avg_handoff_seconds | decimal(8,2) | |
| payment_success_rate | decimal(5,2) | |
| dau / wau / mau | int | |
| session_to_order_rate | decimal(5,2) | |
| avg_rating | decimal(3,2) | |
| complaints_count | int | |
| cancelled_orders | int | |

---

## <a name="events"></a>4. Event Tracking Schema

### Автоматически отслеживаемые события (через Listeners)

| Event Key              | Trigger                        | Properties                       |
|-----------------------|--------------------------------|----------------------------------|
| `order_created`       | `checkout.order.save.after`    | order_id, channel, total, items_count |
| `order_status_changed`| `sales.order.update-status.after` | order_id, status, channel, total |
| `order_cancelled`     | `sales.order.cancel.after`     | order_id, channel, total, reason |

### События от клиентского приложения (через API)

| Event Key          | Description            | Properties                 |
|-------------------|------------------------|----------------------------|
| `page_view`       | Просмотр страницы      | page, url                  |
| `category_view`   | Просмотр категории     | category_id, category_name |
| `product_view`    | Просмотр товара        | product_id, product_name   |
| `add_to_cart`     | Добавление в корзину   | product_id, quantity, price|
| `remove_from_cart`| Удаление из корзины    | product_id                 |
| `checkout_start`  | Начало оформления      | cart_total, items_count    |
| `payment_start`   | Начало оплаты          | method, amount             |
| `payment_success` | Успешная оплата        | method, amount, duration   |
| `payment_fail`    | Неудачная оплата       | method, reason             |
| `session_start`   | Начало сессии          | device_type                |
| `rating_submit`   | Оценка заказа          | order_id, rating, comment  |

### API для трекинга

```
POST /api/analytics/events       → store single event
POST /api/analytics/events/batch → store batch of events (max 100)
POST /api/analytics/sessions     → start/mark session
```

Body (single event):
```json
{
  "event_name": "page_view",
  "session_id": "uuid-...",
  "customer_id": 123,
  "channel": "app",
  "device_type": "ios",
  "properties": {"page": "/menu"}
}
```

---

## <a name="kpi-formulas"></a>5. KPI Формулы

### Section A: North Star Metrics

| KPI | Formula |
|-----|---------|
| **Online Order Share** | `COUNT(orders WHERE channel IN ('app','kiosk')) / COUNT(orders) × 100%` |
| **GMV** | `SUM(orders.base_grand_total)` |
| **AOV** | `GMV / COUNT(orders)` |
| **Avg Ready Time** | `AVG(analytics_order_timestamps.total_seconds) WHERE ready_at IS NOT NULL` |
| **SLA Rate** | `COUNT(within_sla = true) / COUNT(timestamps) × 100%` |
| **Repeat Rate** | `COUNT(DISTINCT customers с ≥2 orders) / COUNT(DISTINCT customers) × 100%` |
| **Payment Success** | `COUNT(payment_attempts.status='success') / COUNT(payment_attempts) × 100%` |

### Section B: Funnel & Conversion

| KPI | Formula |
|-----|---------|
| **DAU** | `COUNT(DISTINCT customer_id) FROM orders WHERE date = today` |
| **WAU** | Same for last 7 days |
| **MAU** | Same for last 30 days |
| **Session → Order** | `COUNT(sessions WHERE has_order) / COUNT(sessions) × 100%` per channel |
| **Funnel Drop-off** | 4 steps: `page_view → add_to_cart → checkout_start → order_created` — unique sessions per step |
| **Time to Payment** | `AVG(TIMESTAMPDIFF(payment_attempts.created_at, order.created_at))` |

### Section C: Retention

| KPI | Formula |
|-----|---------|
| **Cohort Retention D1** | Users from cohort who ordered again within 1 day |
| **Cohort Retention D7** | ...within 7 days |
| **Cohort Retention D30** | ...within 30 days |
| **Orders per User** | `COUNT(orders) / COUNT(DISTINCT customer_id)` |
| **Median Time Between Orders** | Median of `TIMESTAMPDIFF` between consecutive orders per customer |
| **ARPU** | `SUM(revenue) / COUNT(DISTINCT customers with activity)` |
| **RPPU** | `SUM(revenue) / COUNT(DISTINCT paying customers)` |

### Section D: First Visit vs. Second Visit

| KPI | Formula |
|-----|---------|
| **AOV by Visit** | `AVG(base_grand_total)` grouped by customer's order sequence number (1st, 2nd, 3rd+) |
| **Repeat Dish Rate** | `(items ordered in 2nd+ order that appeared in 1st order) / (total items in 2nd+ orders) × 100%` |
| **Order Mix** | Category distribution for 1st order vs 2nd order |

### Section E: Menu Analytics

| KPI | Formula |
|-----|---------|
| **Top Dishes Revenue** | `SUM(order_items.total) GROUP BY product_id ORDER BY revenue DESC` |
| **Top Dishes Quantity** | Same, ordered by `SUM(qty_ordered)` |
| **Attach Rate** | `COUNT(orders containing drink/dessert category) / COUNT(orders) × 100%` |
| **Customization Rate** | `COUNT(order_items with additional JSON options) / COUNT(order_items) × 100%` |
| **Dead Items** | Products with 0 sales in last N days (default 14) |
| **AOV Uplift** | For each product: `AVG(order total where product present) - AVG(all order totals)` |
| **New Dish Trial Rate** | `COUNT(orders with new product) / COUNT(orders) × 100%` |

### Section G: Operations

| KPI | Formula |
|-----|---------|
| **Accept Time** | `AVG(accepted_at - created_at)` |
| **Prepare Time** | `AVG(preparing_at - accepted_at)` or `AVG(ready_at - accepted_at)` |
| **Handoff Time** | `AVG(completed_at - ready_at)` |
| **Incorrect Order Rate** | `COUNT(incidents.type='incorrect_order') / COUNT(orders) × 100%` |
| **Cancel Rate** | `COUNT(incidents.type='cancel') / COUNT(orders) × 100%` |
| **Refund Rate** | `COUNT(incidents.type='refund') / COUNT(orders) × 100%` |

### Section H: Payments & Stability

| KPI | Formula |
|-----|---------|
| **Payment Success by Method** | Grouped by `payment_method`: `success / total × 100%` |
| **Top Fail Reasons** | `GROUP BY fail_reason ORDER BY COUNT DESC` |
| **Crash-Free Sessions** | `(1 - sessions with 'app_crash' event / total sessions) × 100%` |

### Section I: Channels & Format

| KPI | Formula |
|-----|---------|
| **Channel Split** | `COUNT(orders) GROUP BY channel_name` + percentage |
| **Dine-in vs Takeaway** | `COUNT(orders) GROUP BY order_type` |
| **Revenue by Channel** | `SUM(base_grand_total) GROUP BY channel_name` |
| **Post-Order Rating** | `AVG(rating) WHERE rating IS NOT NULL` |
| **NPS** | `(promoters% - detractors%) × 100` where promoters=4-5, detractors=1-2 |

### Section K: Daily Management Dashboard

Quick snapshot (10 cards):
1. Orders today | 2. Revenue today | 3. AOV today | 4. Avg ready time today |
5. Online share today | 6. Repeat rate today | 7. Payment success today |
8. Avg rating today | 9. SLA rate today | 10. Complaints today

---

## <a name="api"></a>6. API Endpoints

### Admin Dashboard Routes (auth required)

| Method | URI | Name | Description |
|--------|-----|------|-------------|
| GET | `/admin/analytics/executive` | admin.analytics.executive | Executive dashboard page |
| GET | `/admin/analytics/daily` | admin.analytics.daily | Daily management page |
| GET | `/admin/analytics/product` | admin.analytics.product | Product analytics page |
| GET | `/admin/analytics/operations` | admin.analytics.operations | Operations page |
| GET | `/admin/analytics/menu` | admin.analytics.menu | Menu analytics page |
| GET | `/admin/analytics/channels` | admin.analytics.channels | Channels page |
| GET | `/admin/analytics/executive/stats` | admin.analytics.executive.stats | JSON KPI data |
| GET | `/admin/analytics/daily/stats` | admin.analytics.daily.stats | JSON daily data |
| GET | `/admin/analytics/product/stats` | admin.analytics.product.stats | JSON product data |
| GET | `/admin/analytics/operations/stats` | admin.analytics.operations.stats | JSON operations data |
| GET | `/admin/analytics/menu/stats` | admin.analytics.menu.stats | JSON menu data |
| GET | `/admin/analytics/channels/stats` | admin.analytics.channels.stats | JSON channels data |

### Public API (throttled 120/min)

| Method | URI | Description |
|--------|-----|-------------|
| POST | `/api/analytics/events` | Receive single event |
| POST | `/api/analytics/events/batch` | Receive batch (max 100) |
| POST | `/api/analytics/sessions` | Start/mark session |

---

## <a name="dashboards"></a>7. Dashboard Views

### Executive (Section A)
- 8 KPI cards с trend % к предыдущему периоду
- Active users (DAU / WAU / MAU)
- ARPU / RPPU
- Channel revenue split bar chart

### Daily (Section K)
- 10 метрик "на сегодня" vs "вчера"
- Таблица топ-5 блюд дня

### Product (Sections B-D)
- Funnel visualization (4 ступени)
- Session→Order conversion по каналам
- Time to payment
- Когортная таблица D1/D7/D30
- Orders per user, Median TBO
- AOV by visit (1st vs 2nd vs 3rd+)
- Repeat dish rate
- Category mix 1st vs 2nd order

### Operations (Sections G-H)
- 4 stage time cards (accept, prepare, handoff, total)
- 5 quality metrics (incorrect, cancel, refund, handoff delay, crash-free)
- Payment success by method + progress bars
- Fail reasons list
- NPS gauge
- Complaints + top themes
- Order heatmap (day × hour)
- Kiosk uptime cards

### Menu (Section E)
- Top dishes by revenue / quantity tables (top 15)
- Attach rate, Customization rate, New dish trial rate
- Top added/removed ingredients
- Dead items grid
- AOV uplift per item table

### Channels (Section I)
- Channel split cards (app/kiosk/cashier) with progress bars
- Dine-in vs Take-away split
- Orders by location table
- Revenue by channel horizontal bars
- Rating stars + NPS gauge + Complaints

---

## <a name="deployment"></a>8. Deployment

### 1. Run migrations
```bash
php artisan migrate
```

### 2. Schedule daily aggregation
Add to `app/Console/Kernel.php` or `routes/console.php`:
```php
Schedule::command('analytics:aggregate-daily')->dailyAt('02:00');
```

### 3. Seed locations (optional)
```php
AnalyticsLocation::create(['name' => 'Точка 1', 'code' => 'loc-001', 'zone' => 'Москва']);
```

### 4. Configure SLA threshold (optional)
In `.env` or `config/analytics.php`:
```
ANALYTICS_SLA_SECONDS=420
```

### 5. Client-side event tracking
Integrate the tracking API in your app/kiosk frontend:
```javascript
// On page view
fetch('/api/analytics/events', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({
    event_name: 'page_view',
    session_id: getSessionId(),
    channel: 'app',
    device_type: 'ios',
    properties: { page: '/menu' }
  })
});
```

### 6. Backfill historical data (optional)
Run the aggregator for past dates:
```bash
php artisan analytics:aggregate-daily --date=2025-01-01
php artisan analytics:aggregate-daily --date=2025-01-02
# ... or create a loop script
```
