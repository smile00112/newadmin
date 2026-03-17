# POST /api/2/menu/by_id

**Получение внешнего меню по ID.**

- Источник данных: Web External Menu
- Группа ограничений: `Data: menu`
- Документация: https://api-ru.iiko.services/docs#tag/Menu/paths/~1api~12~1menu~1by_id/post

---

## Заголовки

| Заголовок | Тип | Обязательный | Описание |
|-----------|-----|--------------|----------|
| `Authorization` | string | Да | Bearer-токен. Пример: `Bearer nRzIn0dJu1L...` |
| `Timeout` | integer | Нет | Таймаут в секундах (по умолчанию 15) |

---

## Тело запроса (Request Body)

**Schema:** `MenuRequest`

```json
{
  "externalMenuId": "15#3",
  "organizationIds": ["00000000-0000-0000-0000-000000000000"],
  "priceCategoryId": "00000000-0000-0000-0000-000000000000",
  "version": 2,
  "language": "ru",
  "startRevision": null
}
```

### Поля запроса

| Поле | Тип | Обязательное | Описание |
|------|-----|-------------|----------|
| `externalMenuId` | string | **Да** | ID внешнего меню. Получается через `GET /api/2/menu` |
| `organizationIds` | string[] (uuid) | **Да** | Массив ID организаций. Получается через `GET /api/1/organizations` |
| `priceCategoryId` | string (uuid) | Нет | ID ценовой категории. Получается через `GET /api/2/menu` |
| `version` | integer | Нет | Версия формата ответа: `2`, `3` или `4`. По умолчанию `2` |
| `language` | string | Нет | Язык внешнего меню |
| `startRevision` | integer (int64) | Нет | Начальная ревизия (для инкрементального обновления) |

> `asyncMode` — deprecated, не использовать.

---

## Ответы

### 200 — Успех

Тело ответа — **oneOf**:
- `ExternalMenuV2` (version=2) — см. [external-menu-v2-schema.md](external-menu-v2-schema.md)
- `ExternalMenuV3` (version=3)
- `ExternalMenuV4` (version=4)

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
