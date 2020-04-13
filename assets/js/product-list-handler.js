var productModifying = false;
var readyPlugin = false;
(function ($) {
    window.fntQEPP =  window.fntQEPP || {};
    window.fntQEPP.ProductListHandler = (function(){

        var module = {};

        module.adminUrl = null;

        module.init = function(){
            var theListElement = $('#posts-filter');
            var postFilterElement = $('#posts-filter');
            var tableHeader = $('#table-header');
            theListElement.off('click','.cell-edit-inline').on('click','.cell-edit-inline',editProductInlineHandler);
            theListElement.off('blur','.input-text-editable').on('blur','.input-text-editable',inputEditableFocusOutHandler);
            theListElement.off('keypress','.input-text-editable').on('keypress', '.input-text-editable', function(e) {
                e.stopPropagation();
                if(!window.fntQEPP.Core.readyHandler){
                    return false;
                }
                if (e.which == 13) {
                    inputEditableFocusOutHandler(e);
                    return false;
                }
            });
            theListElement.off('change','.feature-checkbox').on('change','.feature-checkbox', changeProductFeature);
            theListElement.off('mouseenter', '.wrap-gallery-image,.wrap-thumbnail').on('mouseenter','.wrap-gallery-image,.wrap-thumbnail', mouseHoverInGallery);
            theListElement.off('mouseleave', '.wrap-gallery-image,.wrap-thumbnail').on('mouseleave','.wrap-gallery-image,.wrap-thumbnail', mouseHoverOutGallery);
            theListElement.off('mouseenter', '.wrap-thumbnail').on('mouseenter','.wrap-thumbnail', mouseHoverInThumbnail);
            theListElement.off('mouseleave', '.wrap-thumbnail').on('mouseleave','.wrap-thumbnail', mouseHoverOutThumbnail);
            theListElement.off('click', '.remove-gallery-image').on('click','.remove-gallery-image', deleteGalleryImage);
            theListElement.off('click', '.remove-thumbnail').on('click','.remove-thumbnail', deleteThumbnail);
            postFilterElement.off('click','#button-delete-product').on('click','#button-delete-product',deleteProducts);
            postFilterElement.off('click','#button-move-product-to-trash').on('click','#button-move-product-to-trash',moveProductsToTrash);
            postFilterElement.off('click','#change-product-type').on('click','#change-product-type',changeProductType);
            postFilterElement.off('click','#button-refresh-page').on('click','#button-refresh-page',redirectPage);
            postFilterElement.off('click','#button-page-back').on('click','#button-page-back',redirectPage);
            postFilterElement.off('click','#button-refresh').on('click','#button-refresh',redirectPage);
            postFilterElement.off('click','#button-restore-product').on('click','#button-restore-product', restoreProducts);
            //$('#the-list select.product_cat').attr("multiple",true);
            module.adminUrl = $('#admin-url').val();
            tableHeader.off('click','#button-save-product-in-current-page').on('click','#button-save-product-in-current-page', submitDataFromTableOnActionSaveAll);
            tableHeader.off('click','#button-save-all-product-data').on('click','#button-save-all-product-data',submitDataFromTableOnActionSaveAll);
            postFilterElement.off('click','#search-submit,#post-query-submit,#last-import-filter,#doaction').on('click','#search-submit,#post-query-submit,#last-import-filter,#doaction',submitDataFromTableOnActionSearchAndFilter);
            $(document).ready(function(){
                $('.product-tags').suggest(module.adminUrl + "admin-ajax.php?action=ajax-tag-search&tax=product_tag",
                    {multiple:true, multipleSep: ","}
                );
                theListElement.off('click','.btn-push-tags').on('click','.btn-push-tags',handleProductTagClickOk);
                module.reInitInputMask();
                // uncheck checkbox when do action @NTN
                var checkList = theListElement.find('th .product-single-checkbox-row:checked');
                checkList.removeAttr('checked');
                var headerCheckBox = $('.wp-list-table tr td input#cb-select-all-1, input#cb-select-all-2');
                headerCheckBox.removeAttr('checked');
                // end uncheck checkbox when do action

                // Make scroll at bottom for product table @NTN
                window.fntQEPP.TableScrollHandler.makeScroll();
                // End make scroll at bottom for product table @NTN
            });
            window.onbeforeunload = function (event) {
                var e = event || window.event;
                if(productModifying){
                    // For IE and Firefox prior to version 4
                    if (e) {
                        e.returnValue = initialize_variables.message_show.confirm_redirect_page;
                    }

                    // For Safari
                    return initialize_variables.message_show.confirm_redirect_page;
                }
            };

            theListElement.off('click', '.button-edit-content').on('click','.button-edit-content', function(e){editProductContent($(e.currentTarget));});
            theListElement.off('click', '.button-edit-excerpt').on('click','.button-edit-excerpt', function(e){editProductContent($(e.currentTarget));});
            theListElement.off('blur', '.validate-price').on('blur','.validate-price', validateInputPrice);
            theListElement.off('click', '.edit-featured').on('click','.edit-featured', changeFeatureState);

            theListElement.off('click', '.wrap-thumbnail').on('click','.wrap-thumbnail', changeProductImage);
            theListElement.off('click', '.wrapper-gallery-plus').on('click','.wrapper-gallery-plus', changeProductImage);
            theListElement.off('click', '.edit-inline-checkbox').on('click','.edit-inline-checkbox', editInlineCheckbox);
            theListElement.off('click', '.row-actions a').on('click','.row-actions a', anchorTagDefaultHandler);
            postFilterElement.off('click', '.tablenav-pages a, .subsubsub a, .wp-list-table thead tr th a').on('click', '.tablenav-pages a, .subsubsub a, .wp-list-table thead tr th a', checkIsEditingInlineTable);
            return module;
        };

        // add mask to input type number
        module.reInitInputMask = function () {
            $('.input-only-numbers').inputmask("integer",{allowPlus:false, allowMinus:true});
            $('.input-numbers').inputmask("numeric",{allowPlus:false, allowMinus:true});
        };

        var redirectPage = function(e){
            var url = $(e.currentTarget).attr('button-url');
            window.fntQEPP.Core.showLoading();
            if ( typeof url !== typeof undefined && url !== false ) {
                // Element has this attribute
                window.location.href = url;
            }
        };

        var checkIsEditingInlineTable = function(e){
            if(productModifying){
                if(module.confirmRedirectPage()){
                    return true;
                }else{
                    return false;
                }
            }else{
                return true;
            }
        };

        var anchorTagDefaultHandler = function (event) {
            event.stopPropagation();
            return true;
        };

        var changeFeatureState = function (e) {
            var feature = $(e.currentTarget);
            var columnName = feature.closest('.wrap-input-text').attr('data-product-field-name');
            var productRowId = feature.closest('tr').find('.product-single-checkbox-row').attr('data-product-row-id');
            if(feature.hasClass('not-featured')) {
                feature.removeClass('not-featured');
                changValueProductList(productRowId,columnName,'yes');
            } else {
                feature.addClass('not-featured');
                changValueProductList(productRowId,columnName,'no');
            }
            module.changeRowColor(feature,'modifying');
        };

        var editInlineCheckbox = function(e){
            var current = $(e.currentTarget);
            var productRowId = current.closest('tr').find('.product-single-checkbox-row').attr('data-product-row-id');
            var columnName = current.closest('td').find('.wrap-input-text').attr('data-product-field-name');
            if(columnName == product_column_definition.product_allow_comments){
                if(current.is(':checked')){
                    changValueProductList(productRowId,columnName,'open');
                }else{
                    changValueProductList(productRowId,columnName,'closed');
                }
            }
            if(columnName == product_column_definition.product_meta_sold_individually){
                if(current.is(':checked')){
                    changValueProductList(productRowId,columnName,'yes');
                }else{
                    changValueProductList(productRowId,columnName,'');
                }
            }
            if(columnName == product_column_definition.product_meta_manage_stock){
                if(current.is(':checked')){
                    changValueProductList(productRowId,columnName,'yes');
                }else{
                    changValueProductList(productRowId,columnName,'no');
                }
            }
            module.changeRowColor(current,'modifying');
        };

        var editProductContent = function(selectElement){
            var currentRow = selectElement.closest('tr');
            var productId = currentRow.attr('data-product-id');
            var preRow = currentRow.closest('tbody').find('tr.main-row-' + productId);
            var dataAttribute = preRow.find('.product-single-checkbox-row');
            var productRowId = dataAttribute.attr('data-product-row-id');
            var contentText = '';
            var inputId = '';
            var editor = null;
            var classTd = 'td.product_';
            var classButton = '.button-show-';
            if(currentRow.hasClass('row-edit-content')){
                contentText = '';
                inputId = productId + '-content';
                classTd += 'content';
                classButton += 'content';
                editor = tinyMCE.get(inputId);
                if (editor != null && !editor.hidden) {
                    // Ok, the active tab is Visual
                    contentText = $(editor.getBody()).text();
                } else {
                    // The active tab is HTML, so just query the textarea
                    contentText = $('#' + inputId).val();
                }
                if(contentText != fnt_product_data[productRowId][product_column_definition.product_content]){
                        changValueProductList(productRowId,product_column_definition.product_content,contentText);
                        module.changeRowColor(dataAttribute, 'modifying');
                }
            }else{
                contentText = '';
                inputId = productId + '-excerpt';
                classTd += 'excerpt';
                classButton += 'excerpt';
                editor = tinyMCE.get(inputId);
                if (editor != null && !editor.hidden) {
                    // Ok, the active tab is Visual
                    contentText = $(editor.getBody()).text();
                } else {
                    // The active tab is HTML, so just query the textarea
                    contentText = $('#' + inputId).val();
                }

                if(contentText != fnt_product_data[productRowId][product_column_definition.product_excerpt]){
                        changValueProductList(productRowId, product_column_definition.product_excerpt, contentText);
                        module.changeRowColor(dataAttribute, 'modifying');
                }
            }
            if(!currentRow.hasClass('hidden')){
                currentRow.addClass('hidden');
            }
            //edit display button
            var preCellElement = preRow.find(classTd).find('.wrap-input-text');
            var buttonTextArea = preCellElement.find(classButton);
            if(buttonTextArea.hasClass('button-hide')){
                buttonTextArea.removeClass('button-hide')
            }
            if(!buttonTextArea.hasClass('button-show')){
                buttonTextArea.addClass('button-show')
            }
            // @NTN code for make scroll
            setTimeout(function (){
                window.fntQEPP.TableScrollHandler.hideExtraScroll();
            }, 50);
            // end code for make scroll
        };

        var deleteProducts = function (e) {
            e.preventDefault();
            if(productModifying){
                if(module.confirmRedirectPage()){
                    deletingProductsHandler();
                }
            }else{
                deletingProductsHandler();
                productModifying = false;
            }


            return false;
        };

        var deletingProductsHandler = function () {
            var selectedProductID = [];
            var checkList = $('#the-list').find('th .product-single-checkbox-row:checked');
            _.each(checkList, function(item, index) {
                selectedProductID.push(item.value);
            });
            if(selectedProductID.length <= 0){
                alert(initialize_variables.message_show.no_items_selected);
                return false;
            }
            var dataPass = {
                allProductData: selectedProductID,
                action: 'fnt_product_manage',
                real_action: 'delete_product'
            };
            var confirmationDelete = window.fntQEPP.Settings.isEnabledConfirmationDeletingProducts();
            if(confirmationDelete){
                var conf = confirm(initialize_variables.message_show.message_delete_product);
                if(conf){
                    window.fntQEPP.Core.ajaxRequestManual(dataPass, handleResponseByDeleteProduct, beforeSendDataToSaveAllAction);
                }
            }else{
                window.fntQEPP.Core.ajaxRequestManual(dataPass,handleResponseByDeleteProduct,beforeSendDataToSaveAllAction);
                productModifying = false;
            }
        };

        var handleResponseByDeleteProduct = function(response) {
            var checkList = $('#the-list').find('th .product-single-checkbox-row:checked');
            checkList.text('Delete');
            if(response.result === "SUCCESS"){
                window.location.reload();
            } else {
                window.fntQEPP.Core.hideLoading();
                console.log(initialize_variables.message_show.delete_product_failed);
            }
            return false;
        };

        var moveProductsToTrash = function (e) {
            e.preventDefault();
            var selectedProductID = [];
            var checkList = $('#the-list').find('th .product-single-checkbox-row:checked');
            _.each(checkList, function(item, index) {
                selectedProductID.push(item.value);
            });
            var dataPass = {
                allProductData: selectedProductID,
                action: 'fnt_product_manage',
                real_action: 'move_product_to_trash'
            };
            if(productModifying){
                if(module.confirmRedirectPage()){
                    if(selectedProductID.length <= 0){
                        alert(initialize_variables.message_show.no_items_selected);
                        return false;
                    }
                    window.fntQEPP.Core.ajaxRequestManual(dataPass, handleResponseByMoveProductsToTrash, beforeSendDataToSaveAllAction);
                }
            }
            else{
                if(selectedProductID.length <= 0){
                    alert(initialize_variables.message_show.no_items_selected);
                    return false;
                }
                window.fntQEPP.Core.ajaxRequestManual(dataPass,handleResponseByMoveProductsToTrash,beforeSendDataToSaveAllAction);
                productModifying = false;
            }
        };

        var handleResponseByMoveProductsToTrash = function(response) {
            var checkList = $('#the-list').find('th .product-single-checkbox-row:checked');
            if(response.result === "SUCCESS"){
                checkList.prop( "checked", false );
                window.location.reload();
            } else {
                window.fntQEPP.Core.hideLoading();
                console.log(initialize_variables.message_show.move_to_trash_product_failed);
            }
            return false;
        };

        var changeProductType = function( e ) {
            e.preventDefault();
            var selectedProductID = [];
            var checkList = $( '#the-list' ).find( 'th .product-single-checkbox-row:checked' );
            _.each ( checkList, function( item, index ) {
                selectedProductID.push( item.value );
            } );
            var selectedProductType = $('#change_product_type').val();
            if ( typeof selectedProductType == 'undefined' ) {
                selectedProductType = -1;
            }
            if ( selectedProductType == 0 ) {
                alert( initialize_variables.message_show.please_select_product_type_to_change );
                return false;
            }
            var dataPass = {
                action: 'fnt_product_manage',
                real_action: 'change_product_type',
                allProductData: selectedProductID,
                productType: selectedProductType
            };
            if ( productModifying ) {
                if ( module.confirmRedirectPage() ) {
                    if ( selectedProductID.length <= 0 ) {
                        alert( initialize_variables.message_show.no_items_selected );
                        return false;
                    }
                    window.fntQEPP.Core.ajaxRequestManual( dataPass, handleResponseChangeProductType, function() {
                        window.fntQEPP.Core.showLoading();
                    } );
                }
            } else {
                if ( selectedProductID.length <= 0 ) {
                    alert( initialize_variables.message_show.no_items_selected );
                    return false;
                }
                window.fntQEPP.Core.ajaxRequestManual( dataPass, handleResponseChangeProductType, function() {
                    window.fntQEPP.Core.showLoading();
                } );
                productModifying = false;
            }
        };

        var handleResponseChangeProductType = function( response ) {
            var checkList = $('#the-list').find('th .product-single-checkbox-row:checked');
            if ( response.result === 'SUCCESS') {
                checkList.prop( "checked", false );
                window.location.reload();
            } else {
                window.fntQEPP.Core.hideLoading();
                console.log(initialize_variables.message_show.change_product_type_failed);
            }
            return false;
        };

        var restoreProducts = function (e) {
            e.preventDefault();
            var selectedProductID = [];
            var checkList = $('#the-list').find('th .product-single-checkbox-row:checked');
            _.each(checkList, function(item, index) {
                selectedProductID.push(item.value);
            });
            if(selectedProductID.length <= 0){
                return false;
            }
            var dataPass = {
                allProductData: selectedProductID,
                action: 'fnt_product_manage',
                real_action: 'restore_product'
            };
            if(productModifying){
                if(module.confirmRedirectPage()){
                    window.fntQEPP.Core.ajaxRequestManual(dataPass,handleResponseByRestoreProducts,beforeSendDataToSaveAllAction);
                }
            }else{
                window.fntQEPP.Core.ajaxRequestManual(dataPass,handleResponseByRestoreProducts,beforeSendDataToSaveAllAction);
                productModifying = false;
            }

            return false;
        };

        var handleResponseByRestoreProducts = function(response) {
            var checkList = $('#the-list').find('th .product-single-checkbox-row:checked');
            checkList.text('Restore product');
            if(response.result === "SUCCESS"){
                window.location.reload();
            } else {
                window.fntQEPP.Core.hideLoading();
                console.log(initialize_variables.message_show.restore_product_failed);
            }
            return false;
        };

        var mouseHoverInGallery = function(e){
            var tableCellElement = $(e.currentTarget);
            var removeIcon = tableCellElement.find('.remove-gallery-image');
            removeIcon.attr('style','display:inline-block !important');

        };

        var mouseHoverOutGallery = function(e){
            var tableCellElement = $(e.currentTarget);
            var removeIcon = tableCellElement.find('.remove-gallery-image');
            removeIcon.attr('style','');
        };

        var submitDataFromTableOnActionSearchAndFilter = function (e) {
            e.preventDefault();
            $('#the-list input, #the-list select, #the-list textarea, input[name=\"_wp_http_referer\"], #popup-editor textarea#product-popup-editor').attr("disabled", true);
            $('#posts-filter').submit();
            return false;
        };

        var mouseHoverInThumbnail = function(e){
            var tableCellElement = $(e.currentTarget);
            var removeIcon = tableCellElement.find('.remove-thumbnail');
            removeIcon.attr('style','display:inline-block !important');

        };

        var mouseHoverOutThumbnail = function(e){
            var tableCellElement = $(e.currentTarget);
            var removeIcon = tableCellElement.find('.remove-thumbnail');
            removeIcon.attr('style','');
        };

        var submitDataFromTableOnActionSaveAll = function (e) {
            e.preventDefault();
            if(!window.fntQEPP.Core.readyHandler){
                return false;
            }
            var currentButton = $(e.currentTarget);
            var saveAll = 1;
            if(!currentButton.hasClass('save-all')){
                saveAll = 0;
            }
            if(productModifying || initialize_variables['just_import'] == 1){
                module.saveAllDataFromListTable(saveAll);
            } else {
                alert(initialize_variables.message_show.no_change_detected);
            }
        };

        module.saveAllDataFromListTable = function(saveAll){
            var fullDataPassed = [];
            var validateProductName = true;
            var checkModifying = false;
            _.each(fnt_product_data, function(item, key) {
                _.each(item, function(itemChild, keyChild){
                    if($.isArray(itemChild) && itemChild.length <= 0) {
                        item[keyChild] = '';
                    }
                });
                if(typeof item[product_column_definition.product_title] != 'undefined' && item[product_column_definition.product_title].length <= 0){
                    validateProductName = false;
                }
                if((typeof item['modifying_product'] != 'undefined' && item['modifying_product'] > 0) ||
                    initialize_variables['just_import'] == 1){
                    checkModifying = true;
                    fullDataPassed.push(item);
                }
            });
            if(fullDataPassed.length <= 0){
                return false;
            }
            if(initialize_variables.just_import ==  "1"){
                checkModifying = true;
            }


            // column is not compress
            // because product attributes can content character ", example: screen size(12", 32", 120",...)
            var column_not_compress = ['product_content', 'product_excerpt', 'variation_description', 'product_attributes'];
            // split product content and excerpt or variation_description form allDataPassed
            var dataDesc = [];
            _.each(fullDataPassed, function(item, key) {
                var subData = {};
                _.each(item, function(subItem, subKey) {
                    // get content, excerpt of product or description of variation product
                    // and move to dataDesc variable
                    // add variation_description, variable attributes since ver 1.1
                    if ( $.inArray( subKey, column_not_compress ) != -1 ) {
                        subData[subKey] = subItem;
                        delete item[subKey];
                    }
                });
                dataDesc.push(subData);
            });
            if(checkModifying){
                var dataPass = {
                    all_product_data: window.fntQEPP.Core.compressDataForAJAXSend(fullDataPassed),
                    all_product_content: dataDesc,
                    just_import: initialize_variables.just_import,
                    save_all: saveAll,
                    action: 'fnt_product_manage',
                    real_action: 'edit_multiple'
                };
                if(validateProductName) {
                    window.fntQEPP.Core.ajaxRequestManual(dataPass, handleResponseBySavingAllProductData, beforeSendDataToSaveAllAction);
                }else{
                    alert(initialize_variables.message_show.message_product_name_not_empty);
                }
            }
            productModifying = false;
        };

        module.checkDataModifying = function(){
            var modifying = false;
            _.each(fnt_product_data, function(item){
                if(item['modifying_product'] != 'undefined' && item['modifying_product'] == 1){
                    modifying = true;
                }
            });
            return modifying;
        };

        module.confirmRedirectPage = function(message){
            var conf = true;
            message = typeof message == 'undefined' ? initialize_variables.message_show.message_warning : message;
            if(productModifying){
                conf = confirm(message);
            }
            if(conf){
                productModifying = false;
            }
            return conf;
        };

        var beforeSendDataToSaveAllAction = function(){
            window.fntQEPP.Core.showLoading();
        };

        var handleResponseBySavingAllProductData = function(response){
            if(response.result === "SUCCESS"){
                var saveAll = response.data.save_all;

                if(saveAll <= 0){
                    var totalPages = $('div.tablenav.top span.total-pages').text();
                    if(totalPages == "1"){
                        window.location.href = initialize_variables.plugin_base_url;
                    }else{
                        window.location.reload();
                    }
                } else {
                    if(initialize_variables['just_import'] == 1){
                        window.location.href = initialize_variables.plugin_base_url;
                    }else{
                        window.location.reload();
                    }
                }
            } else {
                productModifying = true;
                window.fntQEPP.Core.hideLoading();
                if(!_.isEmpty(response.data) && !_.isEmpty(response.data.error_array)){
                    alert(response.data.error_array);
                }
            }
            return false;
        };

        var deleteGalleryImage = function(e){
            e.preventDefault();
            e.stopPropagation();
            if(!window.fntQEPP.Core.readyHandler){
                return false;
            }
            var linkDelete = $(e.currentTarget);
            var attachmentDeleteId = linkDelete.attr('data-attachment-id');
            var tableCellElement = linkDelete.closest('td');
            var productRowId = linkDelete.closest('tr').find('.product-single-checkbox-row').attr('data-product-row-id');
            var columnName = tableCellElement.find('.wrap-input-text').attr('data-product-field-name');

            // Since ver 1.1, fix bug
            // In case have more images duplicate, get current image index to delete correctly
            var currentImageSpan = linkDelete.closest('span.wrap-gallery-image');
            var listImageSpan = tableCellElement.find('span.wrap-gallery-image');
            var currentItemIndex = listImageSpan.index( currentImageSpan );

            var oldValue = fnt_product_data[productRowId][columnName].split(',');
            var result = [];
            oldValue.forEach(function(item,index){
                if ( index === currentItemIndex && item === attachmentDeleteId ) {
                    return;
                }
                result.push( item );
            });
            changValueProductList(productRowId,columnName,result.join(','));
            module.changeRowColor(linkDelete,'modifying');
            linkDelete.closest('span.wrap-gallery-image').remove();
            // @NTN code for make scroll
            window.fntQEPP.TableScrollHandler.hideExtraScroll();
            // end @NTN code for make scroll

        };

        var deleteThumbnail = function(e){
            e.preventDefault();
            e.stopPropagation();
            if(!window.fntQEPP.Core.readyHandler){
                return false;
            }
            var curElement = $(e.currentTarget);
            var tableCellElement = curElement.closest('td');
            var productRowId = curElement.closest('tr').find('.product-single-checkbox-row').attr('data-product-row-id');
            var columnName = tableCellElement.find('.wrap-input-text').attr('data-product-field-name');
            changValueProductList(productRowId,columnName,'');
            module.changeRowColor(curElement,'modifying');
            var imageDefault = window.fntQEPP.Core.galleryStaticHtmlDefault;
            curElement.closest('td').find('span.input-text').html(imageDefault);
        };

        var product_column_definition = {
            product_id: 'id',
            product_title: 'post_title',
            product_content: 'product_content',
            product_category: 'product_cat',
            product_meta_edit_last: 'edit_last',
            product_meta_edit_lock: 'edit_lock',
            product_meta_product_attributes: 'product_attributes',
            product_meta_total_sales: 'total_sales',
            product_meta_downloadable: 'downloadable',
            product_meta_virtual: 'virtual',
            product_meta_purchase_note: 'purchase_note',
            product_meta_sale_price_dates_from: 'sale_price_dates_from',
            product_meta_sale_price_dates_to: 'sale_price_dates_to',
            product_meta_sold_individually: 'sold_individually',
            product_meta_manage_stock: 'manage_stock',
            product_meta_upsell_ids: 'upsell_ids',
            product_meta_crosssell_ids: 'crosssell_ids',
            product_meta_product_url: 'product_url',
            product_meta_button_text: 'button_text',
            product_meta_product_version: 'product_version',
            product_meta_regular_price: 'regular_price',
            product_meta_sale_price: 'sale_price',
            product_meta_price: 'price',
            product_excerpt: 'product_excerpt',
            product_meta_tax_status: 'tax_status',
            product_meta_tax_class: 'tax_class',
            product_meta_weight: 'weight',
            product_meta_length: 'length',
            product_meta_width: 'width',
            product_meta_height: 'height',
            product_shipping: '???',
            product_meta_visibility: 'visibility',
            product_meta_feature: 'featured',
            product_meta_stock_status: 'stock_status',
            stock_quantity: 'stock',
            product_meta_backorders: 'backorders',
            product_menu_order: 'menu_order',
            product_tag: '',
            product_allow_comments: 'comment_status',
            product_status: 'status',
            product_slug: 'post_name',
            product_meta_sku: 'sku',
            feature_image: 'thumb',
            product_galleries: 'product_gallery'
        };

        var mediaUploader = function(e){
            var image = wp.media({
                title: 'Upload Image',
                multiple: false
            }).open()
                .on('select', function(){
                    var uploadedImage = image.state().get('selection').first();
                    var uploadedImageJsonData = uploadedImage.toJSON();
                    var cellContentElement = $(e.currentTarget).closest('.wrap-input-text');
                    var thumbnailUrl = uploadedImageJsonData.url;
                    var product_id = cellContentElement.closest('tr').find('.product-single-checkbox-row').attr('data-product-row-id');
                    if ( typeof uploadedImageJsonData.sizes !== 'undefined' && typeof uploadedImageJsonData.sizes.thumbnail !== 'undefined' ) {
                        thumbnailUrl = uploadedImageJsonData.sizes.thumbnail.url;
                    }
                    cellContentElement.find('span.input-text').html(createThumbnailHtml(thumbnailUrl,uploadedImageJsonData.id));
                    if ( fnt_product_data[product_id][product_column_definition.feature_image] !== uploadedImageJsonData.id.toString() ) {
                        module.changeRowColor(cellContentElement,'modifying');
                        changValueProductList(product_id,product_column_definition.feature_image,uploadedImageJsonData.id);
                    }
                });
        };

        var productGallery = function(e){
            var image= wp.media({
                title: 'Upload Image',
                multiple: true
            }).open()
                .on('select', function(){
                    var uploaded_image = image.state().get('selection');
                    var array_gallery = [];
                    var cellContentElement = $(e.currentTarget).closest('.wrap-input-text .input-text');
                    var row_id = cellContentElement.closest('tr').find('.product-single-checkbox-row').attr('data-product-row-id');
                    var listImageOld = [];
                    if (typeof(fnt_product_data[row_id][product_column_definition.product_galleries]) !== 'undefined' &&
                        fnt_product_data[row_id][product_column_definition.product_galleries] !== null) {
                        listImageOld = fnt_product_data[row_id][product_column_definition.product_galleries].split(",");
                    }
                    var flag = true;
                    uploaded_image.forEach(function(item){
                        var object = item.toJSON();
                        // check if image existed in galleries, don't add this
                        // but default of Woocommerce is allow select an existed image in galleries, so I comment this code
                        // if(jQuery.inArray(object.id.toString(),listImageOld) == -1){
                            if( typeof object.sizes !== 'undefined' ) {
                                var imageUrl = '';
                                if ( typeof object.sizes.thumbnail !== 'undefined' ) {
                                    imageUrl = object.sizes.thumbnail.url;
                                } else if ( typeof object.sizes.medium !== 'undefined' ) {
                                    imageUrl = object.sizes.medium.url;
                                } else { // last is full // if ( typeof object.sizes.full !== 'undefined' ) {
                                    imageUrl = object.sizes.full.url;
                                }
                                // cellContentElement.prepend(createImageHtml(object.sizes.thumbnail.url,object.id));
                                $(createImageHtml(imageUrl, object.id)).insertBefore(cellContentElement.find('span.wrap-gallery-image:last-child'));
                                flag = false;
                            }
                            array_gallery.push(object.id.toString());
                        // }
                    });
                    if(flag){
                        return;
                    }
                    array_gallery = array_gallery.filter(Boolean);
                    listImageOld = listImageOld.filter(Boolean);
                    if(listImageOld.length > 0){
                        array_gallery = $.merge(listImageOld,array_gallery);
                    }
                    var strListImage = array_gallery.join(",");
                    module.changeRowColor(cellContentElement,'modifying');
                    changValueProductList(row_id,product_column_definition.product_galleries,strListImage);
                    // @NTN code for make scroll
                    setTimeout(function() {
                        window.fntQEPP.TableScrollHandler.hideExtraScroll();
                    }, 250);
                    // end @NTN code for make scroll
                });
        };

        var changeProductImage = function(e){
            e.stopPropagation();
            e.preventDefault();
            var current = $(e.currentTarget);
            var cellContentElement = current.closest('.wrap-input-text');
            var columnName = cellContentElement.attr('data-product-field-name');
            if(columnName === product_column_definition.feature_image){
                mediaUploader(e);
            }else if(columnName === product_column_definition.product_galleries){
                productGallery(e);
            }

        };

        var createImageHtml = function(src, attachmentId){
            return '<span class="wrap-gallery-image bootstrap-wrapper">' +
                '<span class="glyphicon glyphicon-remove-circle remove-gallery-image" data-attachment-id="' + attachmentId + '"></span>' +
                '<img src="' + src + '" width="40px" height="40px" class="image-thumb-gallery">' +
                '</span>';
        };

        var createThumbnailHtml = function(src, attachmentId){
            return '<span class="wrap-thumbnail bootstrap-wrapper">' +
                '<span class="glyphicon glyphicon-remove-circle remove-thumbnail" data-attachment-id="' + attachmentId + '"></span>' +
                '<img src="' + src + '" width="40px" height="40px" class="image-thumb-gallery">' +
                '</span>';
        };

        var handleProductTagClickOk = function(e){
            e.preventDefault();
            if(!window.fntQEPP.Core.readyHandler){
                return false;
            }
            var currentBtnOk = $(e.currentTarget);
            var productRowId = currentBtnOk.closest('tr').find('.product-single-checkbox-row').attr('data-product-row-id');
            var columnName = currentBtnOk.closest('td').find('div').attr('data-product-field-name');
            var selectedTagsOnInput = currentBtnOk.closest('.wrapper-product-tags').find('.input-text-editable').val();
            var spanInputText = currentBtnOk.closest('div.product_tag').find('.input-text');
            currentBtnOk.closest('.wrapper-product-tags').addClass('hidden');
            if(selectedTagsOnInput.length <= 0){
                selectedTagsOnInput = '';
            }
            if(selectedTagsOnInput[selectedTagsOnInput.length - 1] === ","){
                selectedTagsOnInput = selectedTagsOnInput.substring(0,selectedTagsOnInput.length-1);
            }
            spanInputText.text(selectedTagsOnInput);
            spanInputText.show();
            var listTags = (selectedTagsOnInput.length > 0) ? selectedTagsOnInput.split(',') : [];
            var listTagTemp = [];
            if(listTags.length > 0){
                _.each(listTags, function(item){
                    listTagTemp.push(item.trim());
                });
                listTags = listTagTemp;
            }
            var oldValue = fnt_product_data[productRowId][columnName];
            if(selectedTagsOnInput === "" && oldValue.length > 0 ){
                fnt_product_data[productRowId][columnName] = [];
                fnt_product_data[productRowId]['modifying-product'] = 1;
                module.changeRowColor(currentBtnOk.closest('div'),'modifying');
                return false;
            }
            if(!compareArrayString(oldValue,listTags)){
                changValueProductList(productRowId,columnName,listTags);
                module.changeRowColor(currentBtnOk.closest('div'),'modifying');
            }
            var parent = currentBtnOk.closest('.wrap-input-text');
            if(parent.hasClass('cell-modifying')){
                parent.removeClass('cell-modifying');
            }
            // @NTN code for make scroll
            window.fntQEPP.TableScrollHandler.hideExtraScroll();
            // end @NTN code for make scroll
            return false;
        };

        var changeProductFeature = function(e){
            if(!window.fntQEPP.Core.readyHandler){
                return false;
            }
            var tableCellElement = $(e.currentTarget);
            var newValue = '';
            if(tableCellElement.context.checked){
                newValue = 'yes';
            }else{
                newValue = 'no';
            }
            var columnName = tableCellElement.closest('td').find('div').attr('data-product-field-name');
            var productRowId = tableCellElement.closest('tr').find('td.post_title').find('div.post_title').attr('data-product-row-id');
            var oldValue = fnt_product_data[productRowId][columnName];
            if(oldValue !== newValue){
                changValueProductList(productRowId,columnName,newValue);
                module.changeRowColor(tableCellElement.closest('div'),'modifying');
            }
        };

        var autoSelectedDropdownList = function(e, columnName){
            var tableCellElement = $(e.currentTarget);
            var dropdown = tableCellElement.closest('div').find('select');
            var productRowId = tableCellElement.closest('tr').find('.product-single-checkbox-row').attr('data-product-row-id');
            if(fnt_product_data[productRowId][columnName] !== "undefined"){
                var listId = fnt_product_data[productRowId][columnName];
                if(columnName === 'product_cat'){
                    listId.forEach(function(item,index){
                        $("#"+ columnName +" > option").filter( function() {
                            return $(this).val() == item;
                        }).prop('selected', true);
                    });
                    $("#"+ columnName +" > option").filter( function() {
                        return $(this).val() <= 0;
                    }).prop('selected', false);
                }
                dropdown.val(listId);
            }

        };

        var editProductInlineHandler = function (e) {
            e.preventDefault();
            if(!window.fntQEPP.Core.readyHandler){
                return false;
            }
            // jquery select the table cell contains input and span
            var tableCellElement = $(e.currentTarget);
            // jquery select the input editable for entering of user
            var inputEditableElement = tableCellElement.find('.input-text-editable');
            // jquery select the wrapper input tag element
            var wrapperInputTagEditElement = tableCellElement.find('.wrapper-product-tags');
            // get flag to detect current cell is product-tag cell or not
            var isTagCell = wrapperInputTagEditElement.length > 0;
            if ( tableCellElement.hasClass('cell-modifying')  // check if this cell is modifying
                || ( !isTagCell && ! inputEditableElement.hasClass('hidden')) // or check current cell is not TagCell and this input inside this cell is appearing.
                || ( isTagCell && ! wrapperInputTagEditElement.hasClass('hidden') ) // or check current cell us
            ) {
                // tableCellElement.hasClass('cell-modifying') :
                // ! inputEditableElement.hasClass('hidden') :
                // return false : do not handle anything till to focus out this input field.
                return false;
            }
            // add class cell-modifying to mark flag this cell is editing.
            tableCellElement.addClass('cell-modifying');
            /// jquery select element SPAN which contains current context of this field.
            var inputText = tableCellElement.find('.input-text');
            // get column name of current column
            var columnName = tableCellElement.attr('data-product-field-name');
            // jquery select the dropdownlist inside this cell
            var dropdown = tableCellElement.find('select');
            if(dropdown.length > 0){
                 //if has a dropdownlist then auto populating selected data to it via function autoSelectedDropdownList
                autoSelectedDropdownList(e, columnName);
            }
            // control the workflow of editing follow meaning of each column
            switch (columnName) {
                case 'date':
                case 'product_type':
                case 'product_slug':
                    // do not do any thing with info cell
                    break;
                case 'featured':
                case 'product_content':
                case 'product_excerpt':
                case 'product_gallery':
                case 'thumb':
                    if(tableCellElement.hasClass('cell-modifying')){
                        // check columns are clicked to edit as 'featured', 'product-content',
                        // 'product-excerpt', 'product-gallery' and thumb then remove immediately detecting class cell-modifying
                        // because these cell/fields are implemented editing flow by other mechanism.
                        tableCellElement.removeClass('cell-modifying');
                    }
                    break;
                case 'product_tag':
                    // handle editing for cell product-tag
                    handleProductTagsSelection(e, inputEditableElement);
                    break;
                default :
                    // default case is handling for editing all input text

                    // jquery select all input text editing
                    var inputTextEditing = $('.input-text-editing');
                    if (inputTextEditing.length > 0) {
                        // handle for case on table have some input text is on status editing.
                        // It means that it has not been focus out yet.
                        setTimeout(function(){
                            inputTextEditing.each(function (index, item) {
                                handleFocusOutForInputEditableSelectedElement($(item).closest('td'));
                            });
                        },200);
                    }
                    if (!inputEditableElement.hasClass('input-text-editing')) {
                        // mark flag for this input to be able to detect that it is editing.
                        inputEditableElement.addClass('input-text-editing');
                    }
                    // hide the span content current text
                    inputText.hide();
                    // ???
                    inputText.attr('style','display:none');
                    if(inputEditableElement.hasClass('hidden')){
                        // displaying the input text for editing
                        inputEditableElement.removeClass('hidden');
                    } else {
                        // ???
                        inputEditableElement.show();
                    }
                    // move the text cursor to the end of content inside input text
                    var s = inputEditableElement.val();
                    inputEditableElement.focus();
                    inputEditableElement.val("");
                    inputEditableElement.val(s);
                    // end move the text cursor to the end of content inside input text
                    break;
            }
            // @NTN code for make scroll
            window.fntQEPP.TableScrollHandler.hideExtraScroll();
            // end @NTN code for make scroll
            return false;
        };

        var skuAjaxSender = function(e,oldSku,productId){
            var sku = e.val();
            if(checkSkuExistsGlobal(sku)){
                alert(initialize_variables.message_show.message_SKU_exists_global);
                e.val(oldSku);
                e.closest('div').find('.input-text').text(oldSku);
                return true;
            }
            var inputClass = e.attr('class');
            inputClass += ' sku-edit-inline';
            e.attr('class',inputClass);
            if(sku.length <= 0 ){
                return false;
            }
            var dataPass = {
                data : {sku : sku, old_sku: oldSku, post_type : 'product', product_id: productId},
                action: 'fnt_product_manage',
                real_action: 'check_sku'
            };
            window.fntQEPP.Core.ajaxRequestManual(dataPass, handleResponseCheckSku);
            return false;
        };

        var handleResponseCheckSku = function(response){
            var element = $('#the-list').find('.sku-edit-inline');
            var inputHidden = element.closest('div').find('.input-text-editable');
            if(inputHidden.hasClass('sku-edit-inline')){
                inputHidden.removeClass('sku-edit-inline');
            }
            if(response.result === "SUCCESS"){
            } else {
                alert(response.data.message);
                var selectedElement = element.closest('div').find('.input-text');
                selectedElement.text(response.data.old_sku);
                var productRowId = element.closest('tr').find('.product-single-checkbox-row').attr('data-product-row-id');
                var columnName = element.closest('td').find('div').attr('data-product-field-name');
                inputHidden.val(response.data.old_sku);
                fnt_product_data[productRowId][columnName] = response.data.old_sku;
            }
            return false;
        };

        var handleProductTagsSelection = function(e, inputEditableElement){
            var currentObject = $(e.currentTarget);
            var currentObjectHeight = currentObject.height();
            var currentInputTag = $(e.currentTarget).find('.input-text');
            var inputTagContent = currentInputTag.text().trim();
            var tagSplit = "";
            if(inputTagContent.length > 0){
                tagSplit = inputTagContent.split(',');
            }
            inputEditableElement.closest('.wrapper-product-tags').removeClass('hidden');
            currentInputTag.hide();
            if(tagSplit.length > 0){
                inputEditableElement.focus().val(tagSplit.join(',') + ',');
            } else {
                inputEditableElement.val('');
                inputEditableElement.focus();
            }
            var cellElement = $(e.currentTarget);
            // set text area full of table cell
            var textArea = currentObject.find("textarea.product-tags");
            var buttonOK = currentObject.find("a.btn-push-tags");
            textArea.css({'height': (currentObjectHeight - buttonOK.height() - 9)+ 'px'});
            // end set text area full of table cell
            return false;
        };

        var inputEditableFocusOutHandler = function(e){
            var cellElement = $(e.currentTarget).closest('td');
            if(cellElement.find('.wrap-input-text .input-text-editable').hasClass('product-tags')){
                return false;
            }
            handleFocusOutForInputEditableSelectedElement(cellElement);
            return false;
        };

        var handleFocusOutForInputEditableSelectedElement = function(selectedElement){
            if(typeof(selectedElement) === "undefined"
                || selectedElement === null
                || $(selectedElement).find('.input-text-editing').length === 0
            ){
                return false;
            }
            var selectedElementObj = $(selectedElement);
            var tableCell = selectedElementObj.find('.wrap-input-text');
            // get element of input which need to interact
            var tableCellElement = selectedElementObj.find('.wrap-input-text .input-text');
            var tableCellInputHidden = selectedElementObj.find('.wrap-input-text .input-text-editable');
            var tableCellInputModifying = selectedElementObj.closest('tr').find('th .input-is-modifying');
            // get new value from input text editable
            var newValue = tableCellInputHidden.val();
            var columnName = selectedElementObj.find('.wrap-input-text').attr('data-product-field-name');
            var newText = selectedElementObj.find('.wrap-input-text option:selected').text();
            var oldValue = tableCellElement.text();
            var flag = true;
            var productRowId = selectedElementObj.closest('tr').find('.product-single-checkbox-row').attr('data-product-row-id');
            newText = (typeof newText === 'undefined' || newText === null || newText === '') ? newValue : newText;
            if(newText !== oldValue && flag ){
                // call function to update global variable of product data : fnt_product_data
                // params will parse : row-id column-name and new-value
                if(columnName === product_column_definition.product_title && newValue === ''){
                    tableCellInputHidden.val(oldValue);
                    alert(initialize_variables.message_show.message_product_name_not_empty);
                }else {
                    var continueProcessing = true;
                    if(columnName === product_column_definition.product_meta_sku){
                        var productId = fnt_product_data[productRowId][product_column_definition.product_id];
                        if(tableCellInputHidden.val().length > 0){
                            var resultCheckExistingSku = skuAjaxSender(tableCellInputHidden,fnt_product_data[productRowId][product_column_definition.product_meta_sku],productId);
                            continueProcessing = !resultCheckExistingSku;
                        }
                    }
                    if(columnName === product_column_definition.product_meta_regular_price){
                        if(newValue === fnt_product_data[productRowId][columnName]){
                            continueProcessing = false;
                        }
                    }
                    if(columnName === product_column_definition.product_meta_sale_price){
                        if(newValue === fnt_product_data[productRowId][columnName]){
                            continueProcessing = false;
                        }
                    }
                    if(continueProcessing){
                        if (columnName === product_column_definition.product_meta_regular_price ||
                            columnName === product_column_definition.product_meta_sale_price) {
                            if($.isNumeric(newText)){
                                newValue = parseFloat(newText);
                                tableCellElement.text(window.fntQEPP.Numeral.getFormattedPriceByCurrency(newText));
                            }else{
                                if(newText.length > 0){
                                    newValue = fnt_product_data[productRowId][columnName];
                                }else{
                                    tableCellElement.text('');
                                }
                            }
                        } else if(columnName === product_column_definition.stock_quantity){
                            if($.isNumeric(newText)){
                                newValue = parseInt(newText);
                                tableCellElement.text(newValue);
                            }else{
                                tableCellElement.text('0');
                                newValue = "0";
                            }
                        }
                        else {
                            tableCellElement.text(newText);
                        }
                        changValueProductList(productRowId, columnName, newValue);
                        module.changeRowColor(tableCell, 'modifying');
                        tableCellInputHidden.val(newValue);
                        tableCellInputModifying.val('yes');
                    }
                }
            }
            setTimeout(function(){
                tableCellInputHidden.addClass('hidden');
                tableCellElement.show();
                if(tableCellInputHidden.hasClass('input-text-editing')){
                    tableCellInputHidden.removeClass('input-text-editing');
                }
                // @NTN code for make scroll
                window.fntQEPP.TableScrollHandler.hideExtraScroll();
                // end @NTN code for make scroll
            }, 200);
            setTimeout(function(){
                if(tableCell.hasClass('cell-modifying')){
                    tableCell.removeClass('cell-modifying')
                }
            }, 200);
        };

        var checkSkuExistsGlobal = function(skuValue){
            var result = false;
            _.each(fnt_product_data, function(item, index){
                if(typeof item.sku != 'undefined'){
                    if(item.sku === skuValue){
                        result = true;
                        return false;
                    }
                }
            });
            return result;
        };

        var changValueProductList = function(rowId, changKey, changValue){
            fnt_product_data[rowId]['modifying_product'] = '1';
            fnt_product_data[rowId][changKey] = changValue;
            productModifying = true;
        };

        module.changValueProductListGlobal = function(rowId, changKey, changValue){
            fnt_product_data[rowId]['modifying_product'] = '1';
            fnt_product_data[rowId][changKey] = changValue;
            productModifying = true;
        };

        var compareArrayString = function(arrayOld, arrayNew){
            var strOld = arrayOld.sort().toString();
            var strNew = arrayNew.sort().toString();
            if(strOld.length !== 0 && strNew.length === 0){
                return false;
            }
            if(strOld === strNew){
                return true;
            }
            return false;
        };

        var validateInputPrice = function(e){
            var cellElement = $(e.currentTarget);
            var changeBackground = cellElement.closest('td');
            var columnName = cellElement.closest('td').find('div').attr('data-product-field-name');
            var productRowId = cellElement.closest('tr').find('.product-single-checkbox-row').attr('data-product-row-id');
            var salePrice = fnt_product_data[productRowId][product_column_definition.product_meta_sale_price];
            var regularPrice = fnt_product_data[productRowId][product_column_definition.product_meta_regular_price];
            salePrice = salePrice.toString().length > 0 ? salePrice : 0;
            regularPrice = regularPrice.toString().length > 0 ? regularPrice : 0;
            if(parseFloat(salePrice) > parseFloat(regularPrice) ){
                changeColorBackground(changeBackground, columnName, true);
            }else{
                changeColorBackground(changeBackground, columnName, false);
            }
        };

        var changeColorBackground = function(changeBackground, columnName, setAction){
            var nextCellElement = '';
            var cellElement = '';
            cellElement = changeBackground.find('div.cell-edit-inline');
            if(setAction){
                if(columnName === product_column_definition.product_meta_sale_price){
                    cellElement.attr('style','background-color:' + 'red' + '!important');
                    cellElement.attr('title', 'Sale price must be smaller than Regular price');
                }else{
                    nextCellElement = changeBackground.next('td').find('div.cell-edit-inline');
                    nextCellElement.attr('style','background-color:' + 'red' + '!important');
                    nextCellElement.attr('title', 'Sale price must be smaller than Regular price');
                }
            }else{
                var renderDefault = '';
                if(columnName === product_column_definition.product_meta_sale_price){
                    cellElement.removeAttr('style');
                    cellElement.removeAttr('title');
                    renderDefault = changeBackground.find('.cell-edit-inline');
                }else{
                    nextCellElement = changeBackground.next('td').find('div.cell-edit-inline');
                    nextCellElement.removeAttr('style');
                    nextCellElement.removeAttr('title');
                    if(nextCellElement.hasClass('background-validate-sale-price')){
                        nextCellElement.removeClass('background-validate-sale-price');
                    }
                    renderDefault = nextCellElement.find('.cell-edit-inline');
                }
                if(renderDefault.hasClass('background-validate-sale-price')){
                    renderDefault.removeClass('background-validate-sale-price');
                }
            }
        };

        module.changeRowColor = function (currentCell, actionColor) {
            var bgColor = "";
            var hasColorClass = "";
            switch (actionColor){
                case 'modifying':
                    bgColor = window.fntQEPP.Settings.getSetting('modifyingRowColor','#cfe4f9');
                    hasColorClass = 'modifying-row-color';
                    break;
                case 'adding':
                    bgColor = window.fntQEPP.Settings.getSetting('addingRowColor','#b6e0b6');
                    hasColorClass = 'adding-row-color';
                    break;
                default :
                    break;
            }
            $(currentCell).closest('tr').attr('style','background-color:' + bgColor + '!important');
            if(!$(currentCell).closest('tr').hasClass(hasColorClass)){
                $(currentCell).closest('tr').addClass(hasColorClass)
            }
        };

        module.updateRowColorViaUpdatedSetting = function () {
            $('.modifying-row-color').attr('style','background-color:' + window.fntQEPP.Settings.getSetting('modifyingRowColor','#cfe4f9') + '!important');
            $('.adding-row-color').attr('style','background-color:' + window.fntQEPP.Settings.getSetting('addingRowColor','#b6e0b6') + '!important');
        };

        return module.init();
    })();
})(jQuery);