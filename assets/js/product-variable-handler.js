/*
 This file is use for variable and variations product
 */

(function ($) {
    window.fntQEPP =  window.fntQEPP || {};
    window.fntQEPP.ProductVariableHanlder = (function(){
        var module = {};
        var currentProductID = 0;
        var currentProductType = '';
        module.init = function() {
            // do init
            $(document).ready(function(){
                $('.button-save-attributes').off('click').on('click', saveProductAttributes);
                $('.remove-attribute').off('click').on('click', removeProductAttributes);
                $('.button-add-attribute').off('click').on('click', addProductAttribute);

                // When click button Modify attributes of variable product
                $('button.button-show-attributes').off('click').on('click', showAttributesArea);

                $('button.button-hide-attributes').off('click').on('click', hideAttributesArea);

                // when select an option in dropdown
                $('select.attribute-select-item').off('change').on('change', changeAttributesValue);
                $('select.attribute-select-item').prop('selectedIndex', -1); // set default selected option is blank

                // when click icon remove in an attribute item
                $('span.remove-attribute-item').off('click').on('click', removeAttributeItem);

                // catch window resize to resize body of popup
                $(window).resize(function () {
                    // change height of body popup
                    changPopupBody();
                });

                // when click button "Add new" attribute term
                $('button.button-add-attribute-term').off('click').on('click', addAttributeTerm);
            });
            return module;
        };

        var addAttributeTerm = function(e) {
            e.preventDefault();
            var currentTarget = $(e.currentTarget);
            var currentTaxonomy = currentTarget.attr('taxonomy');

            bootbox.prompt(initialize_variables.message_show.message_add_new_attribute_term, function(term) {
                if ( term === null ) {
                    // do stuff
                } else if ( term == '' ) {
                    alert(initialize_variables.message_show.caution_message_attribute_term_name_empty);
                } else {
                    // ajax call
                    var dataPass = {
                        action: 'fnt_product_manage',
                        real_action: 'add_attribute_term',
                        taxonomy: currentTaxonomy,
                        term: term
                    };
                    window.fntQEPP.Core.ajaxRequestManual( dataPass, handleResponseAddAttributeTerm, function() {
                        window.fntQEPP.Core.showLoading();
                    });
                }
            });
        };

        var handleResponseAddAttributeTerm = function(response) {
            // Check result of response, if FAILED => reject
            if ( response.result == 'FAILED') {
                alert( response.data );
                window.fntQEPP.Core.hideLoading();
                return;
            }
            processDataAttributeItem( response.data );
            window.fntQEPP.Core.hideLoading();
        };

        var processDataAttributeItem = function( data ) {
            var termName = data.name;
            var termSlug = data.slug;
            var taxonomy = data.taxonomy;
            var spanBlockItem = makeAttributeItem( termName, termSlug );
            var selectSingleOption = '<option class="hidden" value="'+termSlug+'">'+termName+'</option>';
            var selectValueOption = '<option value="'+termSlug+'" selected="selected">'+termName+'</option>';

            var currentRow = $('tr.attribute-row.'+taxonomy+currentProductID);

            var selectSingle = currentRow.find('td.column-value select.attribute-select-item');
            var selectValue = currentRow.find('td.column-value select.attribute-value');
            var spanBlock = currentRow.find('td.column-value div.attribute-items');

            // add new item for them
            selectSingle.append(selectSingleOption);
            selectValue.append(selectValueOption);
            spanBlock.append(spanBlockItem);

            module.initWhenAddDefaultAttribute();
        };

        /*
         change popup body height
         */
        var changPopupBody = function() {
            var attributesContentWrapper = $( '#attributes-content-wrapper-of-' + currentProductID );

            var popupBody = attributesContentWrapper.find('.popup-attributes-wrapper .modal-body');
            var popupHeader = attributesContentWrapper.find('.popup-attributes-wrapper .modal-header');
            var popupFooter = attributesContentWrapper.find('.popup-attributes-wrapper .modal-footer');

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

        module.reInitEvents = function() {
            $('button.button-show-attributes').off('click').on('click', showAttributesArea);
        };

        module.initWhenAddDefaultAttribute = function() {
            $('.remove-attribute').off('click').on('click', removeProductAttributes);
            // when select an option in dropdown
            $('select.attribute-select-item').off('change').on('change', changeAttributesValue);
            $('select.attribute-select-item').prop('selectedIndex', -1); // set default selected option is blank

            // when click icon remove in an attribute item
            $('span.remove-attribute-item').off('click').on('click', removeAttributeItem);

            // when click button "Add new" attribute term
            $('button.button-add-attribute-term').off('click').on('click', addAttributeTerm);
        };

        var makeAttributeItem = function( item_name, item_slug ) {
            return '<span class="wrapper-attribute-item bootstrap-wrapper">' +
                       '<span class="glyphicon glyphicon-remove remove-attribute-item" item-slug="'+item_slug+'"></span>'+
                        item_name +
                   '</span>';
        };

        // use for add item to default attribute have type select
        var changeAttributesValue = function( e ) {
            var currentTarget = $(e.currentTarget);
            var currentCell = currentTarget.closest('td');
            var selectedAttributeValue = currentCell.find('select.attribute-value');
            var currentDivAttributeItems = currentCell.find('div.attribute-items');
            var selectedValue = currentTarget.val();

            // change status of selected attribute item to "selected"
            var optionChange = selectedAttributeValue.find('option[value="' + selectedValue +'"]');
            optionChange.attr('selected', 'selected');
            // hidden selected item in dropdown list
            var optionSelected = currentTarget.find('option[value="' + selectedValue +'"]');
            optionSelected.addClass('hidden');

            var optionSelectedHtml = optionSelected.html();
            currentTarget.prop('selectedIndex', -1); // set default selected option is blank
            // add item to div attribute-items
            var newAttributeItem = makeAttributeItem( optionSelectedHtml, selectedValue );
            currentDivAttributeItems.append( newAttributeItem );
            // reinit event of icon remove of item
            $('span.remove-attribute-item').off('click').on('click', removeAttributeItem);
        };

        var removeAttributeItem = function( e ) {
            var currentTarget = $(e.currentTarget);
            var currentCell = currentTarget.closest('td');
            var attributeValueSelection = currentCell.find('select.attribute-value');
            var singleAttributeSelection = currentCell.find('select.attribute-select-item');

            var selectedSlug = currentTarget.attr('item-slug');

            // remove current attribute item
            var closestSpan = currentTarget.closest('span.wrapper-attribute-item');
            closestSpan.remove();

            // show item just remove in single selection box
            var singleOptionItem = singleAttributeSelection.find('option[value="' + selectedSlug +'"]');
            singleOptionItem.removeClass('hidden');

            // unselected option in final selection box
            var valueOptionItem = attributeValueSelection.find('option[value="' + selectedSlug +'"]');
            valueOptionItem.removeAttr('selected');
        };

        var hideAttributesArea = function( e ) {
            var currentButton = $(e.currentTarget);
            var productId = currentButton.attr('product-id');
            var variationRow = $('.attributes-row-of-' + productId);
            if ( ! variationRow.hasClass('hidden') ) {
                variationRow.addClass('hidden');
            }

            window.fntQEPP.TableScrollHandler.hideExtraScroll();
            window.fntQEPP.Core.showBodyScroll();
        };
        /**
         * Open area to allow edit attributes for variable product
         * @param e
         */
        var showAttributesArea = function( e ) {
            var currentButton = $(e.currentTarget);
            currentProductID = currentButton.attr('product-id');
            currentProductType = currentButton.attr('product-type');

            var variationRow = $('.attributes-row-of-' + currentProductID);

            if ( variationRow.hasClass('hidden') ) {
                variationRow.removeClass('hidden');
            } else {
                variationRow.addClass('hidden');
            }
            window.fntQEPP.TableScrollHandler.hideExtraScroll();
            changPopupBody();
            window.fntQEPP.Core.hideBodyScroll();
        };

        var selectedValueAttributeType = '';

        var addProductAttribute = function( e ) {
            var currentTarget = $(e.currentTarget);
            var currentTable = currentTarget.closest('.attributes-content-wrapper').find('table.wp-list-attributes-table');
            var currentProductId = currentTable.attr('product-id');
            currentProductID = currentProductId;

            var divWrapper = currentTarget.closest('.tablenav');
            var selectionAttributeType = divWrapper.find('select.add_attribute_taxonomy');
            selectedValueAttributeType = selectionAttributeType.val();
            // set selected option is first item: Custom product attribute
            selectionAttributeType.prop('selectedIndex', 0);

            var isTaxonomy = selectedValueAttributeType == 'fnt_add_custom_attribute' ? 0 : 1;

            var dataPass = {
                action: 'fnt_product_manage',
                real_action: 'add_attributes',
                screen_id: list_args.screen.id,
                selected_attribute_value: selectedValueAttributeType,
                is_taxonomy: isTaxonomy,
                product_type: currentProductType,
                productID: currentProductId
            };
            window.fntQEPP.Core.ajaxRequestManual( dataPass, handleResponseAddAttribute, beforeSendDataToAddAttributes);
        };

        var handleResponseAddAttribute = function( response ) {
            // Check result of response, if FAILED => reject
            if ( response.result == 'FAILED') {
                alert( initialize_variables.message_show.add_attributes_failed + response.data );
                window.fntQEPP.Core.hideLoading();
                return;
            }

            // disable option in selection attributes
            var closestAttributeWrapper = $('#attributes-content-wrapper-of-' + currentProductID);
            var selectionAttributeType = closestAttributeWrapper.find('.add_attribute_taxonomy');
            var optionTarget = selectionAttributeType.find('option[value="' + selectedValueAttributeType +'"]');
            if ( typeof optionTarget != 'undefined' && ! optionTarget.hasClass('add_custom_attribute') ) {
                optionTarget.attr('disabled', 'disabled');
            }

            var tableBody = $('#the-list-attributes-of-'+ currentProductID);
            var attribute_data = response.data;

            tableBody.find('.no-items').remove(); // remove column No attributes available
            tableBody.append( attribute_data['html'] );

            module.updateRowPosition(); // update position

            module.initWhenAddDefaultAttribute();

            // hide icon loading
            window.fntQEPP.Core.hideLoading();
        };

        var removeProductAttributes = function( e ) {
            var currentTarget = $(e.currentTarget);
            var currentRow = currentTarget.closest('tr.attribute-row');
            var currentAttribute = currentTarget.attr('attribute-key');
            if ( ! currentRow.hasClass('blank_attribute') ) {
                var currentProductId = currentTarget.attr('product-id');
                // remove in fnt_product_attributes
                if ( typeof fnt_product_attributes[currentProductId][currentAttribute] != 'undefined' ) {
                    delete fnt_product_attributes[currentProductId][currentAttribute];
                }
            }

            var currentTableBody = currentRow.closest('tbody');

            // remove current row
            currentRow.remove();
            // update position of attributes
            module.updateRowPosition();
            // update alternate
            window.fntQEPP.Core.updateAlternateTable( currentTableBody );

            // control scroll of table
            window.fntQEPP.TableScrollHandler.hideExtraScroll();

            // enable option in selection attributes
            var closestAttributeWrapper = currentTableBody.closest('.attributes-content-wrapper');
            var selectionAttributeType = closestAttributeWrapper.find('.add_attribute_taxonomy');
            var optionTarget = selectionAttributeType.find('option[value="' + currentAttribute +'"]');
            if ( typeof optionTarget != 'undefined' ) {
                optionTarget.removeAttr('disabled');
            }
            // done
        };

        var columnsChange = [ 'position', 'name', 'value', 'is_visible', 'is_variation' ];

        var saveProductAttributes = function( e ) {
            var currentTarget = $(e.currentTarget);
            var wrapperAttributes = currentTarget.closest('.attributes-content-wrapper');
            var tableAttributes = wrapperAttributes.find('.wp-list-attributes-table');
            var listAttributes = tableAttributes.find('tbody.the-list-attributes tr.attribute-row:not(.hidden, .blank_attribute)');

            var currentProductId = tableAttributes.attr('product-id');
            currentProductID = currentProductId;
            _.each ( listAttributes, function( item, index ) {
                var currentRow = $(item);
                var currentAttribute = currentRow.attr('attribute-key');

                var value = null;
                _.each ( columnsChange, function( item ) {
                    switch ( item ) {
                        case 'position':
                            if ( typeof currentRow.find('.attribute-position').attr('value') != 'undefined' ) {
                                value = currentRow.find('.attribute-position').attr('value');
                                value = parseInt( value ); // convert this from string to integer
                                // change value of each item
                                if ( value != null ) {
                                    fnt_product_attributes[currentProductId][currentAttribute][item] = value;
                                }
                            }
                            break;
                        case 'name':
                            if ( typeof currentRow.find('.attribute-name') != 'undefined' ) {
                                value = currentRow.find('.attribute-name').val();
                                // change value of each item
                                if ( value != null ) {
                                    fnt_product_attributes[currentProductId][currentAttribute][item] = value;
                                }
                            }
                            break;
                        case 'value':
                            if ( typeof currentRow.find('.attribute-value') != 'undefined' ) {
                                value = currentRow.find('.attribute-value').val();
                                if ( typeof value == 'undefined' ) {
                                    value = currentRow.find('.attribute-value').attr('value');
                                }
                                // change value of each item
                                if ( value != null ) {
                                    fnt_product_attributes[currentProductId][currentAttribute][item] = value;
                                }
                            }
                            break;
                        case 'is_visible':
                            if ( typeof currentRow.find('.attribute-is-visible') != 'undefined' ) {
                                value = ( typeof currentRow.find('.attribute-is-visible').attr('checked') != 'undefined'
                                          && currentRow.find('.attribute-is-visible').attr('checked') == 'checked' )
                                            ? 1 : 0;
                                // change value of each item
                                if ( value != null ) {
                                    fnt_product_attributes[currentProductId][currentAttribute][item] = value;
                                }
                            }
                            break;
                        case 'is_variation':
                            if ( typeof currentRow.find('.attribute-is-variation') != 'undefined' ) {
                                value = ( typeof currentRow.find('.attribute-is-variation').attr('checked') != 'undefined'
                                && currentRow.find('.attribute-is-variation').attr('checked') == 'checked' )
                                    ? 1 : 0;
                                // change value of each item
                                if ( value != null ) {
                                    fnt_product_attributes[currentProductId][currentAttribute][item] = value;
                                }
                            }
                            break;
                    }
                } );
            } );

            // add new custom attributes to save
            var listBlankAttributes = getAddingCustomAttributes();
            var cloneProductAttributes = $.extend(true, {}, fnt_product_attributes);
            _.each( listBlankAttributes, function( item ) {
                // if new attribute have name then add to list attributes
                if ( item['name'] != null && item['name'] != '' ) {
                    var itemObject = $.extend({}, item); // convert item from array to object
                    cloneProductAttributes[currentProductId][item['position']] = itemObject;
                }
            } );

            var dataPass = {
                action: 'fnt_product_manage',
                real_action: 'save_attributes',
                screen_id: list_args.screen.id,
                product_id: currentProductId,
                product_type: currentProductType,
                product_attributes: cloneProductAttributes[currentProductId]
            };

            setTimeout(function() {
                window.fntQEPP.Core.ajaxRequestManual( dataPass, handleResponseSaveAttributes, beforeSendDataToSaveAttributes );
            }, 100);
        };

        var getAddingCustomAttributes = function() {
            var currentTableBody = $('#the-list-attributes-of-' + currentProductID);
            var listBlankAttributesRow = currentTableBody.find('tr.attribute-row.blank_attribute:not(.hidden)');

            var listBlankAttributes = [];

            _.each ( listBlankAttributesRow, function( item, index ) {
                var currentRow = $(item);
                var value = null;
                var rowData = [];
                _.each ( columnsChange, function( item ) {
                    switch ( item ) {
                        case 'position':
                            if ( typeof currentRow.find('.attribute-position').attr('value') != 'undefined' ) {
                                value = currentRow.find('.attribute-position').attr('value');
                                value = parseInt( value ); // convert this from string to integer
                                // change value of each item
                                if ( value != null ) {
                                    rowData[item] = value;
                                }
                            }
                            break;
                        case 'name':
                            if ( typeof currentRow.find('.attribute-name') != 'undefined' ) {
                                value = currentRow.find('.attribute-name').val();
                                // change value of each item
                                if ( value != null ) {
                                    rowData[item] = value;
                                }
                            }
                            break;
                        case 'value':
                            if ( typeof currentRow.find('.attribute-value') != 'undefined' ) {
                                value = currentRow.find('.attribute-value').val();
                                // change value of each item
                                if ( value != null ) {
                                    rowData[item] = value;
                                }
                            }
                            break;
                        case 'is_visible':
                            if ( typeof currentRow.find('.attribute-is-visible') != 'undefined' ) {
                                value = ( typeof currentRow.find('.attribute-is-visible').attr('checked') != 'undefined'
                                && currentRow.find('.attribute-is-visible').attr('checked') == 'checked' )
                                    ? 1 : 0;
                                // change value of each item
                                if ( value != null ) {
                                    rowData[item] = value;
                                }
                            }
                            break;
                        case 'is_variation':
                            if ( typeof currentRow.find('.attribute-is-variation') != 'undefined' ) {
                                value = ( typeof currentRow.find('.attribute-is-variation').attr('checked') != 'undefined'
                                && currentRow.find('.attribute-is-variation').attr('checked') == 'checked' )
                                    ? 1 : 0;
                                // change value of each item
                                if ( value != null ) {
                                    rowData[item] = value;
                                }
                            }
                            break;
                    }
                } );
                if ( typeof currentRow.attr('is-taxonomy') != 'undefined' ) {
                    rowData['is_taxonomy'] = parseInt( currentRow.attr('is-taxonomy') );
                } else {
                    rowData['is_taxonomy'] = 0;
                }
                listBlankAttributes.push( rowData );
            } );

            return listBlankAttributes;
        };

        /**
         * @param response
         */
        var handleResponseSaveAttributes = function( response ) {
            // Check result of response, if FAILED => reject
            if ( response.result == 'FAILED') {
                alert( initialize_variables.message_show.save_product_error + response.data );
                window.fntQEPP.Core.hideLoading();
                return;
            }
            if ( currentProductType == 'simple' ) {
                // Product simple only save attributes, not change or replace variations
                window.fntQEPP.Core.hideLoading();
                return;
            }
            var variationsData = response.data;
            module.replaceVariationsHtmlJs( variationsData );

            // since ver 1.1
            window.fntQEPP.ProductVariationHanlder.init();
            // end since ver 1.1
            window.fntQEPP.ProductListHandler.reInitInputMask();
            // hide icon loading
            window.fntQEPP.Core.hideLoading();

            productModifying = window.fntQEPP.Core.productModifyingCheck();
        };
        /**
         * Show icon loading
         */
        var beforeSendDataToSaveAttributes = function(){
            window.fntQEPP.Core.showLoading();
        };
        var beforeSendDataToAddAttributes = function(){
            window.fntQEPP.Core.showLoading();
        };
        module.replaceVariationsHtmlJs = function( variationsData, product_id ) {
            if ( typeof product_id == 'undefined' ) {
                product_id = currentProductID;
            }
            if ( typeof variationsData != 'undefined' ) {
                var variationsDataHtml = variationsData['variations_html_data'];
                var variationsDataJs = variationsData['variations_js_data'];
                var selectionDefaultAttributes = variationsData['selection_default_attributes'];

                if ( typeof variationsDataHtml != 'undefined' && typeof variationsDataJs != 'undefined' && typeof selectionDefaultAttributes != 'undefined' ) {
                    // replace html of variations area
                    $( '#the-list-variations-of-' + product_id ).html( variationsDataHtml );
                    // replace html of selection default attributes
                    $( '#selection-default-attribute-wrapper-' + product_id ).html( selectionDefaultAttributes );
                    // replace variations data js
                    _.each ( variationsDataJs, function ( value, index ) {
                        fnt_product_data[index] = value;
                    } );
                }
            }
        };
        module.updateRowPosition = function() {
            var attributesTableBody = $('#the-list-attributes-of-' + currentProductID);
            var listRow = attributesTableBody.find('tr.attribute-row');
            var new_index = 0;
            _.each ( listRow, function( item ) {
                $(item).find('.attribute-position').attr('value', new_index);
                new_index++;
            } );
        };

        return module.init();
    })();
})(jQuery);