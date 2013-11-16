
Ext.define('Amun.Application', {

    user: null,
    services: null,

    constructor: function(config){
        config = config || {};
        Ext.apply(this, config);
    },

    start: function(){
        // discover services
        Amun.xrds.Manager.discover({
            scope: this,
            success: this.onServiceDiscovered,
            failure: function(msg){
                console.log(msg);
            }
        });
    },

    onServiceDiscovered: function(services){
        this.services = services;
        // check user auth
        Amun.Auth.verify({
            scope: this,
            success: this.onAuthentication,
            failure: function(msg){
                console.log(msg);
            }
        });
    },

    onAuthentication: function(user){
        this.user = user;
        if (user.loggedIn == true && user.status == 'Administrator') {
            // start application
            var viewport = Ext.create('Ext.container.Viewport', {
                layout: 'border',
                items: [{
                    region: 'north',
                    title: '<div class="wb-header"><div style="float:left;">Workbench (<a href="' + psx_url + '">' + psx_url + '</a>)</div><div style="float:right;">Logged in as: <a href="' + user.profileUrl + '">' + user.name + '</a><img src="' + user.thumbnailUrl + '" width="16" style="float:right;margin-left:4px" /></div></div>',
                    margins: '0 0 0 0',
                    border: false
                },{
                    region: 'west',
                    layout: 'fit',
                    width: 200,
                    minWidth: 175,
                    maxWidth: 400,
                    margins: '5 5 5 5',
                    items: [{
                        header: false,
                        xtype: 'navigation'
                    }]
                },{
                    region: 'center',
                    layout: 'fit',
                    margins: '5 5 5 0',
                    items: [{
                        header: false,
                        xtype: 'content'
                    }]
                }]
            });
        } else {
            Ext.Msg.alert('Information', 'Please <a href="' + psx_url + '">login</a> with an administrator account', function(){
                window.location = psx_url;
            });
        }
    }

});