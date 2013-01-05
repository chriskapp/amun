
Ext.define('Amun.service.explorer.Grid', {
    extend: 'Amun.Grid',

    explorerPath: [],
    navPath: null,

    columnConfig: {
        name: 520,
        size: 80,
        perms: 80,
        date: 120
    },

    getTbar: function(){
        this.navPath = Ext.create('Ext.toolbar.Toolbar', {
            border: false,
            padding: 0,
            margins: 0
        });
        this.buildNavPath();

        return [{
            text: 'Add File',
            iconCls: 'wb-icon-add',
            cls: 'wb-content-add',
            scope: this,
            handler: this.onAddClick
        },{
            text: 'Edit File',
            iconCls: 'wb-icon-edit',
            cls: 'wb-content-edit',
            disabled: true,
            scope: this,
            handler: this.onEditClick
        },{
            text: 'Delete File',
            iconCls: 'wb-icon-delete',
            cls: 'wb-content-delete',
            disabled: true,
            scope: this,
            handler: this.onDeleteClick
        }, '-', this.navPath, '->',{
            xtype: 'combobox',
            cls: 'wb-content-search-filterBy',
            width: 100,
            store: this.searchColumns,
            value: this.searchColumns.slice(0),
            editable: false
        },{
            xtype: 'combobox',
            cls: 'wb-content-search-filterOp',
            width: 85,
            store: ['contains', 'equals', 'startsWith', 'present'],
            value: 'contains',
            editable: false
        },{
            xtype: 'textfield',
            cls: 'wb-content-search-filterValue',
            listeners: {
                scope: this,
                specialkey: this.onSearchEnter
            }
        },{
            text: 'Search',
            iconCls: 'wb-icon-search',
            scope: this,
            handler: this.onSearchClick
        }];
    },

    onSelect: function(el){
        if (this.getSelectionModel().hasSelection()) {
            var rec = this.getSelectionModel().getSelection()[0];

            this.query('button[cls=wb-content-edit]')[0].enable();
            this.query('button[cls=wb-content-delete]')[0].enable();

            this.selectedRecordId = this.getFullPath() + rec.get('name');
        } else {
            this.query('button[cls=wb-content-edit]')[0].disable();
            this.query('button[cls=wb-content-delete]')[0].disable();

            this.selectedRecordId = null;
        }
    },

    onDblClick: function(el){
        var store = this.getStore();
        if (this.getSelectionModel().hasSelection()) {
            var rec = this.getSelectionModel().getSelection()[0];
            if (rec.get('perms').charAt(0) == 'd') {
                if (rec.get('name') == '..') {
                    this.explorerPath.pop();
                } else {
                    this.explorerPath.push(rec.get('name'));
                }

                store.getProxy().setExtraParam('path', this.getFullPath());
                store.load();
            }
        }
        this.buildNavPath();
    },

    onAddClick: function(el, e, eOpts){
        var uri = this.service.getUri() + '/form?method=create&path=' + this.getFullPath();

        this.loadForm(uri);
    },

    onEditClick: function(el, e, eOpts){
        var grid = el.findParentByType('grid');
        var rec = grid.getSelectionModel().getSelection()[0];
        var uri = this.service.getUri() + '/form?method=update&path=' + this.getFullPath() + '/' + rec.get('name');

        this.loadForm(uri);
    },

    onDeleteClick: function(el, e, eOpts){
        var grid = el.findParentByType('grid');
        var rec = grid.getSelectionModel().getSelection()[0];
        var uri = this.service.getUri() + '/form?method=delete&path=' + this.getFullPath() + '/' + rec.get('name');

        this.loadForm(uri);
    },

    buildNavPath: function(){
        this.navPath.removeAll();
        this.navPath.add('.');
        for (var i = 0; i < this.explorerPath.length; i++) {
            this.navPath.add('/');
            this.navPath.add(this.explorerPath[i]);
        }
    },

    getFullPath: function(){
        var path = '';
        for (var i = 0; i < this.explorerPath.length; i++) {
            path+= this.explorerPath[i] + '/';
        }
        return path;
    }

});

