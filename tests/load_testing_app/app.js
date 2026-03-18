// =============================================================================
// API Load Tester — app.js
// =============================================================================

(function () {
    'use strict';

    // -------------------------------------------------------------------------
    // DOM refs
    // -------------------------------------------------------------------------
    const $ = (sel) => document.querySelector(sel);
    const dom = {
        baseUrl:          $('#baseUrl'),
        phones:           $('#phones'),
        verifyCode:       $('#verifyCode'),
        btnAuth:          $('#btnAuth'),
        authLog:          $('#authLog'),
        tokenList:        $('#tokenList'),
        btnClearTokens:   $('#btnClearTokens'),
        streamCount:      $('#streamCount'),
        streamCountDisp:  $('#streamCountDisplay'),
        thinkDelayMin:    $('#thinkDelayMin'),
        thinkDelayMax:    $('#thinkDelayMax'),
        includeCheckout:  $('#includeCheckout'),
        btnStart:         $('#btnStart'),
        btnStop:          $('#btnStop'),
        statusBadge:      $('#statusBadge'),
        logBody:          $('#logBody'),
        btnClearLog:      $('#btnClearLog'),
        statTotal:        $('#statTotal'),
        statAvg:          $('#statAvg'),
        statMin:          $('#statMin'),
        statMax:          $('#statMax'),
        statErrors:       $('#statErrors'),
        statRps:          $('#statRps'),
        breakdownBody:    $('#breakdownBody'),
    };

    // -------------------------------------------------------------------------
    // Constants
    // -------------------------------------------------------------------------
    const STORAGE_TOKENS   = 'loadtest_tokens';
    const STORAGE_BASE_URL = 'loadtest_base_url';
    const DEVICE_NAME      = 'load-tester';
    const REQUEST_TIMEOUT  = 30000;
    const MAX_LOG_ENTRIES  = 500;

    const COUNTRY_CODES = {
        RU: '7', US: '1', UK: '44', DE: '49', FR: '33',
        IT: '39', ES: '34', UA: '380', BY: '375', KZ: '7',
    };

    // -------------------------------------------------------------------------
    // State
    // -------------------------------------------------------------------------
    let runner = null;
    let stats = createEmptyStats();
    let statsInterval = null;
    let productIds = [];
    let logEntryCount = 0;

    // -------------------------------------------------------------------------
    // Stats helpers
    // -------------------------------------------------------------------------
    function createEmptyStats() {
        return {
            total: 0,
            errors: 0,
            durations: [],
            startTime: null,
            endpoints: {},  // key: 'METHOD /path' -> { count, errors, durations[] }
        };
    }

    function recordRequest(method, path, duration, isError) {
        stats.total++;
        if (isError) stats.errors++;
        if (duration != null) stats.durations.push(duration);

        const key = `${method} ${path}`;
        if (!stats.endpoints[key]) {
            stats.endpoints[key] = { method, path, count: 0, errors: 0, durations: [] };
        }
        const ep = stats.endpoints[key];
        ep.count++;
        if (isError) ep.errors++;
        if (duration != null) ep.durations.push(duration);
    }

    function computeStats() {
        const d = stats.durations;
        const total = stats.total;
        const avg = d.length ? Math.round(d.reduce((a, b) => a + b, 0) / d.length) : 0;
        const min = d.length ? Math.round(Math.min(...d)) : null;
        const max = d.length ? Math.round(Math.max(...d)) : 0;
        const elapsed = stats.startTime ? (Date.now() - stats.startTime) / 1000 : 0;
        const rps = elapsed > 0 ? (total / elapsed).toFixed(1) : '0';
        return { total, avg, min, max, errors: stats.errors, rps };
    }

    function updateStatsUI() {
        const s = computeStats();
        dom.statTotal.textContent  = s.total;
        dom.statAvg.textContent    = s.avg;
        dom.statMin.textContent    = s.min != null ? s.min : '—';
        dom.statMax.textContent    = s.max;
        dom.statErrors.textContent = s.errors;
        dom.statRps.textContent    = s.rps;

        // Color avg
        dom.statAvg.className = 'stat-value ' + durationColor(s.avg);
        dom.statMin.className = 'stat-value ' + (s.min != null ? durationColor(s.min) : 'green');
        dom.statMax.className = 'stat-value ' + durationColor(s.max);

        updateBreakdownTable();
    }

    function durationColor(ms) {
        if (ms < 500) return 'green';
        if (ms < 2000) return 'yellow';
        return 'red';
    }

    function updateBreakdownTable() {
        const entries = Object.values(stats.endpoints)
            .sort((a, b) => b.count - a.count);

        dom.breakdownBody.innerHTML = entries.map(ep => {
            const avg = ep.durations.length
                ? Math.round(ep.durations.reduce((a, b) => a + b, 0) / ep.durations.length)
                : '—';
            const min = ep.durations.length ? Math.round(Math.min(...ep.durations)) : '—';
            const max = ep.durations.length ? Math.round(Math.max(...ep.durations)) : '—';
            return `<tr>
                <td><span class="log-method ${ep.method}">${ep.method}</span></td>
                <td>${escapeHtml(ep.path)}</td>
                <td>${ep.count}</td>
                <td>${avg}</td>
                <td>${min}</td>
                <td>${max}</td>
                <td style="color:${ep.errors ? 'var(--red)' : 'var(--text-dim)'}">${ep.errors}</td>
            </tr>`;
        }).join('');
    }

    // -------------------------------------------------------------------------
    // Logging
    // -------------------------------------------------------------------------
    function addLogEntry(streamId, method, url, status, duration) {
        if (logEntryCount === 0) {
            dom.logBody.innerHTML = '';
        }
        logEntryCount++;

        const time = new Date().toLocaleTimeString('ru-RU', { hour12: false });
        const shortUrl = url.replace(/^https?:\/\/[^/]+/, '');
        const statusClass = status >= 500 ? 's5xx' : status >= 400 ? 's4xx' : status >= 200 ? 's2xx' : 'err';
        const durClass = duration == null ? 'error'
            : duration < 500 ? 'fast'
            : duration < 2000 ? 'medium' : 'slow';
        const durText = duration != null ? `${Math.round(duration)}ms` : 'ERR';

        const el = document.createElement('div');
        el.className = 'log-entry';
        el.innerHTML = `
            <span class="log-time">${time}</span>
            <span class="log-stream stream-${streamId}">${streamId}</span>
            <span class="log-method ${method}">${method}</span>
            <span class="log-url" title="${escapeHtml(shortUrl)}">${escapeHtml(shortUrl)}</span>
            <span class="log-status ${statusClass}">${status || 'ERR'}</span>
            <span class="log-duration ${durClass}">${durText}</span>
        `;

        dom.logBody.appendChild(el);

        // Trim old entries
        while (dom.logBody.children.length > MAX_LOG_ENTRIES) {
            dom.logBody.removeChild(dom.logBody.firstChild);
        }

        // Auto-scroll
        dom.logBody.scrollTop = dom.logBody.scrollHeight;
    }

    function addAuthLog(text, type = 'info') {
        const el = document.createElement('div');
        el.className = `auth-log-entry ${type}`;
        el.textContent = text;
        dom.authLog.appendChild(el);
        dom.authLog.scrollTop = dom.authLog.scrollHeight;
    }

    // -------------------------------------------------------------------------
    // Token management (localStorage)
    // -------------------------------------------------------------------------
    function getTokens() {
        try {
            return JSON.parse(localStorage.getItem(STORAGE_TOKENS)) || [];
        } catch {
            return [];
        }
    }

    function saveTokens(tokens) {
        localStorage.setItem(STORAGE_TOKENS, JSON.stringify(tokens));
        renderTokenList();
    }

    function addToken(phone, token) {
        const tokens = getTokens();
        const idx = tokens.findIndex(t => t.phone === phone);
        if (idx >= 0) {
            tokens[idx].token = token;
        } else {
            tokens.push({ phone, token });
        }
        saveTokens(tokens);
    }

    function clearTokens() {
        localStorage.removeItem(STORAGE_TOKENS);
        renderTokenList();
    }

    function renderTokenList() {
        const tokens = getTokens();
        if (tokens.length === 0) {
            dom.tokenList.innerHTML = '<li class="token-empty">Нет сохранённых токенов</li>';
            return;
        }
        dom.tokenList.innerHTML = tokens.map(t => {
            const preview = t.token.substring(0, 16) + '…';
            return `<li class="token-item">
                <span class="phone">${escapeHtml(t.phone)}</span>
                <span class="token-preview">${escapeHtml(preview)}</span>
            </li>`;
        }).join('');
    }

    // -------------------------------------------------------------------------
    // API helper
    // -------------------------------------------------------------------------
    async function apiRequest(method, url, body, token, signal) {
        const headers = {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'x-locale': 'ru',
        };
        if (token) {
            headers['Authorization'] = `Bearer ${token}`;
        }

        const opts = { method, headers, signal };
        if (body && method !== 'GET') {
            opts.body = JSON.stringify(body);
        }

        const t0 = performance.now();
        let response, duration, status, data;
        try {
            const timeoutId = setTimeout(() => {
                if (signal && !signal.aborted) {
                    // The AbortController will handle cancellation
                }
            }, REQUEST_TIMEOUT);

            response = await fetch(url, opts);
            duration = performance.now() - t0;
            clearTimeout(timeoutId);

            status = response.status;
            try {
                data = await response.json();
            } catch {
                data = null;
            }
        } catch (err) {
            duration = performance.now() - t0;
            if (err.name === 'AbortError') throw err;
            return { ok: false, status: 0, data: null, duration, error: err.message };
        }

        return { ok: response.ok, status, data, duration };
    }

    // -------------------------------------------------------------------------
    // Auth flow
    // -------------------------------------------------------------------------
    function parsePhones() {
        const lines = dom.phones.value.trim().split('\n').filter(Boolean);
        return lines.map(line => {
            const parts = line.trim().split(':');
            if (parts.length === 2) {
                const countryKey = parts[0].trim().toUpperCase();
                const phone = parts[1].trim();
                const code = COUNTRY_CODES[countryKey] || countryKey;
                return { countryCode: code, phone, raw: line.trim() };
            }
            // Assume RU if no prefix
            return { countryCode: '7', phone: line.trim(), raw: line.trim() };
        });
    }

    async function authenticateAll() {
        const baseUrl = dom.baseUrl.value.trim().replace(/\/+$/, '');
        if (!baseUrl) {
            addAuthLog('Укажите API Base URL', 'error');
            return;
        }

        const phones = parsePhones();
        if (phones.length === 0) {
            addAuthLog('Укажите хотя бы один телефон', 'error');
            return;
        }

        const code = dom.verifyCode.value.trim() || '123456';

        dom.btnAuth.disabled = true;
        dom.authLog.innerHTML = '';

        for (const p of phones) {
            addAuthLog(`Авторизация ${p.raw}…`, 'info');

            // Step 1: Initiate
            try {
                const initRes = await apiRequest('POST', `${baseUrl}/customer/auth/sms/initiate`, {
                    phone_number: p.phone,
                    country_code: p.countryCode,
                    device_name: DEVICE_NAME,
                }, null, null);

                if (!initRes.ok) {
                    addAuthLog(`Ошибка initiate (${initRes.status}): ${JSON.stringify(initRes.data)}`, 'error');
                    continue;
                }

                const verificationToken = initRes.data?.verification_token || initRes.data?.data?.verification_token;
                if (!verificationToken) {
                    addAuthLog(`Нет verification_token в ответе`, 'error');
                    continue;
                }

                addAuthLog(`Получен verification_token, верификация…`, 'info');

                // Step 2: Verify
                const verifyRes = await apiRequest('POST', `${baseUrl}/customer/auth/verify`, {
                    verification_token: verificationToken,
                    verification_code: code,
                    device_name: DEVICE_NAME,
                }, null, null);

                if (!verifyRes.ok) {
                    addAuthLog(`Ошибка verify (${verifyRes.status}): ${JSON.stringify(verifyRes.data)}`, 'error');
                    continue;
                }

                const token = verifyRes.data?.token;
                if (!token) {
                    addAuthLog(`Нет token в ответе: ${JSON.stringify(verifyRes.data)}`, 'error');
                    continue;
                }

                addToken(p.raw, token);
                addAuthLog(`✓ ${p.raw} — авторизован`, 'success');
            } catch (err) {
                addAuthLog(`Ошибка: ${err.message}`, 'error');
            }
        }

        dom.btnAuth.disabled = false;
    }

    // -------------------------------------------------------------------------
    // Scenarios
    // -------------------------------------------------------------------------
    function getBaseUrl() {
        return dom.baseUrl.value.trim().replace(/\/+$/, '');
    }

    function randomItem(arr) {
        return arr[Math.floor(Math.random() * arr.length)];
    }

    function randomInt(min, max) {
        return Math.floor(Math.random() * (max - min + 1)) + min;
    }

    async function fetchProductIds(baseUrl, token, signal) {
        const res = await apiRequest('GET', `${baseUrl}/nomenclature`, null, token, signal);
        if (res.ok && res.data?.data) {
            const data = res.data.data;
            // Try to extract product IDs from nomenclature response
            const ids = [];
            if (Array.isArray(data)) {
                // Could be array of categories with products
                for (const item of data) {
                    if (item.id) ids.push(item.id);
                    if (item.products && Array.isArray(item.products)) {
                        for (const p of item.products) {
                            if (p.id) ids.push(p.id);
                        }
                    }
                }
            } else if (data.products && Array.isArray(data.products)) {
                for (const p of data.products) {
                    if (p.id) ids.push(p.id);
                }
            } else if (typeof data === 'object') {
                // Walk nested structure
                const walk = (obj) => {
                    if (!obj || typeof obj !== 'object') return;
                    if (obj.id && (obj.sku || obj.type || obj.price !== undefined)) {
                        ids.push(obj.id);
                    }
                    for (const val of Object.values(obj)) {
                        if (Array.isArray(val)) val.forEach(walk);
                        else if (typeof val === 'object') walk(val);
                    }
                };
                walk(data);
            }
            return ids.length > 0 ? ids : [1, 2, 3, 4, 5];
        }
        return [1, 2, 3, 4, 5]; // fallback
    }

    function buildScenarios(includeCheckout) {
        const scenarios = [
            scenarioBrowseAndCart,
            scenarioProfileAndCatalog,
            scenarioCartManagement,
            scenarioActiveUserPolling,
        ];
        if (includeCheckout) {
            scenarios.push(scenarioCheckout);
        }
        return scenarios;
    }

    // Scenario A: Browse & Add to Cart
    async function scenarioBrowseAndCart(ctx) {
        const { baseUrl, token, signal, streamId } = ctx;
        const pId = randomItem(productIds);

        await execStep(ctx, 'GET', `${baseUrl}/nomenclature`);
        await execStep(ctx, 'GET', `${baseUrl}/categories`);
        await execStep(ctx, 'GET', `${baseUrl}/products/${pId}`);
        await execStep(ctx, 'POST', `${baseUrl}/customer/cart/add/${pId}`, {
            product_id: pId,
            quantity: randomInt(1, 3),
        });
        await execStep(ctx, 'GET', `${baseUrl}/customer/cart`);
    }

    // Scenario B: Checkout
    async function scenarioCheckout(ctx) {
        const { baseUrl } = ctx;
        const pId = randomItem(productIds);

        await execStep(ctx, 'GET', `${baseUrl}/customer/cart`);
        // Clear cart first
        await execStep(ctx, 'DELETE', `${baseUrl}/customer/cart/remove`);
        await execStep(ctx, 'POST', `${baseUrl}/customer/cart/add/${pId}`, {
            product_id: pId,
            quantity: 1,
        });
        await execStep(ctx, 'POST', `${baseUrl}/checkout/bonus/auto-apply`);
        await execStep(ctx, 'POST', `${baseUrl}/customer/checkout/save-address`, {
            billing: {
                first_name: 'Тест',
                last_name: 'Тестович',
                email: 'test@loadtest.local',
                address: ['ул. Тестовая, д. 1'],
                city: 'Москва',
                country: 'RU',
                state: '',
                postcode: '101000',
                phone: '79991234567',
                use_for_shipping: true,
            },
        });
        await execStep(ctx, 'POST', `${baseUrl}/customer/checkout/save-shipping`, {
            shipping_method: 'flatrate_flatrate',
        });
        await execStep(ctx, 'POST', `${baseUrl}/customer/checkout/save-payment`, {
            payment: { method: 'cashondelivery' },
        });
        await execStep(ctx, 'POST', `${baseUrl}/customer/checkout/save-order`);
    }

    // Scenario C: Profile & Catalog
    async function scenarioProfileAndCatalog(ctx) {
        const { baseUrl } = ctx;
        const pId = randomItem(productIds);

        await execStep(ctx, 'GET', `${baseUrl}/customer/get`);
        await execStep(ctx, 'GET', `${baseUrl}/customer/bonuses`);
        await execStep(ctx, 'GET', `${baseUrl}/customer/saved-cards`);
        await execStep(ctx, 'GET', `${baseUrl}/customer/active-orders`);
        await execStep(ctx, 'GET', `${baseUrl}/customer/completed-orders`);
        await execStep(ctx, 'GET', `${baseUrl}/nomenclature`);
        await execStep(ctx, 'GET', `${baseUrl}/catalog?sort=id`);
        await execStep(ctx, 'GET', `${baseUrl}/products/${pId}`);
    }

    // Scenario D: Cart Management
    async function scenarioCartManagement(ctx) {
        const { baseUrl } = ctx;
        const pId = randomItem(productIds);

        await execStep(ctx, 'GET', `${baseUrl}/customer/cart`);
        await execStep(ctx, 'DELETE', `${baseUrl}/customer/cart/remove`);
        await execStep(ctx, 'POST', `${baseUrl}/customer/cart/add/${pId}`, {
            product_id: pId,
            quantity: 2,
        });

        // Get cart to find item IDs for update/remove
        const cartRes = await execStep(ctx, 'GET', `${baseUrl}/customer/cart`);
        const cartItems = cartRes?.data?.data?.items || [];
        if (cartItems.length > 0) {
            const qtyUpdate = {};
            for (const item of cartItems) {
                qtyUpdate[item.id] = randomInt(1, 5);
            }
            await execStep(ctx, 'PUT', `${baseUrl}/customer/cart/update`, { qty: qtyUpdate });
            await execStep(ctx, 'DELETE', `${baseUrl}/customer/cart/remove/${cartItems[0].id}`);
        }
    }

    // Scenario E: Active User Polling
    async function scenarioActiveUserPolling(ctx) {
        const { baseUrl } = ctx;

        await execStep(ctx, 'GET', `${baseUrl}/customer/get`);
        await execStep(ctx, 'GET', `${baseUrl}/customer/active-orders`);
        await execStep(ctx, 'GET', `${baseUrl}/customer/bonuses`);
        await execStep(ctx, 'GET', `${baseUrl}/nomenclature`);
        await execStep(ctx, 'GET', `${baseUrl}/customer/saved-cards`);
    }

    // Execute a single step
    async function execStep(ctx, method, url, body) {
        const { token, signal, streamId } = ctx;

        if (signal.aborted) throw new DOMException('Aborted', 'AbortError');

        const res = await apiRequest(method, url, body || null, token, signal);
        const path = url.replace(ctx.baseUrl, '');

        addLogEntry(streamId, method, url, res.status, res.duration);
        recordRequest(method, path, res.duration, !res.ok);

        return res;
    }

    // -------------------------------------------------------------------------
    // Load Runner
    // -------------------------------------------------------------------------
    class LoadRunner {
        constructor(baseUrl, tokens, streamCount, scenarios, thinkDelayMin, thinkDelayMax) {
            this.baseUrl = baseUrl;
            this.tokens = tokens;
            this.streamCount = streamCount;
            this.scenarios = scenarios;
            this.thinkDelayMin = thinkDelayMin;
            this.thinkDelayMax = thinkDelayMax;
            this.abortController = new AbortController();
            this.workers = [];
        }

        async start() {
            const signal = this.abortController.signal;

            // Pre-fetch product IDs using first token
            try {
                productIds = await fetchProductIds(this.baseUrl, this.tokens[0].token, signal);
            } catch (e) {
                if (e.name === 'AbortError') return;
                productIds = [1, 2, 3, 4, 5];
            }

            for (let i = 1; i <= this.streamCount; i++) {
                this.workers.push(this.runWorker(i, signal));
            }

            await Promise.allSettled(this.workers);
        }

        async runWorker(streamId, signal) {
            const tokenEntry = this.tokens[(streamId - 1) % this.tokens.length];
            const ctx = {
                baseUrl: this.baseUrl,
                token: tokenEntry.token,
                signal,
                streamId,
            };

            while (!signal.aborted) {
                const scenario = randomItem(this.scenarios);
                try {
                    await scenario(ctx);
                } catch (err) {
                    if (err.name === 'AbortError') break;
                    // Log error but continue
                    addLogEntry(streamId, '???', 'Scenario error: ' + err.message, 0, null);
                }

                // Think delay
                if (!signal.aborted) {
                    const delay = randomInt(this.thinkDelayMin, this.thinkDelayMax);
                    await sleep(delay, signal);
                }
            }
        }

        stop() {
            this.abortController.abort();
        }
    }

    function sleep(ms, signal) {
        return new Promise((resolve, reject) => {
            if (signal?.aborted) { reject(new DOMException('Aborted', 'AbortError')); return; }
            const timer = setTimeout(resolve, ms);
            signal?.addEventListener('abort', () => {
                clearTimeout(timer);
                reject(new DOMException('Aborted', 'AbortError'));
            }, { once: true });
        });
    }

    // -------------------------------------------------------------------------
    // UI Controls
    // -------------------------------------------------------------------------
    function setRunningState(running) {
        dom.btnStart.disabled = running;
        dom.btnStop.disabled = !running;
        dom.btnAuth.disabled = running;
        dom.statusBadge.textContent = running ? 'Работает' : 'Остановлен';
        dom.statusBadge.className = 'status-badge ' + (running ? 'running' : 'stopped');
    }

    async function startTest() {
        const baseUrl = getBaseUrl();
        if (!baseUrl) {
            addAuthLog('Укажите API Base URL', 'error');
            return;
        }

        const tokens = getTokens();
        if (tokens.length === 0) {
            addAuthLog('Нет авторизованных токенов. Сначала авторизуйтесь.', 'error');
            return;
        }

        const streamCount = parseInt(dom.streamCount.value, 10);
        const thinkMin = parseInt(dom.thinkDelayMin.value, 10) || 1000;
        const thinkMax = parseInt(dom.thinkDelayMax.value, 10) || 3000;
        const includeCheckout = dom.includeCheckout.checked;
        const scenarios = buildScenarios(includeCheckout);

        // Reset stats
        stats = createEmptyStats();
        stats.startTime = Date.now();
        logEntryCount = 0;
        dom.logBody.innerHTML = '';

        setRunningState(true);

        // Start stats updater
        statsInterval = setInterval(updateStatsUI, 1000);

        runner = new LoadRunner(baseUrl, tokens, streamCount, scenarios, thinkMin, thinkMax);

        try {
            await runner.start();
        } catch {
            // runner finished
        }

        clearInterval(statsInterval);
        updateStatsUI(); // final update
        setRunningState(false);
        runner = null;
    }

    function stopTest() {
        if (runner) {
            runner.stop();
        }
    }

    function clearLog() {
        dom.logBody.innerHTML = '<div class="log-empty">Лог очищен</div>';
        logEntryCount = 0;
    }

    // -------------------------------------------------------------------------
    // Persistence helpers
    // -------------------------------------------------------------------------
    function saveBaseUrl() {
        localStorage.setItem(STORAGE_BASE_URL, dom.baseUrl.value.trim());
    }

    function loadBaseUrl() {
        const saved = localStorage.getItem(STORAGE_BASE_URL);
        if (saved) dom.baseUrl.value = saved;
    }

    // -------------------------------------------------------------------------
    // Utilities
    // -------------------------------------------------------------------------
    function escapeHtml(str) {
        const div = document.createElement('div');
        div.appendChild(document.createTextNode(str));
        return div.innerHTML;
    }

    // -------------------------------------------------------------------------
    // Events
    // -------------------------------------------------------------------------
    dom.btnAuth.addEventListener('click', authenticateAll);
    dom.btnClearTokens.addEventListener('click', clearTokens);
    dom.btnStart.addEventListener('click', startTest);
    dom.btnStop.addEventListener('click', stopTest);
    dom.btnClearLog.addEventListener('click', clearLog);

    dom.streamCount.addEventListener('input', () => {
        dom.streamCountDisp.textContent = dom.streamCount.value;
    });

    dom.baseUrl.addEventListener('change', saveBaseUrl);

    // Init
    loadBaseUrl();
    renderTokenList();
})();
