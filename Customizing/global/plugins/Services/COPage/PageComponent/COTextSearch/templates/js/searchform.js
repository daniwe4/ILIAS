il = il || {};
il.TMS = il.TMS || {};
il.TMS.copage = il.TMS.copage || {};

(function ($, copage) {
    copage.fnc = (function ($) {
        var click = function(ele, target) {
            var prev = $(ele).prev();
            var location = target + '&' + prev.attr("name") + '=' + prev.val();
            window.location.href = location;
            return false;
        };

        return {
            click: click
        }

    })($);
})($, il.TMS.copage);
