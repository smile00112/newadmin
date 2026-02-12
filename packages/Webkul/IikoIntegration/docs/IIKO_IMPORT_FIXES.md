# Исправления импорта товаров из iiko API

## Найденные проблемы и исправления

### Проблема 1: Неполное извлечение цен в `handleMultiPriceProduct()`

**Описание:**
В методе `handleMultiPriceProduct()` цены извлекались только из поля `sizePrices`, но не из `itemSizes`. Это приводило к тому, что товары с несколькими размерами в формате `itemSizes` не обрабатывались корректно.

**Исправление:**
Добавлена логика извлечения цен из `itemSizes`, аналогичная той, что используется в методе `importProducts()`:

```php
// Extract prices from sizePrices or itemSizes (new API format)
$prices = $item['sizePrices'] ?? [];
if (empty($prices) && isset($item['itemSizes']) && is_array($item['itemSizes'])) {
    foreach ($item['itemSizes'] as $size) {
        $sizePrice = 0;
        if (isset($size['prices']) && is_array($size['prices']) && count($size['prices']) > 0) {
            $sizePrice = $size['prices'][0]['price'] ?? 0;
        }
        $prices[] = [
            'sizeId' => $size['sizeId'] ?? null,
            'sizeName' => $size['sizeName'] ?? null,
            'sizeCode' => $size['sizeCode'] ?? null,
            'price' => $sizePrice,
        ];
    }
}
```

**Файл:** `packages/Webkul/IikoIntegration/src/Services/IikoNomenclatureImportService.php`
**Строки:** 363-380

### Проблема 2: Неправильная нормализация товаров с несколькими размерами

**Описание:**
В методе `normalizeNomenclatureData()` товары с несколькими размерами (`itemSizes`) разбивались на отдельные items для каждого размера. Это приводило к тому, что каждый item имел только один элемент в `sizePrices`, и товары не обрабатывались как grouped products.

**Исправление:**
Изменена логика нормализации: теперь товары с несколькими размерами сохраняют все размеры в одном item в массиве `sizePrices`:

```php
// Handle itemSizes - convert to sizePrices format for compatibility
$item['id'] = $itemId;
$item['groupId'] = $categoryId;

// Convert itemSizes to sizePrices format if present
if (isset($item['itemSizes']) && is_array($item['itemSizes']) && count($item['itemSizes']) > 0) {
    $sizePrices = [];
    foreach ($item['itemSizes'] as $size) {
        $sizePrice = 0;
        if (isset($size['prices']) && is_array($size['prices']) && count($size['prices']) > 0) {
            $sizePrice = $size['prices'][0]['price'] ?? 0;
        }
        $sizePrices[] = [
            'sizeId' => $size['sizeId'] ?? null,
            'sizeName' => $size['sizeName'] ?? null,
            'sizeCode' => $size['sizeCode'] ?? null,
            'price' => $sizePrice,
        ];
    }
    // Store sizePrices for compatibility with existing import logic
    $item['sizePrices'] = $sizePrices;
    
    // Set price from first size for single-price fallback
    if (count($sizePrices) > 0) {
        $item['price'] = $sizePrices[0]['price'] ?? 0;
    }
}

$items[] = $item;
```

**Файл:** `packages/Webkul/IikoIntegration/src/Services/IikoNomenclatureService.php`
**Строки:** 92-134

## Результаты исправлений

1. **Товары с несколькими размерами теперь корректно обрабатываются** как grouped products с вариантами
2. **Цены извлекаются из обоих источников** (`sizePrices` и `itemSizes`)
3. **Нормализация данных сохраняет структуру** для правильной обработки товаров с вариантами
4. **Улучшена совместимость** с разными форматами ответа API

## Проверенные аспекты

- ✅ Обработка полей `itemId` vs `id` - корректна во всех местах
- ✅ Маппинг типов товаров - корректный
- ✅ Извлечение цен из `itemSizes` и `sizePrices` - исправлено
- ✅ Нормализация данных - исправлена
- ✅ Обработка товаров с несколькими ценами - корректна

## Документация

Создана документация:
- `IIKO_PRODUCT_TYPES_IMPORT.md` - описание типов товаров и критериев их создания при импорте
