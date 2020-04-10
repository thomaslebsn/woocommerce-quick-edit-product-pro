(function ($) {
    window.fntQEPP =  window.fntQEPP || {};
    window.fntQEPP.CompatibleFirefox = (function(){
        var module = {};
        module.init = function(){
            $(document).ready( function() {

            });
            $( window ).load(function() {

            });

            return module;
        };

        return module.init();
    })();
})(jQuery);