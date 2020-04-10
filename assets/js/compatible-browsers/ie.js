(function ($) {
    window.fntQEPP =  window.fntQEPP || {};
    window.fntQEPP.CompatibleIE = (function(){
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