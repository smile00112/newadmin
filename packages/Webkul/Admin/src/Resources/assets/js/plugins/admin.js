export default {
    install(app) {
        app.config.globalProperties.$admin = {
            /**
             * Generates a formatted price.
             *
             * @param {number} price - The price value to be formatted.
             * @param {boolean} price_from_prefix - The price from prefix.
             * @param {number} price_dops - The prices of product delected dops.
             * @returns {string} - The formatted price string.
             */
            formatPrice: (price, price_from_prefix = false, price_dops = 0, log_data = null) => {
console.log(log_data);
                let locale = document.querySelector('meta[http-equiv="content-language"]').content;

                //если есть цена за допы, прибавляем её к цене товара
                if (price_dops > 0) price+=price_dops;

                locale = locale.replace(/([a-z]{2})_([A-Z]{2})/g, '$1-$2');

                const currency = JSON.parse(document.querySelector('meta[name="currency"]').content);

                const symbol = currency.symbol !== '' ? currency.symbol : currency.code;

                const priceFromPrefix  = price_from_prefix ? 'от ' : '';

                if (! currency.currency_position) {
                    return priceFromPrefix + new Intl.NumberFormat(locale, {
                        style: "currency",
                        currency: currency.code,
                    }).format(price);
                }

                const formatter = new Intl.NumberFormat(locale, {
                    style: 'currency',
                    currency: currency.code,
                    minimumFractionDigits: currency.decimal ?? 2
                });

                const formattedCurrency = formatter.formatToParts(price)
                    .map(part => {
                        console.log(part.type);
                        switch (part.type) {
                            case 'currency':
                                return '';

                            case 'group':
                                return currency.group_separator === ''
                                    ? part.value
                                    : currency.group_separator;

                            case 'decimal':
                                return currency.decimal_separator === ''
                                    ? part.value
                                    : currency.decimal_separator;

                            default:
                                return part.value;
                        }
                    })
                    .join('');


                switch (currency.currency_position) {
                    case 'left':
                        return priceFromPrefix + symbol + formattedCurrency;

                    case 'left_with_space':
                        return priceFromPrefix + symbol + ' ' + formattedCurrency;

                    case 'right':
                        return priceFromPrefix + formattedCurrency + symbol;

                    case 'right_with_space':
                        return priceFromPrefix + formattedCurrency + ' ' + symbol;

                    default:
                        return priceFromPrefix + formattedCurrency;
                }
            },
        };
    },
};
