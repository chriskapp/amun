
Ext.define('Amun.Auth', {
    singleton: true,

    verify: function(options){
        options = options || {};
        var me = this,
            scope = options.scope || window;

        // find credentials service
        var uri = Amun.xrds.Manager.findServiceUri('http://ns.amun-project.org/2011/amun/service/my/verifyCredentials');
        if (uri != false) {
            Ext.Ajax.request({
                url: uri + '?format=json',
                success: function(response, opts) {
                    var data = Ext.JSON.decode(response.responseText);
                    var user = Ext.create('Amun.User', data);

                    Ext.callback(options.success, options.scope, [user]);
                },
                failure: function(response, opts) {
                    var msg = 'Could not find verifyCredentials service';
                    Ext.callback(options.failure, options.scope, [msg]);
                }
            });
        } else {
            var msg = 'Could not find verifyCredentials service';
            Ext.callback(options.failure, options.scope, [msg]);
        }
    }

});
