(function ($) {
    window.fntQEPP = window.fntQEPP || {};
    window.fntQEPP.TableScrollHandler = (function () {
        var module = {};
        // check element had horizontal scroll bar yet?
        var wrapWPListTable;
        var tableViewBottomScroll;
        var hasHorizontalScrollBar = function (element) {
            return element.get(0) ? element.get(0).scrollWidth > element.innerWidth() : false;
        };
        var variableInit = function () {
            wrapWPListTable = $(".wrapper-wp-list-table");
            tableViewBottomScroll = $(".table-view-bottom-scroll");
            // init some special of div
            tableViewBottomScroll.width(wrapWPListTable.width());
            // content div
            var tableViewBottomScrollDiv = $(".table-view-bottom-scroll .scroll-div");
            var WPListTable = $(".wrapper-wp-list-table .wp-list-table");
            tableViewBottomScrollDiv.width(WPListTable.width());
        };
        module.hideExtraScroll = function () {
            variableInit();
            // to hide extra scroll when default scroll exists
            var windowHeight = $(window).height();
            var tableHeight = wrapWPListTable.height();
            var tableTop = wrapWPListTable.offset().top;
            var windowScrollTop = $(window).scrollTop();
            if( tableTop > windowHeight) {
                tableViewBottomScroll.addClass('hidden');
            }
            if (windowHeight - tableTop + windowScrollTop > tableHeight) {
                tableViewBottomScroll.addClass('hidden');
            } else if (!hasHorizontalScrollBar(wrapWPListTable)) {
                tableViewBottomScroll.addClass('hidden');
            } else {
                tableViewBottomScroll.removeClass('hidden');
                tableViewBottomScroll.scrollLeft(wrapWPListTable.scrollLeft());
            }
        };
        module.makeScroll = function () {
            // Catch event scroll to hide extra scroll when default scroll exists
            module.hideExtraScroll();
            $(window).scroll(function () {
                module.hideExtraScroll();
            });
            $(window).resize(function () {
                module.hideExtraScroll();
            });
            // Catch event scroll
            tableViewBottomScroll.scroll(function () {
                wrapWPListTable.scrollLeft(tableViewBottomScroll.scrollLeft());
            });
            wrapWPListTable.scroll(function () {
                tableViewBottomScroll.scrollLeft(wrapWPListTable.scrollLeft());
            });
            // End catch event scroll
            // Catch width change of wrap-table when collapse menu
            $('#collapse-menu').on('click', function () {
                variableInit();
            });
            // End catch width change of wrap-table when collapse menu
            // Catch height change of wrap-table when show/hide screen options
            $("#screen-options-link-wrap").off("click").on("click", function () {
                setTimeout(function () {
                    module.hideExtraScroll();
                }, 250);
            });
            // End catch height change of wrap-table when show/hide screen options
            // Catch width change of wrap-table when click check box in screen options
            $("#screen-options-wrap .metabox-prefs").off("click", "input.hide-column-tog").on("click", "input.hide-column-tog", function () {
                setTimeout(function () {
                    module.hideExtraScroll();
                }, 25);
            });
            // End catch width change of wrap-table when click check box in screen options
            /* Catch height change of tag area */
            $('#the-list').off('mouseup mousemove', 'textarea.input-text-editable').on('mouseup mousemove','textarea.input-text-editable', function () {
                setTimeout(function () {
                    module.hideExtraScroll();
                }, 300);
            });
            /* End catch height change of tag area */
            /* Catch height change of wrap when close the message alert */
            $("div.alert a.close").off("click").on("click", function () {
                module.hideExtraScroll();
            });
            /* End catch height change of wrap when close the message alert */
        };
        module.init = function () {
            variableInit();
            return module;
        };
        return module.init();
    })();
})(jQuery);