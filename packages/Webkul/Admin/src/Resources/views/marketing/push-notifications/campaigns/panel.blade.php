<!DOCTYPE html>
<html lang="ru" class="{{ request()->cookie('dark_mode') === '1' ? 'dark' : '' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ isset($campaign) ? 'Редактировать рассылку' : 'Новая рассылка' }}</title>
    <style>
        body { font-family: 'Inter', sans-serif; background: #f8f9fb; margin: 0; padding: 0; overflow-x: hidden; }
        .field-group { margin-bottom: 16px; }
        .field-label { display: block; font-size: 12px; font-weight: 600; color: #374151; margin-bottom: 5px; }
        .field-label.required::after { content: ' *'; color: #ef4444; }
        .field-input { width: 100%; padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 13px; color: #111827; background: white; box-sizing: border-box; transition: border-color 0.15s; outline: none; }
        .field-input:focus { border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99,102,241,0.1); }
        .field-textarea { min-height: 80px; resize: vertical; font-family: inherit; }
        .field-select { appearance: none; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%236b7280'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'%3E%3C/path%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 10px center; background-size: 16px; padding-right: 32px; }
        .section { background: white; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.06); margin-bottom: 12px; overflow: hidden; }
        .section-header { padding: 14px 18px; border-bottom: 1px solid #f3f4f6; display: flex; align-items: center; justify-content: space-between; }
        .section-title { font-size: 13px; font-weight: 700; color: #111827; }
        .section-body { padding: 16px 18px; }
        .btn-primary { display: inline-flex; align-items: center; gap: 6px; padding: 9px 18px; background: linear-gradient(135deg, #6366f1, #4f46e5); color: white; border: none; border-radius: 10px; font-size: 13px; font-weight: 600; cursor: pointer; transition: all 0.15s; }
        .btn-primary:hover { opacity: 0.9; transform: translateY(-1px); }
        .btn-primary:disabled { opacity: 0.5; cursor: not-allowed; transform: none; }
        .btn-secondary { display: inline-flex; align-items: center; gap: 6px; padding: 9px 18px; background: #f3f4f6; color: #374151; border: none; border-radius: 10px; font-size: 13px; font-weight: 600; cursor: pointer; transition: all 0.15s; }
        .btn-secondary:hover { background: #e5e7eb; }
        .audience-badge { display: inline-flex; align-items: center; gap: 6px; padding: 6px 12px; background: linear-gradient(135deg, #ede9fe, #ddd6fe); border-radius: 8px; font-size: 13px; font-weight: 600; color: #6d28d9; }
        .filter-row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
        .error-msg { font-size: 11px; color: #ef4444; margin-top: 3px; display: block; }
        .preview-box { background: linear-gradient(135deg, #f0f9ff, #e0f2fe); border: 1px solid #bae6fd; border-radius: 10px; padding: 14px 16px; margin-bottom: 16px; }
        .preview-title { font-size: 13px; font-weight: 700; color: #0369a1; margin-bottom: 4px; }
        .preview-body { font-size: 12px; color: #0c4a6e; }
        .tag { display: inline-flex; align-items: center; gap: 4px; padding: 2px 8px; background: #ede9fe; color: #7c3aed; border-radius: 5px; font-size: 11px; font-weight: 600; margin: 2px; }
        .checkbox-group { display: flex; flex-wrap: wrap; gap: 8px; }
        .checkbox-item { display: flex; align-items: center; gap: 6px; padding: 6px 10px; border: 1px solid #e5e7eb; border-radius: 8px; cursor: pointer; font-size: 12px; font-weight: 500; color: #374151; transition: all 0.15s; user-select: none; }
        .checkbox-item:hover { border-color: #6366f1; background: #ede9fe; }
        .checkbox-item.selected { border-color: #6366f1; background: #ede9fe; color: #6d28d9; }
        .search-results { margin-top: 6px; border: 1px solid #e5e7eb; border-radius: 10px; background: #fff; max-height: 220px; overflow: auto; }
        .search-item { padding: 8px 10px; border-bottom: 1px solid #f3f4f6; cursor: pointer; }
        .search-item:last-child { border-bottom: 0; }
        .search-item:hover { background: #f9fafb; }
        .selected-users { margin-top: 8px; display: flex; flex-wrap: wrap; gap: 8px; }
        .selected-user-chip { display: inline-flex; align-items: center; gap: 6px; background: #ede9fe; color: #5b21b6; border: 1px solid #ddd6fe; border-radius: 999px; padding: 4px 10px; font-size: 12px; font-weight: 600; }
        .selected-user-chip button { border: 0; background: transparent; color: #5b21b6; cursor: pointer; font-size: 14px; line-height: 1; padding: 0; }
    </style>
</head>
<body>
<div id="push-campaign-panel" style="padding: 16px 20px 80px;">

    {{-- Preview notification --}}
    <div id="notification-preview" class="preview-box">
        <div style="display:flex;align-items:center;gap:8px;margin-bottom:8px;">
            <div style="width:28px;height:28px;background:linear-gradient(135deg,#6366f1,#4f46e5);border-radius:8px;display:flex;align-items:center;justify-content:center;">
                <svg style="width:14px;height:14px;color:white;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" /></svg>
            </div>
            <span style="font-size:11px;font-weight:600;color:#0369a1;">Предпросмотр уведомления</span>
        </div>
        <div class="preview-title" id="preview-title-text">{{ $campaign->title ?? 'Заголовок уведомления' }}</div>
        <div class="preview-body" id="preview-body-text">{{ $campaign->body ?? 'Текст уведомления появится здесь...' }}</div>
    </div>

    {{-- Main form --}}
    <form id="campaign-form">
        @csrf

        {{-- Section: Basic info --}}
        <div class="section">
            <div class="section-header">
                <span class="section-title">Основные настройки</span>
            </div>
            <div class="section-body">
                <div class="field-group">
                    <label class="field-label required">Название кампании</label>
                    <input type="text" name="name" class="field-input" placeholder="Например: Акция выходного дня"
                        value="{{ $campaign->name ?? '' }}" maxlength="255" required>
                    <span class="error-msg" id="err-name"></span>
                </div>
                <div class="field-group">
                    <label class="field-label required">Заголовок пуша</label>
                    <input type="text" name="title" class="field-input" id="push-title" placeholder="Краткий заголовок до 65 символов"
                        value="{{ $campaign->title ?? '' }}" maxlength="65" required>
                    <span class="error-msg" id="err-title"></span>
                </div>
                <div class="field-group">
                    <label class="field-label required">Текст пуша</label>
                    <textarea name="body" class="field-input field-textarea" id="push-body" placeholder="Основной текст уведомления до 240 символов"
                        maxlength="240" required>{{ $campaign->body ?? '' }}</textarea>
                    <span class="error-msg" id="err-body"></span>
                </div>
                <div class="filter-row">
                    <div class="field-group">
                        <label class="field-label">Изображение (URL)</label>
                        <input type="url" name="image_url" class="field-input" placeholder="https://..."
                            value="{{ $campaign->image_url ?? '' }}" maxlength="512">
                    </div>
                    <div class="field-group">
                        <label class="field-label">Deep link</label>
                        <input type="text" name="deep_link" class="field-input" placeholder="surprise://..."
                            value="{{ $campaign->deep_link ?? '' }}" maxlength="512">
                    </div>
                </div>
            </div>
        </div>

        {{-- Section: Segmentation --}}
        <div class="section">
            <div class="section-header">
                <span class="section-title">Сегментация аудитории</span>
                <div class="audience-badge" id="audience-count-badge">
                    <svg style="width:14px;height:14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                    <span id="audience-count-text">Считаю...</span>
                </div>
            </div>
            <div class="section-body">
                <p style="font-size:12px;color:#6b7280;margin-bottom:12px;">Оставьте фильтры пустыми — уведомление получат все пользователи с активным пуш-токеном.</p>

                <div class="field-group" style="position:relative;">
                    <label class="field-label">Выбрать конкретных клиентов</label>
                    <input
                        id="customer-search-input"
                        type="text"
                        class="field-input"
                        placeholder="Начните вводить имя или номер телефона"
                        autocomplete="off"
                    >
                    <div id="customer-search-results" class="search-results" style="display:none;"></div>
                    <div id="selected-customers" class="selected-users"></div>
                    <div id="selected-customer-inputs"></div>
                    <small style="font-size:11px;color:#6b7280;display:block;margin-top:4px;">Если выбраны клиенты, рассылка уйдет только им (при наличии активного push-токена).</small>
                </div>

                <div class="field-group">
                    <label class="field-label">Телефоны (точечная отправка)</label>
                    <textarea
                        name="segment_filters[phones]"
                        class="field-input field-textarea"
                        style="min-height: 96px;"
                        placeholder="Один номер на строку или через запятую"
                    >{{ isset($campaign) && isset($campaign->segment_filters['phones']) ? implode("\n", $campaign->segment_filters['phones']) : '' }}</textarea>
                    <small style="font-size:11px;color:#6b7280;display:block;margin-top:4px;">Если заполнено, рассылка уйдет только клиентам с этими номерами и активным push-токеном.</small>
                </div>

                {{-- Customer groups --}}
                <div class="field-group">
                    <label class="field-label">Группы клиентов</label>
                    <div class="checkbox-group" id="group-checkboxes">
                        @foreach($customerGroups as $group)
                            @php
                                $selectedGroups = $campaign->segment_filters['customer_group_ids'] ?? [];
                                $isSelected = in_array($group->id, $selectedGroups);
                            @endphp
                            <label class="checkbox-item {{ $isSelected ? 'selected' : '' }}" onclick="toggleGroup(this, {{ $group->id }})">
                                <input type="checkbox" name="segment_filters[customer_group_ids][]" value="{{ $group->id }}"
                                    {{ $isSelected ? 'checked' : '' }} style="display:none;">
                                {{ $group->name }}
                            </label>
                        @endforeach
                    </div>
                </div>

                {{-- Gender --}}
                <div class="field-group">
                    <label class="field-label">Пол</label>
                    <select name="segment_filters[gender]" class="field-input field-select">
                        <option value="">Все</option>
                        <option value="Male" {{ ($campaign->segment_filters['gender'] ?? '') === 'Male' ? 'selected' : '' }}>Мужской</option>
                        <option value="Female" {{ ($campaign->segment_filters['gender'] ?? '') === 'Female' ? 'selected' : '' }}>Женский</option>
                        <option value="Other" {{ ($campaign->segment_filters['gender'] ?? '') === 'Other' ? 'selected' : '' }}>Другой</option>
                    </select>
                </div>

                {{-- Has orders --}}
                <div class="field-group">
                    <label class="field-label">Наличие заказов</label>
                    <select name="segment_filters[has_orders]" class="field-input field-select">
                        <option value="">Все</option>
                        <option value="1" {{ isset($campaign->segment_filters['has_orders']) && $campaign->segment_filters['has_orders'] == '1' ? 'selected' : '' }}>Есть хотя бы 1 заказ</option>
                        <option value="0" {{ isset($campaign->segment_filters['has_orders']) && $campaign->segment_filters['has_orders'] == '0' ? 'selected' : '' }}>Нет заказов</option>
                    </select>
                </div>

                {{-- Registration date --}}
                <div class="filter-row">
                    <div class="field-group">
                        <label class="field-label">Зарегистрирован с</label>
                        <input type="date" name="segment_filters[registered_from]" class="field-input"
                            value="{{ $campaign->segment_filters['registered_from'] ?? '' }}">
                    </div>
                    <div class="field-group">
                        <label class="field-label">Зарегистрирован по</label>
                        <input type="date" name="segment_filters[registered_to]" class="field-input"
                            value="{{ $campaign->segment_filters['registered_to'] ?? '' }}">
                    </div>
                </div>

                {{-- Last order date --}}
                <div class="filter-row">
                    <div class="field-group">
                        <label class="field-label">Последний заказ с</label>
                        <input type="date" name="segment_filters[last_order_from]" class="field-input"
                            value="{{ $campaign->segment_filters['last_order_from'] ?? '' }}">
                    </div>
                    <div class="field-group">
                        <label class="field-label">Последний заказ по</label>
                        <input type="date" name="segment_filters[last_order_to]" class="field-input"
                            value="{{ $campaign->segment_filters['last_order_to'] ?? '' }}">
                    </div>
                </div>
            </div>
        </div>

        {{-- Footer actions --}}
        <div style="position:fixed;bottom:0;left:0;right:0;padding:12px 20px;background:white;border-top:1px solid #e5e7eb;display:flex;gap:8px;z-index:100;">
            <button type="submit" class="btn-primary" id="save-btn">
                <svg style="width:15px;height:15px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                Отправить рассылку
            </button>
            <button type="button" class="btn-secondary" id="cancel-btn">Отмена</button>
        </div>
    </form>
</div>

<script>
const CSRF = document.querySelector('meta[name="csrf-token"]').content;
const IS_EDIT = {{ isset($campaign) ? 'true' : 'false' }};
const CAMPAIGN_ID = {{ $campaign->id ?? 'null' }};
const STORE_URL = '{{ route('admin.marketing.push_notifications.campaigns.store') }}';
const UPDATE_URL = IS_EDIT ? '{{ isset($campaign) ? route('admin.marketing.push_notifications.campaigns.update', $campaign->id ?? 0) : '' }}' : null;
const SEND_URL = IS_EDIT ? '{{ isset($campaign) ? route('admin.marketing.push_notifications.campaigns.send', $campaign->id ?? 0) : '' }}' : null;
const SEND_BASE_URL = '{{ url('admin/marketing/push-notifications/campaigns/send') }}';
const AUDIENCE_URL = '{{ route('admin.marketing.push_notifications.campaigns.audience_count') }}';
const CUSTOMER_SEARCH_URL = '{{ url('admin/marketing/push-notifications/campaigns/customer-search') }}';
const PRESELECTED_CUSTOMERS = @json($selectedCustomers ?? []);

const selectedCustomersMap = new Map();

for (const customer of PRESELECTED_CUSTOMERS) {
    selectedCustomersMap.set(Number(customer.id), customer);
}

function renderSelectedCustomers() {
    const chips = document.getElementById('selected-customers');
    const hidden = document.getElementById('selected-customer-inputs');

    chips.innerHTML = '';
    hidden.innerHTML = '';

    for (const customer of selectedCustomersMap.values()) {
        const chip = document.createElement('span');
        chip.className = 'selected-user-chip';
        chip.innerHTML = `<span>${escapeHtml(customer.name || ('Клиент #' + customer.id))} · ${escapeHtml(customer.phone || 'без телефона')}</span><button type="button" data-id="${customer.id}">×</button>`;
        chips.appendChild(chip);

        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'segment_filters[customer_ids][]';
        input.value = String(customer.id);
        hidden.appendChild(input);
    }
}

function escapeHtml(text) {
    return String(text)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function addSelectedCustomer(customer) {
    selectedCustomersMap.set(Number(customer.id), customer);
    renderSelectedCustomers();
    scheduleAudienceCount();
}

function removeSelectedCustomer(id) {
    selectedCustomersMap.delete(Number(id));
    renderSelectedCustomers();
    scheduleAudienceCount();
}

let customerSearchTimer = null;

function searchCustomers(query) {
    const results = document.getElementById('customer-search-results');

    if (query.length < 2) {
        results.style.display = 'none';
        results.innerHTML = '';
        return;
    }

    fetch(`${CUSTOMER_SEARCH_URL}?q=${encodeURIComponent(query)}`, {
        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF },
    })
    .then(r => r.json())
    .then(data => {
        const items = Array.isArray(data.items) ? data.items : [];
        const filtered = items.filter(item => !selectedCustomersMap.has(Number(item.id)));

        if (!filtered.length) {
            results.innerHTML = '<div class="search-item" style="cursor:default;color:#6b7280;">Совпадений не найдено</div>';
            results.style.display = 'block';
            return;
        }

        results.innerHTML = filtered.map(item => {
            const name = escapeHtml(item.name || ('Клиент #' + item.id));
            const phone = escapeHtml(item.phone || 'без телефона');
            const tokens = Number(item.active_tokens || 0);

            return `<div class="search-item" data-id="${item.id}">
                <div class="customer-name" style="font-size:13px;font-weight:600;color:#111827;">${name}</div>
                <div class="customer-phone" style="font-size:11px;color:#6b7280;">${phone} · активных токенов: ${tokens}</div>
            </div>`;
        }).join('');

        results.style.display = 'block';
    })
    .catch(() => {
        results.innerHTML = '<div class="search-item" style="cursor:default;color:#ef4444;">Ошибка поиска</div>';
        results.style.display = 'block';
    });
}

// Live preview
document.getElementById('push-title').addEventListener('input', function() {
    document.getElementById('preview-title-text').textContent = this.value || 'Заголовок уведомления';
});
document.getElementById('push-body').addEventListener('input', function() {
    document.getElementById('preview-body-text').textContent = this.value || 'Текст уведомления появится здесь...';
});

// Toggle group checkbox
function toggleGroup(label, id) {
    const cb = label.querySelector('input[type=checkbox]');
    cb.checked = !cb.checked;
    label.classList.toggle('selected', cb.checked);
    scheduleAudienceCount();
}

// Audience count
let audienceTimer = null;
function scheduleAudienceCount() {
    clearTimeout(audienceTimer);
    audienceTimer = setTimeout(fetchAudienceCount, 600);
}

function buildFilters() {
    const form = document.getElementById('campaign-form');
    const fd = new FormData(form);
    const filters = {};
    const groupIds = [];
    const customerIds = [];

    for (const [key, value] of fd.entries()) {
        if (!value) continue;
        const m = key.match(/^segment_filters\[([^\]]+)\](?:\[\])?$/);
        if (!m) continue;
        const field = m[1];
        if (field === 'customer_group_ids') {
            groupIds.push(parseInt(value));
        } else if (field === 'customer_ids') {
            customerIds.push(parseInt(value));
        } else {
            filters[field] = value;
        }
    }
    if (groupIds.length) filters.customer_group_ids = groupIds;
    if (customerIds.length) filters.customer_ids = customerIds;
    return filters;
}

function fetchAudienceCount() {
    const badge = document.getElementById('audience-count-text');
    badge.textContent = 'Считаю...';
    const filters = buildFilters();
    fetch(AUDIENCE_URL, {
        method: 'POST',
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': CSRF,
        },
        body: JSON.stringify({ segment_filters: filters }),
    })
    .then(r => r.json())
    .then(data => { badge.textContent = data.count.toLocaleString('ru-RU') + ' получателей'; })
    .catch(() => { badge.textContent = '—'; });
}

// Listen to filter changes
document.getElementById('campaign-form').querySelectorAll('select, input[type=date]').forEach(el => {
    el.addEventListener('change', scheduleAudienceCount);
});

document.getElementById('campaign-form').querySelectorAll('textarea[name="segment_filters[phones]"]').forEach(el => {
    el.addEventListener('input', scheduleAudienceCount);
});

document.getElementById('customer-search-input')?.addEventListener('input', function() {
    clearTimeout(customerSearchTimer);
    customerSearchTimer = setTimeout(() => searchCustomers(this.value.trim()), 250);
});

document.getElementById('customer-search-results')?.addEventListener('click', function(event) {
    const item = event.target.closest('.search-item[data-id]');
    if (!item) return;

    addSelectedCustomer({
        id: Number(item.dataset.id),
        name: item.querySelector('.customer-name')?.textContent || '',
        phone: (item.querySelector('.customer-phone')?.textContent || '').split(' · ')[0],
    });

    this.style.display = 'none';
    this.innerHTML = '';
    document.getElementById('customer-search-input').value = '';
});

document.getElementById('selected-customers')?.addEventListener('click', function(event) {
    const btn = event.target.closest('button[data-id]');
    if (!btn) return;
    removeSelectedCustomer(btn.dataset.id);
});

document.addEventListener('click', function(event) {
    const input = document.getElementById('customer-search-input');
    const results = document.getElementById('customer-search-results');
    if (!input || !results) return;
    if (!input.contains(event.target) && !results.contains(event.target)) {
        results.style.display = 'none';
    }
});

// Submit form
document.getElementById('campaign-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    await saveForm();
});

document.getElementById('cancel-btn').addEventListener('click', function() {
    window.parent.postMessage({ type: 'panel-closed' }, '*');
});

async function saveForm() {
    const btn = document.getElementById('save-btn');
    btn.disabled = true;
    btn.textContent = 'Отправляю...';

    // Clear errors
    document.querySelectorAll('.error-msg').forEach(el => el.textContent = '');

    const form = document.getElementById('campaign-form');
    const fd = new FormData(form);
    const payload = {};
    const segmentFilters = {};
    const groupIds = [];
    const customerIds = [];

    for (const [key, value] of fd.entries()) {
        if (key === '_token') continue;
        const sfMatch = key.match(/^segment_filters\[([^\]]+)\](?:\[\])?$/);
        if (sfMatch) {
            const field = sfMatch[1];
            if (field === 'customer_group_ids') {
                if (value) groupIds.push(parseInt(value));
            } else if (field === 'customer_ids') {
                if (value) customerIds.push(parseInt(value));
            } else if (value) {
                segmentFilters[field] = value;
            }
        } else {
            payload[key] = value;
        }
    }

    if (groupIds.length) segmentFilters.customer_group_ids = groupIds;
    if (customerIds.length) segmentFilters.customer_ids = customerIds;
    if (Object.keys(segmentFilters).length) payload.segment_filters = segmentFilters;

    const url = IS_EDIT ? UPDATE_URL : STORE_URL;
    const method = IS_EDIT ? 'PUT' : 'POST';

    try {
        const resp = await fetch(url, {
            method,
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': CSRF,
            },
            body: JSON.stringify(payload),
        });
        const data = await resp.json();

        if (!resp.ok) {
            if (data.errors) {
                for (const [field, msgs] of Object.entries(data.errors)) {
                    const errEl = document.getElementById(`err-${field}`);
                    if (errEl) errEl.textContent = msgs[0];
                }
            }
            btn.disabled = false;
            btn.textContent = 'Отправить рассылку';
            return;
        }

        const campaignId = IS_EDIT ? CAMPAIGN_ID : data.id;
        const sendUrl = IS_EDIT ? SEND_URL : `${SEND_BASE_URL}/${campaignId}`;

        if (!campaignId || !sendUrl) {
            throw new Error('Не удалось определить кампанию для отправки');
        }

        const sendResp = await fetch(sendUrl, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': CSRF,
                'Content-Type': 'application/json',
            },
        });

        const sendData = await sendResp.json().catch(() => ({}));

        if (!sendResp.ok) {
            throw new Error(sendData.message || 'Не удалось запустить рассылку');
        }

        window.parent.postMessage({ type: 'panel-saved', message: sendData.message || 'Рассылка запущена!' }, '*');
    } catch (err) {
        alert(err?.message || 'Ошибка сети. Попробуйте снова.');
        btn.disabled = false;
        btn.textContent = 'Отправить рассылку';
    }
}

// Initial audience count
renderSelectedCustomers();
fetchAudienceCount();
</script>
</body>
</html>
