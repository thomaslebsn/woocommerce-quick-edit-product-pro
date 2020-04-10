/**
 * Backbone Application File
 * @internal Obviously, I've dumped all the code into one file. This should probably be broken out into multiple
 * files and then concatenated and minified but as it's an example, it's all one lumpy file.
 * @package addProductModalBox.backbone_modal
 */

/**
 * @type {Object} JavaScript namespace for our application.
 */
var addProductModalBox = {
	backbone_modal: {
		__instance: undefined
	}
};

/**
 * Primary Modal Application Class
 */
var that = null,
    meta_image_frame = null,
    meta_gallery_frame = null,
    newProductId = -1;


addProductModalBox.backbone_modal.Application = Backbone.View.extend(
	{
		id: "add_product_backbone_modal_dialog",
		events: {
			"click .backbone_modal-close": "closeModal",
			"click #btn-cancel": "closeModal",
			"click #btn-ok": "saveModal",
			"click .navigation-bar a": "doNothing"
		},

		/**
		 * Simple object to store any UI elements we need to use over the life of the application.
		 */
		ui: {
			nav: undefined,
			content: undefined,
            tabSimpleProduct:{
                listProductItems:undefined,
                listProductItemsWrapperInner:undefined,
                buttonAddMore:undefined,
                tabContent:undefined,
                tabList:undefined
            },
            tabGroupedProduct:{
                listProductItems:undefined,
                listProductItemsWrapperInner:undefined,
                tabContent:undefined,
                buttonAddMore:undefined
            },
            tabExternalProduct:{
                listProductItems:undefined,
                tabContent:undefined,
                buttonAddMore:undefined
            }
		},

        dropdownList : ["product_cat","stock_status","back_orders","comment_status", "status"],

        productImage: ["thumb", "product_gallery"],

        formIDs: {
            simpleForm: {
                name: 'add-product-simple-item',
                indexForm : 1,
                tabName: 'tab-simple-product',
                tabTitleInit : 'New Simple Product'
            },
            groupedForm: {
                name: 'add-product-grouped-item',
                indexForm : 1,
                tabName: 'tab-grouped-product',
                tabTitleInit: 'New Grouped Product'
            },
            externalForm: {
                name: 'add-product-external-item',
                indexForm : 1,
                tabName: 'tab-external-product',
                tabTitleInit: 'New External/Affiliate Product'
            }
        },

        that: undefined,

		/**
		 * Container to store our compiled templates. Not strictly necessary in such a simple example
		 * but might be useful in a larger one.
		 */
		templates: {},

		/**
		 * Instantiates the Template object and triggers load.
		 */
		initialize: function () {
			"use strict";

			_.bindAll( this, 'render', 'preserveFocus', 'closeModal', 'saveModal', 'doNothing' );
			this.initialize_templates();
			this.render();
		},


		/**
		 * Creates compiled implementations of the templates. These compiled versions are created using
		 * the wp.template class supplied by WordPress in 'wp-util'. Each template name maps to the ID of a
		 * script tag ( without the 'tmpl-' namespace ) created in template-data.php.
		 */
		initialize_templates: function () {
			this.templates.window = wp.template( "add-product-backbone-modal-window" );
			this.templates.backdrop = wp.template( "add-product-backbone-modal-backdrop" );
			this.templates.menuItem = wp.template( "add-product-backbone-modal-menu-item" );
			this.templates.menuItemSeperator = wp.template( "add-product-backbone-modal-menu-item-separator" );
			/* all templates of adding new simple products */
            this.templates.addSimpleProductItems = wp.template( "add-product-backbone-modal-add-simple-product-item" );
			this.templates.tabContentSimpleProduct = wp.template( "tab-content-simple-product" );
			this.templates.tabListSimpleProduct = wp.template( "tab-list-simple-product" );

			this.templates.tabListGroupedProduct = wp.template( "tab-list-grouped-product" );
            this.templates.tabContentGroupedProduct = wp.template ("tab-content-grouped-product");
            this.templates.addGroupedProductItems = wp.template( "add-product-backbone-modal-add-grouped-product-item" );

            this.templates.tabListExternalProduct = wp.template( "tab-list-external-product" );
            this.templates.tabContentExternalProduct = wp.template ("tab-content-external-product");
            this.templates.addExternalProductItems = wp.template( "add-product-backbone-modal-add-external-product-item" );

            this.templates.productRowItem = wp.template( "product-row-item" );

		},

		/**
		 * Assembles the UI from loaded templates.
		 * @internal Obviously, if the templates fail to load, our modal never launches.
		 */
		render: function () {
			"use strict";

			// Build the base window and backdrop, attaching them to the $el.
			// Setting the tab index allows us to capture focus and redirect it in Application.preserveFocus
			this.$el.attr( 'tabindex', '0' )
				.append( this.templates.window() )
				.append( this.templates.backdrop() );

			// Save a reference to the navigation bar's unordered list and populate it with items.
			// This is here mostly to demonstrate the use of the template class.
			this.ui.nav = this.$( '.navigation-bar nav ul' )
				.append( this.templates.menuItem( {url: "#tab-simple-product", name: "Simple Product"} ) )
                .append( this.templates.menuItemSeperator() )
                .append( this.templates.menuItem( {url: "#tab-grouped-product", name: "Grouped Product"} ) )
				.append( this.templates.menuItemSeperator() )
				.append( this.templates.menuItem( {url: "#tab-external-product", name: "External/Affiliate Product"} ) )
                .append( this.templates.menuItemSeperator() )
                .append( this.templates.menuItem( {url: "#tab-variable-product", name: "Variable Product"} ) )
            ;

			// The l10n object generated by wp_localize_script() should be available, but check to be sure.
			// Again, this is a trivial example for demonstration.
			if ( typeof addProductModalBox_backbone_modal_l10n === "object" ) {
				this.ui.content = this.$( '.backbone_modal-main article' )
					.append( "<p>" + addProductModalBox_backbone_modal_l10n.replace_message + "</p>" );
			}

			// Handle any attempt to move focus out of the modal.
			jQuery( document ).on( "focusin", this.preserveFocus );

			// set overflow to "hidden" on the body so that it ignores any scroll events while the modal is active
			// and append the modal to the body.
			// TODO: this might better be represented as a class "modal-open" rather than a direct style declaration.
			jQuery( "body" ).css( {"overflow": "hidden"} ).append( this.$el );

			// Set focus on the modal to prevent accidental actions in the underlying page
			// Not strictly necessary, but nice to do.
			this.$el.focus();

            this.ui.nav.children('.nav-item').off('click').on('click', this.navItemClick);

            jQuery('.tab-content-block').not(':first').hide();

            that = this;
            this.renderSimpleProductTab();
            this.renderGroupedProductTab();
            this.renderExternalProductTab();
		},

        renderSimpleProductTab: function () {
            // tab Simple Product
            that.ui.tabSimpleProduct.listProductItems = that.$( '#tab-simple-product .list-product-items')
                .append(that.templates.addSimpleProductItems);

            that.ui.tabSimpleProduct.listProductItemsWrapperInner = that.ui.tabSimpleProduct.listProductItems.find('.list-simple-product-items');

            that.ui.tabSimpleProduct.buttonAddMore = that.ui.tabSimpleProduct.listProductItemsWrapperInner.find('.add-more-simple-product').off('click').on('click', that.addMoreSimpleProduct);

            that.ui.tabSimpleProduct.listProductItems.off('click','.cancel-add-product-item').on('click','.cancel-add-product-item', that.cancelProductItem);

            that.ui.tabSimpleProduct.tabContent = that.ui.tabSimpleProduct.listProductItemsWrapperInner.find('.tab-content-simple-product');

            that.ui.tabSimpleProduct.tabList = that.ui.tabSimpleProduct.listProductItemsWrapperInner.find('.tab-list-simple-product');

            that.ui.tabSimpleProduct.listProductItems.off('click', '.product-thumb').on('click', '.product-thumb',that.mediaUploaderHandler);
            that.ui.tabSimpleProduct.listProductItems.off('click', '.remove-thumb').on('click', '.remove-thumb',that.removeThumbnail);
            that.ui.tabSimpleProduct.listProductItems.off('mouseenter', '.gallery-image-hover').on('mouseenter', '.gallery-image-hover',that.mouseHoverInThumbnail);
            that.ui.tabSimpleProduct.listProductItems.off('mouseleave', '.gallery-image-hover').on('mouseleave', '.gallery-image-hover',that.mouseHoverOutThumbnail);

            that.ui.tabSimpleProduct.listProductItems.off('click', '.add-new-gallery').on('click', '.add-new-gallery',that.galleryUploaderHandler);
            that.ui.tabSimpleProduct.listProductItems.off('click', '.remove-gallery').on('click', '.remove-gallery',that.removeGallery);

            that.ui.tabSimpleProduct.listProductItems.off('click', '.cancel-add-product-item').on('click', '.cancel-add-product-item', that.cancelAddProduct);

            that.ui.tabSimpleProduct.listProductItems.off('change', '.post-title').on('change', '.post-title', that.titleOnchange);

            that.ui.tabSimpleProduct.listProductItems.off('blur', '.new-sku').on('blur', '.new-sku', that.CheckSku);
            that.ui.tabSimpleProduct.tabList.off('click', '.remove-tab-product').on('click', '.remove-tab-product', that.removeTabList);
            that.ui.tabSimpleProduct.tabList.off('mouseenter', '.li-product-item').on('mouseenter', '.li-product-item', that.mouseHoverInTabProduct);
            that.ui.tabSimpleProduct.tabList.off('mouseleave', '.li-product-item').on('mouseleave', '.li-product-item', that.mouseHoverOutTabProduct);


        },

        renderFormProductSimple: function () {
            var curFormID = that.formIDs.simpleForm.name + '-' + that.formIDs.simpleForm.indexForm;
            var tabID = that.formIDs.simpleForm.tabName + '-' + that.formIDs.simpleForm.indexForm;
            that.formIDs.simpleForm.indexForm += 1;
            that.ui.tabSimpleProduct.tabList.prepend(that.templates.tabListSimpleProduct({tabID : tabID , tabTitle : that.formIDs.simpleForm.tabTitleInit, formID : curFormID }));
            that.ui.tabSimpleProduct.tabContent.append(that.templates.tabContentSimpleProduct({tabID : tabID , tabTitle : that.formIDs.simpleForm.tabTitleInit, formID : curFormID }));
            window.fntQEPP.formSimpleProduct.renderForm('#' + curFormID);
            setTimeout(function () {
                that.ui.tabSimpleProduct.tabList.find('a[href= "#' + tabID + '"]').tab('show');
            },200);
            jQuery('#' + curFormID).validationEngine('attach', {promptPosition:'inline'});
            that.ui.tabSimpleProduct.tabContent.find('input[name="stock"]').inputmask("integer",{allowPlus:false, allowMinus:false});
            that.ui.tabSimpleProduct.tabContent.find('input[name="length"],input[name="width"],input[name="height"],input[name="weight"]').inputmask("numeric",{allowPlus:false, allowMinus:false});
            that.ui.tabSimpleProduct.tabContent.find('input[name="product_tag"]').suggest(window.fntQEPP.ProductListHandler.adminUrl + "admin-ajax.php?action=ajax-tag-search&tax=product_tag",
                {multiple:true, multipleSep: ","}
            );
        },

        renderGroupedProductTab: function () {
            // tab Grouped Product
            that.ui.tabGroupedProduct.listProductItems = that.$( '#tab-grouped-product .list-grouped-product-items-tag')
                .append(that.templates.addGroupedProductItems);

            that.ui.tabGroupedProduct.listProductItemsWrapperInner = that.ui.tabGroupedProduct.listProductItems.find('.list-grouped-product-items');

            that.ui.tabGroupedProduct.buttonAddMore = that.$( '.add-more-grouped-product').off('click').on('click', that.addMoreGroupedProduct);

            that.ui.tabGroupedProduct.listProductItems.off('click','.cancel-add-product-item').on('click','.cancel-add-product-item', that.cancelProductItem);

            that.ui.tabGroupedProduct.listProductItems.off('click','.meta-image-button').on('click','.meta-image-button',that.mediaUploaderHandler);

            that.ui.tabGroupedProduct.tabContent = that.ui.tabGroupedProduct.listProductItemsWrapperInner.find('.tab-content-grouped-product');

            that.ui.tabGroupedProduct.tabList = that.ui.tabGroupedProduct.listProductItemsWrapperInner.find('.tab-list-grouped-product');

            that.ui.tabGroupedProduct.listProductItems.off('click', '.product-thumb').on('click', '.product-thumb',that.mediaUploaderHandler);
            that.ui.tabGroupedProduct.listProductItems.off('click', '.remove-thumb').on('click', '.remove-thumb',that.removeThumbnail);
            that.ui.tabGroupedProduct.listProductItems.off('mouseenter', '.gallery-image-hover').on('mouseenter', '.gallery-image-hover',that.mouseHoverInThumbnail);
            that.ui.tabGroupedProduct.listProductItems.off('mouseleave', '.gallery-image-hover').on('mouseleave', '.gallery-image-hover',that.mouseHoverOutThumbnail);

            that.ui.tabGroupedProduct.listProductItems.off('click', '.add-new-gallery').on('click', '.add-new-gallery',that.galleryUploaderHandler);
            that.ui.tabGroupedProduct.listProductItems.off('click', '.remove-gallery').on('click', '.remove-gallery',that.removeGallery);

            that.ui.tabGroupedProduct.listProductItems.off('click', '.cancel-add-product-item').on('click', '.cancel-add-product-item', that.cancelAddProduct);

            that.ui.tabGroupedProduct.listProductItems.off('change', '.post-title').on('change', '.post-title', that.titleOnchange);
            that.ui.tabGroupedProduct.tabList.off('click', '.remove-tab-product').on('click', '.remove-tab-product', that.removeTabList);
            that.ui.tabGroupedProduct.tabList.off('mouseenter', '.li-product-item').on('mouseenter', '.li-product-item', that.mouseHoverInTabProduct);
            that.ui.tabGroupedProduct.tabList.off('mouseleave', '.li-product-item').on('mouseleave', '.li-product-item', that.mouseHoverOutTabProduct);
        },

        renderFormProductGrouped: function () {
            var curFormID = that.formIDs.groupedForm.name + '-' + that.formIDs.groupedForm.indexForm;
            var tabID = that.formIDs.groupedForm.tabName + '-' + that.formIDs.groupedForm.indexForm;
            that.formIDs.groupedForm.indexForm += 1;
            that.ui.tabGroupedProduct.tabList.prepend(that.templates.tabListGroupedProduct({tabID : tabID , tabTitle : that.formIDs.groupedForm.tabTitleInit, formID : curFormID }));
            that.ui.tabGroupedProduct.tabContent.append(that.templates.tabContentGroupedProduct({tabID : tabID , tabTitle : that.formIDs.groupedForm.tabTitleInit, formID : curFormID }));
            window.fntQEPP.formGroupedProduct.renderForm('#' + curFormID);
            setTimeout(function () {
                jQuery('.nav-pills a[href= "#' + tabID + '"]').tab('show');
            },200);
            jQuery('#' + curFormID).validationEngine('attach', {promptPosition:'inline'});
            that.ui.tabGroupedProduct.tabContent.find('input[name="product_tag"]').suggest(window.fntQEPP.ProductListHandler.adminUrl + "admin-ajax.php?action=ajax-tag-search&tax=product_tag",
                {multiple:true, multipleSep: ","}
            );
        },
        renderExternalProductTab: function () {
            // tab External Product
            that.ui.tabExternalProduct.listProductItems = that.$( '#tab-external-product .list-external-product-items-tag')
                .append(that.templates.addExternalProductItems);

            that.ui.tabExternalProduct.listProductItemsWrapperInner = that.ui.tabExternalProduct.listProductItems.find('.list-external-product-items');

            that.ui.tabExternalProduct.buttonAddMore = that.$('.add-more-external-product').off('click').on('click', that.addMoreExternalProduct);

            that.ui.tabExternalProduct.listProductItems.off('click','.cancel-add-product-item').on('click','.cancel-add-product-item', that.cancelProductItem);

            that.ui.tabExternalProduct.listProductItems.off('click','.meta-image-button').on('click','.meta-image-button',that.mediaUploaderHandler);

            that.ui.tabExternalProduct.tabContent = that.ui.tabExternalProduct.listProductItemsWrapperInner.find('.tab-content-external-product');

            that.ui.tabExternalProduct.tabList = that.ui.tabExternalProduct.listProductItemsWrapperInner.find('.tab-list-external-product');

            that.ui.tabExternalProduct.listProductItems.off('click', '.product-thumb').on('click', '.product-thumb',that.mediaUploaderHandler);
            that.ui.tabExternalProduct.listProductItems.off('click', '.remove-thumb').on('click', '.remove-thumb',that.removeThumbnail);
            that.ui.tabExternalProduct.listProductItems.off('mouseenter', '.gallery-image-hover').on('mouseenter', '.gallery-image-hover',that.mouseHoverInThumbnail);
            that.ui.tabExternalProduct.listProductItems.off('mouseleave', '.gallery-image-hover').on('mouseleave', '.gallery-image-hover',that.mouseHoverOutThumbnail);

            that.ui.tabExternalProduct.listProductItems.off('click', '.add-new-gallery').on('click', '.add-new-gallery',that.galleryUploaderHandler);
            that.ui.tabExternalProduct.listProductItems.off('click', '.remove-gallery').on('click', '.remove-gallery',that.removeGallery);

            that.ui.tabExternalProduct.listProductItems.off('click', '.cancel-add-product-item').on('click', '.cancel-add-product-item', that.cancelAddProduct);

            that.ui.tabExternalProduct.listProductItems.off('change', '.post-title').on('change', '.post-title', that.titleOnchange);

            that.ui.tabExternalProduct.listProductItems.off('blur', '.new-sku').on('blur', '.new-sku', that.CheckSku);
            that.ui.tabExternalProduct.tabList.off('click', '.remove-tab-product').on('click', '.remove-tab-product', that.removeTabList);
            that.ui.tabExternalProduct.tabList.off('mouseenter', '.li-product-item').on('mouseenter', '.li-product-item', that.mouseHoverInTabProduct);
            that.ui.tabExternalProduct.tabList.off('mouseleave', '.li-product-item').on('mouseleave', '.li-product-item', that.mouseHoverOutTabProduct);
        },

        renderFormProductExternal: function () {
            var curFormID = that.formIDs.externalForm.name + '-' + that.formIDs.externalForm.indexForm;
            var tabID = that.formIDs.externalForm.tabName + '-' + that.formIDs.externalForm.indexForm;
            that.formIDs.externalForm.indexForm += 1;
            that.ui.tabExternalProduct.tabList.prepend(that.templates.tabListExternalProduct({tabID : tabID , tabTitle : that.formIDs.externalForm.tabTitleInit, formID : curFormID }));
            that.ui.tabExternalProduct.tabContent.append(that.templates.tabContentExternalProduct({tabID : tabID , tabTitle : that.formIDs.externalForm.tabTitleInit, formID : curFormID }));
            window.fntQEPP.formExternalProduct.renderForm('#' + curFormID);
            setTimeout(function () {
                jQuery('.nav-pills a[href= "#' + tabID + '"]').tab('show');
            },200);
            jQuery('#' + curFormID).validationEngine('attach', {promptPosition:'inline'});
            that.ui.tabExternalProduct.tabContent.find('input[name="product_tag"]').suggest(window.fntQEPP.ProductListHandler.adminUrl + "admin-ajax.php?action=ajax-tag-search&tax=product_tag",
                {multiple:true, multipleSep: ","}
            );
        },

        cancelAddProduct: function(e){
            var currentTab = jQuery(e.currentTarget).closest('.tab-pane');
            var tabId = currentTab.attr('id');
            jQuery("a[href$='" + tabId + "']").closest('li').remove();
            var next = currentTab.next();
            var tabAfterRemoveId = '';
            if(next.length <= 0){
                var prev = currentTab.prev();
                if(prev.length > 0){
                    tabAfterRemoveId = prev.attr('id');
                    var oldPrevClass = prev.attr('class');
                    prev.attr('class', oldPrevClass + ' active in');
                }
            }else{
                tabAfterRemoveId = next.attr('id');
                var oldNextClass = next.attr('class');
                next.attr('class', oldNextClass + ' active in');
            }
            if(tabAfterRemoveId.length > 0){
                var tabActive = jQuery(".nav-pills a[href$='#"+ tabAfterRemoveId +"']");
                tabActive.parent('li').attr('class','active');
                tabActive.tab('show');

            }else{
                jQuery(".nav-pills a[href$='#']").parent('li').attr('class', 'active');
            }
            currentTab.remove();
        },

        removeTabList: function(e){
            var currentElement = jQuery(e.currentTarget);
            var formId = currentElement.attr('form_id');
            jQuery('#' + formId).find('.cancel-add-product-item').trigger('click');
        },

        mouseHoverInTabProduct: function(e){
            var currentElement = jQuery(e.currentTarget);
            var removeCycle = currentElement.find('.remove-tab-product');
            if(removeCycle.hasClass('hidden')){
                removeCycle.removeClass('hidden');
            }
        },

        mouseHoverOutTabProduct: function(e){
            var currentElement = jQuery(e.currentTarget);
            var removeCycle = currentElement.find('.remove-tab-product');
            if(!removeCycle.hasClass('hidden')){
                removeCycle.addClass('hidden');
            }
        },

        titleOnchange: function(e){
            var text = jQuery(e.currentTarget).val();
            var currentTab = jQuery(e.currentTarget).closest('.tab-pane');
            var tabId = currentTab.attr('id');
            var textDefault = "";
            switch (jQuery(e.currentTarget).closest('.tab-content-block').attr('data-product-tab-type')){
                case "simple":
                    textDefault = that.formIDs.simpleForm.tabTitleInit;
                    break;
                case "grouped":
                    textDefault = that.formIDs.groupedForm.tabTitleInit;
                    break;
                case "external":
                    textDefault = that.formIDs.externalForm.tabTitleInit;
                    break;
                case "variable":
                    break;
                default :
                    break;
            }
            text = !_.isEmpty(text) ? text : textDefault;
            currentTab.closest('article').find(".nav-pills a[href$='#"+ tabId +"']").text(text);
        },

        CheckSku: function(e){
            var skuValue = jQuery(e.currentTarget).val();
            var curFormID = jQuery(e.currentTarget).closest('form').attr('id');
            if(that.checkSkuGlobal(skuValue,curFormID)){
                jQuery('#' + curFormID).find('.new-sku').val('');
                jQuery('#' + curFormID).find('.new-sku').focus();

            } else if(skuValue.length > 0){
                var dataPass = {
                    new_sku: skuValue,
                    form_id: curFormID,
                    action: 'fnt_product_manage',
                    real_action: 'check_sku_backform'
                };

                window.fntQEPP.Core.ajaxRequestManual(dataPass, that.handleResponseByCheckSkuProduct, that.beforeSendAjax);
            }

        },

        beforeSendAjax: function(){
            window.fntQEPP.Core.showLoading();
        },

        handleResponseByCheckSkuProduct: function(response){
            window.fntQEPP.Core.hideLoading();
            if(response.result === "SUCCESS"){
                alert(initialize_variables.message_show.message_SKU_exists);
                var formId = response.data.form_id;
                jQuery('#' + formId).find('.new-sku').val('');
                jQuery('#' + formId).find('.new-sku').focus();

            } else {

            }
            return false;
        },


        createProduct: function(formValue){
            var result = {};
            result = that.createMultipleProductType(formValue);
            return result;
        },

        checkSkuGlobal: function (sku, formId) {
            var result = false;
            _.each(jQuery('.backbone_modal-main').find('form'), function(item, index){
                var simpleProduct = jQuery('#' + item.id).serializeArray();
                var newProduct = that.createProduct(simpleProduct);
                if(item.id !== formId && newProduct.sku === sku){
                    alert(initialize_variables.message_show.message_SKU_exists_global);
                    result = true;
                    return false;
                }
            });
            return result;
        },

        createMultipleProductType: function(formValue){
            var productType = that.getObjectInArray(formValue,'product_type');
            var result = {};
            var listFields = [];
            switch (productType){
                case 'simple':
                    listFields = default_product_fields.simple;
                    break;
                case 'grouped':
                    listFields = default_product_fields.grouped;
                    break;
                case 'external':
                    listFields = default_product_fields.external;
                    break;
                case 'variable':
                    listFields = default_product_fields.variable;
                    break;
                default:
                    break;
            }
            result = that.createProductByType(formValue,listFields);
            result['product_type'] = productType;

            return result;
        },

        createProductByType: function(formValue, listFields) {
            var result = {};
            _.each(listFields, function (item, index) {
                if (typeof item != 'undefined') {
                    result[item] = that.getObjectInArray(formValue, item);
                    if(item == default_product_fields.simple.comment_status){
                        result[item] = result[item] === 'on' ? 'open' : 'closed';
                    }
                    if(item == default_product_fields.simple.sold_individually){
                        result[item] = result[item] == 'on' ? 'yes' : '';
                    }
                    if(item == default_product_fields.simple.featured){
                        result[item] = result[item] == "on" ? "yes" : "no";
                    }
                    if(item == default_product_fields.simple.regular_price ||
                       item == default_product_fields.simple.sale_price){
                        result[item] = result[item].replace(/,/g,'');
                    }
                } else {
                    result[item] = '';
                }

            });
            return result;
        },

        getObjectInArray: function(array,key){
            var result = '';
            var arrayResult = [];
            _.each(array, function(item, index){
                if(item.name == key && item.name == 'product_cat'){
                    arrayResult.push(item.value);
                }else if(item.name == key){
                    result = item.value;
                    return;
                }

            });
            if(key == 'product_cat'){
                return arrayResult;
            }
            if(key == 'product_tag'){
                if(result.length > 0) {
                    var valueTrim = result.split(',');
                    _.each(valueTrim, function (item, index) {
                        arrayResult.push(item.trim());
                    });
                }
                return arrayResult;
            }
            return result;
        },

        getValueDropdownList: function(columnName, newProduct,htmlValue){
            var result = '';
            var resultText = [];
            var listDropdown = [];
            var columnValue = newProduct[columnName];
            if(!_.isArray(columnValue)){
                listDropdown.push(columnValue);
            }else{
                listDropdown = columnValue;
            }
            if(_.isArray(listDropdown) && !_.isEmpty(listDropdown)){
                _.each(listDropdown, function(item,index){
                    jQuery(htmlValue).find('.'+ columnName +' > option').filter( function() {
                        if(jQuery(this).val() == item){
                            var curText = jQuery(this).text();
                            resultText.push(jQuery.trim(curText));
                        }
                    });
                });
            }
            return resultText;
        },

        handleAddProductRowItem: function(newProduct){
            var result = that.replaceByProductItem(newProduct);
            _.each(that.dropdownList, function(item, index){
                var valueReplace = that.getValueDropdownList(item,newProduct,result);
                var pattern = '%' + item + '%';
                var re = new RegExp(pattern, 'g');
                result = result.replace(re, valueReplace.length > 0 ? valueReplace.join(',') : "");
            });
            _.each(that.productImage, function(item, index){
                var thumbValue = newProduct[item];
                var imageHtmlDefault = initialize_variables.wrap_image;
                var resultHtml = '';
                var resultArray = [];
                var listImageId = [];
                if(thumbValue.length > 0){
                    var valueArray = thumbValue.split(',');
                    if(!_.isEmpty(valueArray)){
                        _.each(valueArray, function(image){
                            if(image.length > 0){
                                var arrayImage = image.split('@#@');
                                if(arrayImage.length > 1){
                                    var patternId = '%' + 'attachment_id' + '%';
                                    var patternUrl = '%' + 'thumb_url' + '%';
                                    var reId = new RegExp(patternId, 'g');
                                    var reUrl = new RegExp(patternUrl, 'g');
                                    listImageId.push(arrayImage[0]);
                                    var imageHtml = window.fntQEPP.Core.imageStaticHtmlDefault().replace(reId, arrayImage[0]);
                                    imageHtml = imageHtml.replace(reUrl,arrayImage[1]);
                                    resultArray.push(imageHtml);
                                }
                            }
                        });
                    }
                }
                if(resultArray.length > 0) {
                    resultHtml = resultArray.join(' \r\n');
                }
                if(item === 'thumb'){
                    if(resultHtml.length <= 0){
                        imageHtmlDefault = imageHtmlDefault.replace(/%remove_image_default%/g,window.fntQEPP.Core.removeImageStaticHtmlDefault());
                        imageHtmlDefault = imageHtmlDefault.replace(/%wrap_image%/g,'');
                    }else{
                        imageHtmlDefault = imageHtmlDefault.replace(/%remove_image_default%/g,'');
                        imageHtmlDefault = imageHtmlDefault.replace(/%wrap_image%/g,resultHtml);
                        imageHtmlDefault = imageHtmlDefault.replace(/%class_wrap%/g,'remove-thumbnail');
                        imageHtmlDefault = imageHtmlDefault.replace(/%class_hover%/g, 'wrap-thumbnail');
                    }

                }else{
                    imageHtmlDefault = imageHtmlDefault.replace(/%remove_image_default%/g,window.fntQEPP.Core.removeImaageStaticHtmlDefault());
                    imageHtmlDefault = imageHtmlDefault.replace(/%wrap_image%/g,resultHtml);
                    imageHtmlDefault = imageHtmlDefault.replace(/%class_wrap%/g,'remove-gallery-image');
                    imageHtmlDefault = imageHtmlDefault.replace(/%class_hover%/g, 'wrap-gallery-image');
                }
                newProduct[item] = listImageId.length > 0 ? listImageId.join(','): '';
                var patternHtml = '%' + item + '%';
                var reHtml = new RegExp(patternHtml, 'g');
                result = result.replace(reHtml, imageHtmlDefault);
            });
            //get html rich text editor
            var richTextEditor = jQuery('table#create-hidden-for-add-product tbody').html();
            richTextEditor = richTextEditor.replace(/%id%/g, newProduct.id);
            result += richTextEditor;
            jQuery('#the-list').append(result);
            return newProduct;
        },

        replaceByProductItem: function(product){
            var result = '';
            var fieldsByProductType = {};
            switch (product.product_type){
                case 'simple':
                    result = fnt_single_row_blank.simple;
                    fieldsByProductType = default_product_fields.simple;
                    break;
                case 'grouped':
                    result = fnt_single_row_blank.grouped;
                    fieldsByProductType = default_product_fields.grouped;
                    break;
                case 'external':
                    result = fnt_single_row_blank.external;
                    fieldsByProductType = default_product_fields.external;
                    break;
                case 'variable':
                    result = fnt_single_row_blank.variable;
                    fieldsByProductType = default_product_fields.variable;
                    break;
                default:
                    break;
            }
            _.each(fieldsByProductType, function(item, index){
                if(jQuery.inArray(item, that.dropdownList) > -1 || jQuery.inArray(item, that.productImage) > -1){
                    return true;
                }
                var pattern = '%' + item + '%';
                var re = new RegExp(pattern, 'g');
                if(item == default_product_fields.simple.featured){
                    var featured = '';
                    if(product[item] == 'on'){
                        product[item] = 'yes';
                    }else{
                        product[item] = 'no';
                        featured = 'not-featured';
                    }
                    result = result.replace(re,featured);
                    return true;
                }
                if(item == default_product_fields.simple.comment_status){
                    var commentStatus = '';
                    if(product[item] == 'open'){
                        commentStatus = 'checked';
                    }
                    result = result.replace(re,commentStatus);
                    return true;
                }
                if(item == default_product_fields.simple.sold_individually){
                    var individually = '';
                    if(product[item] == 'yes'){
                        individually = 'checked';
                    }
                    result = result.replace(re,individually);
                    return true;
                }

                result = result.replace(re, product[item]);

            });
            return result;
        },

        mouseHoverInThumbnail : function(e){
            var tableCellElement = jQuery(e.currentTarget);
            var columnName = tableCellElement.closest('div').find('span').attr('column-name');
            var removeIcon = '';
            if(columnName == 'product_thumb'){
                removeIcon = tableCellElement.find('.remove-thumb');
            }else{
                removeIcon = tableCellElement.find('.remove-gallery');
            }
            if(removeIcon.hasClass('hidden')){
                removeIcon.removeClass('hidden');
            }
        },

        mouseHoverOutThumbnail : function(e){
            var tableCellElement = jQuery(e.currentTarget);
            var columnName = tableCellElement.closest('div').find('span').attr('column-name');
            var removeIcon = '';
            if(columnName == 'product_thumb'){
                removeIcon = tableCellElement.find('.remove-thumb');
            }else{
                removeIcon = tableCellElement.find('.remove-gallery');
            }
            if(!removeIcon.hasClass('hidden')){
                removeIcon.addClass('hidden');
            }
        },

        mediaUploaderHandler: function(e){

            // Prevents the default action from occuring.
            e.preventDefault();

            var inputContainImageVal = jQuery(e.currentTarget);

            // If the frame already exists, re-open it.
            if ( meta_image_frame ) {
                meta_image_frame.open();
                return;
            }

            // Sets up the media library frame
            meta_image_frame = wp.media.frames.meta_image_frame = wp.media({
                title: meta_image.title,
                button: { text:  meta_image.button },
                library: { type: 'image' }
            });

            // Runs when an image is selected.
            meta_image_frame.on('select', function(){

                // Grabs the attachment selection and creates a JSON representation of the model.
                var media_attachment = meta_image_frame.state().get('selection').first().toJSON();

                // Sends the attachment URL to our custom image input field.
                //var imageAppend = initialize_variables['image_add_new'].replace('%thumb_url%',media_attachment.url);
                var imageHidden = jQuery(inputContainImageVal).closest('form').find('.thumb-value');
                imageHidden.val(media_attachment.id);
                var defaultHtml = window.fntQEPP.Core.imageStaticHtmlDefault();
                defaultHtml = defaultHtml.replace(/%class_hover%/,"gallery-image-hover");
                defaultHtml = defaultHtml.replace(/%class_wrap%/,"remove-thumb");
                defaultHtml = defaultHtml.replace(/%attachment_id%/,media_attachment.id);
                defaultHtml = defaultHtml.replace(/%thumb_url%/,media_attachment.url);
                jQuery(inputContainImageVal).find('span').remove();
                jQuery(inputContainImageVal).append(defaultHtml);
                meta_image_frame = null;
            });

            // Opens the media library frame.
            meta_image_frame.open();
        },

        removeThumbnail: function(e){
            e.stopPropagation();
            var current = jQuery(e.currentTarget).closest('div').find('span');
            var imagePlus = window.fntQEPP.Core.galleryStaticHtmlDefault();
            current.html('');
            current.append(imagePlus);
            var imageHidden = jQuery(current).closest('form').find('.thumb-value');
            imageHidden.val('');
        },

        galleryUploaderHandler: function(e){

            // Prevents the default action from occuring.
            e.preventDefault();

            var inputContainImageVal = jQuery(e.currentTarget);

            // If the frame already exists, re-open it.
            if ( meta_gallery_frame ) {
                meta_gallery_frame.open();
                return;
            }

            // Sets up the media library frame
            meta_gallery_frame = wp.media.frames.meta_image_frame = wp.media({
                title: meta_image.title,
                button: { text:  meta_image.button },
                library: { type: 'image' },
                multiple: true
            });

            // Runs when an image is selected.
            meta_gallery_frame.on('select', function(){

                // Grabs the attachment selection and creates a JSON representation of the model.
                var media_attachments = meta_gallery_frame.state().get('selection');
                var inputHiddenValue = inputContainImageVal.closest('form').find('.gallery-value');
                var valueGallery = [];
                var older_gallery = inputHiddenValue.val();
                if(older_gallery.length > 0){
                    valueGallery = older_gallery.split(',');
                }
                media_attachments.forEach(function(item){
                    var obj = item.toJSON();
                    if(!that.checkAttachmentExists(inputHiddenValue.val(), obj.id)){
                        var defaultHtml = window.fntQEPP.Core.imageStaticHtmlDefault();
                        defaultHtml = defaultHtml.replace(/%class_hover%/,"gallery-image-hover");
                        defaultHtml = defaultHtml.replace(/%class_wrap%/,"remove-gallery hidden");
                        defaultHtml = defaultHtml.replace(/%attachment_id%/,obj.id);
                        defaultHtml = defaultHtml.replace(/%thumb_url%/,obj.url);
                        inputContainImageVal.parent().prepend(defaultHtml);
                        valueGallery.push(obj.id);
                    }

                    meta_gallery_frame = null;
                });
                inputHiddenValue.val(valueGallery);
            });

            // Opens the media library frame.
            meta_gallery_frame.open();
        },

        removeGallery: function(e){
            e.stopPropagation();
            var current = jQuery(e.currentTarget);
            var attachmentId = current.attr('data-attachment-id');

            var hiddenValue = current.closest('form').find('.gallery-value');
            var listOldId = hiddenValue.val();
            var newHiddenValue = [];
            if(listOldId.length > 0){
                var str = listOldId.split(',');
                _.each(str, function(item){
                    if(item != attachmentId){
                        newHiddenValue.push(item);
                    }
                });
            }
            current.parent('span').remove();
            hiddenValue.val(newHiddenValue);
        },

        checkAttachmentExists: function(listId, attachmentId){
            var result = false;
            if(listId.length > 0){
                var arrayId = listId.split(',');
                _.each(arrayId, function(item){
                    if(item == attachmentId){
                        result = true;
                        return false;
                    }
                });
            }
            return result;
        },

        cancelProductItem:function(e){
            jQuery(e.currentTarget).closest('.form').closest('div').remove();
        },

        navItemClick: function(e){
            var tabID = jQuery(e.currentTarget).find('a').attr("href");
            jQuery('.tab-content-block').hide();
            jQuery(tabID).show();
            return false;
        },

        addMoreSimpleProduct: function (e) {
            that.renderFormProductSimple();
        },
        addMoreGroupedProduct: function (e){
            that.renderFormProductGrouped();
        },
        addMoreExternalProduct: function (e){
            that.renderFormProductExternal();
        },
		/**
		 * Ensures that keyboard focus remains within the Modal dialog.
		 * @param e {object} A jQuery-normalized event object.
		 */
		preserveFocus: function ( e ) {
			"use strict";
			if ( this.$el[0] !== e.target && ! this.$el.has( e.target ).length ) {
				this.$el.focus();
			}
		},

		/**
		 * Closes the modal and cleans up after the instance.
		 * @param e {object} A jQuery-normalized event object.
		 */
		closeModal: function ( e ) {
			"use strict";
            that.formIDs.simpleForm.indexForm = 1;
            that.formIDs.groupedForm.indexForm = 1;
			this.undelegateEvents();
			jQuery( document ).off( "focusin" );
			jQuery( "body" ).css( {"overflow": "auto"} );
			this.remove();
			addProductModalBox.backbone_modal.__instance = undefined;
		},

		/**
		 * Responds to the btn-ok.click event
		 * @param e {object} A jQuery-normalized event object.
		 * @todo You should make this your own.
		 */
		saveModal: function ( e ) {
			"use strict";
            var validateData = true;
            _.each(jQuery('.backbone_modal-main').find('form'),function(item, index){
                var valid = jQuery('#' + item.id).validationEngine('validate',{
                    validateNonVisibleFields: true,
                    promptPosition : 'inline',
                    ajaxFormValidation: true,
                    ajaxFormValidationMethod: 'post'
                });
                if(!valid){
                    validateData = false;
                    return false;
                }

            });
            if(!validateData){
                return false;
            }
            var fullDataPassed = [];
            _.each(jQuery('.backbone_modal-main').find('form'), function(item, index){
                var simpleProduct = jQuery('#' + item.id).serializeArray();
                var newProduct = that.createProduct(simpleProduct);
                if(newProduct['post_title'] === ""){
                    validateData = false;
                    return;
                }
                var newId = that.getMinIdProduct();
                newProduct[default_product_fields.simple.id] = newId - 1;
                newProduct[default_product_fields.simple.status] = 'pending';
                window.fntQEPP.ProductListHandler.updateRowColorViaUpdatedSetting();
                fullDataPassed.push(newProduct);
            });
            var dataPass = {
                all_product_data: fullDataPassed,
                action: 'fnt_product_manage',
                real_action: 'add_multiple'
            };
            if(validateData) {
                window.fntQEPP.Core.ajaxRequestManual(dataPass, that.handleResponseBySavingAllProductData, that.beforeSendDataToSaveAllAction);
            }else{
                alert(initialize_variables.message_show.message_product_name_not_empty);
            }
            this.closeModal( e );
		},

        handleResponseBySavingAllProductData : function(response){
            if(response.result === "SUCCESS"){
                that.closeModal();
                window.location.href = initialize_variables.plugin_base_url;
            } else {
                window.fntQEPP.Core.hideLoading();
                console.log(initialize_variables.message_show.message_save_product_failed);
            }
            return false;
        },

        getMinIdProduct: function(){
            var result = 0;
            _.each(fnt_product_data, function(item, index){
                if(item[default_product_fields.simple.id] < result){
                    result = item[default_product_fields.simple.id];
                }
            });
            return result;
        },

		/**
		 * Ensures that events do nothing.
		 * @param e {object} A jQuery-normalized event object.
		 * @todo You should probably delete this and add your own handlers.
		 */
		doNothing: function ( e ) {
			"use strict";
			e.preventDefault();
		}

	} );

jQuery( function ( $ ) {
	"use strict";
	/**
	 * Attach a click event to the meta-box button that instantiates the Application object, if it's not already open.
	 */
    // =======================Declare variable============================
    // declare iframe id
    var iframeID = 'fnt-iframe';
    // select iframe use to edit/add product
    var iframe;
    // get popup content iframe
    var popupEditProduct = $('#popup-edit-product');
    // button close in header of popup
    var buttonClosePopup = $('#popup-edit-product button.popup-close-button');
    // button cancel without save product
    var buttonCancelPopup = $('#popup-edit-product button.button-cancel');
    // button save change/add product
    var buttonSaveProduct = $('#popup-edit-product button.button-save');
    // popup title
    var popupTitle = $( '#popup-edit-product .modal-header .editor-title h4' );
    // set default popup title
    var popupTitleString = initialize_variables.message_show.add_product_by_popup_title;
    // product id of link clicked
    var productId = 0;
    // Since 1.0.5, to fix bug can't save attributes of product type different "variable"
    var productType; // Let's know product type of product editing in case "Edit", not available for case "Add"
    // flag if is clicked on button Add product
    var buttonAddClicked = false;
    // flag if is clicked on button Save product in footer of popup
    var buttonSaveClicked = false;
    // flag popup is open
    var popupOpened = false;
    // popup loading
    var popupLoading = $('#popup-edit-product .wrap-center');
    // popup iframe parent
    var popupIframeParent = $('#popup-edit-product .wp-editor-wrapper');
    // product had been response
    var productResponse = null;
    // get body tag to hidden scroll when show popup
    var body = $('body');
    // form submit
    var iframeForm;
    // link to iframe
    var iframeLink;
    // get popup body
    var popupBody;
    // content editor
    var contentEditor;
    // buttons control of popup
    var buttonCTA = $('#popup-edit-product .popup-button-control');
    // =======================End declare============================
    // ========================Functions============================
    // catch event, declare, or other things
    var iframeLoaded = function () {
        buttonCTA = $('#popup-edit-product .popup-button-control');
        // if iframe doesn't exists, don't do code bellow
        if ( typeof iframe == 'undefined' ) {
            return;
        }
        // catch event iframe is loaded
        iframe.load( function() {
            // if popup is opened
            if ( popupOpened ) {
                // ================Code fix bug of editor in Firefox=========================
                // todo: find best way to remove timeOut
                // fix bug in editor, then show page post edit
                // check browser is FireFox???
                var timeOut = 0;
                if ( isFireFox() ) {
                    timeOut = 500;
                }
                setTimeout(function() {
                    fixEditorInFirefox();
                    // enable button CTA of popup
                    buttonSaveProduct.removeAttr('disabled');
                    // enable edit in popup content when Show
                    if ( typeof popupBody != 'undefined' && popupBody.hasClass( 'disabled-element' ) ) {
                        popupBody.removeClass('disabled-element');
                    }
                }, timeOut);
                // =========================================================================
                // show iframe
                showElement(iframe);
                // hide popup loading
                hiddenElement(popupLoading);

                // focus to product title when iframe loaded
                var productTitle = iframe.contents().find('#title');
                // move the text cursor to the end of content inside input text
                var tempValue = productTitle.val();
                productTitle.focus();
                productTitle.val('');
                productTitle.val(tempValue);
                // end move the text cursor to the end of content inside input text
                // Prevent current form post submit action
                iframe.contents().find('#post').off('submit').on('submit', function(e) {
                    e.preventDefault();
                });
                // if saved product variations, do save change in popup
                iframe.get(0).contentWindow.jQuery( '#woocommerce-product-data' ).on( 'woocommerce_variations_saved', function(e) {
                    if ( buttonSaveClicked ) {
                        doSaveProduct();
                    } else if ( ! buttonAddClicked // since ver 1.1, to sync variations
                                && productType == 'variable' // since ver 1.0.5, check product type must be variable
                    ) {
                        sendAjaxGetProductVariationsJustEdit();
                    } else {
                        enableEditPopup();
                    }
                });
                // catch event save attribute
                iframe.get(0).contentWindow.jQuery( '#variable_product_options' ).on( 'reload', function(e) {
                    if ( ! buttonAddClicked && productType == 'variable' ) {
                        sendAjaxGetProductVariationsJustEdit();
                    } else {
                        enableEditPopup();
                    }
                });
                // disable popup area when click save change on popup
                iframe.get(0).contentWindow.jQuery( 'button.save_attributes, button.save-variation-changes' ).on( 'click', function(e) {
                    disableEditPopup();
                });
                iframe.get(0).contentWindow.jQuery( 'li.variations_tab a' ).on( 'click', function () {
                    preventInputEnter();
                    var inputOfVariationsWrap = iframe.get(0).contentWindow.jQuery( '#woocommerce-product-data #variable_product_options input[type=text]' );
                    inputOfVariationsWrap.off('keyup keypress').on('keyup keypress', function(e) {
                        if (e.which == 13 || e.keyCode == 13) {
                            e.preventDefault();
                            return false;
                        }
                        return true;
                    } );
                } );

                // set productId, get id of product in iframe
                if ( ! buttonAddClicked ) {

                }
                // productId = iframe.get(0).contentWindow.jQuery( 'form#post input#post_ID').val();
            }
        } );
    };
    var preventInputEnter = function () {
        var iframePopupInput = iframe.contents().find('form#post input[type=text]');
        // catch event submit when press Enter in product title
        // and prevent Submit action
        iframePopupInput.off('keyup keypress').on('keyup keypress', function(e) {
            if (e.which == 13 || e.keyCode == 13) {
                e.preventDefault();
                return false;
            }
            return true;
        } );
    };
    var saveProduct = function () {
        buttonSaveClicked = true;
        disableEditPopup();
        var buttonSaveVariations = iframe.contents().find('.save-variation-changes');
        if ( typeof buttonSaveVariations != 'undefined' && buttonSaveVariations.length > 0 && typeof buttonSaveVariations.attr('disabled') == 'undefined' ) {
            buttonSaveVariations.trigger('click');
        } else {
            doSaveProduct();
        }
    };
    var doSaveProduct = function () {
        // wp_editor: move text editing from tab visual to tab text
        moveValueToTextTab();
        sendAjaxEditProduct();
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
    // reload parent page
    var windowReload = function() {
        window.location.reload();
    };
    // Catch when window are change size
    $(window).resize(function () {
        // change height of body popup
        changIframeHeight();
    });
    // for display correct iframe in popup
    var changIframeHeight = function () {
        var popupBody = $('#popup-edit-product .modal-body');
        var popupHeader = $('#popup-edit-product .modal-header');
        var popupFooter = $('#popup-edit-product .modal-footer');
        if( typeof popupBody == 'undefined' || typeof popupHeader == 'undefined' || typeof popupFooter == 'undefined') {
            return;
        }

        var popupHeaderTop = popupHeader.offset().top;
        var popupHeaderHeight = popupHeader.height();
        var popupHeaderBottom = popupHeaderTop + popupHeaderHeight;
        var popupFooterTop = popupFooter.offset().top;
        var popupBodyHeight = popupFooterTop - popupHeaderBottom;
        if(typeof popupBody != 'undefined' && typeof iframe != 'undefined'){
            popupBody.css({'height': (popupBodyHeight + 33) + 'px'});
            iframe.css({'height': (popupBodyHeight + 33) + 'px'});
        }
    };
    // Add an iframe to popup content
    var addIframeToPopup = function () {
        // add iframe to body content
        popupIframeParent.append('<iframe id="' + iframeID + '" width="100%" height="400" class="hidden"></iframe>');
        // update iframe selector
        iframe = $('#' + iframeID);
    };
    // Remove iframe from popup content
    var removeIframeOnPopup = function () {
        if ( typeof iframe != 'undefined' ) {
            iframe.remove();
        }
    };
    // Show popup and init some variable
    var showPopupProduct = function ( e ) {
        // check if current row is modified, then disable user edit this product by popup
        // And check if current row is variable product and itself variation product, then disable user edit this product by popup too
        var currentTarget = $(e.currentTarget);
        if ( typeof currentTarget != 'undefined' ) {
            if ( buttonAddClicked && productModifying ) { // case click add product via popup
                alert(initialize_variables.message_show.edit_product_by_popup_alert);
                return;
            }
            var productRowID = currentTarget.attr('data-product-row-id');
            var productID = currentTarget.attr('product-id');
            var variationsModify = $('#the-list-variations-of-' + productID + ' tr.modifying-row-color');
            if ( typeof fnt_product_data[productRowID] != 'undefined' ) {
                // check current product is modify or not, if is modify: alert message and don't allow to edit product via popup
                if ( typeof fnt_product_data[productRowID]['modifying_product'] != 'undefined' && fnt_product_data[productRowID]['modifying_product'] == 1 ) {
                    alert(initialize_variables.message_show.edit_product_by_popup_alert);
                    return;
                }
                // check current product variations area, if have and have any variation is modify: alert message and don't allow to edit product via popup
                if ( typeof variationsModify != 'undefined' && variationsModify.length > 0 ) {
                    alert(initialize_variables.message_show.variation_modify_alert);
                    return;
                }
            }
        }
        // end check current row is modified

        // popup is opened
        popupOpened = true;
        //
        addIframeToPopup();
        hideBodyScroll();
        var buttonAdd = $(e.currentTarget);
        // get link for load into iframe
        iframeLink = buttonAdd.attr('link');
        // update popup title
        changePopupTitle();
        // display popup
        showElement(popupEditProduct);
        // change height content of popup
        changIframeHeight();
        // update selector of popup body
        popupBody = $('#popup-edit-product .modal-body');
        // show popup loading
        showElement(popupLoading);
        // hide iframe
        hiddenElement(iframe);
        // get product id in element attribute, if click add product, productId will set when Iframe loaded
        if ( ! buttonAddClicked ) { // if click edit product via popup
            productId = buttonAdd.attr('product-id');
            productType = buttonAdd.attr('product-type');
        }
        // add src for iframe to load screen
        iframe.attr('src', iframeLink);
        buttonSaveProduct.attr('disabled', 'disabled');
        iframeLoaded();
    };

    var isFireFox = function () {
        return navigator.userAgent.indexOf("Firefox") != -1;
    };

    // Fix bug wp_editor in Firefox browser
    var fixEditorInFirefox = function () {
        try {
            // check browser is FireFox???
            if ( isFireFox() && typeof iframe.get(0).contentWindow.tinymce != 'undefined' ) {
                // reinit excerpt editor
                iframe.get(0).contentWindow.tinymce.init( iframe.get(0).contentWindow.tinyMCEPreInit.mceInit['excerpt'] );
                // delete error post content editor
                var contentVisualTab = iframe.contents().find('#wp-content-editor-container div.mce-tinymce:first');
                contentVisualTab.remove();
                // remove broken of content editor
                var ed = iframe.get(0).contentWindow.tinymce.get('content');
                var contentValue = iframe.get(0).contentWindow.jQuery('textarea#content').val();
                iframe.get(0).contentWindow.tinymce.remove( ed );
                // reinit content editor
                iframe.get(0).contentWindow.tinymce.init( iframe.get(0).contentWindow.tinyMCEPreInit.mceInit['content'] );
                // check the visual tab of content editor is opening???
                var contentVisualHidden = iframe.get(0).contentWindow.tinymce.get('content').hidden;
                // switch to html tab
                iframe.get(0).contentWindow.switchEditors.go( 'content', 'html' );
                // set correct value to tab html - textarea tag
                iframe.get(0).contentWindow.jQuery('textarea#content').val(contentValue);
                // clear data undo/redo of tinymce
                iframe.get(0).contentWindow.tinymce.get( 'content' ).undoManager.clear();
                // if tab visual is active when open popup, switch back to tab visual
                if ( ! contentVisualHidden ) {
                    iframe.get(0).contentWindow.switchEditors.go( 'content', 'tmce' );
                }
            }
        } catch ( error ) {
            console.log(error.message);
        }
    };
    // change title of header of popup
    var changePopupTitle = function ( value ) {
        if( value ) {
            popupTitle.html( value );
        } else {
            popupTitle.html( popupTitleString );
        }
    };

    // close popup and update some variable
    var closePopupProduct = function () {
        // reset flag
        buttonSaveClicked = false;
        // remove iframe
        removeIframeOnPopup();
        // close popup
        hiddenElement(popupEditProduct);
        // update flag
        popupOpened = false;
        // update flag
        buttonAddClicked = false;
        // reset productId
        productId = 0;
        // reset productType
        productType = undefined;
        // show window scroll
        bodyShowScroll();
    };
    // hide an element
    var hiddenElement = function ( element ) {
        if ( typeof element != 'undefined' && ! element.hasClass( 'hidden' ) ) {
            element.addClass( 'hidden' );
        }
    };
    // show an element
    var showElement = function ( element ) {
        if ( typeof element != 'undefined' && element.hasClass( 'hidden' ) ) {
            element.removeClass( 'hidden' );
        }
    };

    var sendAjaxEditProduct = function () {
        // get data of post form
        $.extend(FormSerializer.patterns, {
            validate: /^[a-z_][a-z0-9_-]*(?:\[(?:\d*|[a-z0-9_-]+)\])*$/i,
            key:      /[a-z0-9_-]+|(?=\[\])/gi,
            named:    /^[a-z0-9_-]+$/i
        });
        //
        var formData = iframe.contents().find('#post').serializeObject();
        if(!_.isEmpty(formData["meta"])){
            formData["meta"] = window.fntQEPP.Core.cleanArrayAndFormatToObj(formData["meta"]);
        }
        // process data of form
        var productContent = formData['content'];
        delete formData['content'];
        var productExcerpt = formData['excerpt'];
        delete formData['excerpt'];
        // prepare data will send to server
        var dataPass = {
            action: 'fnt_product_manage',
            real_action: 'call_function_edit_product',
            edit_mode: buttonAddClicked == true ? 'add' : 'edit',
            product_data: window.fntQEPP.Core.compressDataForAJAXSendWithoutFormatArray(formData),
            content: productContent,
            excerpt: productExcerpt
        };
        window.fntQEPP.Core.ajaxRequestManual( dataPass, handleResponseEditProduct );
    };
    var handleResponseEditProduct = function ( response ) {
        // Check result of response, if FAILED => reject
        if ( response.result == 'FAILED') {
            enableEditPopup();
            changePopupTitle( initialize_variables.message_show.save_product_error + ' ' + response.data );
            return;
        }
        // if edit product success, response value larger 0
        var productID = parseInt( response.data );
        if ( productID != 'NaN' && productID > 0 ) {
            if ( buttonAddClicked ) { // if is Add product
                // reset button Saving
                buttonSaveProduct.button('reset');
                // reset color of button save
                if ( buttonSaveProduct.hasClass( 'modifying-button' ) ) {
                    buttonSaveProduct.removeClass( 'modifying-button' );
                }
                // hidden popup
                hiddenElement(popupEditProduct);
                // update flag productModifying
                productModifying = false;
                // reload page
                $('#popup-edit-product').remove();
                window.fntQEPP.Core.showLoading();
                windowReload();
            } else {
                sendAjaxGetProductJustEdit();
            }
            changePopupTitle();
        } else { // if is Edit product
            enableEditPopup();
            changePopupTitle( initialize_variables.message_show.save_product_error );
        }
    };
    /*
    Send to server product id, which is editing
    Response data of product
     */
    var sendAjaxGetProductJustEdit = function () {
        var dataPass = {
            action: 'fnt_product_manage',
            real_action: 'get_product_just_edit',
            screen_id: list_args.screen.id, // since ver 1.1, use to get new Instance of WP_LIST_TABLE, use function none static of instance
            product_id: productId
        };
        window.fntQEPP.Core.ajaxRequestManual( dataPass, handleResponseGetProductJustEdit);
    };
    var disableEditPopup = function() {
        // disable button CTA of popup
        buttonCTA.attr('disabled', 'disabled');
        // Show loading for button SAVE
        buttonSaveProduct.button('loading');
        // change color for button save
        if ( ! buttonSaveProduct.hasClass( 'modifying-button' ) ) {
            buttonSaveProduct.addClass( 'modifying-button' );
        }
        // disabled edit in popup content when Saving
        if ( typeof popupBody != 'undefined' && ! popupBody.hasClass( 'disabled-element' ) ) {
            popupBody.addClass('disabled-element');
        }
    };
    var enableEditPopup = function() {
        // enable button CTA
        buttonCTA.removeAttr('disabled');
        // reset button Saving
        buttonSaveProduct.button('reset');
        // reset color of button save
        if ( buttonSaveProduct.hasClass( 'modifying-button' ) ) {
            buttonSaveProduct.removeClass( 'modifying-button' );
        }
        if ( typeof popupBody != 'undefined' && popupBody.hasClass( 'disabled-element' ) ) {
            popupBody.removeClass('disabled-element');
        }
        buttonSaveClicked = false;
    };
    // process value response
    var handleResponseGetProductJustEdit = function ( response ) {
        // Check result of response, if FAILED => reject
        if ( response.result == 'FAILED') {
            enableEditPopup();
            changePopupTitle( initialize_variables.message_show.save_product_error + response.data );
            return;
        }
        // replace table row
        replaceDataInHTML(response);
        // since ver 1.1
        window.fntQEPP.ProductVariationHanlder.init();
        // end since ver 1.1
        window.fntQEPP.ProductListHandler.reInitInputMask();
        enableEditPopup();
    };

    /**
     * Use for replace html of variations area and js variable when use feature edit product variable via popup
     * @param variationsData
     */
    var replaceVariationsHtmlJs = function( variationsData ) {
        if ( typeof variationsData != 'undefined' ) {
            var variationsDataHtml = variationsData['variations_html_data'];
            var variationsDataJs = variationsData['variations_js_data'];
            var selectionDefaultAttributes = variationsData['selection_default_attributes'];

            if ( typeof variationsDataHtml != 'undefined' && typeof variationsDataJs != 'undefined' && typeof selectionDefaultAttributes != 'undefined' ) {
                // replace html of variations area
                $( '#the-list-variations-of-' + productId ).html( variationsDataHtml );
                // replace html of selection default attributes
                $( '#selection-default-attribute-wrapper-' + productId ).html( selectionDefaultAttributes );
                // replace variations data js
                _.each ( variationsDataJs, function ( value, index ) {
                    fnt_product_data[index] = value;
                } );
            }
        }
    };

    var replaceAttributesHtmlJs = function( attributesData ) {
        if ( typeof attributesData != 'undefined' ) {
            var attributesDataHtml = attributesData['attributes_html_data'];
            var attributesDataJs = attributesData['attributes_js_data'];
            var selectionOptionAttribute = attributesData['selection_option_attribute'];

            if ( typeof attributesDataHtml != 'undefined' && typeof attributesDataJs != 'undefined' && typeof selectionOptionAttribute != 'undefined' ) {
                // replace html of attributes area
                $( '#the-list-attributes-of-' + productId ).html( attributesDataHtml );
                // replace html of selection option type to add attributes
                $( '#selection-option-add-attribute-wrapper-' + productId ).html( selectionOptionAttribute );
                // replace attributes data js
                _.each ( attributesDataJs, function ( value, index ) {
                    fnt_product_attributes[index] = value;
                } );
            }
        }
    };
    var replaceCatSelection = function( catSelectionData ) {
        var catSelection = $('.popup-select-product-type-wrapper');
        var catSelectionModalBody = catSelection.find('.modal-body');
        catSelectionModalBody.html( catSelectionData );
    };
    /*
     This function will replace old value of row edited
     */
    var replaceDataInHTML = function( response ) {
        var productDataHtml = response.data['product_html_data'];
        var productDataJs = response.data['product_js_data'];
        // since ver 1.1
        // get product variation data if parent product is variable
        var variationsData = response.data['product_variation_data'];
        if ( typeof variationsData != 'undefined' ) {
            replaceVariationsHtmlJs( variationsData );
        }
        var catSelectionData = response.data['cat_selection'];
        if ( typeof catSelectionData != 'undefined' ) {
            replaceCatSelection( catSelectionData );
        }
        // end since ver 1.1
        if( typeof productDataHtml == 'undefined' || typeof productDataJs == 'undefined') {
            return;
        }
        var rowClass = 'tr.main-row-' + productId;
        var rowEdited = '#the-list ' + rowClass;
        _.each ( productDataHtml, function( value, index ) {
            if ( value != '' ) {
                var cellTarget = $( rowEdited + ' td.column-' + index );
                var cellReplace = $( rowEdited + ' td.column-' + index + ' div.ajax-replace' );
                // do replace
                cellReplace.remove();
                cellTarget.prepend( value );
            }
        } );
        // replace item in array global fnt_product_data
        fnt_product_data['row-' + productId] = productDataJs;
        // remove row color if have
        var currentRow = $( rowClass );
        if ( typeof currentRow != 'undefined' ) {
            // if have inline style
            currentRow.removeAttr( 'style' );
            // or have class style for color
            if ( currentRow.hasClass( 'adding-row-color' ) ) {
                currentRow.removeClass( 'adding-row-color' );
            }
        }
        // ReInit events for button Edit Desc/Short Desc
        window.fntQEPP.editorDialog.init();
        // ReInit events for button show attributes
        window.fntQEPP.ProductVariableHanlder.reInitEvents();
    };

    /**
     * Use for sync variations area when edit product variable via popup
     * since ver 1.1
     */
    var sendAjaxGetProductVariationsJustEdit = function() {
        var dataPass = {
            action: 'fnt_product_manage',
            real_action: 'get_product_variations_just_edit',
            screen_id: list_args.screen.id,
            product_id: productId
        };
        window.fntQEPP.Core.ajaxRequestManual( dataPass, handleResponseGetProductVariationsJustEdit );
    };

    var handleResponseGetProductVariationsJustEdit = function( response ) {
        // Check result of response, if FAILED => reject
        if ( response.result == 'FAILED') {
            changePopupTitle( initialize_variables.message_show.save_product_error + response.data );
            enableEditPopup();
            return;
        }
        var variationsData = response.data;
        replaceVariationsHtmlJs( variationsData );
        replaceAttributesHtmlJs( variationsData );

        // since ver 1.1
        window.fntQEPP.ProductVariationHanlder.init();
        window.fntQEPP.ProductVariableHanlder.initWhenAddDefaultAttribute();
        // end since ver 1.1
        window.fntQEPP.ProductListHandler.reInitInputMask();
        enableEditPopup();
    };
    // check wp_editor which tab is active
    var tabTextIsActive = function ( textArea ) {
        return textArea.attr( 'aria-hidden' ) == 'false';
    };
    /*
    wp_editor
    Move value of visual tab to text tab
     */
    var moveValueToTextTab = function () {
        // get the content of editor
        var contentEditor = iframe.get(0).contentWindow.tinymce.get('content');
        var contentTextArea = iframe.contents().find('textarea#content');

        // Check if element doesn't found, ignore process
        if ( typeof contentEditor != 'undefined' && contentEditor != null &&
             typeof contentTextArea != 'undefined' && contentTextArea != null ) {
            // Check if tab visual is active, then move the content to tab text
            if ( ! tabTextIsActive( contentTextArea ) ) {
                // Move value form visual tab to text tab
                contentEditor.save();
            }
        } else {
            alert('Sorry, we have error when save product!');
            enableEditPopup();
        }

        var expertEditor = iframe.get(0).contentWindow.tinymce.get('excerpt');
        var expertTextArea = iframe.contents().find('textarea#excerpt');

        // Check if element doesn't found, ignore process
        if ( typeof expertEditor != 'undefined' && expertEditor != null &&
             typeof expertTextArea != 'undefined' && expertTextArea != null ) {
            // Check if tab visual is active, then move the content to tab text
            if ( ! tabTextIsActive( expertTextArea ) ) {
                // Move value form visual tab to text tab
                expertEditor.save();
            }
        } else {
            alert('Sorry, we have error when save product!');
            enableEditPopup();
        }
    };
    // =======================Capture events===========================
    // catch event click on button add or edit inline link bellow product title
    $( ".fnt-edit-product").off('click').on('click', function ( e ) {
        popupTitleString = $(e.currentTarget).attr('title');
        showPopupProduct(e);
    });
    $( ".add-product-backbone-modal").off('click').on('click', function ( e ) {
        popupTitleString = $(e.currentTarget).attr('title');
        buttonAddClicked = true;
        showPopupProduct(e);
    });
    // click button close
    buttonClosePopup.off('click').on('click', closePopupProduct);
    // click button cancel
    buttonCancelPopup.off('click').on('click', closePopupProduct);
    // click button save
    buttonSaveProduct.off('click').on('click', saveProduct);
    // =======================End capture events===========================
} );