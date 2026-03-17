# ExternalMenuV2 — Полная схема ответа

Схема ответа эндпоинта `POST /api/2/menu/by_id` при `version=2`.

---

## Корневой объект: ExternalMenuV2

| Поле | Тип | Обязательное | Nullable | Описание |
|------|-----|-------------|----------|----------|
| `id` | integer | **Да** | Нет | ID внешнего меню |
| `name` | string | Нет | Нет | Название внешнего меню (по умолчанию `""`) |
| `description` | string | Нет | Да | Описание внешнего меню |
| `buttonImageUrl` | string | Нет | Да | Ссылка на изображение |
| `formatVersion` | integer | Нет | Нет | Версия формата меню (по умолчанию `2`) |
| `revision` | integer | Нет | Да | Ревизия меню |
| `itemCategories` | [ExternalMenuCategory](#externalmenuCategory)[] | **Да** | Нет | Категории позиций меню |
| `comboCategories` | [ComboCategoryDto](#combocategorydto)[] | **Да** | Нет | Категории комбо |
| `productCategories` | [ProductCategoryDto](#productcategorydto)[] | Нет | Нет | Категории продуктов |
| `customerTagGroups` | [CustomerTagGroup](#customertaggroup)[] | Нет | Нет | Группы тегов клиентов |
| `intervals` | [IntervalDto](#intervaldto)[] | Нет | Нет | Интервалы доступности меню |

---

## ExternalMenuCategory

Категория позиций в меню.

| Поле | Тип | Обязательное | Nullable | Описание |
|------|-----|-------------|----------|----------|
| `id` | string (uuid) | Нет | Да | ID категории |
| `name` | string | Нет | Нет | Название категории (по умолчанию `""`) |
| `description` | string | Нет | Нет | Описание категории |
| `buttonImageUrl` | string | Нет | Да | Ссылка на изображение кнопки |
| `headerImageUrl` | string | Нет | Да | Ссылка на изображение заголовка |
| `iikoGroupId` | string (uuid) | Нет | Да | ID группы в iiko |
| `items` | [ExternalMenuItem](#externalmenuitem)[] | **Да** | Нет | Позиции меню в категории |
| `scheduleId` | string (uuid) | Нет | Да | GUID расписания категории |
| `scheduleName` | string | Нет | Да | Название расписания категории |
| `schedules` | [PeriodScheduleDto](#periodscheduledto)[] | Нет | Нет | Интервалы расписания категории |
| `isHidden` | boolean | Нет | Нет | Флаг видимости (по умолчанию `false`) |
| `tags` | [TagDto](#tagdto)[] | Нет | Нет | Теги категории |
| `labels` | [LabelDto](#labeldto)[] | Нет | Нет | Лейблы категории |

---

## ExternalMenuItem

Позиция меню (блюдо / комбо).

| Поле | Тип | Обязательное | Nullable | Описание |
|------|-----|-------------|----------|----------|
| `itemId` | string (uuid) | Нет | Нет | ID продукта |
| `sku` | string | Нет | Нет | Код продукта. Пример: `"002345-35cm"` |
| `name` | string | Нет | Нет | Название продукта |
| `description` | string | Нет | Нет | Описание продукта |
| `type` | enum: `DISH`, `COMBO` | Нет | Нет | Тип позиции (по умолчанию `DISH`) |
| `orderItemType` | enum: `Product`, `Compound` | **Да** | Нет | Продукт или составной. Зависит от наличия схемы модификаторов |
| `measureUnit` | string | Нет | Нет | Единица измерения |
| `canBeDivided` | boolean | Нет | Нет | Можно ли делить (по умолчанию `false`) |
| `canSetOpenPrice` | boolean | Нет | Нет | Можно ли задать свободную цену (по умолчанию `false`) |
| `useBalanceForSell` | boolean | Нет | Нет | Использовать баланс для продажи (по умолчанию `false`) |
| `isHidden` | boolean | Нет | Нет | Флаг скрытия (по умолчанию `false`) |
| `isMarked` | boolean | Нет | Нет | Маркировка (по умолчанию `false`) |
| `productCategoryId` | string (uuid) | Нет | Да | GUID категории продукта |
| `modifierSchemaId` | string (uuid) | **Да** | Нет | ID схемы модификаторов |
| `modifierSchemaName` | string | Нет | Да | Название схемы модификаторов |
| `itemSizes` | [ExternalMenuItemSize](#externalmenuitemsize)[] | **Да** | Нет | Размеры позиции |
| `taxCategory` | [TaxCategoryDto](#taxcategorydto)[] | **Да** | Нет | Налоговая категория |
| `allergens` | [AllergenGroupDto](#allergengroupdto)[] | Нет | Нет | Аллергены |
| `tags` | [TagDto](#tagdto)[] | Нет | Нет | Теги |
| `labels` | [LabelDto](#labeldto)[] | Нет | Нет | Лейблы |
| `customerTagGroups` | [SelectedCustomerTag](#selectedcustomertag)[] | Нет | Нет | Группы тегов клиентов |
| `paymentSubject` | string | Нет | Да | Предмет расчёта |
| `paymentSubjectCode` | string | Нет | Да | Код предмета расчёта |
| `outerEanCode` | string | Нет | Да | Внешний EAN-код |
| `barcodes` | [BarcodeDto](#barcodedto)[] | Нет | Да | Штрих-коды |

---

## ExternalMenuItemSize

Размер позиции меню с ценами и модификаторами.

| Поле | Тип | Обязательное | Nullable | Описание |
|------|-----|-------------|----------|----------|
| `sizeId` | string (uuid) | **Да** | Нет | ID размера. Может быть пустым, если размер по умолчанию единственный |
| `sku` | string | Нет | Нет | Уникальный код размера (код продукта + название размера) |
| `sizeCode` | string | Нет | Да | Код размера |
| `sizeName` | string | Нет | Да | Название размера. Пустое, если размер один |
| `isDefault` | boolean | Нет | Нет | Размер по умолчанию. `true` если размер единственный |
| `portionWeightGrams` | float | Нет | Нет | Вес порции в граммах (по умолчанию `0`) |
| `measureUnitType` | string | Нет | Нет | Тип единицы измерения (по умолчанию `"GRAM"`) |
| `isHidden` | boolean | Нет | Нет | Скрыт (по умолчанию `false`) |
| `buttonImageUrl` | string | Нет | Да | Ссылка на изображение |
| `prices` | [ExternalMenuPriceByDepartmentsDto](#externalmenuPricebydepartmentsdto)[] | Нет | Нет | Цены по подразделениям |
| `itemModifierGroups` | [ExternalMenuModifierGroup](#externalmenumodifiergroup)[] | **Да** | Нет | Группы модификаторов |
| `nutritionPerHundredGrams` | [NutritionInfoDto](#nutritioninfodto)[] | **Да** | Нет | КБЖУ на 100 г продукта |
| `nutritions` | [NutritionInfoDto](#nutritioninfodto)[] | Нет | Нет | КБЖУ на 100 г по подразделениям |

---

## ExternalMenuModifierGroup

Группа модификаторов для размера.

| Поле | Тип | Обязательное | Nullable | Описание |
|------|-----|-------------|----------|----------|
| `itemGroupId` | string (uuid) | Нет | Да | ID группы модификаторов |
| `sku` | string | Нет | Нет | Код группы модификаторов |
| `name` | string | Нет | Нет | Название группы |
| `description` | string | Нет | Нет | Описание группы |
| `restrictions` | [ModifierRestrictionsDto](#modifierrestrictionsdto) | Нет | Да | Ограничения группы (мин/макс) |
| `items` | [ExternalMenuModifierItem](#externalmenumodifieritem)[] | Нет | Нет | Модификаторы в группе |
| `canBeDivided` | boolean | Нет | Нет | Можно ли делить (по умолчанию `false`) |
| `isHidden` | boolean | Нет | Нет | Скрыта (по умолчанию `false`) |
| `childModifiersHaveMinMaxRestrictions` | boolean | Нет | Нет | Могут ли дочерние модификаторы иметь собственные ограничения (по умолчанию `false`) |

---

## ExternalMenuModifierItem

Модификатор (добавка / опция).

| Поле | Тип | Обязательное | Nullable | Описание |
|------|-----|-------------|----------|----------|
| `itemId` | string (uuid) | Нет | Нет | ID модификатора |
| `sku` | string | Нет | Нет | Код модификатора |
| `name` | string | Нет | Нет | Название модификатора |
| `description` | string | Нет | Нет | Описание модификатора |
| `portionWeightGrams` | float | Нет | Нет | Вес в граммах (по умолчанию `0`) |
| `measureUnitType` | string | Нет | Нет | Тип единицы измерения (по умолчанию `"GRAM"`, макс. 30 символов) |
| `isHidden` | boolean | Нет | Нет | Скрыт (по умолчанию `false`) |
| `isMarked` | boolean | Нет | Нет | Маркировка (по умолчанию `false`) |
| `position` | integer | Нет | Да | Позиция сортировки |
| `independentQuantity` | boolean | Нет | Нет | Независимое количество (по умолчанию `false`) |
| `productCategoryId` | string | Нет | Да | ID категории продукта |
| `buttonImageUrl` | string | Нет | Да | Ссылка на изображение |
| `restrictions` | [ModifierRestrictionsDto](#modifierrestrictionsdto)[] | Нет | Да | Ограничения модификатора |
| `prices` | [ExternalMenuPriceByDepartmentsDto](#externalmenuPricebydepartmentsdto)[] | Нет | Нет | Цены по подразделениям |
| `allergenGroups` | [AllergenGroupDto](#allergengroupdto)[] | Нет | Нет | Аллергены |
| `nutritionPerHundredGrams` | [NutritionInfoDto](#nutritioninfodto)[] | Нет | Да | КБЖУ на 100 г |
| `tags` | [TagDto](#tagdto)[] | Нет | Нет | Теги |
| `labels` | [LabelDto](#labeldto)[] | Нет | Нет | Лейблы |
| `customerTagGroups` | [SelectedCustomerTag](#selectedcustomertag)[] | Нет | Нет | Группы тегов клиентов |
| `paymentSubject` | string | Нет | Да | Предмет расчёта |
| `paymentSubjectCode` | string | Нет | Да | Код предмета расчёта |
| `outerEanCode` | string | Нет | Да | Внешний EAN-код |
| `barcodes` | [BarcodeDto](#barcodedto)[] | Нет | Да | Штрих-коды |

---

## ModifierRestrictionsDto

Ограничения по количеству модификатора (используется как для группы, так и для отдельного модификатора).

| Поле | Тип | Обязательное | Nullable | Описание |
|------|-----|-------------|----------|----------|
| `minQuantity` | integer | Нет | Нет | Минимальное количество (по умолчанию `0`) |
| `maxQuantity` | integer | Нет | Нет | Максимальное количество (по умолчанию `1`) |
| `freeQuantity` | integer | Нет | Нет | Бесплатное количество (по умолчанию `0`) |
| `byDefault` | integer | Нет | Нет | Количество по умолчанию (по умолчанию `0`) |
| `hideIfDefaultQuantity` | boolean | Нет | Нет | Скрывать, если количество по умолчанию (по умолчанию `false`) |

---

## ExternalMenuPriceByDepartmentsDto

Цена продукта/модификатора по подразделениям.

| Поле | Тип | Обязательное | Nullable | Описание |
|------|-----|-------------|----------|----------|
| `price` | float | **Да** | Нет | Цена. `null` означает «не для продажи». Цена принадлежит ценовой категории из запроса |
| `organizations` | string[] (uuid) | Нет | Нет | Список GUID организаций |
| `taxCategoryId` | string (uuid) | Нет | Да | ID налоговой категории |

---

## NutritionInfoDto

Пищевая ценность на 100 г.

| Поле | Тип | Обязательное | Nullable | Описание |
|------|-----|-------------|----------|----------|
| `fats` | float | Нет | Нет | Жиры (по умолчанию `0`) |
| `proteins` | float | Нет | Нет | Белки (по умолчанию `0`) |
| `carbs` | float | Нет | Нет | Углеводы (по умолчанию `0`) |
| `energy` | float | Нет | Нет | Калории (по умолчанию `0`) |
| `saturatedFattyAcid` | float | Нет | Да | Насыщенные жирные кислоты |
| `salt` | float | Нет | Да | Соль |
| `sugar` | float | Нет | Да | Сахар |
| `organizations` | string[] (uuid) | Нет | Нет | Список GUID организаций (для `nutritions` — группировка по подразделениям) |

---

## ComboCategoryDto

Категория комбо-наборов.

| Поле | Тип | Обязательное | Nullable | Описание |
|------|-----|-------------|----------|----------|
| `id` | string (uuid) | **Да** | Нет | ID категории (может быть null) |
| `name` | string | Нет | Да | Название категории |
| `combos` | [ComboDto](#combodto)[] | Нет | Нет | Комбо в категории |

---

## ComboDto

Комбо-набор.

| Поле | Тип | Обязательное | Nullable | Описание |
|------|-----|-------------|----------|----------|
| `id` | string (uuid) | **Да** | Нет | ID комбо |
| `name` | string | **Да** | Нет | Название комбо |
| `description` | string | Нет | Нет | Описание комбо |
| `price` | float | Нет | Нет | Цена комбо (если стратегия `FIXED`), по умолчанию `0` |
| `priceStrategy` | enum: `BY_COMPONENT`, `FIXED`, `CALCULATE` | **Да** | Нет | Стратегия ценообразования |
| `startDate` | string (datetime) | **Да** | Нет | Дата начала действия. Пример: `"2018-01-01T00:00:00+00:00"` |
| `expirationDate` | string (datetime) | **Да** | Нет | Дата окончания действия |
| `image` | [ButtonImageDto](#buttonimagedto)[]  | **Да** | Нет | Изображения комбо |
| `groups` | [ComboGroupDto](#combogroupdto)[] | Нет | Нет | Группы позиций в комбо |
| `sizes` | [ComboSizeDto](#combosizedto)[] | Нет | Нет | Доступные размеры комбо (может быть пустым) |

---

## ComboGroupDto

Группа позиций в комбо.

| Поле | Тип | Обязательное | Nullable | Описание |
|------|-----|-------------|----------|----------|
| `id` | string (uuid) | **Да** | Нет | ID группы |
| `name` | string | **Да** | Нет | Название группы |
| `isMainGroup` | boolean | **Да** | Нет | Является ли основной группой. Основная группа содержит блюда, вокруг которых строится комбо. При добавлении основного блюда система может предложить «собрать комбо» |
| `skipStep` | boolean | Нет | Нет | Пропустить шаг (по умолчанию `false`) |
| `items` | [ComboGroupItemDto](#combogroupitemdto)[] | Нет | Нет | Позиции в группе |

---

## ComboGroupItemDto

Позиция в группе комбо.

| Поле | Тип | Обязательное | Nullable | Описание |
|------|-----|-------------|----------|----------|
| `itemId` | string (uuid) | **Да** | Нет | ID позиции |
| `sizeId` | string (uuid) | Нет | Да | ID размера позиции |
| `priceModificationAmount` | float | Нет | Да | Доплата/скидка к цене |
| `forbiddenModifiers` | string[] | Нет | Нет | ID запрещённых модификаторов |
| `sizes` | [ComboGroupItemSizeDto](#combogroupitemsizedto)[] | Нет | Нет | Размеры позиции в комбо |

---

## ComboGroupItemSizeDto

Размер позиции в комбо-группе.

| Поле | Тип | Обязательное | Nullable | Описание |
|------|-----|-------------|----------|----------|
| `comboSizeId` | string (uuid) | **Да** | Нет | ID размера комбо, к которому привязан размер позиции |
| `sizeId` | string (uuid) | Нет | Да | ID размера позиции |
| `name` | string | Нет | Нет | Название размера |
| `shortName` | string | Нет | Нет | Короткое название размера |
| `prices` | [ExternalMenuPriceByDepartmentsDto](#externalmenuPricebydepartmentsdto)[] | Нет | Нет | Цены |

---

## ComboSizeDto

Размер комбо.

| Поле | Тип | Обязательное | Nullable | Описание |
|------|-----|-------------|----------|----------|
| `id` | string (uuid) | **Да** | Нет | ID размера |
| `name` | string | **Да** | Нет | Название размера |
| `shortName` | string | **Да** | Нет | Короткое название |
| `buttonImage` | [ButtonImageDto](#buttonimagedto) | Нет | Да | Изображение размера |

---

## ButtonImageDto

Изображение.

| Поле | Тип | Обязательное | Nullable | Описание |
|------|-----|-------------|----------|----------|
| `url` | string | Нет | Да | URL изображения |
| `hash` | string | Нет | Да | Хеш изображения |

---

## ProductCategoryDto

Категория продукта.

| Поле | Тип | Обязательное | Nullable | Описание |
|------|-----|-------------|----------|----------|
| `id` | string | **Да** | Нет | ID категории |
| `name` | string | **Да** | Нет | Название категории |
| `isDeleted` | boolean | Нет | Нет | Удалена (по умолчанию `false`) |

---

## CustomerTagGroup

Группа тегов клиентов.

| Поле | Тип | Обязательное | Nullable | Описание |
|------|-----|-------------|----------|----------|
| `id` | string | **Да** | Нет | ID группы |
| `name` | string | **Да** | Нет | Название группы |
| `selectSeveralTags` | boolean | Нет | Нет | Можно выбрать несколько тегов (по умолчанию `false`) |
| `items` | [CustomerTagItem](#customertagitem)[] | Нет | Нет | Теги в группе |

---

## CustomerTagItem

Тег клиента.

| Поле | Тип | Обязательное | Nullable | Описание |
|------|-----|-------------|----------|----------|
| `id` | string | **Да** | Нет | ID тега |
| `name` | string | **Да** | Нет | Название тега |

---

## SelectedCustomerTag

Выбранный тег клиента (привязка к позиции/модификатору).

| Поле | Тип | Обязательное | Nullable | Описание |
|------|-----|-------------|----------|----------|
| `customerTagGroupId` | string (uuid) | **Да** | Нет | GUID группы тегов |
| `selectedTagIds` | string[] | Нет | Нет | Список выбранных ID тегов |

---

## IntervalDto

Интервал доступности меню.

| Поле | Тип | Обязательное | Nullable | Описание |
|------|-----|-------------|----------|----------|
| `organizationId` | string (uuid) | **Да** | Нет | GUID организации |
| `fromTime` | string | **Да** | Нет | Время начала. Пример: `"09:00"` |
| `toTime` | string | **Да** | Нет | Время окончания. Пример: `"23:00"` |

---

## PeriodScheduleDto

Расписание доступности.

| Поле | Тип | Обязательное | Nullable | Описание |
|------|-----|-------------|----------|----------|
| `begin` | string | **Да** | Нет | Время начала. Пример: `"09:00"` |
| `end` | string | **Да** | Нет | Время окончания. Пример: `"23:00"` |
| `daysOfWeek` | enum[]  | **Да** | Нет | Дни недели: `monday`, `tuesday`, `wednesday`, `thursday`, `friday`, `saturday`, `sunday` |

---

## TaxCategoryDto

Налоговая категория.

| Поле | Тип | Обязательное | Nullable | Описание |
|------|-----|-------------|----------|----------|
| `id` | string | **Да** | Нет | ID категории |
| `name` | string | **Да** | Нет | Название категории |
| `percentage` | float | Нет | Нет | Процент налога (по умолчанию `0`) |

---

## AllergenGroupDto

Аллерген.

| Поле | Тип | Обязательное | Nullable | Описание |
|------|-----|-------------|----------|----------|
| `id` | string (uuid) | Нет | Нет | ID аллергена |
| `code` | string | Нет | Нет | Код аллергена |
| `name` | string | Нет | Нет | Название аллергена |
| `isDeleted` | boolean | Нет | Нет | Удалён (по умолчанию `false`) |

---

## TagDto

Тег.

| Поле | Тип | Обязательное | Nullable | Описание |
|------|-----|-------------|----------|----------|
| `id` | string (uuid) | Нет | Нет | GUID тега |
| `name` | string | Нет | Нет | Название тега |

---

## LabelDto

Лейбл (метка категории: «Веган», «Алкоголь», «Острое» и т.д.).

| Поле | Тип | Обязательное | Nullable | Описание |
|------|-----|-------------|----------|----------|
| `code` | string | Нет | Нет | Код лейбла |
| `name` | string | Нет | Нет | Название лейбла |

---

## BarcodeDto

Штрих-код.

| Поле | Тип | Обязательное | Nullable | Описание |
|------|-----|-------------|----------|----------|
| `barcode` | string | **Да** | Нет | Штрих-код |
| `container` | string | Нет | Да | Контейнер |

---

## Иерархия объектов (визуальная схема)

```
ExternalMenuV2
├── productCategories: ProductCategoryDto[]
├── customerTagGroups: CustomerTagGroup[]
│   └── items: CustomerTagItem[]
├── intervals: IntervalDto[]
├── itemCategories: ExternalMenuCategory[]
│   ├── tags: TagDto[]
│   ├── labels: LabelDto[]
│   ├── schedules: PeriodScheduleDto[]
│   └── items: ExternalMenuItem[]
│       ├── allergens: AllergenGroupDto[]
│       ├── tags: TagDto[]
│       ├── labels: LabelDto[]
│       ├── taxCategory: TaxCategoryDto[]
│       ├── customerTagGroups: SelectedCustomerTag[]
│       ├── barcodes: BarcodeDto[]
│       └── itemSizes: ExternalMenuItemSize[]
│           ├── prices: ExternalMenuPriceByDepartmentsDto[]
│           ├── nutritionPerHundredGrams: NutritionInfoDto[]
│           ├── nutritions: NutritionInfoDto[]
│           └── itemModifierGroups: ExternalMenuModifierGroup[]
│               ├── restrictions: ModifierRestrictionsDto
│               └── items: ExternalMenuModifierItem[]
│                   ├── restrictions: ModifierRestrictionsDto[]
│                   ├── prices: ExternalMenuPriceByDepartmentsDto[]
│                   ├── allergenGroups: AllergenGroupDto[]
│                   ├── nutritionPerHundredGrams: NutritionInfoDto[]
│                   ├── tags: TagDto[]
│                   ├── labels: LabelDto[]
│                   ├── customerTagGroups: SelectedCustomerTag[]
│                   └── barcodes: BarcodeDto[]
└── comboCategories: ComboCategoryDto[]
    └── combos: ComboDto[]
        ├── image: ButtonImageDto[]
        ├── sizes: ComboSizeDto[]
        │   └── buttonImage: ButtonImageDto
        └── groups: ComboGroupDto[]
            └── items: ComboGroupItemDto[]
                └── sizes: ComboGroupItemSizeDto[]
                    └── prices: ExternalMenuPriceByDepartmentsDto[]
```
