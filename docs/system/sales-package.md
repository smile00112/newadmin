# Sales Package Specification

> **Package:** `bagisto/laravel-sales`
> **Namespace:** `Webkul\Sales`
> **Path:** `packages/Webkul/Sales`
> **License:** MIT

---

## Overview

Order lifecycle management: orders, invoices, shipments, refunds, transactions, downloadable link purchases, order status workflow, and status history tracking. Provides repository-based business logic with ACID transaction handling.

**Related specs:** [Core](core-package.md) · [Product](product-package.md) · [Checkout](checkout-package.md) · [Customer](customer-package.md) · [Admin](admin-package.md) · [Shop](shop-package.md) · [Packages Map](packages-map.md)

---

## Service Providers

### SalesServiceProvider

- Loads migrations from `Database/Migrations`
- Registers `OrderObserver` on Order model

### ModuleServiceProvider

Registers **14 Concord models:** Order, OrderItem, OrderAddress, OrderComment, OrderPayment, OrderTransaction, OrderStatusHistory, Invoice, InvoiceItem, Shipment, ShipmentItem, Refund, RefundItem, DownloadableLinkPurchased.

---

## Models

### Order

| Property | Value |
|----------|-------|
| **Table** | `orders` |
| **Casts** | `order_labels` → array, `rating` → boolean |

**Status Constants:** `pending`, `pending_payment`, `processing`, `preparing`, `ready`, `completed`, `canceled`, `closed`, `fraud`, `failed`

**Key Fields:** `increment_id` (unique), `status`, `customer_email`, `customer_first_name`, `customer_last_name`, `grand_total`, `base_grand_total`, `sub_total`, `tax_amount`, `discount_amount`, `shipping_amount`, `table_number`, `rating`, `rating_comment`, `order_labels`

**Financial Fields (with base_ variants):** `grand_total_invoiced`, `grand_total_refunded`, `sub_total_invoiced`, `shipping_invoiced`, `tax_amount_invoiced`

**Key Relationships:**
- `items()` → HasMany OrderItem (parent only)
- `all_items()` → HasMany OrderItem (all)
- `addresses()` → HasMany OrderAddress
- `comments()` → HasMany OrderComment
- `statusHistory()` → HasMany OrderStatusHistory (desc)
- `shipments()`, `invoices()`, `refunds()`, `transactions()` → HasMany
- `payment()` → HasOne OrderPayment
- `cart()` → BelongsTo CartProxy
- `customer()`, `channel()` → MorphTo
- `billing_address()`, `shipping_address()` → Accessor methods

**Accessors:** `customer_full_name`, `datetime`, `status_label` (dynamic from DB), `total_due`, `base_total_due`

### OrderItem

| Property | Value |
|----------|-------|
| **Table** | `order_items` |
| **Key Fields** | `sku`, `type`, `name`, `qty_ordered`, `qty_shipped`, `qty_invoiced`, `qty_canceled`, `qty_refunded`, `price`, `total`, `tax_amount`, `discount_amount`, `product_id`, `additional` (json) |

**Methods:** `canShip()`, `canInvoice()`, `canCancel()`, `getQtyToShipAttribute()`, `getQtyToInvoiceAttribute()`, `getTypeInstance()`, `isStockable()`

### OrderAddress

Extends Core `Address`. Types: `order_shipping`, `order_billing`.

### OrderPayment

**Table:** `order_payment`. Casts: `additional` → array.

### OrderComment

**Table:** `order_comments`. Fillable: `comment`, `customer_notified`, `order_id`.

### OrderTransaction

**Table:** `order_transactions`. Accessor: `payment_title` (from config).

### OrderStatus

**Table:** `order_statuses`. Fields: `code` (unique), `name`, `icon`, `color`, `sort_order`, `is_system`. 10 seeded system statuses.

### OrderStatusHistory

**Table:** `order_status_history`. Fields: `order_id`, `old_status`, `new_status`, `user_type`, `user_id`, `user_name`, `source` (cron/api/webhook/admin/system). Only `created_at` timestamp.

### Invoice

| Property | Value |
|----------|-------|
| **Table** | `invoices` |
| **States** | `pending`, `pending_payment`, `paid`, `overdue`, `refunded` |
| **Traits** | `InvoiceReminder`, `PaymentTerm` |

**Key Fields:** `increment_id`, `state`, `total_qty`, `sub_total`, `grand_total`, `shipping_amount`, `tax_amount`, `discount_amount`, `reminders`, `next_reminder_at`, `email_sent`, `transaction_id`

**Relationships:** `order()`, `items()`, `address()`, `customer()`, `channel()`

### InvoiceItem

**Table:** `invoice_items`. Relationships: `invoice()`, `order_item()`, `children()`, `product()`.

### Shipment

**Table:** `shipments`. Fields: `total_qty`, `total_weight`, `carrier_code`, `carrier_title`, `track_number`, `status`, `inventory_source_id`.

### ShipmentItem

**Table:** `shipment_items`. Relationships: `shipment()`, `order_item()`, `product()`.

### Refund

**Table:** `refunds`. State: `refunded`. Fields: `adjustment_refund`, `adjustment_fee`, + standard financial fields.

### RefundItem

**Table:** `refund_items`. Relationships: `refund()`, `order_item()`, `child()`, `product()`.

### DownloadableLinkPurchased

**Table:** `downloadable_link_purchased`. Status: `available`, `pending`, `expired`.

### OrderWorkflowSetting

**Table:** `order_workflow_settings`. Methods: `get(key, default)`, `set(key, value)`, `allAsArray()`.

---

## Repositories

### OrderRepository

**Dependencies:** OrderItemRepository, ProductCustomizableOptionRepository, DownloadableLinkPurchasedRepository

| Method | Description |
|--------|-------------|
| `create(array)` | Create order with retry (MAX_ORDER_CREATE_RETRIES = 2) |
| `createOrderIfNotThenRetry(array, attempt)` | Creates order with items, addresses, payment |
| `cancel(Order\|int)` | Cancel order, return qty to inventory |
| `generateIncrementId()` | Uses OrderSequencer |
| `updateOrderStatus(Order, status)` | Update with before/after events |
| `collectTotals(Order)` | Recalculate order totals |
| `canCancel(Order)` / `isInCompletedState(Order)` | State checks |

### OrderItemRepository

| Method | Description |
|--------|-------------|
| `collectTotals(OrderItem)` | Aggregate invoice/shipment/refund data |
| `manageInventory(OrderItem)` | Create ordered_inventories records |
| `returnQtyToProductInventory(OrderItem)` | Return qty on cancel |
| `manageCustomizableOptions(OrderItem)` | Handle custom options |

### InvoiceRepository

- `create(array, invoiceState, orderState)` — Creates invoice with items; dispatches `sales.invoice.save.before/after`
- `collectTotals(Invoice)`, `canRefund(Invoice)`

### ShipmentRepository

- `create(array, orderState)` — Creates shipment with items; dispatches `sales.shipment.save.before/after`

### RefundRepository

- `create(array)` — Creates refund with fee/adjustment; dispatches `sales.refund.save.before/after`

### InvoiceItemRepository / ShipmentItemRepository

- `updateProductInventory(array)` — Updates product stock; dispatches `catalog.product.update.after`

### RefundItemRepository

- `returnQtyToProductInventory(OrderItem, qty)` — Complex return logic (non-shipped vs shipped qty)

---

## Events Dispatched

| Event | Source | Data |
|-------|--------|------|
| `checkout.order.save.before/after` | OrderRepository::create | array / Order |
| `sales.order.cancel.before/after` | OrderRepository::cancel | Order |
| `sales.order.update-status.before/after` | OrderRepository::updateOrderStatus | Order |
| `sales.invoice.save.before/after` | InvoiceRepository::create | array / Invoice |
| `sales.shipment.save.before/after` | ShipmentRepository::create | array / Shipment |
| `sales.refund.save.before/after` | RefundRepository::create | array / Refund |
| `catalog.product.update.after` | InvoiceItemRepo, ShipmentItemRepo | Product |

---

## Observer

### OrderObserver

- `created(Order)` — Creates OrderStatusHistory entry
- `updated(Order)` — Creates OrderStatusHistory when status changes
- `detectSource()` → cron, api, webhook, admin, system
- `resolveUserType()` → admin, customer, null
- `resolveUserName()` → name from Auth guard

---

## Traits

| Trait | Purpose |
|-------|---------|
| `InvoiceReminder` | Automated overdue invoice reminders. Methods: `sendInvoiceReminder()`, `scopeInOverdueAndRemindersLimit()` |
| `PaymentTerm` | Payment deadline calculation from config. Methods: `hasPaymentTerm()`, `getPaymentTerm()`, `getFormattedPaymentTerm()` |

---

## Generators (Sequencing)

| Generator | Config Path | Purpose |
|-----------|-------------|---------|
| `OrderSequencer` | `sales.order_settings.order_number.*` | Order increment ID |
| `InvoiceSequencer` | `sales.invoice_settings.invoice_number.*` | Invoice increment ID |

Base `Sequencer` properties: length, prefix, suffix, generatorClass.

---

## Database Migrations (27 files)

### Core Tables

| Table | Key Columns |
|-------|-------------|
| `orders` | increment_id, status, customer_*, grand_total, sub_total, tax_amount, discount_amount, shipping_amount, table_number, rating, order_labels |
| `order_items` | sku, type, name, qty_ordered/shipped/invoiced/canceled/refunded, price, total, tax_amount, product_id, additional |
| `order_payment` | additional (json) |
| `order_comments` | comment, customer_notified, order_id |
| `order_transactions` | order_id, payment_method |
| `order_statuses` | code, name, icon, color, sort_order, is_system |
| `order_status_history` | order_id, old_status, new_status, user_type, user_id, source |
| `order_workflow_settings` | key, value (json) |
| `invoices` | increment_id, state, total_qty, grand_total, reminders, next_reminder_at, transaction_id |
| `invoice_items` | invoice_id, order_item_id, name, sku, qty, price, total, tax_amount |
| `shipments` | total_qty, total_weight, carrier_code, track_number, inventory_source_id |
| `shipment_items` | shipment_id, order_item_id, qty, weight, price |
| `refunds` | increment_id, state, adjustment_refund, adjustment_fee, grand_total |
| `refund_items` | refund_id, order_item_id, qty, price, total, discount_amount |
| `downloadable_link_purchased` | product_name, url, download_bought, download_used, status |

### Seeded Data (10 System Statuses)

pending, pending_payment, processing, preparing, ready, completed, canceled, closed, fraud, failed — each with icon, color, and sort_order.

---

## Schema Relationships

```
Order (1) ──→ (∞) OrderItem
         ├──→ (∞) OrderAddress (billing + shipping)
         ├──→ (1) OrderPayment
         ├──→ (∞) OrderComment
         ├──→ (∞) OrderStatusHistory
         ├──→ (∞) OrderTransaction
         ├──→ (∞) Invoice ──→ (∞) InvoiceItem → OrderItem
         ├──→ (∞) Shipment ──→ (∞) ShipmentItem → OrderItem
         ├──→ (∞) Refund ──→ (∞) RefundItem → OrderItem
         └──→ (∞) DownloadableLinkPurchased → OrderItem
```

---

## Transaction Handling

All major operations use `DB::beginTransaction()` / `commit()` / `rollback()` for ACID compliance. Post-transaction events dispatched **outside transaction** to avoid holding DB locks.

---

## Dependencies on Other Packages

| Package | Usage |
|---------|-------|
| [Core](core-package.md) | Repository base, Address model, Channel model |
| [Checkout](checkout-package.md) | CartProxy, CartAddress for cart-to-order conversion |
| [Product](product-package.md) | Product types, ProductCustomizableOptionRepository |
| [Customer](customer-package.md) | CustomerProxy for order ownership |
| [Inventory](packages-map.md) | InventorySource for shipments |
| [Shop](shop-package.md) | InvoiceOverdueReminder mail class |

---

## Routes

No routes defined — Sales is a backend business logic package. Routes are defined in [Admin](admin-package.md) and [Shop](shop-package.md).

---

## Config

No explicit config files. Uses `core()->getConfigData()`:
- `sales.order_settings.order_number.*` — Order number format
- `sales.invoice_settings.invoice_number.*` — Invoice number format
- `sales.invoice_settings.invoice_reminders.*` — Reminder limit and interval
- `sales.invoice_settings.payment_terms.due_duration` — Payment deadline
