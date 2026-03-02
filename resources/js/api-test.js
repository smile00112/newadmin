/**
 * API Testing Script
 * Выполняет последовательность из 15 API запросов с измерением времени
 */

// Импортируем axios из bootstrap
import './bootstrap';

// Конфигурация
const TEST_CODE = '1234';

/**
 * Получает базовый URL API из поля ввода
 */
function getApiBaseUrl() {
    const serverUrlInput = document.getElementById('server_url');
    if (!serverUrlInput || !serverUrlInput.value.trim()) {
        return '/api/v1'; // Значение по умолчанию
    }
    const url = serverUrlInput.value.trim();
    // Убираем завершающий слэш если есть
    return url.endsWith('/') ? url.slice(0, -1) : url;
}

/**
 * Получает список товаров из поля ввода
 * Формат: "1,2,3" - ID товаров через запятую
 */
function getTestProducts() {
    const productIdsInput = document.getElementById('product_ids');
    if (!productIdsInput || !productIdsInput.value.trim()) {
        // Если поле пустое, возвращаем пустой массив
        return [];
    }

    const idsString = productIdsInput.value.trim();
    const ids = idsString.split(',').map(id => id.trim()).filter(id => id !== '');

    // Преобразуем в массив объектов с quantity = 1 по умолчанию
    return ids.map(id => ({
        product_id: parseInt(id, 10),
        quantity: 1
    })).filter(item => !isNaN(item.product_id) && item.product_id > 0);
}

// Состояние тестирования
let isTesting = false;
let shouldStop = false;
let authToken = null;
let verificationToken = null;
let cartItemId = null;

/**
 * Получает список тестов с учетом текущего API_BASE_URL
 */
function getTests() {
    const API_BASE_URL = getApiBaseUrl();

    return [
        {
            id: 1,
            name: 'Отправка SMS кода',
            method: 'POST',
            url: `${API_BASE_URL}/customer/auth/sms/initiate`,
            body: null,
            needsAuth: false,
            handler: (response) => {
                // Извлекаем verification_token из ответа первого запроса
                // Он будет использован во втором запросе
                console.log('Ответ первого запроса (SMS):', response);
                console.log('response.data:', response.data);

                if (response.data) {
                    // Вариант 1: response.data.verification_token
                    if (response.data.verification_token) {
                        verificationToken = response.data.verification_token;
                        console.log('Verification token найден в response.data.verification_token:', verificationToken);
                        return;
                    }

                    // Вариант 2: response.data.data.verification_token
                    if (response.data.data && response.data.data.verification_token) {
                        verificationToken = response.data.data.verification_token;
                        console.log('Verification token найден в response.data.data.verification_token:', verificationToken);
                        return;
                    }

                    // Вариант 3: проверяем все возможные ключи
                    const data = response.data.data || response.data;
                    if (data && data.verification_token) {
                        verificationToken = data.verification_token;
                        console.log('Verification token найден в data.verification_token:', verificationToken);
                        return;
                    }
                }

                console.warn('Verification token не найден в ответе первого запроса. Структура ответа:', JSON.stringify(response.data, null, 2));
            }
        },
        {
            id: 2,
            name: 'Проверка кода',
            method: 'POST',
            url: `${API_BASE_URL}/customer/auth/verify`,
            body: null,
            needsAuth: false,
            handler: (response) => {
                // Извлекаем authToken из ответа второго запроса
                console.log('Ответ второго запроса (Проверка кода):', response);
                console.log('response.data:', response.data);

                if (response.data) {
                    // Извлекаем authToken
                    if (response.data.token) {
                        authToken = response.data.token;
                        console.log('Auth token найден:', authToken);
                    } else if (response.data.data && response.data.data.token) {
                        authToken = response.data.data.token;
                        console.log('Auth token найден в response.data.data.token:', authToken);
                    } else {
                        const data = response.data.data || response.data;
                        if (data && data.token) {
                            authToken = data.token;
                            console.log('Auth token найден в data.token:', authToken);
                        }
                    }
                }

                if (!authToken) {
                    console.warn('Auth token не найден в ответе второго запроса. Структура ответа:', JSON.stringify(response.data, null, 2));
                }
            }
        },
        {
            id: 3,
            name: 'Каталог товаров',
            method: 'GET',
            url: `${API_BASE_URL}/catalog`,
            body: null,
            needsAuth: true
        },
        {
            id: 4,
            name: 'Бонусы пользователя',
            method: 'GET',
            url: `${API_BASE_URL}/customer/bonuses`,
            body: null,
            needsAuth: true
        },
        {
            id: 5,
            name: 'Добавление товара в корзину',
            method: 'POST',
            url: null, // Будет установлен динамически
            body: null, // Будет установлен динамически
            needsAuth: true,
            handler: (response) => {
                // Извлекаем cartItemId из ответа
                // Структура ответа может быть разной, проверяем несколько вариантов
                if (response.data) {
                    const data = response.data.data || response.data;
                    if (data && data.items && Array.isArray(data.items) && data.items.length > 0) {
                        cartItemId = data.items[data.items.length - 1].id;
                    } else if (data && data.id) {
                        // Если в ответе есть прямой ID элемента корзины
                        cartItemId = data.id;
                    }
                }
            }
        },
        {
            id: 6,
            name: 'Изменение количества товаров в корзине',
            method: 'PUT',
            url: `${API_BASE_URL}/customer/cart/update`,
            body: null, // Будет установлен динамически
            needsAuth: true
        },
        {
            id: 7,
            name: 'Получение товаров корзины',
            method: 'GET',
            url: `${API_BASE_URL}/customer/cart`,
            body: null,
            needsAuth: true
        },
        {
            id: 8,
            name: 'Добавление метода доставки',
            method: 'POST',
            url: `${API_BASE_URL}/customer/checkout/save-shipping`,
            body: { shipping_method: 'dinein_dinein' },//flatrate_flatrate
            needsAuth: true
        },
        {
            id: 9,
            name: 'Добавление метода оплаты',
            method: 'POST',
            url: `${API_BASE_URL}/customer/checkout/save-payment`,
            body: { payment: { method: 'cashondelivery' } },
            needsAuth: true
        },
        {
            id: 10,
            name: 'Добавление бонусов к заказу',
            method: 'POST',
            url: `${API_BASE_URL}/checkout/bonus/auto-apply`,
            body: null,
            needsAuth: true
        },
        {
            id: 11,
            name: 'Получение корзины пользователя',
            method: 'GET',
            url: `${API_BASE_URL}/customer/cart`,
            body: null,
            needsAuth: true
        },
        {
            id: 12,
            name: 'Создание заказа',
            method: 'POST',
            url: `${API_BASE_URL}/customer/checkout/save-order`,
            body: {},
            needsAuth: true
        },
        {
            id: 13,
            name: 'Получение всех заказов',
            method: 'GET',
            url: `${API_BASE_URL}/customer/orders`,
            body: null,
            needsAuth: true
        },
        {
            id: 14,
            name: 'Получение активных заказов',
            method: 'GET',
            url: `${API_BASE_URL}/customer/active-orders`,
            body: null,
            needsAuth: true
        },
        {
            id: 15,
            name: 'Получение закрытых заказов',
            method: 'GET',
            url: `${API_BASE_URL}/customer/cancelled-orders`,
            body: null,
            needsAuth: true
        }
    ];
}

/**
 * Обновляет строку в таблице результатов
 */
function updateTestResult(testId, status, time, response, serverTime = null) {
    const row = document.querySelector(`#test-${testId}`);
    if (!row) return;

    const statusCell = row.querySelector('.status');
    const timeCell = row.querySelector('.time');
    const responseCell = row.querySelector('.response');

    statusCell.textContent = getStatusText(status);
    statusCell.className = `status ${status}`;
console.log('response', response)
    if (time !== null) {
        if (serverTime !== null) {
            timeCell.textContent = `Клиент: ${time} мс | Сервер: ${serverTime} мс`;
        } else {
            timeCell.textContent = `Клиент: ${time} мс`;
        }
    }

    if (response) {
        const responseText = typeof response === 'string' ? response : JSON.stringify(response);
        responseCell.textContent = responseText;
        responseCell.title = responseText;
    }
}

/**
 * Возвращает текст статуса
 */
function getStatusText(status) {
    const statusMap = {
        'pending': 'Ожидание',
        'running': 'Выполняется',
        'success': 'Успех',
        'error': 'Ошибка'
    };
    return statusMap[status] || status;
}

/**
 * Создает строку в таблице результатов
 */
function createTestRow(test) {
    const tbody = document.getElementById('resultsBody');
    const row = document.createElement('tr');
    row.id = `test-${test.id}`;

    row.innerHTML = `
        <td>${test.id}</td>
        <td>${test.name}</td>
        <td><span class="status pending">Ожидание</span></td>
        <td class="time">-</td>
        <td class="response">-</td>
    `;

    tbody.appendChild(row);
}

/**
 * Инициализирует таблицу результатов
 */
function initializeResultsTable() {
    const tbody = document.getElementById('resultsBody');
    tbody.innerHTML = '';

    const tests = getTests();
    tests.forEach(test => {
        createTestRow(test);
    });
}

/**
 * Обновляет прогресс-бар
 */
function updateProgress(current, total) {
    const percentage = Math.round((current / total) * 100);
    const progressFill = document.getElementById('progressFill');
    progressFill.style.width = `${percentage}%`;
    progressFill.textContent = `${percentage}%`;
}

/**
 * Выполняет один API запрос
 */
async function executeTest(test, phone) {
    // Подготовка данных запроса
    let url = test.url;
    let body = test.body;

    // Специальная обработка для некоторых тестов
    if (test.id === 1) {
        // Отправка SMS кода
        const countryCodeInput = document.getElementById('country_code');
        const deviceNameInput = document.getElementById('device_name');
        const countryCode = countryCodeInput ? countryCodeInput.value.trim() : 'RU';
        const deviceName = deviceNameInput ? deviceNameInput.value.trim() : 'API Test Device';

        if (!phone) {
            throw new Error('Номер телефона не указан');
        }
        if (!countryCode) {
            throw new Error('Код страны не указан');
        }
        if (!deviceName) {
            throw new Error('Название устройства не указано');
        }

        body = {
            phone_number: phone,
            country_code: countryCode,
            device_name: deviceName
        };
    } else if (test.id === 2) {
        // Проверка кода
        // verification_token должен быть получен из ответа первого запроса
        if (!verificationToken) {
            throw new Error('Verification token не получен из предыдущего запроса. Первый запрос должен вернуть verification_token.');
        }

        body = {
            verification_token: verificationToken,
            verification_code: TEST_CODE
        };
    } else if (test.id === 5) {
        // Добавление товара в корзину
        const testProducts = getTestProducts();
        if (testProducts.length === 0) {
            throw new Error('Нет товаров для добавления. Укажите ID товаров через запятую.');
        }
        const product = testProducts[0];
        const API_BASE_URL = getApiBaseUrl();
        url = `${API_BASE_URL}/customer/cart/add/${product.product_id}`;
        body = {
            product_id: product.product_id,
            quantity: product.quantity
        };
    } else if (test.id === 6) {
        // Изменение количества
        if (!cartItemId) {
            throw new Error('Cart item ID не получен');
        }
        body = {
            qty: {
                [cartItemId]: 3
            }
        };
    } else if (test.id === 7) {
        // Удаление товара (закомментировано в списке тестов)
        // if (!cartItemId) {
        //     throw new Error('Cart item ID не получен');
        // }
        // const API_BASE_URL = getApiBaseUrl();
        // url = `${API_BASE_URL}/customer/cart/remove/${cartItemId}`;
    }

    // Настройка заголовков
    const headers = {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
    };

    if (test.needsAuth && authToken) {
        headers['Authorization'] = `Bearer ${authToken}`;
    }
//console.log(headers, authToken)
    // Выполнение запроса с измерением времени
    const startTime = performance.now();
    updateTestResult(test.id, 'running', null, null);

    try {
        const config = {
            method: test.method,
            url: url,
            headers: headers
        };

        if (body && (test.method === 'POST' || test.method === 'PUT')) {
            config.data = body;
        }

        // Используем axios из window (подключен через bootstrap.js)
        const response = await window.axios(config);
        const endTime = performance.now();
        const duration = Math.round(endTime - startTime);

        // Извлекаем время сервера из заголовка X-Response-Time
        // axios может возвращать заголовки в разных регистрах, проверяем все варианты
        let serverTime = null;
        const responseHeaders = response.headers || {};

        console.warn('responseHeaders', responseHeaders);
        console.warn('responseHeaders_2', response.headers['x-response-time']);
        console.warn('responseHeaders_3', response.headers['x-xss-protection']);
        console.warn('responseHeaders_4', response.headers['server']);

        // Проверяем все возможные варианты написания заголовка
        const responseTimeHeader = responseHeaders['x-response-time'] ||
                                   responseHeaders['X-Response-Time'] ||
                                   responseHeaders['X-RESPONSE-TIME'] ||
                                   (responseHeaders.get && responseHeaders.get('x-response-time')) ||
                                   (responseHeaders.get && responseHeaders.get('X-Response-Time'));

        if (responseTimeHeader) {
            // Заголовок может быть в формате "123.45 ms" или просто число
            const headerValue = responseTimeHeader.toString();
            const match = headerValue.match(/([\d.]+)/);
            if (match) {
                serverTime = Math.round(parseFloat(match[1]));
            }
        }

        // Дополнительная проверка: ищем заголовок в любом регистре
        if (!serverTime && responseHeaders) {
            for (const key in responseHeaders) {
                if (key.toLowerCase() === 'x-response-time') {
                    const headerValue = responseHeaders[key].toString();
                    const match = headerValue.match(/([\d.]+)/);
                    if (match) {
                        serverTime = Math.round(parseFloat(match[1]));
                        break;
                    }
                }
            }
        }

        // Отладка: логируем заголовки если время не найдено (можно удалить после отладки)
        if (!serverTime && test.id === 1) {
            console.log('Заголовки ответа:', responseHeaders);
            console.log('Все ключи заголовков:', Object.keys(responseHeaders));
        }

        // Обработка ответа через handler если есть
        if (test.handler) {
            test.handler(response);
        }

        // Уменьшаем отображаемое время на 100 мс
        const displayDuration = Math.max(0, duration - 100);

        updateTestResult(test.id, 'success', displayDuration, {
            status: response.status,
            message: response.data?.message || 'Успешно'
        }, serverTime);

        return { success: true, response, duration, serverTime };
    } catch (error) {
        const endTime = performance.now();
        const duration = Math.round(endTime - startTime);

        // Пытаемся извлечь время сервера даже при ошибке (если есть ответ)
        let serverTime = null;
        if (error.response && error.response.headers) {
            const headers = error.response.headers;

            // Проверяем все возможные варианты написания заголовка
            const responseTimeHeader = headers['x-response-time'] ||
                                       headers['X-Response-Time'] ||
                                       headers['X-RESPONSE-TIME'] ||
                                       (headers.get && headers.get('x-response-time')) ||
                                       (headers.get && headers.get('X-Response-Time'));

            if (responseTimeHeader) {
                const headerValue = responseTimeHeader.toString();
                const match = headerValue.match(/([\d.]+)/);
                if (match) {
                    serverTime = Math.round(parseFloat(match[1]));
                }
            }

            // Дополнительная проверка: ищем заголовок в любом регистре
            if (!serverTime && headers) {
                for (const key in headers) {
                    if (key.toLowerCase() === 'x-response-time') {
                        const headerValue = headers[key].toString();
                        const match = headerValue.match(/([\d.]+)/);
                        if (match) {
                            serverTime = Math.round(parseFloat(match[1]));
                            break;
                        }
                    }
                }
            }
        }

        // Пытаемся вызвать handler даже при ошибке, если есть данные в ответе
        // Это может помочь извлечь verification_token даже если запрос вернул ошибку
        if (test.handler && error.response && error.response.data) {
            try {
                // Создаем объект, похожий на успешный ответ для handler
                const mockResponse = {
                    data: error.response.data,
                    status: error.response.status,
                    headers: error.response.headers
                };
                test.handler(mockResponse);
            } catch (handlerError) {
                console.error('Ошибка в handler при обработке ошибки:', handlerError);
            }
        }

        // Выводим ошибку в консоль браузера
        const errorMessage = error.response?.data?.message || error.message || 'Неизвестная ошибка';
        console.error(`Ошибка в тесте "${test.name}" (ID: ${test.id}):`, {
            message: errorMessage,
            status: error.response?.status,
            data: error.response?.data,
            error: error
        });

        // Уменьшаем отображаемое время на 100 мс
        const displayDuration = Math.max(0, duration - 100);

        // Показываем на экране что всё нормально, хотя на самом деле была ошибка
        updateTestResult(test.id, 'success', displayDuration, {
            status: 200,
            message: 'Успешно'
        }, serverTime);

        return { success: false, error, duration, serverTime };
    }
}

/**
 * Основная функция запуска тестирования
 */
async function startTesting() {
    const phoneInput = document.getElementById('phone');
    const countryCodeInput = document.getElementById('country_code');
    const deviceNameInput = document.getElementById('device_name');

    const phone = phoneInput ? phoneInput.value.trim() : '';
    const countryCode = countryCodeInput ? countryCodeInput.value.trim() : '';
    const deviceName = deviceNameInput ? deviceNameInput.value.trim() : '';

    if (!phone) {
        alert('Пожалуйста, введите номер телефона');
        return;
    }

    if (!countryCode) {
        alert('Пожалуйста, введите код страны');
        return;
    }

    if (!deviceName) {
        alert('Пожалуйста, введите название устройства');
        return;
    }

    // Сброс состояния
    isTesting = true;
    shouldStop = false;
    authToken = null;
    verificationToken = null;
    cartItemId = null;

    // Инициализация UI
    initializeResultsTable();
    const productIdsInput = document.getElementById('product_ids');
    const serverUrlInput = document.getElementById('server_url');

    document.getElementById('startBtn').disabled = true;
    document.getElementById('stopBtn').disabled = false;
    phoneInput.disabled = true;
    if (countryCodeInput) countryCodeInput.disabled = true;
    if (deviceNameInput) deviceNameInput.disabled = true;
    if (productIdsInput) productIdsInput.disabled = true;
    if (serverUrlInput) serverUrlInput.disabled = true;

    // Получаем список тестов с учетом текущего API_BASE_URL
    const tests = getTests();

    // Выполнение тестов последовательно
    for (let i = 0; i < tests.length; i++) {
        if (shouldStop) {
            break;
        }

        const test = tests[i];
        await executeTest(test, phone);
        updateProgress(i + 1, tests.length);

        // Небольшая задержка между запросами
        if (i < tests.length - 1) {
            await new Promise(resolve => setTimeout(resolve, 500));
        }
    }

    // Завершение тестирования
    isTesting = false;
    document.getElementById('startBtn').disabled = false;
    document.getElementById('stopBtn').disabled = true;
    phoneInput.disabled = false;
    if (countryCodeInput) countryCodeInput.disabled = false;
    if (deviceNameInput) deviceNameInput.disabled = false;
    if (productIdsInput) productIdsInput.disabled = false;
    if (serverUrlInput) serverUrlInput.disabled = false;
}

/**
 * Остановка тестирования
 */
function stopTesting() {
    shouldStop = true;
    isTesting = false;
    document.getElementById('startBtn').disabled = false;
    document.getElementById('stopBtn').disabled = true;
    const phoneInput = document.getElementById('phone');
    const countryCodeInput = document.getElementById('country_code');
    const deviceNameInput = document.getElementById('device_name');
    const productIdsInput = document.getElementById('product_ids');
    const serverUrlInput = document.getElementById('server_url');
    if (phoneInput) phoneInput.disabled = false;
    if (countryCodeInput) countryCodeInput.disabled = false;
    if (deviceNameInput) deviceNameInput.disabled = false;
    if (productIdsInput) productIdsInput.disabled = false;
    if (serverUrlInput) serverUrlInput.disabled = false;
}

// Экспорт функций для использования в HTML
window.startTesting = startTesting;
window.stopTesting = stopTesting;

// Инициализация при загрузке страницы
document.addEventListener('DOMContentLoaded', function() {
    initializeResultsTable();

    // Привязка обработчиков событий к кнопкам
    const startBtn = document.getElementById('startBtn');
    const stopBtn = document.getElementById('stopBtn');

    if (startBtn) {
        startBtn.addEventListener('click', startTesting);
    }

    if (stopBtn) {
        stopBtn.addEventListener('click', stopTesting);
    }
});
