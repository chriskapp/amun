
Ext.define('Amun.service.phpinfo.Grid', {
    extend: 'Amun.Grid',

    columnConfig: {
        group: 80,
        key: 400,
        value: 400
    },

    buildGrid: function(service, result){
        // define model
        var fields = [];
        for (var i = 0; i < result.length; i++) {
            fields.push({
                name: result[i],
                type: 'string'
            });
        }

        var modelNs = 'Workbench.model.' + service.getName();
        Ext.define(modelNs, {
            extend: 'Ext.data.Model',
            fields: fields,
            idProperty: 'id'
        });

        // create store
        var store = Ext.create('Ext.data.Store', {
            model: modelNs,
            autoLoad: true,
            remoteSort: true,
            remoteFilter: true,
            pageSize: 32,
            groupField: 'group',
            proxy: {
                type: 'ajax',
                url: service.getUri(),
                filterParam: 'filterValue',
                limitParam: 'count',
                pageParam: null,
                sortParam: 'sortBy',
                directionParam: 'sortOrder',
                startParam: 'startIndex',
                simpleSortMode: true,
                reader: {
                    type: 'json',
                    root: 'entry',
                    idProperty: 'id',
                    totalProperty: 'totalResults'
                }
            }
        });

        // columns
        var columns = [];
        var searchColumns = [];

        // check whether we have an config
        var config = this.getColumnConfig();
        if (typeof config == 'object') {
            for (var k in config) {
                columns.push({
                    text: k,
                    width: config[k],
                    dataIndex: k
                });
                searchColumns.push(k);
            }
        } else {
            // we have no config select all available fields
            for (var i = 0; i < result.length; i++) {
                columns.push({
                    text: result[i],
                    dataIndex: result[i]
                });
                searchColumns.push(k);
            }
        }

        // build grid
        return {
            store: store,
            columns: columns,
            border: false,
            cls: 'wb-content-grid',
            selModel: {
                listeners: {
                    scope: this,
                    selectionchange: this.onSelect
                }
            },
            features: [{
                id: 'group',
                ftype: 'groupingsummary',
                groupHeaderTpl: '{name}',
                hideGroupedHeader: true,
                enableGroupingMenu: false
            }],
            tbar: ['->',{
                xtype: 'combobox',
                cls: 'wb-content-search-filterBy',
                width: 100,
                store: searchColumns,
                value: searchColumns.slice(0),
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
            }],
            bbar: Ext.create('Ext.PagingToolbar', {
                store: store,
                displayInfo: true,
                displayMsg: 'Displaying record {0} - {1} of {2}',
                emptyMsg: 'No records to display',
            })
        };
    }

});

