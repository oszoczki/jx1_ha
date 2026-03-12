/**
 * Admin panel JS: growl notifications (jQuery)
 */
(function(global, $) {
    'use strict';

    function showGrowl(message, type) {
        type = type || 'info';

        var $container = $('#growl-container');
        if (!$container.length) {
            $container = $('<div id="growl-container"></div>').css({
                position: 'fixed',
                top: '1rem',
                right: '1rem',
                zIndex: 9999,
                maxWidth: '360px'
            });
            $('body').append($container);
        }

        var $el = $('<div></div>')
            .addClass('growl growl-' + type)
            .attr('role', 'alert')
            .text(message);

        $container.append($el);

        $el.delay(4000).fadeOut(300, function() {
            $(this).remove();
        });
    }

    global.showGrowl = showGrowl;
})(window, window.jQuery);
