# Типы товаров и критерии их создания при импорте из iiko

## Структура ответа API `/api/2/menu/by_id`

API возвращает номенклатуру в формате ExternalMenuV2 (подробная схема: `docs/iiko-api/external-menu-v2-schema.md`).

Ключевые разделы:
- `itemCategories[]` — категории с вложенными позициями (`ExternalMenuItem`)
- `comboCategories[]` — категории комбо-наборов (`ComboDto`)

Каждая позиция (`ExternalMenuItem`) имеет:
- `type`: `DISH` | `COMBO` — тип позиции
- `orderItemType`: `Product` | `Compound` — обычный или составной товар
- `itemSizes[]` — размеры с ценами и **своими** группами модификаторов
- `itemModifierGroups[]` — общие группы модификаторов (deprecated, перенесены в `itemSizes`)

Пример:

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

- **DISH** — блюдо (основной товар меню)
- **COMBO** — комбо-набор (составной товар)
- **MODIFIER** — модификатор/ингредиент (дополнительный компонент)
- **GROUP** — группа товаров (набор связанных товаров)

Дополнительно поле `orderItemType`:
- **Product** — обычный товар
- **Compound** — составной товар со схемой модификаторов

## Маппинг типов iiko → наши типы

### Базовый маппинг (`mapIikoProductType()`)

| Тип iiko | Наш тип | Описание |
|----------|---------|----------|
| `dish`, `product` | `simple` | Простой товар |
| `combo` | `bundle` | Комбо → бандл |
| `modifier` | `ingredient` | Ингредиент для конструктора |
| `group`, `grouped` | `grouped` | Группированный товар |
| default | `simple` | По умолчанию простой |

### Поведенческий маппинг (приоритеты в `importProducts()`)

Базовый маппинг перекрывается поведенческой логикой, которая анализирует **структуру данных**:

| Приоритет | Условие | Итоговый тип | Метод |
|-----------|---------|-------------|-------|
| **1** | N размеров + модификаторы | `grouped` (parent) + `constructor` (variants) | `handleConfigurableConstructorProduct()` |
| **2** | Модификаторы (1 размер / без размеров) | `constructor` | `handleConstructorProduct()` |
| **3** | `orderItemType === 'Compound'` (без явных модификаторов) | `constructor` | через `importProducts()` |
| **4** | N размеров, без модификаторов | `grouped` + `simple` variants | `handleMultiPriceProduct()` |
| **5** | 1 размер, без модификаторов | из `mapIikoProductType()` | `createProduct()` |
| **combo** | `comboCategories[]` | `bundle` | `handleComboProduct()` |

## Критерии создания типов товаров при импорте

### 1. Простой товар (`simple`)

**Создается когда:**
- Тип `DISH` или `PRODUCT` из iiko
- Одна цена (один размер или без размеров)
- Нет групп модификаторов
- `orderItemType !== 'Compound'`

**Характеристики:**
- SKU: `iiko_{itemId}`
- Видим индивидуально (`visible_individually = 1`)

### 2. Ингредиент (`ingredient`)

**Создается когда:**
- Тип `MODIFIER` из iiko, или
- Является модификатором внутри `itemModifierGroups` при импорте конструктора

**Характеристики:**
- SKU: `iiko_{itemId}`
- Не видим индивидуально (`visible_individually = 0`)
- Используется в конструкторах

### 3. Группированный товар (`grouped`)

**Создается когда:**
- Несколько размеров (`itemSizes` > 1) без модификаторов, или
- Тип `GROUP`/`GROUPED` из iiko

**Структура (для multi-price):**
```
Пицца Маргарита (grouped, iiko_{itemId})
├── Пицца Маргарита - Маленькая (simple, iiko_{itemId}_price_{sizeId}, 300 руб)
├── Пицца Маргарита - Средняя (simple, 450 руб)
└── Пицца Маргарита - Большая (simple, 600 руб)
```

### 4. Конструктор (`constructor`)

**Создается когда:**
- Товар имеет `itemModifierGroups` (в item или в первом itemSize)
- Один размер или без размеров
- Также: `orderItemType === 'Compound'` (без явных модификаторов в ответе)

**Характеристики:**
- SKU: `iiko_{itemId}`
- Модификаторы из iiko → группы конструктора
- Каждый модификатор → `ingredient` товар
- Видим индивидуально (`visible_individually = 1`)

### 5. Grouped + Constructor (товар с размерами и модификаторами)

**Создается когда:**
- Несколько размеров (`itemSizes` > 1) **И** есть модификаторы

**Структура:**
```
Пицца Соренто (grouped, iiko_{itemId})
├── Пицца Соренто - 25 см (constructor, iiko_{itemId}_size_{sizeId1}, 500 руб)
│   ├── Группа: Добавки (cheese, bacon, ...)
│   └── Группа: Соусы (ketchup, mayo, ...)
├── Пицца Соренто - 30 см (constructor, iiko_{itemId}_size_{sizeId2}, 700 руб)
│   ├── Группа: Добавки (те же или другие)
│   └── Группа: Соусы
└── Пицца Соренто - 35 см (constructor, iiko_{itemId}_size_{sizeId3}, 900 руб)
    └── ...
```

**Особенности:**
- Модификаторы извлекаются **per-size** (`extractPerSizeModifierGroups()`)
- Каждый размер-вариант получает свой набор модификаторов
- Если модификаторы у всех размеров одинаковые — используются общие
- `itemSizes` сохраняются в оригинальном виде (не мержатся по имени)

### 6. Бандл (`bundle`) — комбо из iiko

**Создается когда:**
- Импорт из `comboCategories[]` (раздел ExternalMenuV2)
- Каждый `ComboDto` → один `bundle` товар

**Структура:**
```
Обед Выгодный (bundle, iiko_combo_{comboId})
├── Опция: Основное блюдо (select, обязательная)
│   ├── Бургер классический
│   └── Чикенбургер
├── Опция: Напиток (select)
│   ├── Кола
│   └── Сок
└── Опция: Десерт (select)
    ├── Маффин
    └── Пирожок
```

**Особенности:**
- Группы комбо (`ComboGroupDto`) → опции бандла (`bundle_options`)
- `isMainGroup` → `is_required = true`
- Стратегия ценообразования (`priceStrategy`) сохраняется в `additional`
- Для опций ищутся существующие товары по `iiko_id` или SKU
- Изображения комбо скачиваются из `image[].url`

## Нормализация данных API

Выполняется в `IikoNomenclatureService::normalizeNomenclatureData()`:

1. `itemCategories[]` → `groups[]` + `items[]` (плоские массивы)
2. `itemSizes[].prices[0].price` → `sizePrices[]` (для совместимости)
3. **`itemSizes` сохраняются в оригинале** — используются `extractPerSizeModifierGroups()` и `extractSizePrices()` для получения модификаторов per-size
4. `comboCategories[]` — передаются как есть, обрабатываются отдельно

## Особенности обработки

### Поиск существующих товаров

1. По `iiko_id` в `additional->iiko_id` (JSON)
2. По SKU (`iiko_{itemId}`)

### Формат SKU

| Тип | SKU |
|-----|-----|
| Простой/конструктор | `iiko_{itemId}` |
| Grouped-вариант (цена) | `iiko_{itemId}_price_{sizeId}` |
| Grouped-конструктор-вариант | `iiko_{itemId}_size_{sizeId}` |
| Комбо (бандл) | `iiko_combo_{comboId}` |
| Ингредиент | `iiko_{modifierItemId}` |

### Использование `orderItemType`

Поле `orderItemType: Compound` используется как дополнительный сигнал: если товар помечен как `Compound`, но API не вернул `itemModifierGroups` — товар всё равно создаётся как `constructor` (пустой конструктор, который позже может быть наполнен при следующей синхронизации).
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
