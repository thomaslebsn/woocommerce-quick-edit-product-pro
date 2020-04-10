(function ($) {
    /**
     * This form only is used to render form fields but modal save actions will be handle save all settings. */
    window.fntQEPP =  window.fntQEPP || {};
    window.fntQEPP.formSettings = (function(){
        var module = {},
            model = null,
            fields = null,
            form = null,
            formElementID = "#form-settings",
            modelSubmission = null,
            buttonSubmit = null,
            sMInstance = null,
            settingsData = null;
        module.init = function(){
            return module;
        };

        var createModel = function () {
            model = Backbone.Model.extend({
                defaults: {
                    confirmationDeletingAction: window.fntQEPP.Settings.getSetting('confirmationDeletingAction','0'),
                    //confirmationSaveAllAction: window.fntQEPP.Settings.getSetting('confirmationSaveAllAction','0'),
                    modifyingRowColor: window.fntQEPP.Settings.getSetting('modifyingRowColor','#cfe4f9'),
                    addingRowColor: window.fntQEPP.Settings.getSetting('addingRowColor', '#0073AA'),
                    setProductStatusOnCreating: window.fntQEPP.Settings.getSetting('setProductStatusOnCreating', 'pending')
                },
                validate: function(attributes, options) {
                    this.errorModel.clear();
                    if (!_.isEmpty(_.compact(this.errorModel.toJSON())))
                        return initialize_variables.message_show.export_product_have_some_error;
                }
            });
        };


        var createFields = function () {
            fields = [{
                name: "confirmationDeletingAction",
                label: "Delete Products Confirmation:",
                control: "radio",
                options: [
                    {label: "Enabled", value: "1"},
                    {label: "Disabled", value: "0"}
                ]
            },
                //{
                //    name:"confirmationSaveAllAction",
                //    label: "Save All Products Confirmation:",
                //    control: "radio",
                //    options: [
                //        {label: "Enabled", value: "1"},
                //        {label: "Disabled", value: "0"}
                //    ]
                //},
                {
                    name: "modifyingRowColor",
                    label: "Choose a color",
                    extraClasses: ["input-medium-width"],
                    helpMessage: initialize_variables.message_show.form_setting_modifying_row_color,
                    control: "color-picker"
                },
                {
                    name: "addingRowColor",
                    label: "Choose a color",
                    extraClasses: ["input-medium-width"],
                    helpMessage: initialize_variables.message_show.form_setting_adding_row_color,
                    control: "color-picker"
                },
                {
                    name: "setProductStatusOnCreating",
                    label: "Choose product status for new product",
                    extraClasses: ["select-medium-width"],
                    helpMessage: initialize_variables.message_show.form_setting_set_product_status_on_creating,
                    control: "select",
                    options: [
                        {label: "Pending", value: "pending"},
                        {label: "Published", value: "publish"}
                    ]
                }
            ];
        };

        var createForm = function () {
            form = new Backform.Form({
                el: $(formElementID),
                model: new model(),
                fields: fields
            });
        };

        module.saveSettings = function (settingModalInstance) {
            sMInstance = settingModalInstance;
            settingsData = sMInstance.$el.find('#form-settings').serializeArray();
            var dataPass = {
                settingsData: settingsData,
                action: 'fnt_product_manage',
                real_action: 'save_settings'
            };
            window.fntQEPP.Core.ajaxRequestManual(dataPass,handleResponseBySaveSettingAction);
            return false;
        };

        var handleResponseBySaveSettingAction = function (response) {
            window.fntQEPP.Core.hideLoading();
            if(response.result === 'SUCCESS'){
                window.fntQEPP.Settings.setMultipleSettings(settingsData);
                window.fntQEPP.ProductListHandler.updateRowColorViaUpdatedSetting();
                sMInstance.ui.buttons.cancelSetting.trigger('click');
            } else {
            }
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