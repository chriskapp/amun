
Ext.define('Amun.Grid', {
    extend: 'Ext.grid.Panel',

    service: null,
    result: null,
    selectedRecordId: null,

    columnConfig: false,
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

        // columns
        var columns = [];
        var searchColumns = [];
        var fields = '';

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
                fields+= k + ',';
            }
        } else {
            // we have no config select all available fields
            for (var i = 0; i < result.length; i++) {
                columns.push({
                    text: result[i],
                    dataIndex: result[i]
                });
                searchColumns.push(result[i]);
                fields+= result[i] + ',';
            }
        }

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
                extraParams: {fields: fields},
                simpleSortMode: true,
                reader: {
                    type: 'json',
                    root: 'entry',
                    idProperty: 'id',
                    totalProperty: 'totalResults'
                }
            }
        });

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
    },

    /**
     * This method should be overwrite by extending classes to provide a grid 
     * config. It is recommended that the complete width of all columns is 800. 
     * The method should return an object wich looks like:
     * {
     *  "column1": "width",
     *  "column2": "width"
     * }
     *
     * @return object
     */
    getColumnConfig: function(){
        if (this.columnConfig === false) {
            // content
            if (this.service.hasType('http://ns.amun-project.org/2011/amun/service/content/gadget')) {
                return {
                    id: 80,
                    name: 300,
                    title: 300,
                    date: 120
                };
            } else if (this.service.hasType('http://ns.amun-project.org/2011/amun/service/media')) {
                return {
                    id: 80,
                    name: 300,
                    mimeType: 200,
                    size: 100,
                    date: 120
                };
            // user
            } else if (this.service.hasType('http://ns.amun-project.org/2011/amun/service/user/account')) {
                return {
                    id: 80,
                    name: 300,
                    email: 200,
                    countryTitle: 100,
                    date: 120
                };
            } else if (this.service.hasType('http://ns.amun-project.org/2011/amun/service/user/activity')) {
                return {
                    id: 80,
                    authorName: 100,
                    verb: 100,
                    summary: 400,
                    date: 120
                };
            // system
            } else if (this.service.hasType('http://ns.amun-project.org/2011/amun/service/core/service')) {
                return {
                    id: 80,
                    name: 200,
                    source: 200,
                    license: 100,
                    version: 100,
                    date: 120
                };
            } else if (this.service.hasType('http://ns.amun-project.org/2011/amun/service/mail')) {
                return {
                    id: 80,
                    name: 200,
                    from: 200,
                    subject: 320
                };
            } else if (this.service.hasType('http://ns.amun-project.org/2011/amun/service/oauth')) {
                return {
                    id: 80,
                    status: 80,
                    name: 120,
                    email: 120,
                    url: 280,
                    date: 120
                };
            } else if (this.service.hasType('http://ns.amun-project.org/2011/amun/service/country')) {
                return {
                    id: 80,
                    title: 360,
                    code: 120,
                    longitude: 120,
                    latitude: 120
                };
            // service
            } else if (this.service.hasType('http://ns.amun-project.org/2011/amun/service/comment')) {
                return {
                    id: 80,
                    authorName: 120,
                    pageTitle: 120,
                    text: 360,
                    date: 120
                };
            } else if (this.service.hasType('http://ns.amun-project.org/2011/amun/service/news')) {
                return {
                    id: 80,
                    authorName: 120,
                    pageTitle: 120,
                    title: 360,
                    date: 120
                };
            } else if (this.service.hasType('http://ns.amun-project.org/2011/amun/service/page')) {
                return {
                    id: 80,
                    authorName: 120,
                    pageTitle: 120,
                    content: 360,
                    date: 120
                };
            }

            return false;
        } else {
            return this.columnConfig;
        }
    }

});
