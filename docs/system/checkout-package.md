# Checkout Package Specification

> **Package:** `bagisto/laravel-checkout`
> **Namespace:** `Webkul\Checkout`
> **Path:** `packages/Webkul/Checkout`
> **License:** MIT

---

## Overview

Shopping cart and checkout flow package. Manages cart lifecycle (create, add, update, remove), address handling, shipping rate collection, payment method selection, tax calculation (item and shipping), coupon application, bonus deduction, guest/customer cart merging, and minimum order validation.

**Related specs:** [Core](core-package.md) · [Product](product-package.md) · [Sales](sales-package.md) · [Customer](customer-package.md) · [Admin](admin-package.md) · [Shop](shop-package.md) · [Packages Map](packages-map.md)

---

## Service Providers

### CheckoutServiceProvider

| Responsibility | Detail |
|----------------|--------|
| Helpers | Loads `Http/helpers.php` |
| Migrations | `Database/Migrations` |
| Sub-providers | `EventServiceProvider`, `ModuleServiceProvider` |

### EventServiceProvider

- **Subscriber:** `CustomerEventsHandler` — listens to `customer.after.login`, calls `Cart::mergeCart($customer)`

### ModuleServiceProvider

Registers 5 Concord models: Cart, CartAddress, CartItem, CartPayment, CartShippingRate.

---

## Models

### Cart

| Property | Value |
|----------|-------|
| **Table** | `cart` |
| **Casts** | `additional` → JSON |

**Key Fields:** `customer_email`, `customer_first_name`, `customer_last_name`, `shipping_method`, `coupon_code`, `is_gift`, `items_count`, `items_qty`, `grand_total`, `base_grand_total`, `sub_total`, `tax_total`, `shipping_amount`, `discount_amount`, `checkout_method`, `is_guest`, `is_active`, `applied_cart_rule_ids`, `table_number`, `bonus_amount`, `base_bonus_amount`

**Currency Fields:** `exchange_rate`, `global_currency_code`, `base_currency_code`, `channel_currency_code`, `cart_currency_code`

**Key Relationships:**
- `customer()` → BelongsTo CustomerProxy
- `channel()` → BelongsTo ChannelProxy
- `items()` → HasMany CartItemProxy (parent only, `parent_id` IS NULL)
- `all_items()` → HasMany CartItemProxy (all including children)
- `billing_address()` → HasOne CartAddressProxy (`type: cart_billing`)
- `shipping_address()` → HasOne CartAddressProxy (`type: cart_shipping`)
- `shipping_rates()` → HasMany CartShippingRateProxy
- `selected_shipping_rate()` → Matching cart's `shipping_method`
- `payment()` → HasOne CartPaymentProxy

**Methods:** `haveStockableItems()`, `hasDownloadableItems()`, `hasProductsWithQuantityBox()`, `hasGuestCheckoutItems()`

### CartItem

| Property | Value |
|----------|-------|
| **Table** | `cart_items` |
| **Casts** | `additional` → array |

**Key Fields:** `quantity`, `sku`, `type`, `name`, `price`, `base_price`, `custom_price`, `total`, `base_total`, `tax_percent`, `tax_amount`, `discount_percent`, `discount_amount`, `price_incl_tax`, `total_incl_tax`, `applied_tax_rate`, `parent_id`, `product_id`, `cart_id`

**Relationships:** `product()`, `cart()`, `parent()`, `children()`, `child()`

**Methods:** `getTypeInstance()` — returns product type instance

### CartAddress

Extends `Webkul\Core\Models\Address`. Types: `cart_billing`, `cart_shipping`. Global scope filters by address type.

**Key Fields:** `cart_id`, `customer_id`, `parent_address_id`, `address_type`, `first_name`, `last_name`, `email`, `address`, `country`, `state`, `city`, `postcode`, `phone`

**Relationships:** `cart()`, `shipping_rates()`

### CartPayment

| Property | Value |
|----------|-------|
| **Table** | `cart_payment` |
| **Casts** | `additional` → array |

**Key Fields:** `method`, `method_title`, `cart_id`, `additional` (JSON — can store Alfabank binding_id, client_id)

### CartShippingRate

**Table:** `cart_shipping_rates`. Fillable: `carrier`, `carrier_title`, `method`, `method_title`, `method_description`, `price`, `base_price`, `price_incl_tax`, `base_price_incl_tax`, `discount_amount`, `tax_percent`, `tax_amount`, `cart_id`, `cart_address_id`.

---

## Repositories

### CartRepository

**Eager Load:** `items.product`, `customer.addresses`, `shipping_address`, `billing_address`, `shipping_rates`

- `findWithRelations($id)` — Find cart with eager-loaded relations
- `findOneWhereWithRelations(array $where)` — Find one by conditions with relations

### CartItemRepository

- `getProduct($cartItemId)` — Get product ID from cart item

### CartAddressRepository

Standard CRUD operations.

---

## Cart Service Class (`src/Cart.php`)

**Core container service** — manages all shopping cart operations.

### Constructor Dependencies

CartRepository, CartItemRepository, CartAddressRepository, ProductRepository, TaxCategoryRepository, WishlistRepository, CustomerAddressRepository

### Cart Management

| Method | Description |
|--------|-------------|
| `initCart(?Customer $customer)` | Initialize cart from session or customer |
| `createCart(array $data)` | Create new cart (guest or customer) |
| `removeCart(Cart $cart)` | Delete cart completely |
| `resetCart()` | Clear current cart reference |
| `mergeCart(Customer $customer)` | Merge guest cart into customer's cart on login |
| `refreshCart()` | Reload cart with latest data |
| `getCart()` | Get current active cart or null |

### Item Management

| Method | Description |
|--------|-------------|
| `addProduct(Product $product, array $data)` | Add product to cart with options |
| `removeItem(int $itemId)` | Remove item from cart |
| `updateItems(array $data)` | Update quantities and prices |
| `getItemByProduct(array $data, ?array $parentData)` | Find existing matching item |

### Address Management

| Method | Description |
|--------|-------------|
| `saveAddresses(array $params)` | Save billing and shipping addresses |
| `updateOrCreateBillingAddress(array $params)` | Create/update billing |
| `updateOrCreateShippingAddress(array $params)` | Create/update shipping |

### Shipping & Payment

| Method | Description |
|--------|-------------|
| `saveShippingMethod(string $code)` | Set shipping method |
| `resetShippingMethod()` | Clear shipping method and rates |
| `savePaymentMethod(array\|string $params)` | Save payment (supports Alfabank saved cards) |

### Coupon & Wishlist

| Method | Description |
|--------|-------------|
| `setCouponCode(?string $code)` | Apply coupon |
| `removeCouponCode()` | Remove coupon |
| `moveToCart(Wishlist $item, ?int $qty)` | Move wishlist → cart |
| `moveToWishlist(int $itemId, int $qty)` | Move cart → wishlist |

### Validation

| Method | Description |
|--------|-------------|
| `hasError()` | Check cart errors |
| `getErrors()` | Get errors (code, message, amount) |
| `validateItems()` | Validate items (price changes, inactive products) |
| `isItemsHaveSufficientQuantity()` | All items have sufficient qty |
| `haveMinimumOrderAmount()` | Minimum order amount check |

### Tax Calculation

| Method | Description |
|--------|-------------|
| `collectTotals()` | Recalculate all totals (subtotal, tax, discount, shipping, bonus, grand) |
| `calculateItemsTax()` | Tax on items based on address |
| `calculateShippingTax()` | Tax on shipping rate |

**Tax Calculation Constants:**
```php
TAX_CALCULATION_BASED_ON_SHIPPING_ORIGIN  = 'shipping_origin'
TAX_CALCULATION_BASED_ON_BILLING_ADDRESS  = 'billing_address'
TAX_CALCULATION_BASED_ON_SHIPPING_ADDRESS = 'shipping_address'
```

### Price Field Convention (Dual Currency)

| Prefix | Meaning |
|--------|---------|
| `*_price` | Display/cart currency |
| `base_*` | Base/store currency |
| `*_incl_tax` | Price including tax |

---

## Events Dispatched

| Event | Location | Data |
|-------|----------|------|
| `checkout.cart.add.before/after` | `addProduct()` | Product ID / Cart |
| `checkout.cart.delete.before/after` | `removeItem()` | Cart Item ID |
| `checkout.cart.update.before/after` | `updateItems()` | CartItem |
| `checkout.cart.collect.totals.before/after` | `collectTotals()` | Cart |
| `checkout.cart.calculate.items.tax.before/after` | `calculateItemsTax()` | Cart |
| `checkout.cart.calculate.shipping.tax.before/after` | `calculateShippingTax()` | Cart |

---

## Facade & Helpers

| Name | Access |
|------|--------|
| `Cart` facade | `Webkul\Checkout\Facades\Cart` — static proxy to Cart class |
| `cart()` helper | Returns `Webkul\Checkout\Cart` instance |

---

## Exceptions

- **BillingAddressNotFoundException** — thrown when billing address required but missing

---

## Database Migrations (10 files)

| Table | Key Columns |
|-------|-------------|
| `cart` | customer_*, shipping_method, coupon_code, is_guest, is_active, grand_total, sub_total, tax_total, shipping_amount, discount_amount, bonus_amount, table_number |
| `cart_items` | quantity, sku, type, name, price, total, tax_amount, discount_amount, additional, parent_id, product_id |
| `cart_payment` | method, method_title, cart_id, additional |
| `cart_shipping_rates` | carrier, method, price, tax_amount, discount_amount, cart_id, cart_address_id |
| `cart_item_inventories` | Item inventory tracking by source |

### Notable Alterations

- `2024_04_19` — Added tax-inclusive price columns to cart_items
- `2024_04_23` — Added shipping/tax-inclusive fields to cart
- `2024_04_23` — Added tax fields to cart_shipping_rates
- `2026_02_23` — Added `table_number` for dine-in/pickup orders

---

## Proxy Models

CartProxy, CartItemProxy, CartAddressProxy, CartPaymentProxy, CartShippingRateProxy — enable model swapping via Concord.

---

## Dependencies on Other Packages

| Package | Usage |
|---------|-------|
| [Core](core-package.md) | Base Repository, Address model, ChannelProxy, `core()` helper |
| [Product](product-package.md) | ProductProxy, product type instances |
| [Customer](customer-package.md) | CustomerProxy, CustomerAddressRepository, WishlistRepository, login event |
| [Shipping](packages-map.md) | Shipping facade for rate calculations |
| [Tax](packages-map.md) | Tax facade, TaxCategoryRepository |
| [AlfabankPayment](packages-map.md) | SavedCard model, SavedCardsService (optional) |

---

## Routes & Config

- **No routes** — defined in [Shop](shop-package.md) and [Admin](admin-package.md)
- **No config files** — uses `core()->getConfigData()` for tax and order settings
