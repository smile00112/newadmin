# Shop Package Specification

> **Package:** `bagisto/laravel-shop`
> **Namespace:** `Webkul\Shop`
> **Path:** `packages/Webkul/Shop`
> **License:** MIT

---

## Overview

Storefront package for customer-facing interface. Provides product browsing, customer authentication, shopping cart, one-page checkout, email notifications, REST API endpoints, middleware (theme/locale/currency), search, compare, wishlist, SMS verification, and GDPR compliance.

**Related specs:** [Core](core-package.md) · [Product](product-package.md) · [Sales](sales-package.md) · [Checkout](checkout-package.md) · [Customer](customer-package.md) · [Admin](admin-package.md) · [Packages Map](packages-map.md)

---

## Service Providers

### ShopServiceProvider

| Responsibility | Detail |
|----------------|--------|
| Config | Merges `menu.customer` (Config/menu.php) |
| Middleware Group | `'shop'` → Theme, Locale, Currency |
| Middleware Aliases | `theme`, `locale`, `currency`, `cache.response`, `customer` |
| Routes | Web (`Routes/web.php`) with `['web', 'shop', PreventRequestsDuringMaintenance]`; API (`Routes/api.php`) with `['api', 'throttle:api', 'shop']` |
| Migrations | `Database/Migrations` |
| Translations | `Resources/lang` (namespace: `shop`) |
| Views | `Resources/views` (namespace: `shop`) |
| Pagination | Default views → `shop::partials.pagination` |
| Components | Anonymous Blade from `Resources/views/components` (prefix: `shop`) |
| Sub-providers | `EventServiceProvider` |

### EventServiceProvider

| Event | Listener |
|-------|----------|
| `customer.registration.after` | `Customer::afterCreated()` |
| `customer.password.update.after` | `Customer::afterPasswordUpdated()` |
| `customer.subscription.after` | `Customer::afterSubscribed()` |
| `customer.note.create.after` | `Customer::afterNoteCreated()` |
| `customer.account.gdpr-request.create.after` | `GDPR::afterGdprRequestCreated()` |
| `customer.account.gdpr-request.update.after` | `GDPR::afterGdprRequestUpdated()` |
| `checkout.order.save.after` | `Order::afterCreated()` |
| `sales.order.cancel.after` | `Order::afterCanceled()` |
| `sales.order.comment.create.after` | `Order::afterCommented()` |
| `sales.invoice.save.after` | `Invoice::afterCreated()` |
| `sales.invoice.send_duplicate_email` | `Invoice::afterCreated()` |
| `sales.shipment.save.after` | `Shipment::afterCreated()` |
| `sales.refund.save.after` | `Refund::afterCreated()` |

---

## Controllers

### Storefront Controllers

| Controller | Key Methods |
|------------|-------------|
| HomeController | `index()`, `contactUs()`, `sendContactUsMail()` |
| CartController | `index()` — cart page |
| OnepageController | `index()`, `success()` — checkout |
| ProductController | `download()`, `downloadSample()` — file downloads |
| SearchController | `index()`, `upload()` — product search, image search |
| CompareController | `index()` — product comparison |
| SubscriptionController | `store()`, `destroy()` — email subscriptions |
| ProductsCategoriesProxyController | Fallback route handler (slug resolution) |
| BookingProductController | `index()` — booking slots |

### Customer Controllers

| Controller | Key Methods |
|------------|-------------|
| CustomerController | `account()`, `edit()`, `update()`, `destroy()`, `reviews()` |
| SessionController | `index()`, `store()` (login), `destroy()` (logout) |
| RegistrationController | `index()`, `store()`, `verifyAccount()`, `resendVerificationEmail()` |
| ForgotPasswordController | `create()`, `store()` |
| ResetPasswordController | `create()`, `store()` |
| GDPRController | `index()`, `store()`, `pdfView()`, `htmlView()`, `revoke()`, `cookieConsent()` |

### Customer Account Controllers

AddressController (CRUD), OrderController (list, view, reorder, cancel, printInvoice), WishlistController, DownloadableProductController

### API Controllers

| Controller | Key Methods |
|------------|-------------|
| ProductController | `index()` — product listing with filters |
| CartController | `index()`, `store()`, `update()`, `destroy()`, `storeCoupon()`, `destroyCoupon()`, `estimateShippingMethods()`, `crossSellProducts()` |
| CategoryController | `index()`, `tree()`, `getAttributes()`, `getAttributeOptions()`, `getProductMaxPrice()` |
| ReviewController | `index()`, `store()`, `translate()` |
| CompareController | `index()`, `store()`, `destroy()`, `destroyAll()` |
| CoreController | `getCountries()`, `getStates()` |
| OnepageController | `summary()`, `storeAddress()`, `storeShippingMethod()`, `storePaymentMethod()`, `storeOrder()` |
| CustomerController | `login()`, SMS verification methods |
| WishlistController | Wishlist CRUD |
| AddressController | Address management |

---

## Routes

### Web Routes

**Storefront** (`store-front-routes.php`):
| Route | Name | Cache |
|-------|------|-------|
| `GET /` | `shop.home.index` | No |
| `GET /page/{slug}` | `shop.cms.page` | Yes |
| `GET /contact-us` | `shop.home.contact_us` | Yes |
| `GET /search` | `shop.search.index` | Yes |
| `GET /compare` | `shop.compare.index` | Yes |
| `GET /booking-slots/{id}` | `shop.booking-product.slots.index` | No |
| *Fallback* | `shop.product_or_category.index` | Yes |

**Customer** (`customer-routes.php`, prefix: `/customer`):

Unauthenticated: login, register, forgot/reset password, verify account.
Authenticated (middleware: `customer`, `NoCacheMiddleware`): profile, wishlist, GDPR, addresses, orders, downloadable products, reviews.

**Checkout** (`checkout-routes.php`):
| Route | Name |
|-------|------|
| `GET /checkout/cart` | `shop.checkout.cart.index` |
| `GET /checkout/onepage` | `shop.checkout.onepage.index` |
| `GET /checkout/onepage/success` | `shop.checkout.onepage.success` |

### API Routes (prefix: `/api`)

- **Core:** `/core/countries`, `/core/states`
- **Categories:** `/categories`, `/categories/tree`, `/categories/attributes`, `/categories/max-price/{id?}`
- **Products:** `/products`, `/products/{id}/related`, `/products/{id}/up-sell`
- **Reviews:** `/product/{id}/reviews`
- **Compare:** `/compare-items`
- **Cart:** `/checkout/cart/*` (CRUD, coupon, shipping, cross-sell)
- **Bonus:** `/checkout/bonus/apply`, `/checkout/bonus/remove` (auth required)
- **Onepage:** `/checkout/onepage/summary`, `/checkout/onepage/addresses`, `/checkout/onepage/shipping-methods`, `/checkout/onepage/payment-methods`, `/checkout/onepage/orders`

---

## Middleware

| Middleware | Purpose | Registration |
|------------|---------|-------------|
| Theme | Sets active theme from channel config | `shop` group |
| Locale | Sets application locale from request/session | `shop` group |
| Currency | Handles currency selection | `shop` group |
| CacheResponse | Caches static page responses | Alias `cache.response` |
| AuthenticateCustomer | Customer auth check | Alias `customer` |

---

## Customer Menu (Config/menu.php)

1. Profile — `shop.customers.account.profile.index`
2. Address — `shop.customers.account.addresses.index`
3. Orders — `shop.customers.account.orders.index`
4. Downloadables — `shop.customers.account.downloadable_products.index`
5. Reviews — `shop.customers.account.reviews.index`
6. Wishlist — `shop.customers.account.wishlist.index`
7. GDPR Data Request — `shop.customers.account.gdpr.index`

---

## View Structure

| Directory | Content |
|-----------|---------|
| categories/ | Category listing |
| checkout/ | Cart and onepage checkout |
| cms/ | CMS pages |
| compare/ | Product comparison |
| components/ | Blade anonymous components (shop:: prefix) |
| customers/ | Auth and account views |
| emails/ | Email templates |
| errors/ | Error pages |
| home/ | Home and contact |
| partials/ | Pagination, reusable partials |
| products/ | Product detail |
| search/ | Search results |

### Frontend Build

- **Vue 3** + **Vite** + **Tailwind CSS**
- **Vee-Validate** for form validation
- **Flatpickr** for date picker
- Entry: `src/Resources/assets/{css,js}/app.{css,js}`
- Output: `themes/shop/default/build`

---

## Services

| Service | Purpose |
|---------|---------|
| SmsService | Sends SMS verification codes via PhoneVerificationCode model |
| InMemoryVerificationService | In-memory fallback (Laravel Cache, 6-digit codes, 5-min expiry) |

---

## Listeners (Email Notifications)

| Listener | Events | Sends |
|----------|--------|-------|
| Customer | registration, password update, subscription, note | Welcome, password change, subscription, note emails |
| Order | order save, cancel, comment | Confirmation, cancellation, comment emails |
| Invoice | invoice save, send duplicate | Invoice emails |
| Shipment | shipment save | Shipment notification |
| Refund | refund save | Refund notification |
| GDPR | gdpr-request create/update | GDPR confirmation, status emails |

---

## Mail Classes

| Domain | Classes |
|--------|---------|
| Customer | EmailVerificationNotification, RegistrationNotification, UpdatePasswordNotification, SubscriptionNotification, NoteNotification, GDPR/* |
| Order | CreatedNotification, CanceledNotification, CommentedNotification |
| Invoice, Shipment, Refund | Corresponding notification classes |
| Other | ContactUs, Mailable (base) |

---

## HTTP Resources (API Transformers)

CartResource, CartItemResource, ProductResource, CategoryResource, CategoryTreeResource, WishlistResource, CompareItemResource, ProductReviewResource, AttributeResource, AttributeOptionResource, AddressResource

---

## Form Validation (Http/Requests/)

LoginRequest, RegistrationRequest, ProfileRequest, AddressRequest, ForgotPasswordRequest, ContactRequest, CartAddressRequest

---

## DataGrids

OrderDataGrid, DownloadableProductDataGrid, GDPRRequestsDatagrid

---

## Models

| Model | Table | Purpose |
|-------|-------|---------|
| PhoneVerificationCode | `phone_verification_codes` | SMS verification with 6-digit codes, expiry, auth_type |

---

## Database Migrations

| Migration | Table |
|-----------|-------|
| `2024_01_01_000000` | `phone_verification_codes` (phone, code, auth_type, customer_id, expires_at, used) |

---

## Dependencies on Other Packages

| Package | Usage |
|---------|-------|
| [Core](core-package.md) | Utilities, configuration, repositories |
| [Customer](customer-package.md) | Customer model, repository, groups |
| [Attribute](packages-map.md) | Attribute repositories, enums |
| [Product](product-package.md) | Product repositories, types, downloads |
| [Category](packages-map.md) | Category repository |
| [Checkout](checkout-package.md) | Cart facade, models, addresses |
| [Sales](sales-package.md) | Order repository, models |
| [Payment](packages-map.md) | Payment facade |
| [Shipping](packages-map.md) | Shipping facade |
| [Theme](packages-map.md) | Theme customization |
| [CartRule](packages-map.md) | Cart rule coupons |
| [Tax](packages-map.md) | Tax facade |
| [Marketing](packages-map.md) | Search terms |
| [RestApi](packages-map.md) | CustomerTokenLogService |
| [Bonus](packages-map.md) | Bonus integration |
| [MagicAI](packages-map.md) | AI features |
| [BookingProduct](packages-map.md) | Booking slots |
| [GDPR](packages-map.md) | GDPR functionality |

---

## Configuration Checks

```
sales.checkout.shopping_cart.cart_page            — Enable/disable cart page
sales.checkout.shopping_cart.allow_guest_checkout — Allow guest checkout
customer.settings.email.verification             — Require email verification
customer.settings.login_options.redirected_to_page — Redirect after login
catalog.products.search.engine                    — Search engine (elastic/database)
catalog.products.search.storefront_mode           — Storefront search mode
```

---

## Language Support

20 locales: ar, bn, ca, de, en, es, fa, fr, he, hi_IN, it, ja, nl, pl, pt_BR, ru, sin, tr, uk, zh_CN
