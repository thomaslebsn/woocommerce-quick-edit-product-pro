(function ($) {
    window.fntQEPP =  window.fntQEPP || {};
    window.fntQEPP.CompatibleSafari = (function(){
        var module = {},
            modalBottomHeight = 120;

        /* Event Fire to trigger anywhere */
        module.eventRecalculateModalHeight = 'safari.re-calculate.modal.height';

        module.init = function(){
            $(document).ready( function() {
                $(document).off(module.eventRecalculateModalHeight,".tab-content-block").on(module.eventRecalculateModalHeight,".tab-content-block",safariRecalculateModalHeight);
            });
            $( window ).load(function() {

            });

            return module;
        };

        var safariRecalculateModalHeight = function(e){
            if(module.isSafari()){
                var parentHeight = $('.backbone_modal-main').height();
                $(e.currentTarget).css({"height":(parentHeight - modalBottomHeight)});
            }
        };

        module.isSafari = function(){
            return navigator.userAgent.indexOf('Safari') != -1 && navigator.userAgent.indexOf('Chrome') == -1;
        };

        return module.init();
    })();
})(jQuery);