# Core Package Specification

> **Package:** `bagisto/laravel-core`
> **Namespace:** `Webkul\Core`
> **Path:** `packages/Webkul/Core`
> **License:** MIT

---

## Overview

Foundation package for the entire platform. Provides channels, locales, currencies, system configuration, Elasticsearch connectivity, visitor tracking, image caching, ACL, menus, and base repository/model abstractions.

**Related specs:** [Product](product-package.md) · [Sales](sales-package.md) · [Admin](admin-package.md) · [Shop](shop-package.md) · [Checkout](checkout-package.md) · [Customer](customer-package.md) · [Packages Map](packages-map.md)

---

## Service Providers

### CoreServiceProvider

Primary provider — registered in Laravel auto-discovery.

| Responsibility | Detail |
|----------------|--------|
| Helpers | Loads `Http/helpers.php` |
| Commands | `BagistoVersion`, `ExchangeRateUpdate`, `InvoiceOverdueCron` |
| Overrides | Laravel Up/Down commands, Exception Handler, Maintenance middleware, Blade compiler |
| Migrations | `Database/Migrations` |
| Translations | `Resources/lang` (namespace: `core`) |
| Views | `Resources/views` (namespace: `core`) |
| Events | Tracer styling hooks (`bagisto.shop.layout.body.after`, `bagisto.admin.layout.head`) |
| Schedule | Invoice overdue cron daily at 03:00 |
| Sub-providers | `EventServiceProvider`, `ImageServiceProvider`, `VisitorServiceProvider` |

### EventServiceProvider

| Event | Listener |
|-------|----------|
| `Prettus\Repository\Events\RepositoryEntityCreated` | `CleanCacheRepository` |
| `Prettus\Repository\Events\RepositoryEntityUpdated` | `CleanCacheRepository` |
| `Prettus\Repository\Events\RepositoryEntityDeleted` | `CleanCacheRepository` |
| `Spatie\ResponseCache\Events\ResponseCacheHit` | `ResponseCacheHit` |

### ImageServiceProvider

- Registers singleton `image` (Intervention Image Manager)
- Image cache route: `GET /imagecache/{template}/{filename}`

### VisitorServiceProvider

- Overrides Shetabit Visitor provider
- Binds custom `Visitor` class to `shetabit-visitor`
- Registers visitor tracking macros

### ModuleServiceProvider

Registers 10 Concord models: Channel, CoreConfig, Country, CountryState (+Translations), Currency, CurrencyExchangeRate, Locale, SubscribersList, Visit, Address.

### EnvValidatorServiceProvider

Validates `DB_PREFIX` format on boot (regex: `/[^A-Za-z0-9_]/`).

---

## Models

### Channel

| Property | Value |
|----------|-------|
| **Table** | `channels` |
| **Fillable** | `code`, `name`, `description`, `theme`, `hostname`, `default_locale_id`, `base_currency_id`, `root_category_id`, `home_seo`, `is_maintenance_on`, `maintenance_mode_text`, `allowed_ips` |
| **Casts** | `home_seo` → array |
| **Translatable** | `name`, `description`, `maintenance_mode_text`, `home_seo` |

**Relationships:**
- `belongsToMany(Locale)` via `channel_locales`
- `belongsTo(Locale)` as `default_locale`
- `belongsToMany(Currency)` via `channel_currencies`
- `belongsToMany(InventorySource)` via `channel_inventory_sources`
- `belongsTo(Currency)` as `base_currency`
- `belongsTo(Category)` as `root_category`

### CoreConfig

| Property | Value |
|----------|-------|
| **Table** | `core_config` |
| **Fillable** | `code`, `value`, `channel_code`, `locale_code` |

### Currency

| Property | Value |
|----------|-------|
| **Table** | `currencies` |
| **Fillable** | `code`, `name`, `symbol`, `decimal`, `group_separator`, `decimal_separator`, `currency_position` |

**Relationships:** `hasOne(CurrencyExchangeRate)`

### CurrencyExchangeRate

| Property | Value |
|----------|-------|
| **Table** | `currency_exchange_rates` |
| **Fillable** | `target_currency`, `rate` |

### Locale

| Property | Value |
|----------|-------|
| **Table** | `locales` |
| **Fillable** | `code`, `name`, `direction` |

### Country / CountryState

- `countries` table — with `CountryTranslation` (translatable: `name`)
- `country_states` table — with `CountryStateTranslation` (translatable: `default_name`)

### SubscribersList

| Property | Value |
|----------|-------|
| **Table** | `subscribers_list` |
| **Fillable** | `email`, `is_subscribed`, `token`, `customer_id`, `channel_id` |

### Address

| Property | Value |
|----------|-------|
| **Table** | `addresses` |
| **Casts** | `use_for_shipping` (bool), `default_address` (bool) |

**Relationships:** `belongsTo(Customer)`

### Visit

Extends Shetabit `Visit` model. Fields: `method`, `request`, `url`, `referer`, `languages`, `useragent`, `headers`, `device`, `platform`, `browser`, `ip`, `visitor_id`, `visitor_type`, `channel_id`.

---

## Repositories

### ChannelRepository

- `create(array $data)` — Creates channel with translations, syncs locales/currencies/inventory sources, uploads logo & favicon
- `update(array $data, $id)` — Updates with relationship syncing
- `uploadImages(array $data, $channel, $type)` — Handles image uploads to `channel/{id}`

### CoreConfigRepository

- `create(array $data)` — Saves config with channel/locale support
- `search(Collection $items, string $searchTerm, array $path)` — Searches configurations
- Events: `core.configuration.save.before`, `core.configuration.save.after`

### CurrencyRepository

- CRUD with events: `core.currency.create|update|delete.before/after`
- `delete($id)` — Prevents deletion if only one currency exists

### LocaleRepository

- CRUD with events: `core.locale.create|update|delete.before/after`
- `uploadImage(array $localeImages, $locale)` — Stores logo to `locales/`

### ExchangeRateRepository, CountryRepository, CountryStateRepository, VisitRepository, SubscribersListRepository

Standard CRUD via base `Repository`.

### AbstractSettingRepository

Base class for settings with request + persistent caching (TTL 600s). Methods: `getAllSettings()`, `getSetting()`, `setSetting()`, `fetchAllSettings()`.

---

## Facades & Helpers

### Facades

| Facade | Accessor |
|--------|----------|
| `Core` | `Webkul\Core\Core` |
| `Acl` | `Webkul\Core\Acl` |
| `Menu` | `Webkul\Core\Menu` |
| `SystemConfig` | `Webkul\Core\SystemConfig` |
| `ElasticSearch` | `Webkul\Core\ElasticSearch` |

### Global Helpers

```php
core()              // Webkul\Core\Core instance
menu()              // Webkul\Core\Menu instance
acl()               // Webkul\Core\Acl instance
system_config()     // Webkul\Core\SystemConfig instance
clean_path()        // Cleaned path string
array_permutation() // Array permutations generator
```

---

## Key Service Classes

### Core (`src/Core.php`)

Central service. Key methods:

| Category | Methods |
|----------|---------|
| **Channels** | `getAllChannels()`, `getCurrentChannel()`, `getDefaultChannel()` |
| **Locales** | `getAllLocales()`, `getCurrentLocale()`, `getDefaultLocaleCodeFromDefaultChannel()` |
| **Currencies** | `getAllCurrencies()`, `getBaseCurrency()`, `getChannelBaseCurrency()` |
| **Config** | `getConfigData(string $path)`, `getConfigField(string $fieldName)` |
| **Geo** | `getCountries()`, `getStates()` |

**Version:** `2.3.x-dev`

### Acl (`src/Acl.php`)

ACL management. Methods: `addItem(AclItem)`, `getItems()`, `getRoles()`, `prepareAclItems()`.

### Menu (`src/Menu.php`)

Dynamic menu system. Methods: `addItem(MenuItem)`, `getItems(?string $area)`, `getCurrentActiveMenu()`. Supports hierarchical menus with permission-based filtering.

### SystemConfig (`src/SystemConfig.php`)

System configuration UI management. Methods: `addItem(Item)`, `getItems()`, `getActiveConfigurationItem()`, `getConfigField()`, `search()`.

### ElasticSearch (`src/ElasticSearch.php`)

Elasticsearch client management. Supports default (basic auth), API key, and Elastic Cloud connections. Methods: `makeConnection()`, `getDefaultConnection()`.

### Visitor (`src/Visitor.php`)

Visitor tracking with channel support. Dispatches `UpdateCreateVisitIndex` and `UpdateCreateVisitableIndex` jobs.

---

## Traits

| Trait | Purpose |
|-------|---------|
| `CurrencyFormatter` | Currency formatting utilities |
| `Sanitizer` | SVG sanitization, MIME type validation |
| `PDFHandler` | PDF generation utilities |

---

## Events Dispatched

| Event | Source |
|-------|--------|
| `core.configuration.save.before/after` | CoreConfigRepository::create() |
| `core.currency.create/update/delete.before/after` | CurrencyRepository |
| `core.locale.create/update/delete.before/after` | LocaleRepository |
| `bagisto.shop.layout.body.after` | CoreServiceProvider (tracer style) |
| `bagisto.admin.layout.head` | CoreServiceProvider (tracer style) |

---

## Console Commands

| Command | Signature | Description |
|---------|-----------|-------------|
| BagistoVersion | `bagisto:version` | Displays current version |
| ExchangeRateUpdate | `exchange-rate:update` | Updates currency exchange rates from API |
| InvoiceOverdueCron | `invoice:cron` | Processes overdue invoices (daily 03:00) |

---

## Database Migrations (20 files)

| Table | Key Columns |
|-------|-------------|
| `locales` | id, code, name, direction, logo_path |
| `countries` | id, code, name |
| `currencies` | id, code, name, symbol, decimal, group_separator, decimal_separator, currency_position |
| `currency_exchange_rates` | id, target_currency, rate |
| `channels` + `channel_locales` + `channel_currencies` | code, theme, hostname, logo, favicon, home_seo, root_category_id |
| `core_config` | id, code, value, channel_code, locale_code |
| `country_translations` | country_id, locale, name |
| `country_states` + `country_state_translations` | country_id, code, default_name |
| `subscribers_list` | email, is_subscribed, token, customer_id, channel_id |
| `channel_inventory_sources` | channel_id, inventory_source_id |
| `addresses` | address_type, customer_id, first_name, last_name, address, city, country, phone |
| `channel_translations` | channel_id, locale, name, description, home_seo |

---

## Dependencies on Other Packages

| Package | Usage |
|---------|-------|
| [Customer](customer-package.md) | Address relationships, CustomerGroup model |
| [Category](packages-map.md) | Channel root category |
| [Inventory](packages-map.md) | Channel inventory sources |
| [Sales](sales-package.md) | Invoice handling, invoice:cron |
| [Admin](admin-package.md) | Exception handling override |
| [Theme](packages-map.md) | Event rendering |
| [Tax](packages-map.md) | TaxCategory model |

---

## Key Config Values

```
app.channel                              — Default channel code
app.currency                             — Base currency code
app.fallback_locale                      — Fallback locale code
services.exchange_api.default            — Exchange rate API provider
general.general.visitor_options          — Enable/disable visitor tracking
customer.settings.wishlist.wishlist_option — Wishlist display
general.gdpr.settings.enabled            — GDPR compliance
```
