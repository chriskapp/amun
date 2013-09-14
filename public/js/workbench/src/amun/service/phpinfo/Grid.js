
Ext.define('Amun.service.phpinfo.Grid', {
    extend: 'Amun.Grid',
    requires: [
        'Ext.grid.feature.Grouping'
    ],

    features: [{
        ftype: 'grouping',
        groupHeaderTpl: '{name} ({rows.length})',
        hideGroupedHeader: true,
        startCollapsed: true
    }],

    getTbar: function(){
        return ['->',{
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

