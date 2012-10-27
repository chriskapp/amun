
Ext.define('Amun.xrds.Manager', {
    singleton: true,

    services: [],

    discover: function(options){
        options = options || {};
        var me = this,
            scope = options.scope || window;

        Ext.Ajax.request({
            url: psx_url,
            scope: this,
            success: function(response, opts) {
                var xrdsUrl = response.getResponseHeader('X-XRDS-Location');
                if (xrdsUrl != '') {
                    var serviceStore = new Ext.data.XmlStore({
                        proxy: {
                            type: 'ajax',
                            url: xrdsUrl,
                            reader: {
                                type: 'xml',
                                root: 'XRD',
                                record: 'Service'
                            }
                        },
                        record: 'Service',
                        fields: ['URI']
                    });

                    serviceStore.load({
                        scope: this,
                        callback: function(records, operation, success){
                            this.services = this.parseRecords(records);
                            Ext.callback(options.success, options.scope, [this.services]);
                        }
                    });
                } else {
                    var msg = 'Could not find XRDS header';
                    Ext.callback(options.failure, options.scope, [msg]);
                }
            },
            failure: function(response, opts) {
                var msg = 'Could not request home url';
                Ext.callback(options.failure, options.scope, [msg]);
            }
        });
    },

    parseRecords: function(records){
        var result = [];
        for (var i = 0; i < records.length; i++) {
            // get uri
            var uri = Ext.dom.Query.selectNode('URI:first', records[i].raw).textContent;

            // get types
            var types = Ext.dom.Query.select('Type', records[i].raw);
            var ty = [];
            for (var j = 0; j < types.length; j++) {
                ty.push(types[j].textContent);
            }

            result.push(Ext.create('Amun.Service', {
                uri: uri,
                types: ty
            }));
        }

        console.log('Found ' + result.length + ' services');

        return result;
    },

    getServices: function(){
        return this.services;
    },

    findService: function(type){
        for (var i = 0; i < this.services.length; i++) {
            if (this.services[i].hasType(type)) {
                return this.services[i];
            }
        }
        return false;
    },

    findServiceUri: function(type){
        var service = this.findService(type);
        if (service != false) {
            return service.getUri();
        }
        return false;
    }

});
