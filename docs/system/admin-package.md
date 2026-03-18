# Admin Package Specification

> **Package:** `bagisto/laravel-admin`
> **Namespace:** `Webkul\Admin`
> **Path:** `packages/Webkul/Admin`
> **License:** MIT

---

## Overview

Admin panel for the platform. Provides all back-office controllers, DataGrids, ACL permissions, admin menus, route groups, Blade views & components, reporting helpers, mail notifications, AI assistant, and form validation.

**Related specs:** [Core](core-package.md) · [Product](product-package.md) · [Sales](sales-package.md) · [Checkout](checkout-package.md) · [Customer](customer-package.md) · [Shop](shop-package.md) · [Packages Map](packages-map.md)

---

## Service Providers

### AdminServiceProvider

| Responsibility | Detail |
|----------------|--------|
| Config | Merges `menu.admin` (menu.php), `acl` (acl.php), `core` (system.php) |
| Routes | `Routes/web.php` with `web` + `PreventRequestsDuringMaintenance` middleware |
| Translations | `Resources/lang` (namespace: `admin`) |
| Views | `Resources/views` (namespace: `admin`) |
| Components | Anonymous Blade components from `Resources/views/components` |
| Sub-providers | `EventServiceProvider` |

### EventServiceProvider

| Event | Listener |
|-------|----------|
| `customer.create.after` | `Customer::afterCreated()` |
| `customer.gdpr-request.create.after` | `GDPR::afterGdprRequestCreated()` |
| `customer.gdpr-request.update.after` | `GDPR::afterGdprRequestUpdated()` |
| `admin.password.update.after` | `Admin::afterPasswordUpdated()` |
| `checkout.order.save.after` | `Order::afterCreated()` |
| `sales.order.cancel.after` | `Order::afterCanceled()` |
| `sales.invoice.save.after` | `Invoice::afterCreated()` |
| `sales.shipment.save.after` | `Shipment::afterCreated()` |
| `sales.refund.save.after` | `Refund::afterCreated()` |

---

## Controllers

### Dashboard & Core

| Controller | Purpose |
|------------|---------|
| DashboardController | Stats: overall, today, stock-threshold, sales, visitors, top products/customers |
| ConfigurationController | System configuration management |
| NotificationController | Notification handling |
| ApplicationErrorController | Error tracking and display |
| TinyMCEController | File upload handler |
| AIAssistantController | AI chat (GPT-4o-mini) |
| MagicAIController | AI content/image generation |

### Catalog (9 controllers)

ProductController, AttributeController, AttributeFamilyController, CategoryController, ConstructorTemplateController, IngredientCompatibilityController, + type-specific: SimpleController, ConfigurableController, BundleController, VirtualController, DownloadableController, GroupedController

### Sales (7 controllers)

OrderController, InvoiceController, ShipmentController, RefundController, CartController, BookingController, TransactionController

### Customers (6 controllers)

CustomerController, CustomerGroupController, AddressController, ReviewController, GDPRController, + sub-controllers (CartController, WishlistController, CompareController, OrderController)

### Marketing (8 controllers)

CartRuleController, CartRuleCouponController, CatalogRuleController, CampaignController, TemplateController, EventController, SubscriptionController, SearchSynonymController, SearchTermController, SitemapController, URLRewriteController

### Settings (11 controllers)

ChannelController, CurrencyController, LocaleController, ExchangeRateController, InventorySourceController, PickupPointController, UserController, RoleController, ThemeController, ProductCategoryPositionsController, ImportController, TaxRateController, TaxCategoryController

### Reporting (3 controllers)

CustomerController, ProductController, SaleController

### Auth (4 controllers)

SessionController, ForgetPasswordController, ResetPasswordController, AccountController

---

## Routes

**Base prefix:** `config('app.admin_url')` (typically `/admin`)
**Middleware:** `web`, `NoCacheMiddleware`, admin auth

### Route Groups

| Group | Prefix | Key Routes |
|-------|--------|------------|
| Auth | `/admin` | login, forgot-password, reset-password |
| Sales | `/sales` | orders/\*, invoices/\*, shipments/\*, refunds/\*, transactions/\*, bookings/\*, carts/\* |
| Catalog | `/catalog` | products/\*, attributes/\*, families/\*, categories/\*, constructor-templates/\*, ingredient-compatibility/\* |
| Customers | `/customers` | CRUD, groups/\*, addresses/\*, reviews/\*, gdpr/\*, wishlist, compare, orders |
| Marketing | `/marketing` | promotions/cart-rules/\*, promotions/catalog-rules/\*, communications/\*, search-seo/\* |
| CMS | `/cms` | pages CRUD |
| Reporting | `/reporting` | customers/\*, products/\*, sales/\* |
| Settings | `/settings` | channels/\*, currencies/\*, locales/\*, exchange-rates/\*, inventory-sources/\*, users/\*, roles/\*, tax/\*, themes/\*, data-transfer/\*, order-statuses/\* |
| Configuration | `/configuration` | search, {slug}/{slug2}/\* |
| Notifications | `/notifications` | list, get, read, read-all |
| REST | `/` | dashboard/\*, datagrid/\*, tinymce/\*, magic-ai/\*, ai-assistant/\*, account/\*, logout |
| Errors | `/application-errors` | list, view, read, delete |

---

## DataGrids

### Catalog

ProductDataGrid, AttributeDataGrid, AttributeFamilyDataGrid, CategoryDataGrid, ConstructorGroupTemplateDataGrid, IngredientIncompatibilityTemplateDataGrid

### Sales

OrderDataGrid, OrderInvoiceDataGrid, OrderShipmentDataGrid, OrderRefundDataGrid, OrderTransactionDataGrid, BookingDataGrid

### Customers

CustomerDataGrid, GroupDataGrid, ReviewDataGrid, GDPRDataGrid, + View sub-grids (OrderDataGrid, InvoiceDataGrid, ReviewDataGrid)

### Marketing

CartRuleDataGrid, CartRuleCouponDataGrid, CatalogRuleDataGrid, CampaignDataGrid, EmailTemplateDataGrid, EventDataGrid, NewsLetterDataGrid, SearchTermDataGrid, SearchSynonymDataGrid, URLRewriteDataGrid, SitemapDataGrid

### Settings

ChannelDataGrid, CurrencyDataGrid, ExchangeRatesDataGrid, LocalesDataGrid, UserDataGrid, RolesDataGrid, InventorySourcesDataGrid, TaxCategoryDataGrid, TaxRateDataGrid, ImportDataGrid

### Other

ThemeDataGrid, ApplicationErrorDataGrid

---

## ACL Permissions (Config/acl.php)

| Domain | Permissions |
|--------|-------------|
| Dashboard | access, application errors |
| Sales | orders (create/view/cancel), invoices, shipments, refunds, transactions, bookings |
| Catalog | products (CRUD), attributes, families, categories |
| Customers | customers (CRUD, login-as), addresses, groups, reviews, GDPR |
| Marketing | cart rules, catalog rules, coupons, campaigns, templates, events, subscriptions, SEO tools |
| CMS | pages (CRUD) |
| Settings | channels, currencies, locales, exchange rates, users, roles, inventory, tax, themes, configs |
| Reporting | customer, product, sales |

---

## Menu Configuration (Config/menu.php)

1. Dashboard
2. Sales (Orders, Shipments, Invoices, Refunds, Transactions, Bookings)
3. Catalog (Ingredients, Products, Categories, Attributes, Families, Constructor Templates)
4. Customers (Customers, Groups, Reviews, GDPR)
5. Marketing (Promotions, Communications, SEO)
6. CMS (Pages)
7. Configuration
8. Settings (Channels, Locales, Currencies, Users, Roles, Tax, Inventory, Themes, Import)
9. Reporting
10. Application Errors

---

## System Configuration (Config/system.php)

| Section | Key Settings |
|---------|-------------|
| General | locale/weight options, breadcrumbs, visitor tracking, header/footer content |
| Email | Admin/customer notification toggles |
| Advanced | Product options, order management, shipping, payment |

---

## View Structure

### Blade Views (Resources/views/)

account, application-errors, catalog, cms, configuration, customers, dashboard, emails, marketing, notifications, reporting, sales, settings, users

### Blade Components (Resources/views/components/)

accordion, button, charts, datagrid, drawer, dropdown, flash-group, flat-picker, form, layouts, media, modal, products, quantity-changer, seo, shimmer, skeleton, star-rating, table, tabs, tinymce, tree

---

## Helper Classes

### Dashboard Helper

Constructor injects: Sale, Product, Customer, Visitor reporting classes. Methods: `getOverAllStats()`, `getTodayStats()`, `getStockThresholdProducts()`, `getSalesStats()`, `getVisitorStats()`, `getTopSellingProducts()`, `getTopCustomers()`.

### AIAssistantService

AI-powered admin assistant (OpenAI GPT-4o-mini). Queries products, categories, customers, orders, pages.

---

## Mail Classes

| Domain | Mailable |
|--------|----------|
| Orders | CreatedNotification, CanceledNotification, CommentedNotification, InvoicedNotification, ShippedNotification, RefundedNotification, InventorySourceNotification |
| Customers | NewCustomerNotification, RegistrationNotification, GDPR notifications |
| Admin | ResetPasswordNotification |

---

## Form Validation (Http/Requests/)

AddressRequest, CartAddressRequest, CartRuleRequest, CatalogRuleRequest, CategoryRequest, ConfigurationForm, InventoryRequest, InventorySourceRequest, MassDestroyRequest, MassUpdateRequest, PickupPointRequest, ProductForm, TaxRateRequest, UserForm

---

## Custom Validations

- `ConfigurableUniqueSku` — Ensures configurable product SKUs are unique per variant
- `ProductCategoryUniqueSlug` — Ensures category slugs are unique within parent

---

## HTTP Resources (API Response)

AddressResource, AttributeOptionResource, AttributeResource, CartItemResource, CartResource, CategoryTreeResource, CompareItemResource, OrderItemResource, ProductResource, TaxCategoryResource, TransactionResource, WishlistItemResource

---

## Dependencies on Other Packages

| Package | Usage |
|---------|-------|
| [Core](core-package.md) | Middleware, repository bases, config |
| [Product](product-package.md) | Repositories, helpers, types |
| [Customer](customer-package.md) | Repositories, models |
| [Sales](sales-package.md) | Repositories, models, statuses |
| [Attribute](packages-map.md) | Repositories |
| [Category](packages-map.md) | Repositories |
| [Inventory](packages-map.md) | Models, repositories |
| [CMS](packages-map.md) | PageRepository |
| [Checkout](checkout-package.md) | Cart facade, repositories |
| [CartRule](packages-map.md) | Cart rule repositories |
| [Tax](packages-map.md) | Tax categories |
| [User](packages-map.md) | User middleware |
| [Notification](packages-map.md) | Notification models |
| [BookingProduct](packages-map.md) | Booking repositories |

---

## Database

No migrations — relies on other packages. Factories: CatalogRuleFactory, CurrencyExchangeRateFactory, ThemeFactory.
