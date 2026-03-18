# Customer Package Specification

> **Package:** `bagisto/laravel-customer`
> **Namespace:** `Webkul\Customer`
> **Path:** `packages/Webkul/Customer`
> **License:** MIT

---

## Overview

Customer accounts management: authentication, customer groups, addresses, wishlist, compare items, notes, Google reCAPTCHA, VAT validation. Provides the `Customer` authenticatable model used across [Shop](shop-package.md) and [Admin](admin-package.md) packages.

**Related specs:** [Core](core-package.md) · [Product](product-package.md) · [Sales](sales-package.md) · [Checkout](checkout-package.md) · [Admin](admin-package.md) · [Shop](shop-package.md) · [Packages Map](packages-map.md)

---

## Service Providers

### CustomerServiceProvider

| Responsibility | Detail |
|----------------|--------|
| Migrations | `Database/Migrations` |
| Translations | `Resources/lang` (namespace: `customer`) |
| Views | `Resources/views` |
| Validator | Custom `captcha` validation rule |

### ModuleServiceProvider

Registers 6 Concord models: Customer, CustomerGroup, CustomerAddress, Wishlist, CompareItem, CustomerNote.

---

## Models

### Customer

| Property | Value |
|----------|-------|
| **Table** | `customers` |
| **Extends** | `Authenticatable` (Laravel) |
| **Traits** | `HasApiTokens`, `HasFactory`, `Notifiable`, `Visitor` |

**Fillable:** `first_name`, `last_name`, `gender`, `date_of_birth`, `email`, `phone`, `password`, `api_token`, `token`, `customer_group_id`, `channel_id`, `subscribed_to_news_letter`, `status`, `is_verified`, `is_suspended`, `telegram_id`, `whatsapp_id`

**Casts:** `subscribed_to_news_letter` → boolean

**Hidden:** `password`, `api_token`, `remember_token`

**Appended:** `image_url`, `name` (computed: "FirstName LastName")

**Key Relationships:**
- `group()` → BelongsTo CustomerGroup
- `addresses()` → HasMany CustomerAddress
- `default_address()` → HasOne CustomerAddress (where `default_address = 1`)
- `orders()` → HasMany Order
- `invoices()` → HasManyThrough Invoice via Order
- `wishlist_items()` → HasMany Wishlist
- `reviews()` → HasMany ProductReview
- `notes()` → HasMany CustomerNote
- `all_carts()` / `active_carts()` / `inactive_carts()` → HasMany Cart
- `subscription()` → HasOne SubscribersList
- `tokenLogs()` → HasMany CustomerTokenLog (RestApi)
- `channel()` → BelongsTo Channel

**Key Methods:** `emailExists(email)`, `sendPasswordResetNotification(token)`, `isWishlistShared()`, `getWishlistSharedLink()`, `haveActiveOrders()`

### CustomerGroup

| Property | Value |
|----------|-------|
| **Table** | `customer_groups` |
| **Fillable** | `name`, `code` (unique), `is_user_defined` (default: 1) |

**Relationships:** `customers()` → HasMany Customer

### CustomerAddress

| Property | Value |
|----------|-------|
| **Table** | `addresses` (shared with Core Address, global scope filter) |
| **Extends** | `Webkul\Core\Models\Address` |
| **Address Type** | `'customer'` (constant) |

Global scope automatically filters by `address_type = 'customer'`.

### Wishlist

| Property | Value |
|----------|-------|
| **Table** | `wishlist_items` |
| **Casts** | `additional` → array |

**Fields:** `channel_id`, `product_id`, `customer_id`, `additional` (JSON), `moved_to_cart`, `shared`

**Relationships:** `product()`, `channel()`, `customer()`

### CompareItem

| Property | Value |
|----------|-------|
| **Table** | `compare_items` |

**Relationships:** `customer()`, `product()`

### CustomerNote

| Property | Value |
|----------|-------|
| **Table** | `customer_notes` |
| **Fillable** | `note`, `customer_id`, `customer_notified` |

**Relationships:** `customer()`

---

## Repositories

### CustomerRepository

| Method | Description |
|--------|-------------|
| `haveActiveOrders($customer)` | Check pending/processing orders |
| `getCurrentGroup()` | Get authenticated customer's group (or guest from core) |
| `uploadImages($data, $customer, $type)` | Upload customer images |
| `syncNewRegisteredCustomerInformation($customer)` | Sync guest orders to registered customer (updates orders, addresses, shipments, downloadable links) |

### CustomerAddressRepository, CustomerGroupRepository, CompareItemRepository, WishlistRepository, CustomerNoteRepository

Standard CRUD operations via base `Repository`.

---

## Facade: Captcha

### Captcha Service (`src/Captcha.php`)

Google reCAPTCHA integration.

**Constants:**
```
CLIENT_ENDPOINT = 'https://www.google.com/recaptcha/api.js'
SITE_VERIFY_ENDPOINT = 'https://google.com/recaptcha/api/siteverify'
```

**Key Methods:**
| Method | Description |
|--------|-------------|
| `isActive()` | Check if captcha enabled in config |
| `getSiteKey()` / `getSecretKey()` | Retrieve keys from core config |
| `render()` | Render captcha HTML |
| `renderJS()` | Render captcha JavaScript |
| `validateResponse($response)` | Validate reCAPTCHA response (Guzzle) |
| `getValidations($rules)` | Merge captcha validation rules |
| `getCaptchaView()` / `getCaptchaJSView()` | Render captcha views |

**Config keys:** `customer.captcha.credentials.status`, `.site_key`, `.secret_key`

---

## Validation Rules

### VatIdRule

Validates EU VAT ID format using `VatValidator`.

### VatValidator

Supports 31 countries: AT, AE, BE, BG, CY, CZ, DE, DK, EE, EL, ES, FI, FR, GB, HR, HU, IE, IN, IT, JP, LT, LU, LV, MT, NL, PL, PT, RO, SE, SI, SK.

Methods: `validate($vatNumber, $formCountry)`, `vatCleaner($vatNumber)`, `splitVat($vatNumber)`.

---

## Notifications

### CustomerResetPassword

Extends Laravel `ResetPassword`. Uses custom mail view: `shop::emails.customers.forget-password`.

### CustomerUpdatePassword

Extends `Mailable`. View: `shop::emails.customer.update-password`.

---

## Database Migrations (8 files)

| Table | Key Columns |
|-------|-------------|
| `customer_groups` | code (unique), name, is_user_defined |
| `customers` | first_name, last_name, gender, email (unique), phone (unique), password, api_token, customer_group_id → FK, channel_id → FK, status, is_verified, is_suspended, telegram_id, whatsapp_id |
| `customer_password_resets` | email, token |
| `wishlist_items` | channel_id → FK, product_id → FK, customer_id → FK, additional (JSON), moved_to_cart, shared |
| `compare_items` | product_id → FK, customer_id → FK |
| `customer_notes` | customer_id → FK, note, customer_notified |

### Notable Alterations

- `2023_07_25` — Dropped `notes` column from customers, created `customer_notes` table
- `2024_06_04` — Added `channel_id` FK to customers (sets existing to first channel)

---

## Proxy Models

CustomerProxy, CustomerGroupProxy, CustomerAddressProxy, WishlistProxy, CompareItemProxy, CustomerNoteProxy — enable model swapping via Concord.

---

## Contracts

Marker interfaces: `Customer`, `CustomerAddress`, `CustomerGroup`, `CustomerNote`, `CompareItem`, `Wishlist`, `Captcha` (defines reCAPTCHA endpoints).

---

## Dependencies on Other Packages

| Package | Usage |
|---------|-------|
| [Core](core-package.md) | Address base model, Channel, SubscribersList |
| [Product](product-package.md) | ProductReview relationship |
| [Sales](sales-package.md) | Order and Invoice relationships |
| [Checkout](checkout-package.md) | Cart relationship |
| [RestApi](packages-map.md) | CustomerTokenLog relationship |

---

## Routes & Config

- **No routes** — defined in [Shop](shop-package.md) and [Admin](admin-package.md)
- **No config files** — uses `core()->getConfigData('customer.captcha.credentials.*')`

---

## Factories (Testing)

| Factory | Key States |
|---------|------------|
| CustomerFactory | default (verified, active), `male()`, `female()` |
| CustomerAddressFactory | Italian locale faker, address_type: `customer` |
| CustomerGroupFactory | Random name, code, is_user_defined |
| CompareItemFactory | Basic product + customer |
