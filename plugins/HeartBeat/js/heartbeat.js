var heartBeat = (function(global, $) {
    return {
        beat: function (options) {
            global.setInterval(function() {
                $.get(options.endpoint);
            }, options.interval || 15000);
        }
    };
})(window, $);
