
Ext.define('Amun.Grid', {
    extend: 'Ext.grid.Panel',

    service: null,
    result: null,
    selectedRecordId: null,

    windowCache: {},

    initComponent: function(){
        var me = this;
        me.addEvents('reload');

        var el = this.buildGrid(this.service, this.result);
        Ext.apply(me, el);

        me.callParent();
    },

    reload: function(){
        this.getStore().load();
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
        var types = service.getTypes();
        var config = null;
        for (var i = 0; i < types.length; i++) {
            var ty = Amun.Application.columnConfig.getConfigByType(types[i]);
            if (ty != null) {
                config = ty;
            }
        }

        if (config) {
            for (var k in config) {
                columns.push({
                    text: k,
                    //width: config[k],
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
            tbar: [{
                text: 'Add Record',
                iconCls: 'wb-icon-add',
                cls: 'wb-content-add',
                scope: this,
                handler: this.onAddClick
            },{
                text: 'Edit Record',
                iconCls: 'wb-icon-edit',
                cls: 'wb-content-edit',
                disabled: true,
                scope: this,
                handler: this.onEditClick
            },{
                text: 'Delete Record',
                iconCls: 'wb-icon-delete',
                cls: 'wb-content-delete',
                disabled: true,
                scope: this,
                handler: this.onDeleteClick
            },'->',{
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
    },

    loadForm: function(uri){
        var win = this.windowCache[uri];
        if (win == undefined) {
            // request form
            Ext.Ajax.request({
                url: uri,
                scope: this,
                success: function(response, opts){
                    var result = Ext.JSON.decode(response.responseText);

                    // build grid
                    this.buildForm(result, uri);
                },
                failure: function(response){
                    Ext.Msg.alert('Error', response.responseText);
                }
            });
        } else {
            // hide other windows
            for (var key in this.windowCache) {
                this.windowCache[key].hide();
            }

            // reset form
            var form = win.query('form');
            if (form.length > 0) {
                form[0].reload();
            }

            // show window
            win.show();
        }
    },

    buildForm: function(result, uri){
        if (typeof(result.success) != 'undefined' && result.success == false) {
            // add message
            var panel = {
                layout: 'fit',
                border: false,
                bodyStyle: 'padding:5px;',
                html: result.text
            };
        } else {
            // check whether we have a custom form class else we build the form 
            // based on the json we received
            var form;
            var className = 'Amun.' + this.service.getNamespace() + '.Form';
            var extClass = Ext.ClassManager.get(className);

            if (extClass != null) {
                form = Ext.create(className, {
                    recordId: this.getSelectedRecordId(),
                    form: result
                });
            } else {
                form = Ext.create('Amun.Form', {
                    recordId: this.getSelectedRecordId(),
                    form: result
                });
            }

            // add events
            form.on('submit', function(el){
                var form = el.getForm();
                if (form.isValid()) {
                    var params = '';
                    if (form.hasUpload()) {
                        params = '?format=json&htmlMime=1';
                    }
                    form.submit({
                        url: el.getAction() + params,
                        method: 'POST',
                        headers: {
                            'X-HTTP-Method-Override': el.getMethod(),
                            'Accept': 'application/json'
                        },
                        success: function(form, action) {
                            Ext.Msg.alert('Success', action.result.text, function(){
                                this.reload();
                                this.fireEvent('reload');
                            }, this);
                            var win = this.windowCache[uri];
                            win.hide();
                        },
                        failure: function(form, action) {
                            Ext.Msg.alert('Failed', action.result.text);
                        },
                        scope: this
                    });
                }
            }, this);

            form.on('reset', function(el){
                var form = el.getForm();
                form.reset();
            }, this);

            // build form
            var panel = Ext.create('Ext.panel.Panel', {
                layout: 'fit',
                border: false,
                items: [form]
            });
        }

        // hide other windows
        for (var key in this.windowCache) {
            this.windowCache[key].hide();
        }

        // build window
        win = Ext.create('widget.window', {
            title: 'Form',
            closable: true,
            closeAction: 'hide',
            width: 800,
            height: 600,
            resizeable: false,
            layout: 'fit',
            items: [panel]
        });
        win.show();

        // add to cache
        this.windowCache[uri] = win;
    },

    getSelectedRecordId: function(){
        return this.selectedRecordId;
    },

    onSelect: function(el){
        if (this.getSelectionModel().hasSelection()) {
            var rec = this.getSelectionModel().getSelection()[0];

            this.query('button[cls=wb-content-edit]')[0].enable();
            this.query('button[cls=wb-content-delete]')[0].enable();

            this.selectedRecordId = rec.get('id');
        } else {
            this.query('button[cls=wb-content-edit]')[0].disable();
            this.query('button[cls=wb-content-delete]')[0].disable();

            this.selectedRecordId = null;
        }
    },

    onAddClick: function(el, e, eOpts){
        var uri = this.service.getUri() + '/form?method=create';

        this.loadForm(uri);
    },

    onEditClick: function(el, e, eOpts){
        var grid = el.findParentByType('grid');
        var rec = grid.getSelectionModel().getSelection()[0];
        var uri = this.service.getUri() + '/form?method=update&id=' + rec.get('id');

        this.loadForm(uri);
    },

    onDeleteClick: function(el, e, eOpts){
        var grid = el.findParentByType('grid');
        var rec = grid.getSelectionModel().getSelection()[0];
        var uri = this.service.getUri() + '/form?method=delete&id=' + rec.get('id');

        this.loadForm(uri);
    },

    onSearchClick: function(el){
        var grid = el.findParentByType('grid');
        var filterBy = grid.query('combo[cls=wb-content-search-filterBy]')[0].getValue();
        var filterOp = grid.query('combo[cls=wb-content-search-filterOp]')[0].getValue();
        var filterValue = grid.query('textfield[cls=wb-content-search-filterValue]')[0].getValue();
        var store = grid.getStore();

        store.getProxy().setExtraParam('filterBy', filterBy);
        store.getProxy().setExtraParam('filterOp', filterOp);
        store.getProxy().setExtraParam('filterValue', filterValue);

        store.load();
    },

    onSearchEnter: function(el, e){
        if (e.getKey() == e.ENTER) {
            this.onSearchClick(el);
        }
    }

});