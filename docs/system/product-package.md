# Product Package Specification

> **Package:** `bagisto/laravel-product`
> **Namespace:** `Webkul\Product`
> **Path:** `packages/Webkul/Product`
> **License:** MIT

---

## Overview

Product catalog management package. Implements the product type system (Simple, Configurable, Grouped, Bundle, Constructor, Ingredient, ConfigurableConstructor), EAV attribute values, inventory tracking, price indexing, Elasticsearch indexing, product images/videos, reviews, downloadable products, customizable options, and the food-industry constructor/ingredient system.

**Related specs:** [Core](core-package.md) · [Sales](sales-package.md) · [Checkout](checkout-package.md) · [Customer](customer-package.md) · [Admin](admin-package.md) · [Shop](shop-package.md) · [Packages Map](packages-map.md)

---

## Service Providers

### ProductServiceProvider

| Responsibility | Detail |
|----------------|--------|
| Helpers | Loads `Http/helpers.php` |
| Config | Merges `product_types.php` |
| Migrations | `Database/Migrations` |
| Translations | `Resources/lang` (namespace: `product`) |
| Observer | `ProductObserver` on `ProductProxy` |
| Schedule | Daily price indexer at 00:01 |
| Commands | `Indexer`, `RecalculateConstructorPrices` |
| Sub-providers | `EventServiceProvider` |

### EventServiceProvider

| Event | Listener | Method |
|-------|----------|--------|
| `catalog.product.create.after` | `Product` | `afterCreate()` |
| `catalog.product.update.after` | `Product` | `afterUpdate()` |
| `catalog.product.delete.before` | `Product` | `beforeDelete()` |
| `checkout.order.save.after` | `Order` | `afterCancelOrCreate()` |
| `sales.order.cancel.after` | `Order` | `afterCancelOrCreate()` |
| `sales.refund.save.after` | `Refund` | `afterCreate()` |

### ModuleServiceProvider

Registers **28 Concord models** including Product, ProductFlat, ProductAttributeValue, bundle/grouped/downloadable/customizable models, constructor models, ingredient models, and all inventory/price index models.

---

## Product Type System

### Configuration (`Config/product_types.php`)

| Type | Key | Class | Sort | Active |
|------|-----|-------|------|--------|
| Simple | `simple` | `Webkul\Product\Type\Simple` | 1 | Yes |
| Configurable | `configurable` | `Webkul\Product\Type\Configurable` | 3 | Yes |
| Grouped | `grouped` | `Webkul\Product\Type\Grouped` | 5 | Yes |
| Bundle | `bundle` | `Webkul\Product\Type\Bundle` | 7 | Yes |
| Ingredient | `ingredient` | `Webkul\Product\Type\Ingredient` | 8 | Yes |
| Constructor | `constructor` | `Webkul\Product\Type\Constructor` | 9 | Yes |
| ConfigurableConstructor | `configurable_constructor` | `Webkul\Product\Type\ConfigurableConstructor` | 10 | Yes |
| Booking | `booking` | `Webkul\Product\Type\Booking` | 2 | No |
| Virtual | `virtual` | `Webkul\Product\Type\Virtual` | 4 | No |
| Downloadable | `downloadable` | `Webkul\Product\Type\Downloadable` | 6 | No |

### AbstractType (Base Class)

**Key Properties:**
- `isComposite`, `isStockable`, `showQuantityBox`, `hasVariants`, `isChildrenCalculated`
- `canBeMovedFromWishlistToCart`, `canBeAddedToCartWithoutOptions`, `canBeCopied`
- `skipAttributes` — attributes excluded from this type

**Key Methods:**
- `create()`, `update()`, `copy()` — CRUD lifecycle
- `getPrice()`, `getFinalPrice()`, `getProductPrices()` — pricing
- `addToCart()`, `getCartItemValidationResult()` — cart integration
- `getProductImages()`, `getChildrenIds()` — data access

### Type Properties Matrix

| Type | Composite | Stockable | Qty Box | Variants | Children Calculated |
|------|-----------|-----------|---------|----------|---------------------|
| Simple | No | Yes | Yes | No | No |
| Configurable | Yes | — | Yes | Yes | No |
| Grouped | Yes | — | — | No | — |
| Bundle | Yes | — | Yes | No | Yes |
| Constructor | Yes | — | — | No | — |
| Ingredient | No | — | Yes | No | No |
| ConfigurableConstructor | Yes | — | Yes | Yes | — |
| Virtual | No | No | Yes | No | No |
| Downloadable | No | No | — | No | No |
| Booking | Yes | No | — | No | — |

---

## Models

### Product

| Property | Value |
|----------|-------|
| **Table** | `products` |
| **Fillable** | `type`, `attribute_family_id`, `sku`, `parent_id`, `category_image`, `is_half_portion`, `half_portion_pair_product_id` |
| **Casts** | `additional` → array, `is_half_portion` → boolean |

**Key Relationships:**
- `product_flats()` → HasMany (one per locale/channel)
- `parent()` → BelongsTo (self-referential for variants)
- `attribute_family()` → BelongsTo
- `super_attributes()` → BelongsToMany
- `attribute_values()` → HasMany
- `categories()` → BelongsToMany
- `images()` / `videos()` → HasMany (ordered by position)
- `reviews()` / `approvedReviews()` → HasMany
- `inventories()` / `inventory_sources()` → HasMany / BelongsToMany
- `customer_group_prices()` / `catalog_rule_prices()` / `price_indices()` → HasMany
- `grouped_products()` / `bundle_options()` / `customizable_options()` / `constructor()` → HasMany

### ProductAttributeValue

| **Table** | `product_attribute_values` |
|-----------|---------------------------|
| **Fillable** | `product_id`, `attribute_id`, `locale`, `channel`, `unique_id`, `text_value`, `boolean_value`, `integer_value`, `float_value`, `datetime_value`, `date_value`, `json_value` |

Attribute type → field mapping: text/textarea → `text_value`, price → `float_value`, boolean → `boolean_value`, select → `integer_value`, multiselect/checkbox → `text_value`, file/image → `text_value` (path).

### ProductFlat

**Table:** `product_flat` — denormalized product data for fast queries.

### ProductImage / ProductVideo

**Tables:** `product_images`, `product_videos`. Fields: `type`, `path`, `product_id`, `position`. Appends: `url`.

### Bundle Models

- **ProductBundleOption** (`product_bundle_options`): `type`, `is_required`, `sort_order`, translatable `label`
- **ProductBundleOptionProduct** (`product_bundle_option_products`): `qty`, `sort_order`, `product_id`, `is_default`

### Grouped Models

- **ProductGroupedProduct** (`product_grouped_products`): `qty`, `sort_order`, `product_id`, `associated_product_id`

### Customizable Option Models

- **ProductCustomizableOption** (`product_customizable_options`): `type`, `is_required`, `max_characters`, translatable `label`
- **ProductCustomizableOptionPrice** (`product_customizable_option_prices`): `price`

### Downloadable Models

- **ProductDownloadableLink** (`product_downloadable_links`): `price`, `url`, `file`, `downloads`, translatable `title`
- **ProductDownloadableSample** (`product_downloadable_samples`): `url`, `file`, `sort_order`

### Constructor Models (Food/Product Customization)

- **ProductConstructor** (`product_constructor`): `visible`, `required`, `combo`, `discount`, `discount_type`, `discount_value`, `design` (line/category/table), `min_selected_sum`, `parent_id`
- **ProductConstructorGroup** (`product_constructor_group`): `name`, `field_type`, `checked_type`, `quantity_min/max`, `show_title`, `opened_by_default`, `zero_price`, `required`, `hidden`, `sort`, `double_portions`, `half_portions`, `sale_by_sizes`, `portion_sizes`
- **ProductConstructorGroupProduct** (`product_constructor_group_products`): pivot with `group_id`, `product_id`, `sort`, `default`, `parent_id`
- **ProductConstructorGroupTemplate** — templates for constructor groups

### Ingredient Models

- **ProductIngredientsIncompatibility** (`product_ingredients_incompatibilities`): `template_id`, `parent_id`, `product_id`
- **ProductIngredientsIncompatibilityTemplate** — templates for incompatibility rules

### Inventory & Pricing Models

| Model | Table | Key Fields |
|-------|-------|------------|
| ProductInventory | `product_inventories` | `qty`, `product_id`, `inventory_source_id`, `vendor_id` |
| ProductInventoryIndex | `product_inventory_indices` | `qty`, `product_id`, `channel_id` |
| ProductOrderedInventory | — | Tracks ordered quantities |
| ProductSalableInventory | — | Tracks saleable quantities |
| ProductPriceIndex | `product_price_indices` | `min_price`, `max_price`, `regular_min_price`, `regular_max_price`, `channel_id`, `customer_group_id` |
| ProductCustomerGroupPrice | `product_customer_group_prices` | `qty`, `price`, `customer_group_id`, `unique_id` |

### ProductReview / ProductReviewAttachment

**Table:** `product_reviews`. Fields: `comment`, `title`, `rating`, `status`, `product_id`, `customer_id`, `name`.

---

## Repositories

### ProductRepository

- `create(array $data)` — Creates product using type instance
- `update(array $data, $id)` — Updates via type instance
- `copy($id)` — Clones product with relationships
- `findBySlug(string $slug)` — Find by URL slug
- `getSuggestions(?string $query)` — Search suggestions
- `setSearchEngine(string $engine)` — Switch database/elastic

### ProductAttributeValueRepository

- `saveValues($data, $product, $attributes)` — Saves attribute values with channel/locale support

### ProductMediaRepository (base for Image/Video)

- `upload($data, $product, $type)` — Uploads with resize to 1200px, WebP conversion (quality 80), position ordering

### ProductInventoryRepository

- `saveInventories($data, $product)` — Updates/creates inventory per source

### ProductCustomerGroupPriceRepository

- `saveCustomerGroupPrices($data, $product)` — Saves tier prices
- `prices($product, $customerGroupId)` — Gets prices for group

### ProductGroupedProductRepository

- `saveGroupedProducts($data, $product)` — Saves linked products with quantities

### ProductBundleOptionRepository / ProductBundleOptionProductRepository

- `saveBundleOptions($data, $product)` — Saves bundle options and their products

### ProductCustomizableOptionRepository

- `saveCustomizableOptions($data, $product)` — Saves customizable options

### ProductConstructorRepository

- `saveConstructor($data, $product)` — Saves constructor with groups (handles JSON parsing)
- `saveConstructorGroups($data, $constructor)` — Manages constructor groups

### ProductDownloadableLinkRepository / ProductDownloadableSampleRepository

- `upload($data, $productId)` — Uploads to private storage
- `saveLinks($data, $product)` — Saves link records

### ElasticSearchRepository

- `getIndexName()` — `products_{channel}_{locale}_index`
- `search(array $params, array $options)` — ES search with filters
- `getSuggestions(?string $queryText)` — Search term suggestions

---

## Indexers

| Indexer | Table | Description |
|---------|-------|-------------|
| **Flat** | `product_flat` | Denormalized data for fast queries. Batch size 100. |
| **Price** | `product_price_indices` | Min/max prices per channel/customer group. Considers catalog rules and tax. |
| **Inventory** | `product_inventory_indices` | Available qty per channel. |
| **ElasticSearch** | ES index | Full-text searchable documents per locale/channel. |

---

## Jobs

| Job | Purpose |
|-----|---------|
| `UpdateProductFlatIndex` | Queues flat index refresh for a product |
| `UpdateCreatePriceIndex` | Queues price index recomputation |
| `UpdateCreateInventoryIndex` | Queues inventory index update |
| `ElasticSearch\UpdateCreateIndex` | Queues ES indexing |
| `ElasticSearch\DeleteIndex` | Removes product from ES on deletion |

---

## Console Commands

| Command | Signature | Description |
|---------|-----------|-------------|
| Indexer | `indexer:index [--type=price\|inventory\|flat\|elastic]` | Manual indexing |
| RecalculateConstructorPrices | — | Recalculates constructor product prices |

---

## Facades & Helpers

| Facade/Helper | Accessor |
|---------------|----------|
| `ProductImage` | `Webkul\Product\ProductImage` |
| `ProductVideo` | `Webkul\Product\ProductVideo` |
| `product_image()` | ProductImage facade instance |
| `product_video()` | ProductVideo facade instance |
| `product_toolbar()` | Toolbar helper |

### ProductImage Service

- `getGalleryImages($product)` — Cached image URLs (small, medium, large, original)
- `getProductBaseImage($product)` — First image or fallback placeholder
- `getProductVariantImage($cartItem)` — Variant-specific image

---

## Observer

### ProductObserver

- `saved($product)` — Clears catalog API cache, warms nomenclature cache
- `deleted($product)` — Deletes product media directory, clears API cache
- Debounces cache clearing per request

---

## Database Migrations (48 files)

### Core Tables

| Table | Key Columns |
|-------|-------------|
| `products` | id, sku, type, parent_id, attribute_family_id, additional, category_image, is_half_portion |
| `product_categories` | BelongsToMany pivot |
| `product_relations`, `product_up_sells`, `product_cross_sells` | Related products |
| `product_super_attributes` | Configurable variant attributes |
| `product_attribute_values` | EAV values (text, float, boolean, integer, datetime, date, json, unique_id) |

### Media & Review Tables

| Table | Key Columns |
|-------|-------------|
| `product_images`, `product_videos` | type, path, product_id, position |
| `product_reviews` | rating, title, comment, status, customer_id |
| `product_review_attachments` | Review images |

### Inventory & Pricing Tables

| Table | Key Columns |
|-------|-------------|
| `product_inventories` | qty, product_id, inventory_source_id, vendor_id |
| `product_ordered_inventories` | Ordered quantities |
| `product_inventory_indices` | qty, product_id, channel_id |
| `product_customer_group_prices` | qty, price, customer_group_id, unique_id |
| `product_price_indices` | min_price, max_price, channel_id, customer_group_id |

### Flat Table

`product_flat` — denormalized product data with all searchable/filterable attributes, nutrition columns.

### Type-Specific Tables

| Table | For Type |
|-------|----------|
| `product_bundle_options` + `_translations` + `_products` | Bundle |
| `product_grouped_products` | Grouped |
| `product_customizable_options` + `_translations` + `_prices` | Customizable |
| `product_downloadable_links` + `_translations` | Downloadable |
| `product_downloadable_samples` + `_translations` | Downloadable |
| `product_constructor` + `_group` + `_group_products` + `_group_templates` | Constructor |
| `product_ingredients_incompatibilities` + `_templates` | Ingredients |

---

## Dependencies on Other Packages

| Package | Usage |
|---------|-------|
| [Attribute](packages-map.md) | Attributes and attribute families |
| [Inventory](packages-map.md) | Inventory sources |
| [Category](packages-map.md) | Product–category relationships |
| [Core](core-package.md) | Base repository, channels, locales |
| [Customer](customer-package.md) | Customer data, wishlist, customer groups |
| [Checkout](checkout-package.md) | Cart integration |
| [Sales](sales-package.md) | Order, refund, invoice events |
| [CatalogRule](packages-map.md) | Catalog rule prices |
| [Tax](packages-map.md) | Tax rules for pricing |
| [BookingProduct](packages-map.md) | Booking product type |
| [RestApi](packages-map.md) | API cache warming in observer |
| [Marketing](packages-map.md) | Search synonyms for search |

---

## Language Support

20 locales: en, ar, bn, ca, de, es, fa, fr, he, hi_IN, it, ja, nl, pl, pt_BR, ru, sin, tr, uk, zh_CN
