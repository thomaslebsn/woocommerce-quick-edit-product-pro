(function ($) {
    window.fntQEPP =  window.fntQEPP || {};
    window.fntQEPP.Settings = (function(){
        var module = {},
            settings = {
                'confirmationDeletingAction':undefined,
                //'confirmationSaveAllAction':undefined,
                'modifyingRowColor':undefined,
                'addingRowColor':undefined
            };
        module.init = function(){
            settings = {
                'confirmationDeletingAction':initialize_variables['fnt-setting-data']['confirmationDeletingAction'],
                //'confirmationSaveAllAction':initialize_variables['fnt-setting-data']['confirmationSaveAllAction'],
                'modifyingRowColor':initialize_variables['fnt-setting-data']['modifyingRowColor'],
                'addingRowColor':initialize_variables['fnt-setting-data']['addingRowColor'],
                'setProductStatusOnCreating':initialize_variables['fnt-setting-data']['setProductStatusOnCreating']
            };
            return module;
        };

        module.setSetting = function (key, value) {
            if(settings.hasOwnProperty(key)){
                settings[key] = value;
                return true;
            }
            return false;
        };

        module.setMultipleSettings = function (settingData) {
            if(!_.isArray(settingData)) return false;
            _.each(settingData, function(item, key){
                if(settings.hasOwnProperty(item['name'])){
                    window.fntQEPP.Settings.setSetting(item['name'],item['value']);
                }
            });
        };

        module.getSetting = function (key, defaultValue) {
            if(settings.hasOwnProperty(key)){
                if(typeof settings[key] == 'undefined'){
                    return defaultValue;
                }
                return settings[key];
            }
            return (defaultValue) ? defaultValue : null;
        };

        module.isEnabledConfirmationDeletingProducts = function () {
            var checkingValue = module.getSetting('confirmationDeletingAction','0');
            return parseInt(checkingValue) !== 0;
        };

        module.settingsData = function () {
            return settings;
        };

        return module.init();
    })();
})(jQuery);