
Ext.define('Amun.service.content.page.Grid', {
    extend: 'Ext.panel.Panel',

    tree: null,
    grid: null,

    initComponent: function(){
        var me = this;

        var config = {
            layout: 'border',
            border: false,
            items: [this.buildTree(), this.buildGrid()]
        };
        Ext.apply(me, config);

        me.callParent();
    },

    reload: function(){
        // tree
        this.tree.getStore().load();

        // grid
        this.grid.reload();
    },

    buildTree: function(){
        var store = Ext.create('Ext.data.TreeStore', {
            autoLoad: true,
            proxy: {
                type: 'ajax',
                url: url + 'api/content/page/tree?format=json'
            },
            reader: {
                type: 'json',
                root: 'tree',
                defaultRootProperty: 'children'
            }
        });

        store.on('load', function(el, node){
            node.firstChild.expand();
        });

        this.tree = Ext.create('Ext.tree.Panel', {
            region: 'west',
            margins: '0 5 0 0',
            header: false,
            border: false,
            width: 200,
            store: store,
            viewConfig: {
                plugins: {
                    ptype: 'treeviewdragdrop',
                    containerScroll: true,
                },
                listeners: {
                    beforedrop: function(node, data, dropRec, dropPosition) {
                        return data.records.length == 1 && dropPosition != 'append';
                    }
                }
            },
            hideHeaders: true,
            useArrows: true,
            rootVisible: false
        });

        this.tree.on('itemmove', function(el, oldParent, newParent, index, eOpts){
            var n = this.getStore().getNodeById(newParent.get('id'));
            if (n) {
                var data = [];
                var i = 0;
                n.eachChild(function(el){
                    data.push({
                        id: el.get('id'),
                        sort: i
                    });
                    i++;
                });
                if (data.length > 0) {
                    var params = {
                        entry: data
                    };
                    // save sort
                    var uri = Amun.xrds.Manager.findServiceUri('http://ns.amun-project.org/2011/amun/service/content/page');
                    Ext.Ajax.request({
                        url: uri + '/tree?format=json',
                        method: 'POST',
                        headers: {
                            'X-HTTP-Method-Override': 'PUT',
                            'Accept': 'application/json'
                        },
                        jsonData: params,
                        scope: this,
                        success: function(response, opts) {
                            try {
                                var result = Ext.JSON.decode(response.responseText);
                                if (result.success == true) {
                                    // successful
                                    return;
                                }
                            } catch(e) {
                            }
                            this.getStore().load();
                        },
                        failure: function(response, opts) {
                            this.getStore().load();
                        }
                    });
                }
            }
        });

        this.tree.on('celldblclick', function(el, td, index, rec){
            var uri = this.grid.service.getUri() + '/form?method=update&id=' + rec.get('id');

            this.grid.loadForm(uri);
        }, this);

        this.tree.on('select', function(el, rec){
            var rec = this.grid.getStore().getById(rec.get('id'));
            this.grid.getSelectionModel().select([rec]);
        }, this);

        return this.tree;
    },

    buildGrid: function(){
        this.grid = Ext.create('Amun.Grid', {
            border: false,
            region: 'center',
            service: this.service,
            result: this.result,
            columnConfig: {
                id: 80,
                title: 300,
                template: 300,
                date: 120
            }
        });

        this.grid.on('reload', function(){
            this.tree.getStore().load();
        }, this);

        return this.grid;
    }

});

