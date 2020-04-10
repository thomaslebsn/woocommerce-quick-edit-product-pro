(function ($) {
    window.fntQEPP =  window.fntQEPP || {};
    window.fntQEPP.TableRowSorter = (function(){
        var module = {};
        module.init = function() {
            $(document).ready(function(){
                $('.wp-list-attributes-table').rowSorter({
                    handler: 'td.column-position',
                    onDrop: function(tbody, row, new_index, old_index) {
                        // update position of row
                        window.fntQEPP.ProductVariableHanlder.updateRowPosition();
                        // update alternate
                        window.fntQEPP.Core.updateAlternateTable( $(tbody) );
                    }
                });
            });
            return module;
        };
        return module.init();
    })();
})(jQuery);