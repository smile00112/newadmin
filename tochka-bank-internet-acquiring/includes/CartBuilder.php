<?php
// Файл: includes/CartBuilder.php

declare(strict_types=1);

namespace Tochka\Woocommerce;

use WC_Order;
use WC_Order_Item_Product;
use WC_Order_Item_Shipping;
use WC_Product;
use WC_Shipping_Zones;
use WC_Tax;

/**
 * Отвечает за формирование фискальной корзины по правилам 54-ФЗ.
 * Реализует логику распределения скидок и копеечной коррекции.
 */
final class CartBuilder
{
    private WC_Order $order;
    private array $settings;
    private array $cartItems = [];
    private float $calculatedItemsTotal = 0.0;

    /**
     * @var array Локальный кеш для ставок НДС, чтобы избежать повторных запросов к БД.
     */
    private static array $taxRatesCache = [];

    public function __construct(WC_Order $order, array $settings)
    {
        $this->order = $order;
        $this->settings = $settings;
    }

    /**
     * Основной метод, который собирает и возвращает фискальную корзину.
     * @return array
     */
    public function build(): array
    {
        $this->addOrderItems();
        $this->addShipping();
        $this->applyDiscounts();
        $this->applyPrecisionCorrection();

        return $this->cartItems;
    }

    /**
     * Добавляет товары из заказа в корзину.
     */
    private function addOrderItems(): void
    {
        foreach ($this->order->get_items() as $item) {
            /** @var WC_Order_Item_Product $item */
            if (!$item instanceof WC_Order_Item_Product) {
                continue;
            }

            $product = $item->get_product();
            if (!$product) {
                continue;
            }

            $quantity = (float)$item->get_quantity();
            if ($quantity <= 0) {
                continue;
            }

            // Цена за единицу с учетом налога, но до применения скидок заказа
            $pricePerUnit = (float)$item->get_subtotal() / $quantity;
            $pricePerUnitWithTax = $pricePerUnit + ((float)$item->get_subtotal_tax() / $quantity);
            $totalWithTax = $pricePerUnitWithTax * $quantity;

            $this->cartItems[] = [
                'name'     => mb_substr($item->get_name(), 0, 128),
                'price'    => $pricePerUnitWithTax,
                'quantity' => $quantity,
                'sum'      => $totalWithTax,
                'tax'      => $this->getItemVatRate($item),
                'item_type'=> $this->getFiscalItemType($product),
            ];
        }
    }

    /**
     * Добавляет доставку как отдельную позицию.
     */
    private function addShipping(): void
    {
        if ((float)$this->order->get_shipping_total() > 0) {
            $shippingTotalWithTax = (float)$this->order->get_shipping_total() + (float)$this->order->get_shipping_tax();

            $this->cartItems[] = [
                'name'     => __('Доставка', 'tochka-bank-internet-acquiring'),
                'price'    => $shippingTotalWithTax,
                'quantity' => 1.0,
                'sum'      => $shippingTotalWithTax,
                'tax'      => $this->getShippingVatRate(),
                'item_type'=> 'service',
            ];
        }
    }

    /**
     * Пропорционально распределяет общую скидку заказа по позициям в корзине.
     */
    private function applyDiscounts(): void
    {
        $this->recalculateTotal();
        $orderTotal = $this->order->get_total();

        // Скидка - это разница между суммой позиций "до скидки" и финальной суммой заказа
        $discountAmount = $this->calculatedItemsTotal - $orderTotal;

        if ($discountAmount <= 0.01 || $this->calculatedItemsTotal == 0) {
            return;
        }

        // Распределяем скидку пропорционально стоимости каждой позиции
        foreach ($this->cartItems as &$item) {
            $itemShare = $item['sum'] / $this->calculatedItemsTotal;
            $itemDiscount = $discountAmount * $itemShare;

            $item['sum'] -= $itemDiscount;
            $item['price'] = $item['sum'] / $item['quantity'];
        }
        unset($item);
    }

    /**
     * Корректирует копеечные расхождения, возникшие из-за округлений.
     * Разницу добавляет к самой дорогой позиции, чтобы минимизировать искажения.
     */
    private function applyPrecisionCorrection(): void
    {
        $this->recalculateTotal(false); // Пересчет без округления
        $orderTotal = $this->order->get_total();

        $diff = $orderTotal - $this->calculatedItemsTotal;

        if (abs($diff) >= 0.01 && !empty($this->cartItems)) {
            $keyToCorrect = -1;
            $maxPrice = -1;
            // Ищем самую дорогую позицию
            foreach ($this->cartItems as $key => $item) {
                if ($item['price'] > $maxPrice) {
                    $maxPrice = $item['price'];
                    $keyToCorrect = $key;
                }
            }

            if ($keyToCorrect !== -1) {
                $this->cartItems[$keyToCorrect]['sum'] += $diff;
                $this->cartItems[$keyToCorrect]['price'] = $this->cartItems[$keyToCorrect]['sum'] / $this->cartItems[$keyToCorrect]['quantity'];
            }
        }

        // Финальное форматирование всех цен
        foreach ($this->cartItems as &$item) {
            $item['price'] = number_format($item['price'], 2, '.', '');
            $item['sum'] = number_format($item['sum'], 2, '.', '');
        }
        unset($item);
    }

    /**
     * Определяет признак предмета расчета (товар/услуга).
     */
    private function getFiscalItemType(WC_Product $product): string
    {
        // 1. Ищем признак в атрибуте товара
        $itemType = $product->get_attribute('fiscal-item-type');

        // 2. Если атрибут пуст или некорректен, берем из настроек
        if (empty($itemType) || !in_array($itemType, ['goods', 'service'])) {
            $itemType = $this->settings['default_fiscal_item_type'] ?? 'goods';
        }

        return $itemType;
    }

    /**
     * Получает ставку НДС для товарной позиции с кешированием.
     * Логика определения ставки:
     * 1. Если товар помечен как "не налогооблагаемый" в WooCommerce, всегда возвращается 'none'.
     * 2. Если товар налогооблагаемый, и для него определена ставка в WooCommerce, используется эта ставка.
     * 3. Если товар налогооблагаемый, но ставка не найдена, используется "Ставка НДС по умолчанию" из настроек плагина.
     *
     * @param WC_Order_Item_Product $item Объект товарной позиции.
     * @return string Код ставки НДС (например, 'vat20', 'none').
     */
    private function getItemVatRate(WC_Order_Item_Product $item): string
    {
        $product = $item->get_product();
        // Защита от случая, если товар был удален
        if (!$product) {
            return $this->settings['default_vat'] ?? 'none';
        }

        if (!$product->is_taxable()) {
            return 'none';
        }

        $tax_class = $item->get_tax_class();

        $rates = $this->getRatesCached($tax_class);

        if (!empty($rates)) {
            $rate_obj = array_shift($rates);
            return 'vat' . (int)$rate_obj['rate'];
        }

        return $this->settings['default_vat'] ?? 'none';
    }

    /**
     * Получает ставку НДС для доставки, основываясь на исходных настройках метода.
     * @return string
     */
    private function getShippingVatRate(): string
    {
        /** @var WC_Order_Item_Shipping[] $shipping_items */
        $shipping_items = $this->order->get_items('shipping');
        if (empty($shipping_items)) { return $this->settings['default_vat'] ?? 'none'; }

        $shipping_item = reset($shipping_items);
        $instance_id = $shipping_item->get_instance_id();

        $shipping_zone = WC_Shipping_Zones::get_zone_by('instance_id', $instance_id);
        $shipping_method = null;
        if ($shipping_zone) {
            $shipping_methods_in_zone = $shipping_zone->get_shipping_methods();
            if (isset($shipping_methods_in_zone[$instance_id])) {
                $shipping_method = $shipping_methods_in_zone[$instance_id];
            }
        }

        // Если мы нашли исходный метод доставки, смотрим его реальный статус налога
        if ($shipping_method && 'none' === $shipping_method->get_option('tax_status')) {
            return 'none';
        }

        // Если статус налога не 'none' или метод не найден, используем старую логику
        $tax_class = $shipping_item->get_tax_class();
        if (empty($tax_class)) {
            return 'none';
        }

        $rates = $this->getRatesCached($tax_class);
        if (!empty($rates)) {
            $rate_obj = array_shift($rates);
            return 'vat' . (int)$rate_obj['rate'];
        }

        return $this->settings['default_vat'] ?? 'none';
    }

    /**
     * Получает ставки НДС для налогового класса, используя многоуровневое кеширование.
     *
     * @param string $tax_class Слаг налогового класса.
     * @return array
     */
    private function getRatesCached(string $tax_class): array
    {
        // 1. Проверяем статический кеш (в рамках одного запроса)
        if (isset(self::$taxRatesCache[$tax_class])) {
            return self::$taxRatesCache[$tax_class];
        }

        // 2. Проверяем объектный кеш WordPress (между запросами, если он включен)
        $cache_key = 'tochka_tax_rates_' . $tax_class;
        $cached_rates = wp_cache_get($cache_key, 'tochka_payments');
        if (false !== $cached_rates) {
            self::$taxRatesCache[$tax_class] = $cached_rates; // Сохраняем в статический кеш для будущих вызовов
            return $cached_rates;
        }

        // 3. Если в кеше нет, делаем запрос к БД
        $rates = WC_Tax::get_rates($tax_class);

        // 4. Сохраняем результат в оба кеша
        wp_cache_set($cache_key, $rates, 'tochka_payments', 5 * MINUTE_IN_SECONDS); // Кешируем на 5 минут
        self::$taxRatesCache[$tax_class] = $rates;

        return $rates;
    }

    /**
     * Вспомогательный метод для пересчета общей суммы позиций.
     */
    private function recalculateTotal(bool $round = true): void
    {
        $total = 0.0;
        foreach ($this->cartItems as $item) {
            $total += $round ? round($item['sum'], 2) : $item['sum'];
        }
        $this->calculatedItemsTotal = $total;
    }
}