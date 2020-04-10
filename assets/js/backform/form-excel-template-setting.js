(function ($) {
    window.fntQEPP =  window.fntQEPP || {};
    window.fntQEPP.formExcelTemplateSetting = (function(){
        var module = {},
            model = null,
            fields = null,
            form = null,
            formElementID = "#form-create-excel-template-import",
            modelSubmission = null,
            buttonSubmit = null;
        module.init = function(){
            return module;
        };

        var createModel = function () {
            model = Backbone.Model.extend({
                defaults: {
                    selectedColumnsToCreateTemplate: [],
                    selectedProductType: ""
                },
                validate: function(attributes, options) {
                    this.errorModel.clear();
                    var selectedColumnsToCreateTemplate = this.get('selectedColumnsToCreateTemplate');
                    $('.selectedColumnsToCreateTemplate').removeClass('has-error');
                    $('.selectedColumnsToCreateTemplate span').remove();
                    if(typeof(selectedColumnsToCreateTemplate) === 'undefined' || selectedColumnsToCreateTemplate.length <= 0){
                        var errorMessageSelectedColumns = 'You must choose at least 1 columns for create template!';
                        this.errorModel.set({selectedColumnsToCreateTemplate: errorMessageSelectedColumns});
                        $('.selectedColumnsToCreateTemplate').addClass('has-error');
                        $('.selectedColumnsToCreateTemplate .checklist')
                            .append('<span class="' + Backform.helpClassName + ' error"> ' + errorMessageSelectedColumns + '</span>');
                    }

                    var selectedProductType = this.get('selectedProductType');
                    if(typeof(selectedProductType) === 'undefined' || selectedProductType === "" || selectedProductType === "*"){
                        this.errorModel.set({selectedProductType: "Product type is required!"});
                    }
                    if (!_.isEmpty(_.compact(this.errorModel.toJSON())))
                        return "Have some errors. Please fix them before continuing your process.";
                }
            });
        };


        var createFields = function () {
            fields = [{
                name:"selectedColumnsToCreateTemplate",
                label: "Select columns:",
                control: "check-list",
                options: [
                    {label: "Product name", value: "post_title"},
                    {label: "Description", value: "product_content"},
                    {label: "Short Description", value: "product_excerpt"},
//                    {label: "Category", value: "product_cat"},
                    {label: "Tags", value: "product_tag"}
                ]
            }, {
                name: "selectedProductType",
                label: "Select product type",
                control: "select",
                options: [
                    {label: "Select a product type", value: "*"},
                    {label: "Simple product", value: "simple"},
                    {label: "Grouped product", value: "grouped"},
                    {label: "External/Affiliate product", value: "external"},
                    {label: "Variable product", value: "variable"}
                ],
                extraClasses: ["select-product-type"]
            }, {
                name: "selectedAttribute",
                label: "Select attributes",
                data: initialize_variables.list_attributes,
                control: "variable-attribute"
            }, {
                id: "proceed-create-template",
                control: "button",
                label: "Create Template",
                extraClasses: ["button", "button-primary", "button-large"]
            }
            ];
        };

        var createForm = function () {
            form = new Backform.Form({
                el: $(formElementID),
                model: new model(),
                fields: fields, // Will get converted to a collection of Backbone.Field models
                events: {
                    "submit": function(e) {
                        e.preventDefault();
                        buttonSubmit = form.fields.get("proceed-create-template");
                        if (form.model.isValid()) {
                            modelSubmission = form.model;
                            handleCreateTemplateAction();
                        } else {
                            buttonSubmit.set({status: "error", message: form.model.validationError});
                        }
                        return false;
                    }
                }
            });
        };

        var handleCreateTemplateAction = function () {
            var dataPass = {
                selectedColumns: modelSubmission.get('selectedColumnsToCreateTemplate'),
                selectedProductType: modelSubmission.get('selectedProductType'),
                selectedAttribute: modelSubmission.get('selectedAttribute'),
                action: 'fnt_product_manage',
                real_action: 'save_create_template_setting'
            };
            window.fntQEPP.Core.ajaxRequestManual(dataPass,saveCreateTempateSettingCallback);
        };

        var saveCreateTempateSettingCallback = function(response){
            if(response.result === "SUCCESS"){
                var createTemplateUrl = jQuery('#create-template-url').val();
                var isOpenedNewTab = window.open(createTemplateUrl, '_blank');
                if(isOpenedNewTab){
                    //Browser has allowed it to be opened
                    isOpenedNewTab.focus();
                }else{
                    //Browser has blocked it
                    alert('Please allow popups for this site');
                }
                buttonSubmit.set({status: "success", message: initialize_variables.message_show.excel_template_setting_successfully});
            } else {
                buttonSubmit.set({status: "error", message: excel_template_setting_successfully.excel_template_setting_some_error});
            }
            return false;
        };

        module.renderForm = function () {
            createModel();
            createFields();
            createForm();
            if(form !== null){
                form.render();
                $(".tab-content-block").trigger(window.fntQEPP.CompatibleSafari.eventRecalculateModalHeight);
            }
        };

        return module.init();
    })();
})(jQuery);