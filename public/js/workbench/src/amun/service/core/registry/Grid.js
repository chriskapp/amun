
Ext.define('Amun.service.core.registry.Grid', {
    extend: 'Amun.Grid',

    getTbar: function(){
        return [{
            text: 'Edit Record',
            iconCls: 'wb-icon-edit',
            cls: 'wb-content-edit',
            disabled: true,
            scope: this,
            handler: this.onEditClick
        },'->',{
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
    }

});

