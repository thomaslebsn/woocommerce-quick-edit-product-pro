(function ($) {
	window.fntQEPP =  window.fntQEPP || {};
	window.fntQEPP.editorDialog = ( function() {
		var module = {};
        // this variable is store the content of each product desc or short desc
        var productContentObj;
        //
        var body = $('body');
        var popupEditorID = 'product-popup-editor';
		module.init = function() {
            // catch event when click on button edit desc or short desc, then show popup editor to edit content
			$( '.button-show-content, .button-show-excerpt' ).off( 'click' ).on( 'click', showPopup );
            // catch event when click button 'x' in header or button cancel in footer, then close popup without save change
			$( '#popup-editor .popup-close-button.close, #popup-editor .btn.button-cancel' ).off( 'click' ).on( 'click', closePopup );
            // catch event when click button save in footer, then close popup and save change
			$( '#popup-editor .btn.button-save' ).off( 'click' ).on( 'click', saveEditorContent );
            // catch window resize to resize body of popup
            $(window).resize(function () {
                // change height of body popup
                changPopupBody();
            });

            return module;
		};

        /*
        change popup body height
         */
        var changPopupBody = function() {
            var popupBody = $('#popup-editor .modal-body');
            var popupHeader = $('#popup-editor .modal-header');
            var popupFooter = $('#popup-editor .modal-footer');

            var popupHeaderTop = popupHeader.offset().top;
            var popupHeaderHeight = popupHeader.height();
            var popupHeaderBottom = popupHeaderTop + popupHeaderHeight;
            var popupFooterTop = popupFooter.offset().top;
            var popupBodyHeight = popupFooterTop - popupHeaderBottom;
            if ( typeof popupBody != 'undefined' ) {
                // change height of body
                popupBody.css({'height': (popupBodyHeight) + 'px'});
                // move content scroll to top
                var editorWrapper = popupBody.find('.wp-editor-wrapper');
                editorWrapper.scrollTop(0);
            }
            mapEditorHeight();
        };

        /*
         Change height of editor to fit with height of popup body
         */
        var mapEditorHeight = function () {
            var wrapE = $('#wp-' + popupEditorID + '-wrap').height();
            var popupBodyHeight = $('#popup-editor .modal-body').height();
            var deltaHeight = wrapE - popupBodyHeight;
            var currentEditorHeight = $('#' + popupEditorID + '_ifr').height();
            var heightChange = currentEditorHeight - deltaHeight - 10; // 10px of margin-top and margin-bottom
            if ( typeof tinymce != 'undefined' ) {
                var ed = tinymce.get( popupEditorID );
                if ( typeof ed != 'undefined' ) {
                    ed.theme.resizeTo( '100%', heightChange );
                }
            }
        };

        /*
        This function use to close popup when this is showed
         */
        var closePopup = function ( e ) {
            bodyShowScroll();
            // select popup element
            var popupEditor = $( '#popup-editor' );
            // hidden it if it showed
            if ( ! popupEditor.hasClass( 'hidden' ) ) {
                popupEditor.addClass( 'hidden' );
            }
            // hidden image toolbar in popup if have
            //setTimeout(function() {
                hiddenImageToolbar();
            //}, 150);
            // show body scroll
            $('body').removeClass('hide-scroll');
        };

        // hidden image toolbar in popup if have
        var hiddenImageToolbar = function() {
            var imageToolbar = $('.mce-wp-image-toolbar');
            if ( typeof imageToolbar != 'undefined' ) {
                if ( ! imageToolbar.hasClass( 'hidden-important' ) ) {
                    imageToolbar.addClass( 'hidden-important' );
                }
            }
        };
        // show image toolbar in popup if have
        var showImageToolbar = function() {
            var imageToolbar = $('.mce-wp-image-toolbar');
            if ( typeof imageToolbar != 'undefined' ) {
                if ( imageToolbar.hasClass( 'hidden-important' ) ) {
                    imageToolbar.removeClass( 'hidden-important' );
                }
            }
        };

        // Hide scroll bar of main screen to disable scroll when show popup
        // only scroll able in popup
        var hideBodyScroll = function () {
            if ( typeof body != 'undefined' && ! body.hasClass( 'hidden-scroll-bar' ) ) {
                body.addClass( 'hidden-scroll-bar' );
            }
        };
        // Show scroll bar of main screen
        var bodyShowScroll = function () {
            if ( typeof body != 'undefined' && body.hasClass( 'hidden-scroll-bar' ) ) {
                body.removeClass( 'hidden-scroll-bar' );
            }
        };
        /*
        This function will show popup
        Then we get the value of productContentObj and fill this to editor for editable
         */
		var showPopup = function ( e ) {
            // if editor is not ready, ignore this action
            if ( typeof tinymce == 'undefined' ) {
                return;
            }
            // select popup element
            var popupEditor = $( '#popup-editor' );
            // show popup
            if ( popupEditor.hasClass( 'hidden' ) ) {
                popupEditor.removeClass( 'hidden' );
            }
            changPopupBody();
            hideBodyScroll();
			// Get button had clicked
			var buttonObj = $( e.currentTarget );
            var elementId = buttonObj.attr('id');
            // select element store data of content in tag input
            productContentObj = buttonObj.closest( 'span.input-text' ).find( 'textarea.value-editor' );
			// Get value of object was click
			var valueEditor = productContentObj.val();
            var currentProductId = buttonObj.closest( 'tr' ).find( 'th .product-single-checkbox-row' ).attr( 'data-product-row-id' );
            popupEditor.attr( 'data-product-row-id', currentProductId );
            popupEditor.attr( 'element-id', elementId );
            // ============================================
            // Sets the HTML contents of the activeEditor editor
            // Check the editor are exists yet
            // The editor are exists
            if( tinymce.get( popupEditorID ) ) {
                // turn tab text active
                window.switchEditors.go( popupEditorID, 'html' );
                // fill data to editor in tab text
                setContentOfTextArea( valueEditor );
                // turn tab visual active
                window.switchEditors.go( popupEditorID, 'tmce' );
                // remove undo and redo data
                tinymce.get( popupEditorID ).undoManager.clear();
            } else { // The editor does not exists
                setContentOfTextArea( valueEditor );
                // turn tab visual active
                window.switchEditors.go( popupEditorID, 'tmce' );
            }
            // ============================================
            // select popup title element: Edit description and Edit short description
            var popupTitle = $( '#popup-editor .modal-header .editor-title h4' );
            // change the title correctly for popup
            if ( buttonObj.hasClass( 'button-show-excerpt' ) ) {
                popupTitle.html( initialize_variables.message_show.edit_short_description_title );
                popupEditor.attr( 'column-name', 'product_excerpt' );
            } else {
                popupTitle.html( initialize_variables.message_show.edit_description_title );
                popupEditor.attr( 'column-name', 'product_content' );
            }
            $('body').addClass('hide-scroll');
            showImageToolbar();
			return false;
		};

        // get content of tab text in wp_editor
        var getContentOfTextArea = function () {
            var textArea = $( 'textarea#' + popupEditorID );
            return textArea.val();
        };

        // set content for tab text in wp_editor
        var setContentOfTextArea = function ( content ) {
            var textArea = $( 'textarea#' + popupEditorID );
            return textArea.val( content );
        };

        /*
        This function will store back to input tag
        And close popup
         */
		var saveEditorContent = function() {
            if ( ! tinymce.get( popupEditorID ).isHidden() ) {
                tinymce.get( popupEditorID ).save();
            }
            //window.switchEditors.go( popupEditorID, 'html' );
            // get content of editor
            var popupEditorValue = getContentOfTextArea();
            //set value to variable global
            var current = $( '#popup-editor' );
            //Get info current product
            var productRowId = current.attr( 'data-product-row-id' );
            var columnName = current.attr( 'column-name' );
            var elementId = current.attr( 'element-id' );

            // check if editor have undo or redo data, change the value of global list and change color of row changed
            // check if value is change
            if ( fnt_product_data[ productRowId ][ columnName ] != popupEditorValue ) {
                window.fntQEPP.ProductListHandler.changValueProductListGlobal( productRowId, columnName, popupEditorValue );
                window.fntQEPP.ProductListHandler.changeRowColor( $( '#' + elementId ), 'modifying' );
            }
            // store value of editor
            productContentObj.val( popupEditorValue );
            // close popup
            closePopup();
			return false;
		};
		return module.init();
	})();
})(jQuery);