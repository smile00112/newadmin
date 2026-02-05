/**
 * Глобальные переменные, предоставляемые WordPress и WooCommerce.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-i18n/
 * @see https://github.com/woocommerce/woocommerce-blocks/blob/trunk/docs/third-party-developers/extensibility/payment-method-integration.md
 *
 * @property {object} wc.wcBlocksRegistry          - Реестр для регистрации блоков WooCommerce.
 * @property {object} wp.element                   - Библиотека React (createElement, Fragment).
 * @property {object} wp.i18n                      - Функции для локализации (__, _x, _n, _nx).
 * @property {object} wp.htmlEntities              - Функции для работы с HTML-сущностями.
 * @property {object} window.tochka_payments_settings - Объект с настройками, переданный из PHP.
 */

/* global wc, wp, tochka_payments_settings */
const { registerPaymentMethod } = wc.wcBlocksRegistry;
const { createElement, Fragment } = wp.element;
const { __ } = wp.i18n;
const { decodeEntities } = wp.htmlEntities;

const settings = window.tochka_payments_settings || {};
const paymentMethodName = settings.name || 'tochka_payments';

/**
 * Компонент для отображения иконки платежного метода.
 * Он рендерит HTML-строку, полученную из PHP.
 */
const Icon = () => {
    if (settings.icon) {
        return createElement('div', {
            dangerouslySetInnerHTML: { __html: settings.icon },
            style: { marginRight: '10px' },
        });
    }
    return null;
};

/**
 * Компонент для заголовка, включающий иконку и текст.
 */
const Label = () => {
    const title = decodeEntities(settings.title || __('Точка Банк', 'tochka-bank-internet-acquiring'));
    return createElement(
        Fragment,
        null,
        createElement(Icon, null),
        createElement('span', null, title)
    );
};

/**
 * Компонент для описания под заголовком.
 */
const Content = () => {
    return decodeEntities(settings.description || '');
};

const TochkaPaymentMethod = {
    name: paymentMethodName,
    label: createElement(Label, null),
    content: createElement(Content, null),
    edit: createElement(Content, null),
    canMakePayment: () => true,
    ariaLabel: decodeEntities(settings.title || ''),
    supports: {
        features: settings.supports || [],
    },
};

registerPaymentMethod(TochkaPaymentMethod);