
Ext.define('Amun.content.page.Grid', {
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
                url: url + 'api/core/content/page/tree?format=json'
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
            hideHeaders: true,
            useArrows: true,
            rootVisible: false
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

