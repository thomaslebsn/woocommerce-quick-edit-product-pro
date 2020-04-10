(function ($) {
    window.fntQEPP = window.fntQEPP || {};
    window.fntQEPP.CategoriesSelectHanlder = (function () {
        var module = {};
        var catSelection;
        var productID = 0;
        var listCategories;

        module.init = function () {
            $(document).ready(function(){
                module.Initialize();
                catSelection = $('.popup-select-product-type-wrapper');
            } );
            return module;
        };
        /**
         * Init events
         * @constructor
         */
        module.Initialize = function() {
            $('.column-product_cat').off('click').on('click', showCatSelection);
            $('.popup-select-product-type-wrapper .close-selection-cat').off('click').on('click', hideCatSelection);
            $('.popup-select-product-type-wrapper .save-selection-cat').off('click').on('click', saveCatSelection);
            $('.popup-select-product-type-wrapper button.button-select-cat').off('click').on('click', getCheckedCategories);
            $('.popup-select-product-type-wrapper input.cat-search').off('keyup').on('keyup', searchCategories);
        };

        var searchCategories = function(e) {
            if ( typeof listCategories == 'undefined' ) {
                return;
            }
            var currentInput = $(e.currentTarget);
            var inputValue = currentInput.val();
            inputValue = inputValue.toLowerCase();
            var correctCategories = [];
            for ( var index in listCategories ) {
                var value = listCategories[index];
                var correctInput = $('#in-product_cat-' + index);
                if ( typeof correctInput != 'undefined' ) {
                    var correctLabel = correctInput.closest('label');
                    if ( value.indexOf( currentInput.val() ) >= 0 ) {
                        if ( correctLabel.hasClass('hidden') ) {
                            correctLabel.removeClass('hidden');
                        }
                    } else {
                        if ( ! correctLabel.hasClass('hidden') ) {
                            correctLabel.addClass('hidden');
                        }
                    }
                }
            }
        };
        var getArrayLabel = function() {
            var checkbox = catSelection.find('input');
            var selectedCheckbox = [];
            _.each ( checkbox, function( item ) {
                var checkbox = $( item );
                // get Name of category
                var currentLabel = checkbox.closest('label');
                var clone = currentLabel.clone();
                clone.find('input').remove();
                var labelName = clone.html();
                if ( typeof labelName != 'undefined') {
                    selectedCheckbox[checkbox.val()] = labelName.trim().toLowerCase();
                }
            } );

            return selectedCheckbox;
        };
        /**
         * Get checked categories of current popup and only show that
         * @param e
         */
        var getCheckedCategories = function(e) {
            var currentButton = $(e.currentTarget);
            if ( currentButton.hasClass('button-checked') || currentButton.hasClass('button-first-init') ) {
                if ( currentButton.hasClass('button-checked') ) {
                    currentButton.removeClass('button-checked');
                }
                if ( currentButton.hasClass('button-first-init') ) {
                    currentButton.removeClass('button-first-init');
                }
                currentButton.html(currentButton.attr('value2'));
                var unSelectedCheckbox = catSelection.find('.modal-body input:checkbox:not(:checked)');
                _.each( unSelectedCheckbox, function( item ){
                    var currentCheckboxLabel = $( item ).closest('label');
                    if ( ! currentCheckboxLabel.hasClass('hidden') ) {
                        currentCheckboxLabel.addClass('hidden');
                    }
                } );
            } else {
                currentButton.addClass('button-checked');
                currentButton.html(currentButton.attr('value1'));
                var labelHidden = catSelection.find('.modal-body label');
                _.each( labelHidden, function( item ){
                    var currentLabel = $( item );
                    if ( currentLabel.hasClass('hidden') ) {
                        currentLabel.removeClass('hidden');
                    }
                } );
            }
        };
        /**
         * Set position for popup categorise
         * @param tableCell
         * @returns {boolean}
         */
        var setPopupPosition = function( tableCell ) {
            if ( typeof tableCell == 'undefined' ) {
                return false;
            }
            var currentPopupContent = catSelection.find('.modal-content');
            var popupTop = getTopPosition( tableCell, currentPopupContent );
            var popupLeftData = getLeftPosition( tableCell, currentPopupContent );
            currentPopupContent.css({'top': (popupTop) + 'px', 'left': (popupLeftData['leftPosition']) + 'px'});
            if ( popupLeftData['arrowLeft'] ) {
                // show left arrow
                showArrowLeft( tableCell );
            } else {
                // show right arrow
                showArrowRight( tableCell );
            }
        };
        /**
         * Get left position to set to popup categories
         * @param tableCell
         * @param currentPopupContent
         * @returns {*}
         */
        var getLeftPosition = function( tableCell, currentPopupContent ) {
            if ( typeof tableCell == 'undefined' || typeof currentPopupContent == 'undefined' ) {
                return false;
            }
            var windowWidth = $(window).width();
            var popupContentWidth = currentPopupContent.width();
            var tableCellLeft = tableCell.offset().left;
            var tableCellRight = tableCellLeft + tableCell.width();
            // set popup position in left of tableCell

            var positionData = [];
            if ( windowWidth - tableCellRight < popupContentWidth ) {
                var leftPosition = tableCellLeft - popupContentWidth - 20;
                positionData['arrowLeft'] = false;
                positionData['leftPosition'] = leftPosition;
                return positionData;
            } else { // set popup position in right of tableCell
                positionData['arrowLeft'] = true;
                positionData['leftPosition'] = tableCellRight + 20;
                return positionData;
            }
        };
        /**
         *
         * @param tableCell, Category cell in table list
         * @param currentPopupContent
         * @returns {number} topPosition will set for popup
         */
        var getTopPosition = function( tableCell, currentPopupContent ) {
            var popupContentHeight = currentPopupContent.height();
            var haftPopupHeight = popupContentHeight/2;
            var tableCellHeight = tableCell.height();
            var haftTableCellHeight = tableCellHeight/2;
            var tableCellTop = tableCell.offset().top;
            var top = tableCellTop - haftPopupHeight - $(window).scrollTop() + haftTableCellHeight;
            if ( top > 0 ) {
                var windowHeight = $(window).height();
                // if topPosition make popup out of bottom window, set topPosition back to 0
                if ( top + popupContentHeight > windowHeight ) {
                    return Math.abs( windowHeight - popupContentHeight - 10 );
                }
                return top;
            } else {
                return 0;
            }
        };
        /**
         * Show icon arrow for popup categories
         * @param tableCell
         * @returns {boolean}
         */
        var showArrowLeft = function( tableCell ) {
            if ( typeof tableCell == 'undefined' ) {
                return false;
            }
            var currentPopupContent = catSelection.find('.modal-content');
            var currentPopupContentTop = currentPopupContent.offset().top;
            var tableCellHeight = tableCell.height();
            var haftTableCellHeight = tableCellHeight/2;
            var tableCellTop = tableCell.offset().top;
            var arrowLeft = catSelection.find('.modal-content .arrow-left');
            var windowScrollTop = $(window).scrollTop();
            var arrowLeftTop = tableCellTop + haftTableCellHeight - currentPopupContentTop - 10;
            arrowLeft.css({'top': arrowLeftTop + 'px'});
            var arrowRight = catSelection.find('.modal-content .arrow-right');
            if ( arrowLeft.hasClass('hidden') ) {
                arrowLeft.removeClass('hidden');
            }
            if ( ! arrowRight.hasClass('hidden') ) {
                arrowRight.addClass('hidden');
            }
        };
        /**
         * Show icon arrow for popup categories
         * @param tableCell
         * @returns {boolean}
         */
        var showArrowRight = function( tableCell ) {
            if ( typeof tableCell == 'undefined' ) {
                return false;
            }
            var currentPopupContent = catSelection.find('.modal-content');
            var currentPopupContentTop = currentPopupContent.offset().top;
            var tableCellHeight = tableCell.height();
            var haftTableCellHeight = tableCellHeight/2;
            var tableCellTop = tableCell.offset().top;
            var arrowLeft = catSelection.find('.modal-content .arrow-left');
            var arrowRight = catSelection.find('.modal-content .arrow-right');
            var windowScrollTop = $(window).scrollTop();
            arrowRight.css({'top': (tableCellTop + haftTableCellHeight - currentPopupContentTop - 10) + 'px'});
            if ( arrowRight.hasClass('hidden') ) {
                arrowRight.removeClass('hidden');
            }
            if ( ! arrowLeft.hasClass('hidden') ) {
                arrowLeft.addClass('hidden');
            }
        };
        /**
         * Show Categories selection
         * @param e
         */
        var showCatSelection = function(e) {
            listCategories = getArrayLabel();
            var currentTarget = $(e.currentTarget);
            var catColumnID = currentTarget.attr("id");
            if(typeof catColumnID !== "undefined" && catColumnID === 'product_cat'){
                return false;
            }
            window.fntQEPP.Core.hideBodyScroll();
            if ( catSelection.hasClass('hidden') ) {
                catSelection.removeClass('hidden');
            }
            setPopupPosition( currentTarget );
            var currentRow = currentTarget.closest('tr');
            productID = currentRow.attr('product-id');
            fillCheckbox();
        };
        /**
         * Hide Categories selection
         * @param e
         */
        var hideCatSelection = function(e) {
            window.fntQEPP.Core.showBodyScroll();
            if ( ! catSelection.hasClass('hidden') ) {
                catSelection.addClass('hidden');
            }
        };
        /**
         * Save Categories selection
         * @param e
         */
        var saveCatSelection = function(e) {
            if ( typeof fnt_product_data === 'undefined' ) {
                console.log('fnt_product_data is undefined');
                return;
            }
            fillCategoryName();
            fnt_product_data['row-' + productID]['product_cat'] = getSelectedCheckbox();
            fnt_product_data['row-' + productID]['modifying_product'] = '1';
            productModifying = window.fntQEPP.Core.productModifyingCheck();
            window.fntQEPP.ProductListHandler.changeRowColor($('.main-row-' + productID).find('td.column-product_cat'), 'modifying');
            hideCatSelection();
        };
        /**
         * Move Categories selected from popup to table cell
         */
        var fillCategoryName = function() {
            var currentRow = $('tr.main-row-' + productID);
            var currentCell = currentRow.find('td.column-product_cat');
            var currentSpanCell = currentCell.find('span');
            currentSpanCell.html( getSelectedCategories() );
        };
        /**
         * Get data in fnt_product_data and fill check box is checked to selection
         */
        var fillCheckbox = function() {
            if ( typeof productID == 'undefined' ) {
                return;
            }
            resetCheckbox();
            var cats = fnt_product_data['row-' + productID]['product_cat'];
            _.each ( cats, function( item ) {
                var checkbox = $('#in-product_cat-' + item);
                if ( typeof checkbox != 'undefined' ) {
                    checkbox.attr( "checked", "checked" );
                }
            } );
        };
        /**
         * Set all checkbox in popup to status uncheck
         */
        var resetCheckbox = function() {
            var checkbox = catSelection.find('input');
            _.each ( checkbox, function( item ) {
                $( item ).removeAttr('checked');
                var currentCheckboxLabel = $( item ).closest('label');
                if ( currentCheckboxLabel.hasClass('hidden') ) {
                    currentCheckboxLabel.removeClass('hidden');
                }
            } );
            // reset button Label
            var popupSelectCatButton = catSelection.find('.button-select-cat');
            if ( popupSelectCatButton.hasClass('button-checked') ) {
                popupSelectCatButton.removeClass('button-checked');
            } else {
                popupSelectCatButton.html(popupSelectCatButton.attr('value1'));
            }
            if ( ! popupSelectCatButton.hasClass('button-first-init') ) {
                popupSelectCatButton.addClass('button-first-init');
            }
            // reset input of search input
            var popupSelectCatSearch = catSelection.find('.cat-search');
            popupSelectCatSearch.val('');
        };
        /**
         * Get ID of categories checked
         * @returns {Array}
         */
        var getSelectedCheckbox = function() {
            var checkbox = catSelection.find('input');
            var selectedCheckbox = [];
            _.each ( checkbox, function( item ) {
                var checkbox = $( item );
                if ( checkbox.attr('checked') == 'checked' ) {
                    selectedCheckbox.push( parseInt( checkbox.val() ) );
                }
            } );
            return selectedCheckbox;
        };
        /**
         * Get the name of categories checked
         * @returns {string}
         */
        var getSelectedCategories = function() {
            var checkbox = catSelection.find('input');
            var selectedCheckbox = [];
            _.each ( checkbox, function( item ) {
                var checkbox = $( item );
                if ( checkbox.attr('checked') == 'checked' ) {
                    // get Name of category
                    var currentLabel = checkbox.closest('label');
                    var clone = currentLabel.clone();
                    clone.find('input').remove();
                    var html = clone.html();
                    selectedCheckbox.push( html );
                }
            } );

            return selectedCheckbox.join(', ');
        };

        return module.init();
    })();
})(jQuery);