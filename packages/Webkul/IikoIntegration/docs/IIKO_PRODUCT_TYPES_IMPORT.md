# Типы товаров и критерии их создания при импорте из iiko

## Структура ответа API `/api/2/menu/by_id`

API возвращает номенклатуру в следующем формате:

```json
{
  "itemCategories": [
    {
      "id": "category-uuid",
      "name": "Название категории",
      "description": "Описание категории",
      "items": [
        {
          "itemId": "item-uuid",
          "id": "item-uuid",
          "name": "Название товара",
          "type": "DISH",
          "description": "Описание товара",
          "itemSizes": [
            {
              "sizeId": "size-uuid",
              "sizeName": "Маленькая",
              "sizeCode": "S",
              "prices": [
                {
                  "price": 100.00
                }
              ]
            },
            {
              "sizeId": "size-uuid-2",
              "sizeName": "Большая",
              "sizeCode": "L",
              "prices": [
                {
                  "price": 150.00
                }
              ]
            }
          ],
          "sizePrices": [
            {
              "sizeId": "size-uuid",
              "sizeName": "Маленькая",
              "price": 100.00
            }
          ]
        }
      ]
    }
  ]
}
```

## Типы товаров в iiko API

API iiko возвращает следующие типы товаров:

- **DISH** - блюдо (основной товар меню)
- **PRODUCT** - продукт (товар для продажи)
- **MODIFIER** - модификатор/ингредиент (дополнительный компонент)
- **GROUP** - группа товаров (набор связанных товаров)

## Маппинг типов iiko → наши типы

Маппинг выполняется в методе `IikoNomenclatureImportService::mapIikoProductType()`:

| Тип iiko | Наш тип | Описание |
|----------|---------|----------|
| `dish`, `product` | `simple` | Простой товар без вариантов |
| `modifier` | `ingredient` | Ингредиент для конструктора |
| `group`, `grouped` | `grouped` | Группированный товар |
| default (любой другой) | `simple` | По умолчанию простой товар |

## Критерии создания типов товаров при импорте

### 1. Простой товар (`simple`)

**Создается когда:**
- Товар имеет тип `DISH` или `PRODUCT` из iiko
- Товар имеет одну цену (один размер или без размеров)
- Товар имеет тип `MODIFIER` (создается как `ingredient`, но технически это `simple`)

**Характеристики:**
- Один SKU: `iiko_{itemId}`
- Одна цена
- Может быть добавлен в корзину напрямую
- Видим индивидуально (`visible_individually = 1`)

### 2. Ингредиент (`ingredient`)

**Создается когда:**
- Товар имеет тип `MODIFIER` из iiko

**Характеристики:**
- Один SKU: `iiko_{itemId}`
- Используется в системе конструктора товаров
- Может иметь несовместимости с другими ингредиентами

### 3. Группированный товар (`grouped`)

**Создается в двух случаях:**

#### 3.1. Товар с несколькими ценами (размерами)

**Создается когда:**
- Товар имеет несколько размеров (`itemSizes` или `sizePrices` с количеством > 1)
- Каждый размер имеет свою цену

**Структура:**
- **Главный товар** (тип `grouped`):
  - SKU: `iiko_{itemId}`
  - Название: оригинальное название товара
  - Видим индивидуально (`visible_individually = 1`)
  - Категория: категория из iiko
  
- **Варианты** (тип `simple`):
  - SKU: `iiko_{itemId}_price_{sizeId}`
  - Название: `{название товара} - {название размера}`
  - Цена: цена размера
  - Не видим индивидуально (`visible_individually = 0`)
  - Категория: специальная категория "Варианты цен iiko"
  - Связан с главным товаром через `parent_id`

**Пример:**
```
Пицца Маргарита (grouped)
├── Пицца Маргарита - Маленькая (simple, 300 руб)
├── Пицца Маргарита - Средняя (simple, 450 руб)
└── Пицца Маргарита - Большая (simple, 600 руб)
```

#### 3.2. Товар типа GROUP из iiko

**Создается когда:**
- Товар имеет тип `GROUP` или `GROUPED` из iiko

**Характеристики:**
- Один SKU: `iiko_{itemId}`
- Тип: `grouped`
- Может содержать связанные товары (обрабатывается как обычный grouped product)

## Обработка цен

### Извлечение цен из API ответа

Цены извлекаются в следующем порядке:

1. **Из `sizePrices`** (если присутствует):
   ```php
   $prices = $item['sizePrices'] ?? [];
   ```

2. **Из `itemSizes`** (если `sizePrices` пуст):
   ```php
   if (empty($prices) && isset($item['itemSizes'])) {
       foreach ($item['itemSizes'] as $size) {
           $price = $size['prices'][0]['price'] ?? 0;
           $prices[] = [
               'sizeId' => $size['sizeId'],
               'sizeName' => $size['sizeName'],
               'sizeCode' => $size['sizeCode'],
               'price' => $price,
           ];
       }
   }
   ```

### Определение типа обработки

```php
if (count($prices) > 1) {
    // Создать grouped product с вариантами
    handleMultiPriceProduct($item, $categoryMap);
} else {
    // Создать простой товар
    createProduct($item, $productType, $categoryMap, $prices);
}
```

## Нормализация данных API

Перед импортом данные нормализуются в методе `IikoNomenclatureService::normalizeNomenclatureData()`:

1. **Извлечение категорий** из `itemCategories`:
   ```php
   foreach ($data['itemCategories'] as $category) {
       $groups[] = [
           'id' => $category['id'],
           'name' => $category['name'],
           'description' => $category['description'],
           'parentGroup' => null,
       ];
   }
   ```

2. **Извлечение товаров** из `itemCategories`:
   ```php
   foreach ($category['items'] as $item) {
       // Конвертация itemSizes в sizePrices
       if (isset($item['itemSizes'])) {
           $item['sizePrices'] = convertItemSizesToSizePrices($item['itemSizes']);
       }
       $items[] = $item;
   }
   ```

3. **Результат нормализации**:
   ```php
   $normalized = [
       'groups' => $groups,
       'items' => $items,
       // ... остальные поля из оригинального ответа
   ];
   ```

## Особенности обработки

### Обработка полей `itemId` vs `id`

Код поддерживает оба варианта для совместимости:
```php
$iikoId = $item['id'] ?? $item['itemId'] ?? null;
```

### Поиск существующих товаров

Товары ищутся в следующем порядке:
1. По `iiko_id` в поле `additional->iiko_id`
2. По SKU (`iiko_{itemId}`)

### Обновление существующих товаров

При обновлении:
- Сохраняется существующий `iiko_id` в `additional`
- Обновляются название, описание, цена
- Обновляются категории
- Обновляется индекс `product_flat`

### Создание новых товаров

При создании:
- Устанавливается `iiko_id` в `additional`
- Устанавливается тип товара согласно маппингу
- Устанавливается статус `1` (активен)
- Устанавливается `visible_individually` в зависимости от типа
- Создается индекс `product_flat`

## Примеры импорта

### Пример 1: Простой товар

**Входные данные:**
```json
{
  "itemId": "123",
  "name": "Кофе эспрессо",
  "type": "DISH",
  "sizePrices": [
    {
      "sizeId": null,
      "sizeName": null,
      "price": 100.00
    }
  ]
}
```

**Результат:**
- Тип: `simple`
- SKU: `iiko_123`
- Цена: 100.00

### Пример 2: Товар с размерами

**Входные данные:**
```json
{
  "itemId": "456",
  "name": "Пицца Маргарита",
  "type": "DISH",
  "itemSizes": [
    {
      "sizeId": "size-1",
      "sizeName": "Маленькая",
      "prices": [{"price": 300.00}]
    },
    {
      "sizeId": "size-2",
      "sizeName": "Большая",
      "prices": [{"price": 600.00}]
    }
  ]
}
```

**Результат:**
- Главный товар: `grouped`, SKU: `iiko_456`
- Вариант 1: `simple`, SKU: `iiko_456_price_size-1`, цена: 300.00
- Вариант 2: `simple`, SKU: `iiko_456_price_size-2`, цена: 600.00

### Пример 3: Модификатор

**Входные данные:**
```json
{
  "itemId": "789",
  "name": "Дополнительный сыр",
  "type": "MODIFIER",
  "sizePrices": [
    {
      "price": 50.00
    }
  ]
}
```

**Результат:**
- Тип: `ingredient`
- SKU: `iiko_789`
- Цена: 50.00

## Важные замечания

1. **Товары с несколькими размерами всегда создаются как grouped products**, даже если они имеют тип `DISH` или `PRODUCT`.

2. **Модификаторы создаются как `ingredient`**, но технически это `simple` товары с особым назначением.

3. **Товары типа GROUP обрабатываются как `grouped`**, но логика их обработки может отличаться в зависимости от структуры данных.

4. **Категория "Варианты цен iiko"** создается автоматически для вариантов товаров с несколькими ценами.

5. **Все товары импортируются в канал по умолчанию** и во все доступные локали.

6. **Индекс `product_flat` обновляется автоматически** после создания или обновления товара.
