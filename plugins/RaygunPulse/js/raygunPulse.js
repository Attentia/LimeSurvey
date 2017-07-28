var raygunPulse = (function (global) {

    var generateUser = function(options){
        // PHP sends a "1" or "0" but RayGun API expects a boolean.
        options.user.isAnonymous = options.user.isAnonymous === "1" ? true : false;
         
        return {
            identifier: options.user.identifier,
            isAnonymous: options.user.isAnonymous,
            firstName: (!options.user.isAnonymous) ? options.user.firstName : null,
            fullName: (!options.user.isAnonymous) ? options.user.firstName + ' ' + options.user.lastName : null,
            email: (!options.user.isAnonymous ) ? options.user.email : null
        };
    };
    
    return {
        init: function (options) {
            global.rg4js('apiKey', options.apiKey);
            global.rg4js('enablePulse', true);
            global.rg4js('setUser', generateUser(options));
            
            // Generate a custom page view
            setTimeout(function () {
                // Only send to Raygun when a group was set.
                if(global.Raygun && options.group) {
                    var url = options.surveyId + '/' + options.group.id + '/' + options.group.name;
                    global.Raygun.trackEvent('pageView', { path: url });
                }
             }, 1000);
        }
    };
})(window);
