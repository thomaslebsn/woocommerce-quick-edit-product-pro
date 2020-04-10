/*
This file is use for variable and variations product
 */

(function ($) {
    window.fntQEPP =  window.fntQEPP || {};
    window.fntQEPP.ProductVariationHanlder = (function(){
        var module = {};
        var currentParentProductID = 0; // current parent product
        var currentSelectedProductID = []; // content list selected ID of row checked
        module.init = function() {
            // do init
            $(document).ready(function(){
                var tableBody = $('#the-list');
                var buttonType = tableBody.find('button.button-show-variation');
                buttonType.off('click').on('click', expandRow);
                // when clicked on button "Save" or button "Cancel"
                $('.variation-button-cta').off('click').on('click', variationCallToAction);

                // when change selection of attributes
                $('select.variation-attributes').off('change').on('change', changeAttribute);

                // When click checkbox in header, then check all check box of variations table
                $('input.cb-select-all-variations').off('click').on('click', clickCheckBoxHeader);

                // When click button delete of variations table
                $('button.button-delete-variations').off('click').on('click', deleteProductVariations);
                // When click button add new of variations table
                $('button.button-add-variation').off('click').on('click', addProductVariation);

                // catch window resize to resize body of popup
                $(window).resize(function () {
                    // change height of body popup
                    changPopupBody();
                });
            });
            return module;
        };

        /**
         * Check current table have variations is modifying or not?
         */
        var productVariationsModifying = function() {
            var currentTableBody = $('#the-list-variations-of-' + currentParentProductID);
            var listRowModifying = currentTableBody.find('tr.modifying-row-color');
            return listRowModifying.length > 0;
        };

        /**
         * Add new product variation
         */
        var addProductVariation = function() {
            if ( productVariationsModifying() ) {
                alert( initialize_variables.message_show.caution_when_have_variation_modifying );
                return;
            }
            var dataPass = {
                action: 'fnt_product_manage',
                real_action: 'add_blank_variation_product',
                screen_id: list_args.screen.id,
                product_id: currentParentProductID
            };
            window.fntQEPP.Core.ajaxRequestManual(dataPass, handleResponseAddVariation, function() {
                window.fntQEPP.Core.showLoading();
            });
        };

        var handleResponseAddVariation = function(response) {
            // Check result of response, if FAILED => reject
            if ( response.result == 'FAILED') {
                alert( initialize_variables.message_show.save_product_error + response.data );
                window.fntQEPP.Core.hideLoading();
                return;
            }
            var variationsData = response.data;
            window.fntQEPP.ProductVariableHanlder.replaceVariationsHtmlJs( variationsData, currentParentProductID );

            // since ver 1.1
            window.fntQEPP.ProductVariationHanlder.init();
            // end since ver 1.1
            window.fntQEPP.ProductListHandler.reInitInputMask();
            // hide icon loading
            window.fntQEPP.Core.hideLoading();

            productModifying = window.fntQEPP.Core.productModifyingCheck();
        };

        /*
         change popup body height
         */
        var changPopupBody = function() {
            var variationsContentWrapper = $( '#variations-content-wrapper-of-' + currentParentProductID );

            var popupBody = variationsContentWrapper.find('.popup-variations-wrapper .modal-body');
            var popupHeader = variationsContentWrapper.find('.popup-variations-wrapper .modal-header');
            var popupFooter = variationsContentWrapper.find('.popup-variations-wrapper .modal-footer');

            // ignore if target is not set
            if ( typeof popupHeader.offset() == 'undefined' ) {
                return;
            }

            var popupHeaderTop = popupHeader.offset().top;
            var popupHeaderHeight = popupHeader.height();
            var popupHeaderBottom = popupHeaderTop + popupHeaderHeight;
            var popupFooterTop = popupFooter.offset().top;
            var popupBodyHeight = popupFooterTop - popupHeaderBottom;
            if ( typeof popupBody != 'undefined' ) {
                // change height of body
                popupBody.css({'height': (popupBodyHeight) + 'px'});
                // move content scroll to top
                popupBody.scrollTop(0);
            }
        };
        /**
         * Delete all product variations checked
         * @param e
         * @returns {boolean}
         */
        var deleteProductVariations = function( e ) {
            if ( productVariationsModifying() ) {
                alert( initialize_variables.message_show.caution_when_have_variation_modifying );
                return;
            }
            var currentButton = $( e.currentTarget );
            var variationContentWrapper = currentButton.closest( 'div.variations-content-wrapper' );
            var checkBoxChecked = variationContentWrapper.find( 'table tbody tr td input.product-single-checkbox-row:checked' );

            var currentRow = currentButton.closest('tr.variation-wrapper-row');
            var parentID = currentRow.attr('parent-id');
            currentParentProductID = parentID;
            var selectedProductID = [];
            _.each ( checkBoxChecked, function( item, index ) {
                selectedProductID.push( item.value );
            } );

            if ( selectedProductID.length <= 0 ) {
                alert( initialize_variables.message_show.no_items_selected );
                return false;
            }

            currentSelectedProductID = selectedProductID;

            var dataPass = {
                action: 'fnt_product_manage',
                real_action: 'delete_product_variations',
                selectedProductID: selectedProductID,
                screen_id: list_args.screen.id,
                product_id: parentID
            };
            var confirmationDelete = window.fntQEPP.Settings.isEnabledConfirmationDeletingProducts();
            if ( confirmationDelete ) {
                var conf = confirm( initialize_variables.message_show.message_delete_product );
                if ( conf ) {
                    window.fntQEPP.Core.ajaxRequestManual( dataPass, handleResponseByDeleteProductVariation, function(){
                        window.fntQEPP.Core.showLoading();
                    } );
                }
            } else {
                window.fntQEPP.Core.ajaxRequestManual( dataPass, handleResponseByDeleteProductVariation, function(){
                    window.fntQEPP.Core.showLoading();
                } );
            }
        };

        /**
         * @param response
         */
        var handleResponseByDeleteProductVariation = function( response ) {
            // Check result of response, if FAILED => reject
            if ( response.result == 'FAILED') {
                alert( initialize_variables.message_show.save_product_error + response.data );
                window.fntQEPP.Core.hideLoading();
                return;
            }
            var variationsData = response.data;
            replaceVariationsHtmlJs( variationsData );

            // since ver 1.1
            window.fntQEPP.ProductVariationHanlder.init();
            // end since ver 1.1
            window.fntQEPP.ProductListHandler.reInitInputMask();
            // hide icon loading
            window.fntQEPP.Core.hideLoading();

            productModifying = window.fntQEPP.Core.productModifyingCheck();
        };

        /**
         * Use for replace html of variations area and js variable when use feature delete product variable
         * @param variationsData
         */
        function replaceVariationsHtmlJs( variationsData ) {
            if ( typeof variationsData != 'undefined' ) {
                var variationsDataHtml = variationsData['variations_html_data'];

                if ( typeof variationsDataHtml != 'undefined' && currentParentProductID != 0 && currentSelectedProductID.length > 0 ) {
                    // replace html of variations area
                    $( '#the-list-variations-of-' + currentParentProductID ).html( variationsDataHtml );
                    // delete variations data js
                    _.each ( currentSelectedProductID, function ( value, index ) {
                        // check if in fnt_product_data isset index
                        if ( typeof fnt_product_data['row-' + value] != 'undefined' ) {
                            delete fnt_product_data['row-' + value];
                        }
                    } );
                }
            }
        }

        // When click checkbox on header, then check all checkbox of table variations
        var clickCheckBoxHeader = function( e ) {
            var currentCheckBox = $(e.currentTarget);
            var currentCheckBoxChecked = typeof currentCheckBox.attr('checked') != 'undefined';
            var currentTable = currentCheckBox.closest('table.wp-list-variation-table');

            var allCheckBoxOfTable = currentTable.find('input.product-single-checkbox-row');

            // check all this checkbox
            allCheckBoxOfTable.prop("checked", currentCheckBoxChecked);
        };

        /**
         * Catch event modify attribute, change attribute of product in current row
         * @param e
         */
        var changeAttribute = function( e ) {
            var currentTarget = $(e.currentTarget); // get current target
            var attrName = currentTarget.attr('name'); // get current attribute name
            var selectedValue = currentTarget.val(); // get selected value of select box
            var columnName = currentTarget.closest('td').find('.wrap-input-text').attr('data-product-field-name');
            var productRowId = currentTarget.closest('tr').find('.product-single-checkbox-row').attr('data-product-row-id');
            var currentProduct = fnt_product_data[productRowId]; // get current Product
            var productAttr = currentProduct[columnName]; // get product attribute
            productAttr[attrName] = selectedValue; // change attribute value
            changValueProductList(productRowId, columnName, productAttr); // change value in global list of product data
            window.fntQEPP.ProductListHandler.changeRowColor(currentTarget, 'modifying'); // change color and modify status of this row/product
        };

        // copy from file product-list-handler.js
        // use for change value of each item in array fnt_product_data
        var changValueProductList = function(rowId, changKey, changValue){
            fnt_product_data[rowId]['modifying_product'] = '1';
            fnt_product_data[rowId][changKey] = changValue;
            productModifying = true;
        };

        // when clicked on button "Save" or button "Cancel"
        var variationCallToAction = function(e) {
            e.preventDefault();
            var currentTarget = $(e.currentTarget);
            var parentId = currentTarget.attr('parent-id');
            var variationRow = $('.variations-row-of-' + parentId);
            // if clicked on button "Cancel"
            if ( currentTarget.hasClass('button-cancel-variation') ) {
                if ( ! variationRow.hasClass('hidden') ) {
                    variationRow.addClass('hidden');
                    window.fntQEPP.Core.showBodyScroll();
                }
            }
            // if clicked on button "Hide variations"
            if ( currentTarget.hasClass('button-hide-variation') ) {
                if ( ! variationRow.hasClass('hidden') ) {
                    variationRow.addClass('hidden');
                    window.fntQEPP.Core.showBodyScroll();
                }
            }
            // if clicked on button "Save"
            if ( currentTarget.hasClass('button-save-variation') ) {
                // save variations product via ajax
                saveProductVariations();
            }
            window.fntQEPP.TableScrollHandler.hideExtraScroll();
        };

        /**
         * Save product variations modifying of current product
         * @returns {boolean}
         */
        var saveProductVariations = function() {
            // get list product id will must update
            var currentTableBody = $('#the-list-variations-of-' + currentParentProductID);
            var listRowModifying = currentTableBody.find('tr.modifying-row-color');

            var listProductRowID = [];
            _.each( listRowModifying, function( item ) {
                var productRowID = $(item).find('td.column-product_id input.product-single-checkbox-row').attr('data-product-row-id');
                listProductRowID.push( productRowID );
            } );

            // if don't have data
            if ( listProductRowID.length <= 0 ) {
                //alert(initialize_variables.message_show.no_change_detected);
                saveDefaultAttribute( true );
                return false;
            }

            var fullDataPassed = [];
            var validateProductName = true;
            //fnt_product_data
            _.each(listProductRowID, function(item) {
                var itemObject = $.extend({}, fnt_product_data[item]);
                fullDataPassed.push(itemObject);
            });

            // column is not compress
            // because product attributes can content character ", example: screen size(12", 32", 120",...)
            var column_not_compress = ['product_content', 'product_excerpt', 'variation_description', 'product_attributes'];
            // split product content and excerpt or variation_description form allDataPassed
            var dataDesc = [];
            _.each(fullDataPassed, function(item) {
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

            var dataPass = {
                all_product_data: window.fntQEPP.Core.compressDataForAJAXSend(fullDataPassed),
                all_product_content: dataDesc,
                just_import: false,
                save_all: false,
                action: 'fnt_product_manage',
                real_action: 'edit_multiple',
                save_variation: true
            };

            window.fntQEPP.Core.ajaxRequestManual(dataPass, handleResponseSaveVariations, function(){
                window.fntQEPP.Core.showLoading();
            });
        };

        /**
         * Set default attribute when load product page
         * @param showLoading
         */
        var saveDefaultAttribute = function( showLoading ) {
            if ( typeof showLoading == 'undefined' ) { // set default value showLoading is false
                showLoading = false;
            }
            var defaultAttributeList = $('#variations-content-wrapper-of-'+currentParentProductID+' .variations-default-attributes select.default-attribute');
            var selectedAttributes = {};
            _.each( defaultAttributeList, function(item) {
                var name = $(item).attr('attribute-name');
                selectedAttributes[name] = $(item).val();
            } );
            if ( selectedAttributes.length <= 0 ) {
                return;
            }
            var dataPass = {
                selectedAttributes: selectedAttributes,
                productID: currentParentProductID,
                action: 'fnt_product_manage',
                real_action: 'save_default_attributes'
            };

            window.fntQEPP.Core.ajaxRequestManual(dataPass, handleResponseSaveDefaultAttributes, function() {
                if ( showLoading ) {
                    window.fntQEPP.Core.showLoading();
                }
            });
        };

        var handleResponseSaveDefaultAttributes = function(response) {
            window.fntQEPP.Core.hideLoading();
            if ( response.result === "FAILED" && response.data != 'value_not_changed' ) {
                alert(response.data);
            }
        };

        var handleResponseSaveVariations = function(response){
            saveDefaultAttribute();

            if ( response.result === "FAILED" ) {
                if(!_.isEmpty(response.data) && !_.isEmpty(response.data.error_array)){
                    alert(response.data.error_array);
                } else {
                    alert('Save failed!');
                }
            } else {
                replaceNewVariablePrice();
                // update
                // get list product id will must update
                var currentTableBody = $('#the-list-variations-of-' + currentParentProductID);
                var listRowModifying = currentTableBody.find('tr.modifying-row-color');

                _.each( listRowModifying, function( item ) {
                    // remove class modifying and row color for row just update
                    if ( $(item).hasClass('modifying-row-color') ) {
                        $(item).removeClass('modifying-row-color');
                        $(item).removeAttr('style');
                    }
                    var productRowID = $(item).find('td.column-product_id input.product-single-checkbox-row').attr('data-product-row-id');
                    // update fnt_product_data, change modifying_product of item just update to -1
                    if ( typeof fnt_product_data[productRowID]['modifying_product'] != 'undefined' ) {
                        fnt_product_data[productRowID]['modifying_product'] = -1;
                    }
                } );
            }
            productModifying = window.fntQEPP.Core.productModifyingCheck();

            return false;
        };

        /**
         * When change variation products price, sync price with parent variable product
         * Replace old variable product price with new variable price sync
         */
        var replaceNewVariablePrice = function() {
            var dataPass = {
                currentVariableID: currentParentProductID,
                action: 'fnt_product_manage',
                real_action: 'get_variable_price_change'
            };

            window.fntQEPP.Core.ajaxRequestManual(dataPass, handleResponseGetVariablePriceChange, function(){
                window.fntQEPP.Core.showLoading();
            });
        };
        var handleResponseGetVariablePriceChange = function(response) {
            if ( response.result === "SUCCESS" ) {
                // Replace variable price data
                var tableBody = $('#the-list');
                var currentVariableRow = tableBody.find('.main-row-' + currentParentProductID );
                var salePriceCell = currentVariableRow.find('.sale_price');
                var regularPriceCell = currentVariableRow.find('.regular_price');

                salePriceCell.html(response.data['sale_price_html']);
                regularPriceCell.html(response.data['regular_price_html']);
            } else {
                alert('Save failed!');
            }
            window.fntQEPP.Core.hideLoading();
        };

        // Expand or Collapse
        var expandRow = function( e ) {
            var currentButton = $(e.currentTarget);
            var productId = currentButton.attr('product-id');
            currentParentProductID = productId;
            var variationRow = $('.variations-row-of-' + productId);

            if ( variationRow.hasClass('hidden') ) {
                variationRow.removeClass('hidden');
            } else {
                variationRow.addClass('hidden');
            }
            window.fntQEPP.TableScrollHandler.hideExtraScroll();
            changPopupBody();
            window.fntQEPP.Core.hideBodyScroll();
        };

        // Fit variation area with parent table
        var fitVariationArea = function() {
            // get parent table
            // get table width
            // get div target
            // call scroll event
            // set div target position
            // end
        };

        return module.init();
    })();
})(jQuery);