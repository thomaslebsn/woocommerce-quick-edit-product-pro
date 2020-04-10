(function ($) {
    window.fntQEPP =  window.fntQEPP || {};
    window.fntQEPP.Core = (function(){
        var module = {},
            overlayElementId = '#overlay';
            module.maskedInputMoneyOptions = null;
            module.imageStaticHtmlDefault = '';
            module.removeImageStaticHtmlDefault = '';
            module.galleryStaticHtmlDefault = '';
            module.imagePlusStaticHtmlDefault = '';
            module.readyHandler = false;
        module.init = function(){
            _.mixin({
                remove: function(obj, key){
                    delete obj[key];
                    return obj;
                }
            });
            $(document).ready( function() {
                // hide meta-boxes on screen options
                $('#screen-options-wrap #add-product-template-block-html-hide').closest('.metabox-prefs').remove();
                _.extend(Backform, {
                    controlLabelClassName: "control-label col-sm-2",
                    controlsClassName: "col-sm-10"
                });
                module.maskedInputMoneyOptions = {
                    allowNegative: false,
                    thousands: currency_format.currency_format_thousand_sep,
                    decimal: currency_format.currency_format_decimal_sep,
                    affixesStay: false,
                    allowZero : false
                };
                if(currency_format.currency_position === 'left'){
                    module.maskedInputMoneyOptions.prefix = currency_format.currency_format_symbol;
                } else {
                    module.maskedInputMoneyOptions.suffix = currency_format.currency_format_symbol;
                }
                module.imageStaticHtmlDefault = wp.template("image-default-html");
                module.removeImaageStaticHtmlDefault = wp.template("remove-image-default-html");
                module.galleryStaticHtmlDefault = wp.template("gallery-default-html");
                // enable button CTA
                enabledButtonCTA();
            });
            $(document).on('keyup', function(e) {
                if (e.which === 27) {
                    module.hideLoading();
                }
            });

            return module;
        };

        var enabledButtonCTA = function() {
            var buttonCTA = $( '.fnt-button-cta' );
            buttonCTA.removeAttr( 'disabled' );
            module.readyHandler = true;
        };

        module.ajaxRequestManual = function(dataPass, callback, beforeSend) {
            var option = {
                url: ajaxurl,
                type: 'POST',
                data: dataPass,
                statusCode: {
                    403: function(xhr) {
                        window.location.href = '/403';
                        return false;
                    }
                },
                dataType: 'JSON',
                beforeSend: _.isFunction(beforeSend) ? beforeSend : function(){
                    return true;
                },
                success: callback
            };
            $.ajax(option);
        };

        module.showLoading = function() {
            if($(overlayElementId).length !== false){
                module.hideLoading();
            }
            // add the overlay with loading image to the page
            var pleaseWaitDiv = '<div id="overlay">' +
                '<img id="loading" src="' + initialize_variables['processing-image'] + '">' +
                '</div>';
            $(pleaseWaitDiv).appendTo('body');
        };

        module.hideLoading = function(){
            $(overlayElementId).remove();
        };
        
        module.initInputColorPicker = function () {
            $('.input-color-picker').colorpicker();
        };

        module.getValueByKeyInListObj = function (key,listObj) {
            if(!_.isArray(listObj) || listObj.length === 0) return null;
            _.each(listObj, function(item, key) {

            });
        };

        module.validateFloat = function(e){
            var current = jQuery(e.currentTarget);
            var newText = current.val();
            var parseValue = 0;
            if(jQuery.isNumeric(newText)){
                parseValue = parseFloat(newText);
            }
            jQuery(e.currentTarget).val(parseValue);
        };

        module.validateInt = function(e){
            var current = jQuery(e.currentTarget);
            var newText = current.val();
            var parseValue = 0;
            if(jQuery.isNumeric(newText)){
                parseValue = parseInt(newText);
            }
            jQuery(e.currentTarget).val(parseValue);
        };

        module.compressDataForAJAXSend = function(arrayDataSend){
            var passData = arrayDataSend; // full_array_data_products send to server
            var results = [];
            _.each(passData, function (item) {
                if(!_.isEmpty(item)){
                    results.push(item);
                }
            });
            var stringifyData = JSON.stringify(results);
            return encodeURIComponent(stringifyData);
        };

        module.compressDataForAJAXSendWithoutFormatArray = function($jsonData){
            var stringifyData = JSON.stringify($jsonData);
            return encodeURIComponent(stringifyData);
        };

        module.splitString = function (str,pattern,removeEmpty) {
            var result = [];
            removeEmpty = typeof removeEmpty !== 'undefined' ? removeEmpty : false;
            if(str.length <= 0){
                return result;
            }
            var strSplit = str.split(pattern);
            _.forEach(strSplit, function (item, index) {
                if(removeEmpty){
                    if(item.length > 0){
                        result.push(item);
                    }
                }else{
                    result.push(item);
                }
            });
            return result;
        };

        module.cleanArrayAndFormatToObj = function(actual) {
            var newObjs = {};
            for (var i = 0; i < actual.length; i++) {
                if (actual[i]) {
                    newObjs[i] = actual[i];
                }
            }
            return newObjs;
        };

        // fill alternate of table
        module.updateAlternateTable = function( tableBody ) {
            var listAttributes = tableBody.find('tr:not(.hidden)');
            var alternate = '';
            _.each ( listAttributes, function( item, index ) {
                alternate = 'alternate' == alternate ? '' : 'alternate';
                if ( alternate == '' ) {
                    $(item).removeClass('alternate');
                    $(item).addClass('transparent');
                } else {
                    $(item).addClass(alternate);
                    $(item).removeClass('transparent');
                }
            } );
        };

        module.hideBodyScroll = function() {
            var body = $('body');
            if ( typeof body != 'undefined' && ! body.hasClass( 'hidden-scroll-bar' ) ) {
                body.addClass( 'hidden-scroll-bar' );
            }
        };
        module.showBodyScroll = function() {
            var body = $('body');
            if ( typeof body != 'undefined' && body.hasClass( 'hidden-scroll-bar' ) ) {
                body.removeClass( 'hidden-scroll-bar' );
            }
        };

        // check global list have product modifying or not?
        module.productModifyingCheck = function() {
            if ( typeof fnt_product_data == 'undefined' ) {
                return false;
            }
            var haveModifyingProduct = false;
            _.each ( fnt_product_data, function ( value, index ) {
                // check if have any product is modify, then return true
                if ( typeof value['modifying_product'] != 'undefined' ) {
                    if ( value['modifying_product'] == '1' ) {
                        haveModifyingProduct = true;
                        // return false; // This just exit function each(), this is not really working
                    }
                }
            } );

            return haveModifyingProduct;
        };

        return module.init();
    })();
})(jQuery);