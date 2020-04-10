(function ($) {
    window.fntQEPP =  window.fntQEPP || {};
    window.fntQEPP.Numeral = (function(){
        var module = {},
            formatCurrencyPattern = null;
        module.defaultFormat = {};
        module.init = function(){
            var currentCurrencyFormat = {};
            currentCurrencyFormat.thousandSep  = currency_format.currency_format_thousand_sep;
            currentCurrencyFormat.decimalSep  = currency_format.currency_format_decimal_sep;
            currentCurrencyFormat.currencySymbol  = currency_format.currency_format_symbol;
            currentCurrencyFormat.currencySingular = ' ';
            currentCurrencyFormat.currencyPlural = ' ';
            currentCurrencyFormat.numberOfDec = currency_format.currency_format_num_decimals;
            currentCurrencyFormat.currencyPosition = currency_format.currency_position;
            //console.log(currentCurrencyFormat);
            initialize(currentCurrencyFormat);
            return module;
        };

        var generateFormatCurrencyPattern = function(){
            var integerPart = '0';
            var thousandPart = '0';
            var decimalPart = '';
            for(i = 1; i <= module.defaultFormat.numberOfDec; i++){
                decimalPart += '0';
            }
            var rawFormattedPrice = integerPart
                                    + module.defaultFormat.thousandSep
                                    + thousandPart
                                    + module.defaultFormat.decimalSep
                                    + decimalPart;
            var resultFormattedPattern = '$' + rawFormattedPrice;
            switch (module.defaultFormat.currencyPosition){
                case 'left':
                default:
                    break;
                case 'left_space':
                    resultFormattedPattern = '$' + ' ' + rawFormattedPrice;
                    break;
                case 'right':
                    resultFormattedPattern = rawFormattedPrice + '$';
                    break;
                case 'right_space':
                    resultFormattedPattern = rawFormattedPrice + ' ' + '$';
                    break;
            }
            return resultFormattedPattern;
        };

        var initialize = function (format_configuration) {
            module.defaultFormat.thousandSep  = ',';
            module.defaultFormat.decimalSep  = '.';
            module.defaultFormat.currencySymbol  = '$';
            module.defaultFormat.currencySingular = 'dollar';
            module.defaultFormat.currencyPlural = 'dollars';
            module.defaultFormat.numberOfDec = 2;
            module.defaultFormat.currencyPosition = 'left';

            if(format_configuration){
                $.extend(module.defaultFormat, format_configuration);
            }
        };

        module.getFormattedPriceByCurrency = function (priceValue) {
            if(formatCurrencyPattern == null){
                formatCurrencyPattern = generateFormatCurrencyPattern();
            }
            return numeral(priceValue).format(formatCurrencyPattern,roundingHandler);
        };

        var roundingHandler = function(value){
            return value.toFixed(module.defaultFormat.numberOfDec);
        };

        return module.init();
    })();
})(jQuery);

/*!
 * numeral.js language configuration
 * language : french (fr)
 * author : Adam Draper : https://github.com/adamwdraper
 */
(function () {
    var language = {
        delimiters: {
            thousands: window.fntQEPP.Numeral.defaultFormat.thousandSep,
            decimal: window.fntQEPP.Numeral.defaultFormat.decimalSep
        },
        abbreviations: {
            thousand: 'k',
            million: 'm',
            billion: 'b',
            trillion: 't'
        },
        ordinal : function (number) {
            return number === 1 ? window.fntQEPP.Numeral.defaultFormat.currencyPlural : window.fntQEPP.Numeral.defaultFormat.currencySingular;
        },
        currency: {
            symbol: window.fntQEPP.Numeral.defaultFormat.currencySymbol
        }
    };

    // Node
    if (typeof module !== 'undefined' && module.exports) {
        module.exports = language;
    }
    // Browser
    if (typeof window !== 'undefined' && this.numeral && this.numeral.language) {
        this.numeral.language('current', language);
        this.numeral.language('current');
    }
}());