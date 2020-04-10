(function ($) {
    window.fntQEPP =  window.fntQEPP || {};
    window.fntQEPP.PopupMessage = (function(){
        var module = {};
        module.init = function() {
            return module;
        };
        module.limitFeatureAlert = function () {
            bootbox.dialog({
                message: '<h4>You must by full version to use this feature!</h4>',
                buttons: {
                    continue: {
                        label: "Continue free",
                        className: "btn-continue",
                        callback: function() {
                            // do your stuff here
                        }
                    },
                    buy: {
                        label: "Buy full version",
                        className: "btn-buy-full-version",
                        callback: function() {
                            window.open('http://finalthemes.com/','_blank');
                        }
                    }
                },
                onEscape: function() { console.log("Escape!"); },
                backdrop: true,
                container: "#popup-message"
            });
        };
        module.confirmMessage = function (message) {
            message = typeof message !== 'undefined' ? message : 'Please insert the message to function!';
            bootbox.confirm({
                message: message,
                callback: function(result) {
                    if (result) {
                        console.log('result' + result);
                    }
                },
                onEscape: function() { console.log("Escape!"); },
                backdrop: true,
                container: "#popup-message"
            });
        };
        return module.init();
    })();
})(jQuery);