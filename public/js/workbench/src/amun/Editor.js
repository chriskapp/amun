
Ext.define('Amun.Editor', {
    extend: 'Ext.window.Window',

    initComponent: function(){
        var me = this;

        var el = {
            title: 'Editor',
            closable: true,
            width: 820,
            height: 600,
            resizable: false,
            layout: 'fit',
            items: [this.buildContainer()]
        };
        Ext.apply(me, el);

        me.callParent();
    },

    buildContainer: function(){
        // check whether we have a custom form class else we build the form 
        // based on the json we received
        var grid;
        var className = 'Amun.' + this.service.getNamespace() + '.Grid';
        var extClass = Ext.ClassManager.get(className);

        var config = {
            title: this.service.getName(),
            closable: true,
            service: this.service,
            page: this.page
        };

        if (extClass != null) {
            grid = Ext.create(className, config);
        } else {
            grid = Ext.create('Amun.Grid', config);

            // filter after page entries
	        var store = grid.getStore();
	        store.getProxy().setExtraParam('filterBy', 'pageId');
	        store.getProxy().setExtraParam('filterOp', 'equals');
	        store.getProxy().setExtraParam('filterValue', this.page.id);
        }

        return grid;
    }

});