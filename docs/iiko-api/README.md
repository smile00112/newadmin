# iiko Cloud API — Справочник спецификаций

Источник: https://api-ru.iiko.services/docs

## Содержание

| Файл | Описание |
|------|----------|
| [menu-by-id.md](menu-by-id.md) | Эндпоинт `POST /api/2/menu/by_id` — запрос и ответ |
| [external-menu-v2-schema.md](external-menu-v2-schema.md) | Полная схема ответа `ExternalMenuV2` со всеми вложенными объектами |
| [deliveries-create.md](deliveries-create.md) | Эндпоинт `POST /api/1/deliveries/create` — создание заказа на доставку (запрос + ответ, все вложенные объекты) |

## Версии формата ответа

Эндпоинт `/api/2/menu/by_id` возвращает **одну из трёх версий** в зависимости от поля `version` в запросе:

| version | Схема ответа |
|---------|-------------|
| 2 (по умолчанию) | `ExternalMenuV2` — описана в этих файлах |
| 3 | `ExternalMenuV3` |
| 4 | `ExternalMenuV4` |

> В данной документации подробно описана только версия 2, как наиболее часто используемая.
