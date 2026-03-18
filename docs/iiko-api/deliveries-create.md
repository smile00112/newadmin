# POST /api/1/deliveries/create

**Создание заказа на доставку.**

- Группа: Deliveries: Create and update
- Группа ограничений: `Orders: deliveries`
- Документация: https://api-ru.iiko.services/docs#tag/Deliveries:-Create-and-update/paths/~1api~11~1deliveries~1create/post
- SDK-пакет (Laravel): [codeofsolomon/iiko_cloud](https://github.com/codeofsolomon/iiko_cloud)

---

## Заголовки

| Заголовок | Тип | Обязательный | Описание |
|-----------|-----|--------------|----------|
| `Authorization` | string | Да | Bearer-токен. Пример: `Bearer nRzIn0dJu1L...` |
| `Timeout` | integer | Нет | Таймаут в секундах (по умолчанию 15) |

---

## Тело запроса (Request Body)

**Schema:** `DeliveryCreateRequest`

```json
{
  "organizationId": "00000000-0000-0000-0000-000000000000",
  "terminalGroupId": "00000000-0000-0000-0000-000000000000",
  "createOrderSettings": {
    "transportToFrontTimeout": 8,
    "checkStopList": false
  },
  "order": {
    "id": null,
    "externalNumber": "ORD-12345",
    "completeBefore": "2026-03-18 18:00:00.000",
    "phone": "+79001234567",
    "orderServiceType": "DeliveryByCourier",
    "deliveryPoint": {
      "coordinates": { "latitude": 55.7558, "longitude": 37.6173 },
      "address": {
        "street": { "classifierId": "..." },
        "house": "10",
        "flat": "5",
        "entrance": "2",
        "floor": "3"
      },
      "comment": "Код домофона 123"
    },
    "customer": {
      "name": "Иван Петров",
      "phone": "+79001234567",
      "type": "regular"
    },
    "guests": { "count": 2, "splitBetweenPersons": false },
    "items": [
      {
        "productId": "00000000-0000-0000-0000-000000000000",
        "type": "Product",
        "amount": 2,
        "price": 500.0,
        "modifiers": [
          { "productId": "...", "amount": 1, "productGroupId": "..." }
        ],
        "comment": "Без лука"
      }
    ],
    "combos": [],
    "payments": [
      {
        "paymentTypeKind": "Cash",
        "sum": 1000.0,
        "paymentTypeId": "00000000-0000-0000-0000-000000000000",
        "isProcessedExternally": false,
        "isPrepay": false
      }
    ],
    "tips": [],
    "comment": "Позвонить за 5 минут",
    "sourceKey": "my-app"
  }
}
```

---

## Корневой объект запроса: DeliveryCreateRequest

| Поле | Тип | Обязательное | Nullable | Описание |
|------|-----|-------------|----------|----------|
| `organizationId` | string (uuid) | **Да** | Нет | ID организации. Получается через `/api/1/organizations` |
| `terminalGroupId` | string (uuid) | Нет | Да | ID терминальной группы, на которую отправляется заказ. Получается через `/api/1/terminal_groups` |
| `createOrderSettings` | [CreateOrderSettings](#createordersettings) | Нет | Да | Параметры создания заказа |
| `order` | [DeliveryCreateOrder](#deliverycreateorder) | **Да** | Нет | Заказ |

---

## CreateOrderSettings

Параметры создания заказа.

| Поле | Тип | Обязательное | Nullable | Описание |
|------|-----|-------------|----------|----------|
| `transportToFrontTimeout` | integer | Нет | Да | Таймаут отправки на терминал (секунды, по умолчанию `8`) |
| `checkStopList` | boolean | Нет | Да | Проверять ли стоп-лист при создании заказа |

---

## DeliveryCreateOrder

Объект заказа в запросе на создание доставки.

| Поле | Тип | Обязательное | Nullable | Описание |
|------|-----|-------------|----------|----------|
| `menuId` | string | Нет | Да | ID внешнего меню |
| `id` | string (uuid) | Нет | Да | ID заказа. Должен быть уникальным (Guid). Если `null` — генерируется автоматически |
| `externalNumber` | string [0..50] | Нет | Да | Внешний номер заказа. Доступно с версии 8.0.6 |
| `completeBefore` | string (yyyy-MM-dd HH:mm:ss.fff) | Нет | Да | Дата/время исполнения. Локальное время терминала без часового пояса. Если `null` — заказ срочный |
| `phone` | string [8..40] | **Да** | Нет | Телефон. Должен начинаться с `+`, минимум 8 цифр |
| `phoneExtension` | string [1..10] | Нет | Да | Добавочный номер. Только цифры. С версии 9.2.5 |
| `orderTypeId` | string (uuid) | Нет | Да | ID типа заказа. Через `/api/1/deliveries/order_types`. Только одно из полей: `orderTypeId` **или** `orderServiceType` |
| `orderServiceType` | enum: `DeliveryByCourier`, `DeliveryByClient` | Нет | Да | Тип сервиса доставки. Только одно из полей: `orderTypeId` **или** `orderServiceType`. С версии 7.0.3 |
| `deliveryPoint` | [DeliveryPoint](#deliverypoint-запрос) | Нет | Да | Точка доставки. Не требуется для самовывоза (`DeliveryByClient`). Иначе — обязательно |
| `comment` | string | Нет | Да | Комментарий к заказу |
| `customer` | [RegularCustomer](#regularcustomer) \| [OneTimeCustomer](#onetimecustomer) | Нет | Да | Клиент |
| `guests` | [OrderGuests](#orderguests) | Нет | Да | Информация о гостях |
| `marketingSourceId` | string (uuid) | Нет | Да | ID маркетингового источника. Через `/api/1/marketing_sources` |
| `operatorId` | string (uuid) | Нет | Да | ID оператора. С версии 7.6.3 |
| `deliveryDuration` | integer (int32) | Нет | Да | Длительность доставки (мин). С версии 8.8.6 |
| `deliveryZone` | string | Нет | Да | Название зоны доставки. С версии 8.8.6 |
| `priceCategoryId` | string (uuid) | Нет | Да | ID ценовой категории. Через `/api/2/menu`. С версии 9.0.5 |
| `items` | [OrderItemRequest](#orderitemrequest)[] | **Да** | Нет | Позиции заказа (минимум 1) |
| `combos` | [OrderCombo](#ordercombo)[] | Нет | Да | Комбо в заказе |
| `payments` | [PaymentRequest](#paymentrequest)[] | Нет | Да | Оплаты заказа. Тип `LoyaltyCard` с версии 7.1.5 |
| `tips` | [TipsPaymentRequest](#tipspaymentrequest)[] | Нет | Да | Чаевые |
| `sourceKey` | string | Нет | Да | Строковый ключ источника (партнёра/интеграции). Ограничивает видимость заказов |
| `discountsInfo` | [DiscountsInfo](#discountsinfo) | Нет | Да | Скидки/надбавки |
| `loyaltyInfo` | [LoyaltyInfo](#loyaltyinfo-запрос) | Нет | Да | Информация о лояльности |
| `chequeAdditionalInfo` | [ChequeAdditionalInfo](#chequeadditionalinfo) | Нет | Да | Дополнительная информация для чека |
| `externalData` | [ExternalData](#externaldata)[] | Нет | Да | Внешние данные заказа. С версии 8.0.6 |

---

## DeliveryPoint (запрос)

Точка доставки.

| Поле | Тип | Обязательное | Nullable | Описание |
|------|-----|-------------|----------|----------|
| `coordinates` | [Coordinates](#coordinates) | Нет | Да | Координаты доставки. С версии 7.7.3 |
| `address` | [DeliveryAddress](#deliveryaddress-запрос) | Нет | Да | Адрес доставки |
| `externalCartographyId` | string [0..100] | Нет | Да | Код точки доставки во внешней системе |
| `comment` | string [0..500] | Нет | Да | Дополнительная информация |

---

## Coordinates

Географические координаты.

| Поле | Тип | Обязательное | Nullable | Описание |
|------|-----|-------------|----------|----------|
| `latitude` | number (double) | **Да** | Нет | Широта |
| `longitude` | number (double) | **Да** | Нет | Долгота |

---

## DeliveryAddress (запрос)

Адрес доставки. Тип `City` допускается, если `addressFormatType == City` (см. `/api/1/organizations/settings`).

| Поле | Тип | Обязательное | Nullable | Описание |
|------|-----|-------------|----------|----------|
| `street` | [Street](#street) | **Да** | Нет | Улица. Достаточно указать только одно из: `classifierId`, `id`, или `name` + `city` |
| `index` | string [0..10] | Нет | Да | Почтовый индекс |
| `house` | string | **Да** | Нет | Дом |
| `building` | string [0..10] | Нет | Да | Корпус/строение |
| `flat` | string [0..100] | Нет | Да | Квартира. Если `useUaeAddressingSystem` — макс. 100, иначе 10 |
| `entrance` | string [0..10] | Нет | Да | Подъезд |
| `floor` | string [0..10] | Нет | Да | Этаж |
| `doorphone` | string [0..10] | Нет | Да | Домофон |
| `regionId` | string (uuid) | Нет | Да | ID района доставки |
| `type` | string | **Да** | Нет | Тип адреса. Пример: `"legacy"` |

---

## Street

Улица. Достаточно указать одно из трёх: `classifierId`, `id`, или `name` + `city`.

| Поле | Тип | Обязательное | Nullable | Описание |
|------|-----|-------------|----------|----------|
| `classifierId` | string | Нет | Да | ID классификатора (КЛАДР/ФИАС) |
| `id` | string (uuid) | Нет | Да | ID улицы в RMS |
| `name` | string | Нет | Да | Название улицы |
| `city` | string | Нет | Да | Город (в связке с `name`) |

---

## RegularCustomer

Постоянный клиент — участвует в программах лояльности, данные сохраняются в базу RMS.

| Поле | Тип | Обязательное | Nullable | Описание |
|------|-----|-------------|----------|----------|
| `id` | string (uuid) | Нет | Да | ID существующего клиента в RMS. Если `null` — поиск по телефону, иначе создаётся новый |
| `name` | string [0..60] | Нет | Да | Имя. Обязательно для новых клиентов (`id == null`) |
| `surname` | string [0..60] | Нет | Да | Фамилия |
| `comment` | string [0..60] | Нет | Да | Комментарий |
| `birthdate` | string (yyyy-MM-dd HH:mm:ss.fff) | Нет | Да | Дата рождения |
| `email` | string | Нет | Да | Email |
| `shouldReceiveOrderStatusNotifications` | boolean | Нет | Да | Получать ли уведомления о статусе заказа |
| `gender` | enum: `NotSpecified`, `Male`, `Female` | Нет | Нет | Пол |
| `type` | string | **Да** | Нет | Всегда `"regular"` |

---

## OneTimeCustomer

Разовый клиент — данные НЕ сохраняются в базу. Используется для агрегаторов или если клиент не согласен на лояльность.

| Поле | Тип | Обязательное | Nullable | Описание |
|------|-----|-------------|----------|----------|
| `name` | string [0..60] | **Да** | Нет | Имя |
| `surname` | string [0..60] | Нет | Да | Фамилия |
| `comment` | string [0..60] | Нет | Да | Комментарий |
| `birthdate` | string (yyyy-MM-dd HH:mm:ss.fff) | Нет | Да | Дата рождения |
| `email` | string | Нет | Да | Email |
| `shouldReceiveOrderStatusNotifications` | boolean | Нет | Да | Получать ли уведомления |
| `gender` | enum: `NotSpecified`, `Male`, `Female` | Нет | Нет | Пол |
| `type` | string | **Да** | Нет | Всегда `"one-time"` |

---

## OrderGuests

Информация о гостях.

| Поле | Тип | Обязательное | Nullable | Описание |
|------|-----|-------------|----------|----------|
| `count` | integer (int32) | **Да** | Нет | Количество персон (определяет количество наборов приборов) |
| `splitBetweenPersons` | boolean | Нет | Да | Разделить заказ между гостями |

---

## OrderItemRequest

Позиция заказа.

| Поле | Тип | Обязательное | Nullable | Описание |
|------|-----|-------------|----------|----------|
| `productId` | string (uuid) | **Да** | Нет | ID позиции меню. Через `/api/1/nomenclature` |
| `type` | string | **Да** | Нет | Всегда `"Product"` |
| `amount` | number (double) [0..999.999] | **Да** | Нет | Количество |
| `productSizeId` | string (uuid) | Нет | Да | ID размера. Обязателен, если у позиции есть шкала размеров |
| `price` | number (double) | **Да** | Нет | Цена за единицу. Может отличаться от цены в меню |
| `positionId` | string (uuid) | Нет | Да | Уникальный ID позиции в заказе (Guid). Если `null` — генерируется автоматически |
| `modifiers` | [OrderItemModifierRequest](#orderitemmodifierrequest)[] | Нет | Да | Модификаторы |
| `comboInformation` | [ComboInformation](#comboinformation) | Нет | Да | Детали комбо, если позиция входит в комбо |
| `comment` | string [0..255] | Нет | Да | Комментарий к позиции |

---

## OrderItemModifierRequest

Модификатор позиции заказа.

| Поле | Тип | Обязательное | Nullable | Описание |
|------|-----|-------------|----------|----------|
| `productId` | string (uuid) | **Да** | Нет | ID модификатора. Через `/api/1/nomenclature` |
| `amount` | number (double) | **Да** | Нет | Количество |
| `productGroupId` | string (uuid) | Нет | Да | ID группы модификаторов (для групповых модификаторов). Обязателен для группового модификатора |
| `price` | number (double) | Нет | Да | Цена за единицу |
| `positionId` | string (uuid) | Нет | Да | Уникальный ID позиции модификатора в заказе (Guid) |

---

## ComboInformation

Информация о принадлежности позиции к комбо.

| Поле | Тип | Обязательное | Nullable | Описание |
|------|-----|-------------|----------|----------|
| `comboId` | string (uuid) | **Да** | Нет | ID комбо (ссылка на `OrderCombo.id`) |
| `comboSourceId` | string (uuid) | **Да** | Нет | ID источника комбо (программы) |
| `comboGroupId` | string (uuid) | **Да** | Нет | ID группы в комбо |

---

## OrderCombo

Комбо в заказе.

| Поле | Тип | Обязательное | Nullable | Описание |
|------|-----|-------------|----------|----------|
| `id` | string (uuid) | **Да** | Нет | ID комбо. Уникальный Guid |
| `name` | string | **Да** | Нет | Название комбо |
| `amount` | integer (int32) | **Да** | Нет | Количество |
| `price` | number (double) | **Да** | Нет | Цена одного комбо |
| `sourceId` | string (uuid) | **Да** | Нет | ID акции/программы комбо |
| `programId` | string (uuid) | Нет | Нет | ID программы карт. С версии 7.6.1 |
| `sizeId` | string (uuid) | Нет | Да | ID размера. С версии 8.5.6 |

---

## PaymentRequest

Оплата заказа.

| Поле | Тип | Обязательное | Nullable | Описание |
|------|-----|-------------|----------|----------|
| `paymentTypeKind` | enum: `Cash`, `Card`, `IikoCard`, `External` | **Да** | Нет | Вид оплаты |
| `sum` | number (double) [0..10 000 000 000] | **Да** | Нет | Сумма |
| `paymentTypeId` | string (uuid) | **Да** | Нет | ID типа оплаты. Через `/api/1/payment_types` |
| `isProcessedExternally` | boolean | Нет | Нет | Обработана ли оплата внешней системой |
| `paymentAdditionalData` | [PaymentAdditionalData](#paymentadditionaldata) | Нет | Да | Дополнительные данные оплаты |
| `isFiscalizedExternally` | boolean | Нет | Нет | Фискализирована ли внешне. С версии 7.6.3 |
| `isPrepay` | boolean | Нет | Нет | **Deprecated**. Предоплата. Недоступно для `LoyaltyCard`. С версии 8.2.6 |

---

## PaymentAdditionalData

Дополнительные данные оплаты.

| Поле | Тип | Обязательное | Nullable | Описание |
|------|-----|-------------|----------|----------|
| `credential` | string | Нет | Да | Учётные данные (номер карты, токен и т.д.) |
| `searchScope` | enum: `Reserved`, `Organization`, `TerminalGroup` | Нет | Нет | Область поиска |
| `type` | string | **Да** | Нет | Тип. Пример: `"IikoCard"`, `"Card"` |

---

## TipsPaymentRequest

Чаевые.

| Поле | Тип | Обязательное | Nullable | Описание |
|------|-----|-------------|----------|----------|
| `paymentTypeKind` | enum: `Cash`, `Card`, `IikoCard`, `External` | **Да** | Нет | Вид оплаты |
| `tipsTypeId` | string (uuid) | Нет | Нет | ID типа чаевых. Через `/api/1/tips_types` |
| `sum` | number (double) [0..10 000 000 000] | **Да** | Нет | Сумма |
| `paymentTypeId` | string (uuid) | **Да** | Нет | ID типа оплаты. Через `/api/1/payment_types` |
| `isProcessedExternally` | boolean | Нет | Нет | Обработана ли внешней системой |
| `paymentAdditionalData` | [PaymentAdditionalData](#paymentadditionaldata) | Нет | Да | Дополнительные данные |
| `isFiscalizedExternally` | boolean | Нет | Нет | Фискализирована ли внешне |
| `isPrepay` | boolean | Нет | Нет | **Deprecated.** Предоплата |

---

## DiscountsInfo

Скидки и надбавки заказа.

| Поле | Тип | Обязательное | Nullable | Описание |
|------|-----|-------------|----------|----------|
| `card` | [DiscountCard](#discountcard) | Нет | Да | Дисконтная карта |
| `discounts` | [DiscountRequest](#discountrequest)[] | Нет | Да | Скидки/надбавки. Тип `iikoCard` с версии 7.4.4 |
| `fixedLoyaltyDiscounts` | boolean | Нет | Да | Зафиксировать скидки лояльности |

---

## DiscountCard

| Поле | Тип | Обязательное | Nullable | Описание |
|------|-----|-------------|----------|----------|
| `track` | string | **Да** | Нет | Трек дисконтной карты |

---

## DiscountRequest

| Поле | Тип | Обязательное | Nullable | Описание |
|------|-----|-------------|----------|----------|
| `discountTypeId` | string (uuid) | **Да** | Нет | ID типа скидки. Через `/api/1/discounts` |
| `sum` | number (double) | Нет | Нет | Сумма скидки/надбавки |
| `selectivePositions` | string[] (uuid) | Нет | Да | ID позиций заказа, к которым применяется |
| `type` | string | **Да** | Нет | Тип. Пример: `"RMS"`, `"iikoCard"` |

---

## LoyaltyInfo (запрос)

Информация о лояльности.

| Поле | Тип | Обязательное | Nullable | Описание |
|------|-----|-------------|----------|----------|
| `coupon` | string | Нет | Да | Номер купона, учтённого при расчёте лояльности |
| `applicableManualConditions` | string[] (uuid) | Нет | Да | Применённые ручные условия |
| `dynamicDiscounts` | [DynamicDiscount](#dynamicdiscount)[] | Нет | Да | Динамические скидки. С версии 9.4.6 |

---

## DynamicDiscount

| Поле | Тип | Обязательное | Nullable | Описание |
|------|-----|-------------|----------|----------|
| `manualConditionId` | string (uuid) | **Да** | Нет | ID применённого ручного условия |
| `sum` | number (double) | **Да** | Нет | Сумма скидки |

---

## ChequeAdditionalInfo

Дополнительная информация для чека.

| Поле | Тип | Обязательное | Nullable | Описание |
|------|-----|-------------|----------|----------|
| `needReceipt` | boolean | **Да** | Нет | Печатать ли бумажный чек |
| `email` | string [0..255] | Нет | Да | Email для отправки чека. `null` — не отправлять |
| `settlementPlace` | string [0..500] | Нет | Да | Место расчёта |
| `phone` | string [8..40] | Нет | Да | Телефон для SMS-чека. `null` — не отправлять |
| `retailAddress` | string [0..256] | Нет | Да | Адрес розничной точки. С версии 9.4.6 |
| `isInternetPayment` | boolean | Нет | Нет | Интернет-оплата. С версии 9.4.6 |

---

## ExternalData

Внешние данные заказа.

| Поле | Тип | Обязательное | Nullable | Описание |
|------|-----|-------------|----------|----------|
| `key` | string | **Да** | Нет | Ключ |
| `value` | string | **Да** | Нет | Значение |
| `isPublic` | boolean | Нет | Нет | Включать ли данные в отчёт о продажах (`true`) или скрыть (`false`) |

---

---

# Ответ (Response)

## 200 — Успех

**Schema:** `CreateDeliveryResponse`

```json
{
  "correlationId": "550e8400-e29b-41d4-a716-446655440000",
  "orderInfo": {
    "id": "550e8400-e29b-41d4-a716-446655440001",
    "posId": null,
    "externalNumber": "ORD-12345",
    "organizationId": "550e8400-e29b-41d4-a716-446655440002",
    "timestamp": 1710700800,
    "creationStatus": "Success",
    "errorInfo": null,
    "order": { "...": "см. DeliveryOrder ниже" }
  }
}
```

---

## CreateDeliveryResponse

Корневой объект ответа.

| Поле | Тип | Обязательное | Nullable | Описание |
|------|-----|-------------|----------|----------|
| `correlationId` | string (uuid) | **Да** | Нет | Correlation ID запроса |
| `orderInfo` | [OrderInfo](#orderinfo) | **Да** | Нет | Информация о созданном заказе |

---

## OrderInfo

Информация о заказе — результат создания.

| Поле | Тип | Обязательное | Nullable | Описание |
|------|-----|-------------|----------|----------|
| `id` | string (uuid) | **Да** | Нет | ID заказа в iikoTransport |
| `posId` | string | Нет | Да | ID в POS-системе (появляется после подтверждения терминалом) |
| `externalNumber` | string | Нет | Да | Внешний номер заказа (из запроса) |
| `organizationId` | string (uuid) | **Да** | Нет | ID организации |
| `timestamp` | integer (int64) | **Да** | Нет | Временная метка (unix timestamp) |
| `creationStatus` | enum: `Success`, `InProgress`, `Error` | **Да** | Нет | Статус создания заказа |
| `errorInfo` | [ErrorInfo](#errorinfo) | Нет | Да | Информация об ошибке (если `creationStatus == Error`) |
| `order` | [DeliveryOrder](#deliveryorder) | Нет | Да | Полный объект заказа. Может быть `null` при `InProgress` |

---

## ErrorInfo

Информация об ошибке создания заказа.

| Поле | Тип | Обязательное | Nullable | Описание |
|------|-----|-------------|----------|----------|
| `code` | enum: `Common`, `TerminalGroupIsNotAlive`, `Timeout`, `OrderAlreadyExists`, ... | **Да** | Нет | Код ошибки |
| `message` | string | Нет | Да | Текстовое описание ошибки |
| `description` | string | Нет | Да | Подробное описание |

### Коды ошибок (ErrorInfoCode)

| Код | Описание |
|-----|----------|
| `Common` | Общая ошибка |
| `TerminalGroupIsNotAlive` | Терминальная группа недоступна |
| `Timeout` | Таймаут |
| `OrderAlreadyExists` | Заказ с таким `id` уже существует |
| `PaymentTypeNotFound` | Тип оплаты не найден |
| `DeliveryPointIsNotInZone` | Точка доставки вне зоны |
| `CustomerNotFound` | Клиент не найден |

---

## DeliveryOrder

Полный объект заказа-доставки в ответе. Содержит все данные заказа, обогащённые сервером после создания.

| Поле | Тип | Обязательное | Nullable | Описание |
|------|-----|-------------|----------|----------|
| `parentDeliveryId` | string (uuid) | Нет | Да | ID родительской доставки (при переносе) |
| `customer` | [CustomerResponse](#customerresponse) | Нет | Да | Клиент |
| `phone` | string | Нет | Да | Телефон |
| `phoneExtension` | string | Нет | Да | Добавочный номер |
| `deliveryPoint` | [DeliveryPointResponse](#deliverypointresponse) | Нет | Да | Точка доставки |
| `status` | enum: см. [OrderStatus](#orderstatus) | **Да** | Нет | Статус заказа |
| `cancelInfo` | [CancelInfo](#cancelinfo) | Нет | Да | Информация об отмене |
| `courierInfo` | [CourierInfo](#courierinfo) | Нет | Да | Информация о курьере |
| `completeBefore` | string (datetime) | **Да** | Нет | Дата/время ожидаемого исполнения |
| `whenCreated` | string (datetime) | **Да** | Нет | Дата/время создания |
| `whenConfirmed` | string (datetime) | Нет | Да | Когда подтверждён |
| `whenPrinted` | string (datetime) | Нет | Да | Когда напечатан |
| `whenCookingCompleted` | string (datetime) | Нет | Да | Когда приготовление завершено |
| `whenSended` | string (datetime) | Нет | Да | Когда отправлен курьеру |
| `whenDelivered` | string (datetime) | Нет | Да | Когда доставлен |
| `whenBillPrinted` | string (datetime) | Нет | Да | Когда напечатан счёт |
| `whenClosed` | string (datetime) | Нет | Да | Когда закрыт |
| `whenPacked` | string (datetime) | Нет | Да | Когда упакован |
| `comment` | string | Нет | Да | Комментарий |
| `problem` | [Problem](#problem) | Нет | Да | Проблема с заказом |
| `operator` | [Waiter](#waiter) | Нет | Да | Оператор (создатель) |
| `deliveryDuration` | integer | Нет | Да | Длительность доставки (мин) |
| `indexInCourierRoute` | integer | Нет | Да | Индекс в маршруте курьера |
| `cookingStartTime` | string (datetime) | **Да** | Нет | Время начала приготовления |
| `movedFromDeliveryId` | string (uuid) | Нет | Да | Перенесено из (ID доставки) |
| `movedFromTerminalGroupId` | string (uuid) | Нет | Да | Перенесено из (ID терминальной группы) |
| `movedFromOrganizationId` | string (uuid) | Нет | Да | Перенесено из (ID организации) |
| `movedToDeliveryId` | string (uuid) | Нет | Да | Перенесено в (ID доставки) |
| `movedToTerminalGroupId` | string (uuid) | Нет | Да | Перенесено в (ID терминальной группы) |
| `movedToOrganizationId` | string (uuid) | Нет | Да | Перенесено в (ID организации) |
| `externalCourierService` | [ExternalCourierService](#externalcourierservice) | Нет | Да | Внешняя курьерская служба |
| `menuId` | string | Нет | Да | ID внешнего меню |
| `deliveryZone` | string | Нет | Да | Название зоны доставки |
| `estimatedTime` | string (datetime) | Нет | Да | Расчётное время доставки |
| `isAsap` | boolean | Нет | Да | Срочный заказ (ASAP) |
| `sum` | number (double) | **Да** | Нет | Сумма заказа |
| `number` | integer | **Да** | Нет | Номер заказа в iiko |
| `sourceKey` | string | Нет | Да | Ключ источника |
| `guestsInfo` | [GuestsInfo](#guestsinfo) | Нет | Да | Информация о гостях |
| `items` | [OrderItemResponse](#orderitemresponse)[] | **Да** | Нет | Позиции заказа |
| `combos` | [ComboResponse](#comboresponse)[] | **Да** | Нет | Комбо |
| `payments` | [PaymentResponse](#paymentresponse)[] | **Да** | Нет | Оплаты |
| `tips` | [TipsResponse](#tipsresponse)[] | **Да** | Нет | Чаевые |
| `discounts` | [DiscountInfoResponse](#discountinforesponse)[] | **Да** | Нет | Скидки |
| `conception` | [Conception](#conception) | Нет | Да | Концепция |
| `terminalGroupId` | string (uuid) | Нет | Да | ID терминальной группы |
| `processedPaymentsSum` | number (double) | **Да** | Нет | Сумма обработанных оплат |
| `orderType` | [OrderType](#ordertype) | Нет | Да | Тип заказа |
| `loyaltyInfo` | [LoyaltyInfoResponse](#loyaltyinforesponse) | Нет | Да | Информация о лояльности |

---

## OrderStatus

Статус заказа-доставки.

| Значение | Описание |
|----------|----------|
| `Unconfirmed` | Не подтверждён |
| `WaitCooking` | Ожидает приготовления |
| `ReadyForCooking` | Готов к приготовлению |
| `CookingStarted` | Приготовление начато |
| `CookingCompleted` | Приготовление завершено |
| `Waiting` | Ожидает |
| `OnWay` | В пути |
| `Delivered` | Доставлен |
| `Closed` | Закрыт |
| `Cancelled` | Отменён |

---

## OrderCreationStatus

| Значение | Описание |
|----------|----------|
| `Success` | Заказ успешно создан |
| `InProgress` | Заказ в процессе создания (терминал ещё не подтвердил) |
| `Error` | Ошибка создания |

---

## CustomerResponse

Клиент в ответе.

| Поле | Тип | Обязательное | Nullable | Описание |
|------|-----|-------------|----------|----------|
| `id` | string (uuid) | **Да** | Нет | ID клиента в RMS |
| `name` | string | Нет | Да | Имя |
| `surname` | string | Нет | Да | Фамилия |
| `phone` | string | Нет | Да | Телефон |
| `comment` | string | Нет | Да | Комментарий |
| `gender` | enum: `NotSpecified`, `Male`, `Female` | **Да** | Нет | Пол |
| `inBlacklist` | boolean | Нет | Да | В чёрном списке |
| `birthdate` | string | Нет | Да | Дата рождения |
| `type` | string | **Да** | Нет | `"regular"` или `"one-time"` |

---

## DeliveryPointResponse

Точка доставки в ответе. Структура аналогична [DeliveryPoint (запрос)](#deliverypoint-запрос), обогащена координатами.

---

## CancelInfo

Информация об отмене заказа.

| Поле | Тип | Обязательное | Nullable | Описание |
|------|-----|-------------|----------|----------|
| `cancelCause` | [CancelCause](#cancelcause) | Нет | Да | Причина отмены |
| `whenCancelled` | string (datetime) | **Да** | Нет | Когда отменён |
| `comment` | string | Нет | Да | Комментарий к отмене |

---

## CancelCause

| Поле | Тип | Обязательное | Nullable | Описание |
|------|-----|-------------|----------|----------|
| `id` | string (uuid) | **Да** | Нет | ID причины |
| `name` | string | **Да** | Нет | Название причины |

---

## CourierInfo

Информация о курьере.

| Поле | Тип | Обязательное | Nullable | Описание |
|------|-----|-------------|----------|----------|
| `courier` | [Courier](#courier) | **Да** | Нет | Курьер |
| `isCourierSelectedManually` | boolean | **Да** | Нет | Выбран вручную |

---

## Courier

| Поле | Тип | Обязательное | Nullable | Описание |
|------|-----|-------------|----------|----------|
| `id` | string (uuid) | **Да** | Нет | ID курьера |
| `name` | string | **Да** | Нет | Имя курьера |
| `phone` | string | Нет | Да | Телефон курьера |

---

## Problem

Проблема с заказом (флаг «проблемный заказ»).

| Поле | Тип | Обязательное | Nullable | Описание |
|------|-----|-------------|----------|----------|
| `hasProblem` | boolean | **Да** | Нет | Есть ли проблема |
| `description` | string | Нет | Да | Описание проблемы |

---

## Waiter

Оператор/официант (сотрудник).

| Поле | Тип | Обязательное | Nullable | Описание |
|------|-----|-------------|----------|----------|
| `id` | string (uuid) | **Да** | Нет | ID сотрудника |
| `name` | string | **Да** | Нет | Имя |
| `phone` | string | Нет | Да | Телефон |

---

## ExternalCourierService

Внешняя курьерская служба.

| Поле | Тип | Обязательное | Nullable | Описание |
|------|-----|-------------|----------|----------|
| `id` | string (uuid) | **Да** | Нет | ID службы |
| `name` | string | **Да** | Нет | Название |

---

## GuestsInfo

Информация о гостях в ответе.

| Поле | Тип | Обязательное | Nullable | Описание |
|------|-----|-------------|----------|----------|
| `count` | integer | **Да** | Нет | Количество гостей |
| `splitBetweenPersons` | boolean | Нет | Да | Разделить между гостями |

---

## OrderItemResponse

Позиция заказа в ответе. Абстрактный тип — конкретный определяется полем `type`.

| Поле | Тип | Обязательное | Nullable | Описание |
|------|-----|-------------|----------|----------|
| `type` | enum: `Product`, `Compound`, `Service` | **Да** | Нет | Тип позиции |
| `status` | enum: см. [OrderItemStatus](#orderitemstatus) | **Да** | Нет | Статус позиции |
| `deleted` | [ItemDeletedInfo](#itemdeletedinfo) | Нет | Да | Информация об удалении |
| `amount` | number (double) | **Да** | Нет | Количество |
| `comment` | string | Нет | Да | Комментарий |
| `whenPrinted` | string (datetime) | Нет | Да | Когда напечатано |
| `size` | [ProductSize](#productsize) | Нет | Да | Размер |
| `comboInformation` | [ComboItemInformationResponse](#comboiteminformationresponse) | Нет | Да | Информация о комбо |

### Дополнительные поля для `type: "Product"` (ProductOrderItem)

| Поле | Тип | Обязательное | Nullable | Описание |
|------|-----|-------------|----------|----------|
| `product` | [ProductRef](#productref) | **Да** | Нет | Ссылка на продукт |
| `modifiers` | [OrderItemModifierResponse](#orderitemmodifierresponse)[] | Нет | Да | Модификаторы |
| `price` | number (double) | **Да** | Нет | Цена за единицу |
| `cost` | number (double) | **Да** | Нет | Стоимость (price × amount) |
| `pricePredefined` | boolean | **Да** | Нет | Цена задана заранее |
| `positionId` | string (uuid) | Нет | Да | ID позиции в заказе |
| `defaultAmount` | integer | Нет | Да | Количество по умолчанию |
| `hideIfDefaultAmount` | boolean | Нет | Да | Скрывать если количество по умолчанию |
| `taxPercent` | number (double) | Нет | Да | Процент налога |
| `freeOfChargeAmount` | integer | Нет | Да | Бесплатное количество |

### Дополнительные поля для `type: "Compound"` (CompoundOrderItem)

| Поле | Тип | Обязательное | Nullable | Описание |
|------|-----|-------------|----------|----------|
| `primaryComponent` | [CompoundOrderItemComponent](#compoundorderitemcomponent) | **Да** | Нет | Основной компонент |
| `secondaryComponent` | [CompoundOrderItemComponent](#compoundorderitemcomponent) | Нет | Да | Дополнительный компонент |
| `commonModifiers` | [OrderItemModifierResponse](#orderitemmodifierresponse)[] | Нет | Да | Общие модификаторы |
| `template` | [CompoundItemTemplate](#compounditemtemplate) | Нет | Да | Шаблон составного блюда |

---

## OrderItemStatus

| Значение | Описание |
|----------|----------|
| `Added` | Добавлена |
| `PrintedNotCooking` | Напечатана, не готовится |
| `CookingStarted` | Приготовление начато |
| `CookingCompleted` | Приготовлена |
| `Served` | Подана |

---

## ProductRef

Ссылка на продукт.

| Поле | Тип | Обязательное | Nullable | Описание |
|------|-----|-------------|----------|----------|
| `id` | string (uuid) | **Да** | Нет | ID продукта |
| `name` | string | **Да** | Нет | Название |

---

## ProductSize

Размер продукта.

| Поле | Тип | Обязательное | Nullable | Описание |
|------|-----|-------------|----------|----------|
| `id` | string (uuid) | **Да** | Нет | ID размера |
| `name` | string | **Да** | Нет | Название размера |

---

## OrderItemModifierResponse

Модификатор позиции в ответе.

| Поле | Тип | Обязательное | Nullable | Описание |
|------|-----|-------------|----------|----------|
| `product` | [ProductRef](#productref) | **Да** | Нет | Ссылка на продукт-модификатор |
| `productGroup` | [ProductGroup](#productgroup) | Нет | Да | Группа модификатора |
| `amount` | number (double) | **Да** | Нет | Количество |
| `amountIndependentOfParentAmount` | boolean | Нет | Нет | Количество не зависит от количества родителя |
| `defaultAmount` | integer | Нет | Да | Количество по умолчанию |
| `hideIfDefaultAmount` | boolean | Нет | Да | Скрывать если по умолчанию |
| `price` | number (double) | **Да** | Нет | Цена |
| `pricePredefined` | boolean | Нет | Нет | Цена задана заранее |
| `positionId` | string (uuid) | Нет | Да | ID позиции |
| `freeOfChargeAmount` | integer | Нет | Да | Бесплатное количество |

---

## ProductGroup

Группа продукта/модификатора.

| Поле | Тип | Обязательное | Nullable | Описание |
|------|-----|-------------|----------|----------|
| `id` | string (uuid) | **Да** | Нет | ID группы |
| `name` | string | **Да** | Нет | Название группы |

---

## ItemDeletedInfo

Информация об удалении позиции.

| Поле | Тип | Обязательное | Nullable | Описание |
|------|-----|-------------|----------|----------|
| `deletionMethod` | [DeletionMethod](#deletionmethod) | **Да** | Нет | Метод удаления |
| `comment` | string | Нет | Да | Комментарий |

---

## DeletionMethod

| Поле | Тип | Обязательное | Nullable | Описание |
|------|-----|-------------|----------|----------|
| `id` | string (uuid) | **Да** | Нет | ID метода |
| `comment` | string | Нет | Да | Описание |
| `removalType` | [RemovalType](#removaltype) | Нет | Да | Тип списания |

---

## RemovalType

| Поле | Тип | Обязательное | Nullable | Описание |
|------|-----|-------------|----------|----------|
| `id` | string (uuid) | **Да** | Нет | ID типа |
| `name` | string | **Да** | Нет | Название |

---

## ComboItemInformationResponse

| Поле | Тип | Обязательное | Nullable | Описание |
|------|-----|-------------|----------|----------|
| `comboId` | string (uuid) | **Да** | Нет | ID комбо |
| `comboSourceId` | string (uuid) | **Да** | Нет | ID источника |
| `comboGroupId` | string (uuid) | **Да** | Нет | ID группы |

---

## CompoundOrderItemComponent

Компонент составного блюда.

| Поле | Тип | Обязательное | Nullable | Описание |
|------|-----|-------------|----------|----------|
| `product` | [ProductRef](#productref) | **Да** | Нет | Продукт |
| `modifiers` | [OrderItemModifierResponse](#orderitemmodifierresponse)[] | Нет | Да | Модификаторы |
| `price` | number (double) | **Да** | Нет | Цена |
| `cost` | number (double) | **Да** | Нет | Стоимость |
| `pricePredefined` | boolean | Нет | Нет | Цена задана заранее |
| `positionId` | string (uuid) | Нет | Да | ID позиции |

---

## CompoundItemTemplate

| Поле | Тип | Обязательное | Nullable | Описание |
|------|-----|-------------|----------|----------|
| `id` | string (uuid) | **Да** | Нет | ID шаблона |
| `name` | string | **Да** | Нет | Название |

---

## ComboResponse

Комбо в ответе заказа. Аналогичен [OrderCombo](#ordercombo), но обогащён серверными данными.

---

## PaymentResponse

Оплата в ответе.

| Поле | Тип | Обязательное | Nullable | Описание |
|------|-----|-------------|----------|----------|
| `id` | string (uuid) | **Да** | Нет | ID платежа |
| `paymentType` | [PaymentTypeRef](#paymenttyperef) | **Да** | Нет | Тип оплаты |
| `isPreliminary` | boolean | **Да** | Нет | Предварительная |
| `isExternal` | boolean | **Да** | Нет | Внешняя |
| `sum` | number (double) | **Да** | Нет | Сумма |
| `isProcessedExternally` | boolean | **Да** | Нет | Обработана внешне |
| `isFiscalizedExternally` | boolean | **Да** | Нет | Фискализирована внешне |
| `isPrepay` | boolean | **Да** | Нет | Предоплата |

---

## PaymentTypeRef

Тип оплаты.

| Поле | Тип | Обязательное | Nullable | Описание |
|------|-----|-------------|----------|----------|
| `id` | string (uuid) | **Да** | Нет | ID типа |
| `name` | string | **Да** | Нет | Название |
| `kind` | enum: `Cash`, `Card`, `IikoCard`, `External` | **Да** | Нет | Вид |

---

## TipsResponse

Чаевые в ответе. Структура аналогична [PaymentResponse](#paymentresponse) с дополнительным полем:

| Поле | Тип | Обязательное | Nullable | Описание |
|------|-----|-------------|----------|----------|
| `tipsType` | [TipsType](#tipstype) | Нет | Да | Тип чаевых |

---

## TipsType

| Поле | Тип | Обязательное | Nullable | Описание |
|------|-----|-------------|----------|----------|
| `id` | string (uuid) | **Да** | Нет | ID типа |
| `name` | string | **Да** | Нет | Название |

---

## DiscountInfoResponse

Скидка в ответе.

| Поле | Тип | Обязательное | Nullable | Описание |
|------|-----|-------------|----------|----------|
| `discountType` | [DiscountType](#discounttype) | **Да** | Нет | Тип скидки |
| `sum` | number (double) | **Да** | Нет | Сумма |
| `selectivePositions` | string[] (uuid) | Нет | Да | Позиции, к которым применена |

---

## DiscountType

| Поле | Тип | Обязательное | Nullable | Описание |
|------|-----|-------------|----------|----------|
| `id` | string (uuid) | **Да** | Нет | ID типа |
| `name` | string | **Да** | Нет | Название |

---

## OrderType

Тип заказа.

| Поле | Тип | Обязательное | Nullable | Описание |
|------|-----|-------------|----------|----------|
| `id` | string (uuid) | **Да** | Нет | ID типа |
| `name` | string | **Да** | Нет | Название |
| `orderServiceType` | enum: `DeliveryByCourier`, `DeliveryByClient` | **Да** | Нет | Вид сервиса |

---

## Conception

Концепция.

| Поле | Тип | Обязательное | Nullable | Описание |
|------|-----|-------------|----------|----------|
| `id` | string (uuid) | **Да** | Нет | ID концепции |
| `name` | string | **Да** | Нет | Название |
| `code` | string | Нет | Да | Код |

---

## LoyaltyInfoResponse

| Поле | Тип | Обязательное | Nullable | Описание |
|------|-----|-------------|----------|----------|
| `coupon` | string | Нет | Да | Купон |
| `appliedManualConditions` | string[] (uuid) | Нет | Да | Применённые ручные условия |

---

## Ответы с ошибками

### 400 — Bad Request

```json
{
  "errorDescription": "string",
  "error": "string"
}
```

### 401 — Unauthorized

Токен авторизации невалиден или истёк.

### 408 — Request Timeout

Превышен таймаут запроса.

### 500 — Server Error

Внутренняя ошибка сервера iiko.

---

## Иерархия объектов (визуальная схема)

### Запрос

```
DeliveryCreateRequest
├── createOrderSettings: CreateOrderSettings
└── order: DeliveryCreateOrder
    ├── deliveryPoint: DeliveryPoint
    │   ├── coordinates: Coordinates
    │   └── address: DeliveryAddress
    │       └── street: Street
    ├── customer: RegularCustomer | OneTimeCustomer
    ├── guests: OrderGuests
    ├── items: OrderItemRequest[]
    │   ├── modifiers: OrderItemModifierRequest[]
    │   └── comboInformation: ComboInformation
    ├── combos: OrderCombo[]
    ├── payments: PaymentRequest[]
    │   └── paymentAdditionalData: PaymentAdditionalData
    ├── tips: TipsPaymentRequest[]
    ├── discountsInfo: DiscountsInfo
    │   ├── card: DiscountCard
    │   └── discounts: DiscountRequest[]
    ├── loyaltyInfo: LoyaltyInfo
    │   └── dynamicDiscounts: DynamicDiscount[]
    ├── chequeAdditionalInfo: ChequeAdditionalInfo
    └── externalData: ExternalData[]
```

### Ответ

```
CreateDeliveryResponse
├── correlationId
└── orderInfo: OrderInfo
    ├── creationStatus: OrderCreationStatus
    ├── errorInfo: ErrorInfo
    └── order: DeliveryOrder
        ├── customer: CustomerResponse
        ├── deliveryPoint: DeliveryPointResponse
        ├── cancelInfo: CancelInfo
        │   └── cancelCause: CancelCause
        ├── courierInfo: CourierInfo
        │   └── courier: Courier
        ├── problem: Problem
        ├── operator: Waiter
        ├── externalCourierService: ExternalCourierService
        ├── guestsInfo: GuestsInfo
        ├── items: OrderItemResponse[]  (Product | Compound | Service)
        │   ├── product: ProductRef
        │   ├── modifiers: OrderItemModifierResponse[]
        │   │   ├── product: ProductRef
        │   │   └── productGroup: ProductGroup
        │   ├── size: ProductSize
        │   ├── deleted: ItemDeletedInfo
        │   │   └── deletionMethod: DeletionMethod
        │   │       └── removalType: RemovalType
        │   ├── comboInformation: ComboItemInformationResponse
        │   ├── primaryComponent: CompoundOrderItemComponent  (Compound only)
        │   ├── secondaryComponent: CompoundOrderItemComponent  (Compound only)
        │   └── template: CompoundItemTemplate  (Compound only)
        ├── combos: ComboResponse[]
        ├── payments: PaymentResponse[]
        │   └── paymentType: PaymentTypeRef
        ├── tips: TipsResponse[]
        │   └── tipsType: TipsType
        ├── discounts: DiscountInfoResponse[]
        │   └── discountType: DiscountType
        ├── conception: Conception
        ├── orderType: OrderType
        └── loyaltyInfo: LoyaltyInfoResponse
```

---

## Источники данных

- **Request Body**: скопировано из [Swagger UI](https://api-ru.iiko.services/docs#tag/Deliveries:-Create-and-update/paths/~1api~11~1deliveries~1create/post)
- **Response DTO**: [codeofsolomon/iiko_cloud](https://github.com/codeofsolomon/iiko_cloud) SDK — `src/Domain/Dto/Responses/CreateDelivery/`
- **Enums**: SDK — `src/Domain/Enums/`
- **Реализация в проекте**: `packages/Webkul/IikoIntegration/src/Services/IikoOrderService.php`
