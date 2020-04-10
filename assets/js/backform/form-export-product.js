var that = null;
(function ($) {
    window.fntQEPP =  window.fntQEPP || {};
    window.fntQEPP.formExportProduct = (function(){
        var module = {},
            model = null,
            fields = null,
            form = null,
            formElementID = "#form-export-product",
            buttonExportProduct = null,
            unSelectedCheckbox = [],
            itemChecked = [],
            dataExportColumnsSetting = [];
        module.init = function(){
            dataExportColumnsSetting = data_export_columns_setting;
            return module;
        };

        var getSelectedCheckBox = function () {
            var checkedItemOnScreenOption = [];
            $('#adv-settings').find('label input.hide-column-tog:checked').each(function(index, item) {
                checkedItemOnScreenOption.push($(this).val());
            });
            var populateCheckedItems = [];
            var valuesDataExportColumnsSetting = getValuesByArrayExportObject();
            _.each(checkedItemOnScreenOption, function(item, index){
                if($.inArray(item, valuesDataExportColumnsSetting) > -1){
                    populateCheckedItems.push(item);
                }
            });
            itemChecked = populateCheckedItems;
        };

        var getValuesByArrayExportObject = function () {
            var result = [];
            _.each(dataExportColumnsSetting, function (item, index) {
                result.push(item.value);
            });
            return result;
        };

        var createModel = function () {
            model = Backbone.Model.extend({
                defaults: {
                    selectedColumnsToExportProduct: itemChecked
                },
                validate: function(attributes, options) {
                    this.errorModel.clear();
                    var selectedColumnsToExportProduct = this.get('selectedColumnsToExportProduct');
                    $('.selectedColumnsToExportProduct').removeClass('has-error');
                    $('.selectedColumnsToExportProduct span').remove();
                    if(typeof(selectedColumnsToExportProduct) === 'undefined' || selectedColumnsToExportProduct.length <= 3){
                        var errorMessageSelectedColumns = initialize_variables.message_show.export_product_error_selected_columns;
                        this.errorModel.set({selectedColumnsToExportProduct: errorMessageSelectedColumns});
                        $('.selectedColumnsToExportProduct').addClass('has-error');
                        $('.selectedColumnsToExportProduct .checklist')
                            .append('<span class="' + Backform.helpClassName + ' error"> ' + errorMessageSelectedColumns + '</span>');
                    }
                    if (!_.isEmpty(_.compact(this.errorModel.toJSON())))
                        return initialize_variables.message_show.export_product_have_some_error;
                }
            });
        };
        var createFields = function () {
            fields = [{
                name:"selectedColumnsToExportProduct",
                label: "Select columns:",
                control: "check-list",
                options: dataExportColumnsSetting,
                isSeparatedColumns: true,
                numOfInstancePerColumn: 6,
                populatedCheckbox: itemChecked
            }, {
                id: "proceed-export-product",
                control: "button",
                label: "Export",
                extraClasses: ["button", "button-primary", "button-large"]
            }];
        };

        var createForm = function () {
            form = new Backform.Form({
                el: $(formElementID),
                model: new model(),
                fields: fields, // Will get converted to a collection of Backbone.Field models
                events: {
                    "submit": function(e) {
                        e.preventDefault();
                        buttonExportProduct = form.fields.get("proceed-export-product");
                        if (form.model.isValid()) {
                            window.fntQEPP.Core.hideLoading();
                            var selectedColumnsToExportProduct = form.model.get('selectedColumnsToExportProduct');
                            saveExportColumnsSetting(selectedColumnsToExportProduct);
                        } else {
                            buttonExportProduct.set({status: "error", message: form.model.validationError});
                        }
                        return false;
                    }
                }
            });
        };

        module.renderForm = function () {
            getSelectedCheckBox();
            createModel();
            createFields();
            createForm();
            if(form !== null){
                form.render();
                $(".tab-content-block").trigger(window.fntQEPP.CompatibleSafari.eventRecalculateModalHeight);
            }
        };

        var saveExportColumnsSetting = function (selectedColumns) {
            var dataPass = {
                selectedColumns: selectedColumns,
                action: 'fnt_product_manage',
                real_action: 'save_export_columns_setting'
            };
            window.fntQEPP.Core.ajaxRequestManual(dataPass,saveExportColumnsSettingCallback);
        };

        var saveExportColumnsSettingCallback = function(response){
            window.fntQEPP.Core.hideLoading();
            if(response.result === "SUCCESS"){
                var exportUrl = $('#current-export-url').val();
                var isOpenedNewTab = window.open(exportUrl, '_blank');
                if(isOpenedNewTab){
                    //Browser has allowed it to be opened
                    isOpenedNewTab.focus();
                }else{
                    //Broswer has blocked it
                    alert('Please allow popups for this site');
                }
                buttonExportProduct.set({status: "success", message: initialize_variables.message_show.export_product_successfully});
            } else {
                buttonExportProduct.set({status: "error", message: initialize_variables.message_show.export_product_process_have_some_error});
            }
            return false;
        };

        return module.init();
    })();
})(jQuery);