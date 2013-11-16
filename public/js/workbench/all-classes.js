/*
 * amun
 * A social content managment system based on the psx framework. For
 * the current version and informations visit <http://amun.phpsx.org>
 *
 * Copyright (c) 2010-2013 Christoph Kappestein <k42b3.x@gmail.com>
 *
 * This file is part of amun. amun is free software: you can
 * redistribute it and/or modify it under the terms of the
 * GNU General Public License as published by the Free Software
 * Foundation, either version 3 of the License, or any later version.
 *
 * amun is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with amun. If not, see <http://www.gnu.org/licenses/>.
*/

Ext.define('Amun.form.Editor', {
    extend: 'Ext.form.field.Text',

    alias: 'widget.aceeditor',
    editorId: null,
    editor: null,

    initComponent: function(){
        var me = this;
        me.editorId = Ext.id();

        var config = {
            cls: 'wb-content-form-editor',
            fieldSubTpl: [
                '<div id="{id}" {inputAttrTpl}>',
                    '<div id="' + me.editorId + '" class="amun-ace-editor"></div>',
                '</div>',
                {
                    disableFormats: true
                }
            ]
        };
        Ext.apply(me, config);

        me.callParent();
    },

    afterRender: function(){
        var me = this;
        me.callParent(arguments);

        // set width and height
        var editor = Ext.select('#' + me.editorId);
        editor.setWidth(this.getWidth());
        editor.setHeight(this.getHeight());

        // ace editor
        me.editor = ace.edit(me.editorId);
        me.editor.setTheme('ace/theme/eclipse');
        //me.editor.getSession().setMode('ace/mode/html');
        me.editor.setValue(me.rawValue, -1);
    },

    setRawValue: function(value){
        var me = this;
        me.rawValue = value;
        return value;
    },

    getRawValue: function(){
        var me = this;
        var value = me.editor != null ? me.editor.getValue() : '';
        return value;
    }

});


Ext.define('Amun.form.Form', {
    extend: 'Ext.window.Window',

    method: 'POST',
    action: null,

    initComponent: function(){
        var me = this;
        me.addEvents('submit', 'reset', 'formLoaded');

        var el = {
            title: 'Form',
            closable: true,
            width: 800,
            height: 600,
            resizable: false,
            layout: 'fit',
            items: [{
                layout: 'fit',
                border: false,
                bodyStyle: 'padding:5px;',
                html: 'Loading ...'
            }]
        };
        Ext.apply(me, el);

        me.callParent();

        this.loadForm();
    },

    reload: function(){
        this.getForm().reset();
    },

    getPage: function(){
        return this.page;
    },

    getRecordId: function(){
        return this.recordId;
    },

    getMethod: function(){
        return this.method;
    },

    getAction: function(){
        return this.action;
    },

    getFormPanel: function(){
        return this.query('panel[cls=wb-content-form]')[0];
    },

    getForm: function(){
        return this.getFormPanel().getForm();
    },

    loadForm: function(){
        var uri = null;
        if (this.type == 'CREATE') {
            uri = this.service.getUri() + '/form?method=create';
        } else if (this.type == 'UPDATE') {
            uri = this.service.getUri() + '/form?method=update&id=' + this.recordId;
        } else if (this.type == 'DELETE') {
            uri = this.service.getUri() + '/form?method=delete&id=' + this.recordId;
        } else {
            console.log('Invalid type');
        }

        if (uri != null) {
            // add page
            if (this.page) {
                uri = uri + '&pageId=' + this.page.id;
            }

            // request form
            Ext.Ajax.request({
                url: uri,
                scope: this,
                success: function(response, opts){
                    var result = Ext.JSON.decode(response.responseText);

                    // build grid
                    this.doFormLoaded(result);
                },
                failure: function(response){
                    Ext.Msg.alert('Error', response.responseText);
                }
            });
        }
    },

    doFormLoaded: function(result){
        // remove loading panel
        this.remove(0);

        // add panel
        if (typeof(result.success) != 'undefined' && result.success == false) {
            // add message
            this.add({
                layout: 'fit',
                border: false,
                bodyStyle: 'padding:5px;',
                html: result.text
            });
        } else {
            this.add(this.buildForm(result));
        }
        this.doLayout();

        this.fireEvent('formLoaded', this);
    },

    buildForm: function(form){
        return Ext.create('Ext.panel.Panel', {
            layout: 'fit',
            border: false,
            items: [this.parseElements(form)]
        });
    },

    buildChildren: function(item){
        // build items
        var items = [];
        if (item.children && item.children.item && item.children.item.length > 0) {
            for (var i = 0; i < item.children.item.length; i++) {
                items.push(this.parseElements(item.children.item[i]));
            }
        }
        return items;
    },

    parseElements: function(item){
        // check for error
        if (typeof(item['success']) != 'undefined' && item['success'] == false) {
            return {
                xtype: 'label',
                text: item.text
            };
        }

        switch (item['class']) {
            case 'form':
                return this.onForm(item);
                break;

            case 'tabbedPane':
                return this.onTabPanel(item);
                break;

            case 'panel':
                return this.onPanel(item);
                break;

            case 'captcha':
                return this.onCaptcha(item);
                break;

            case 'datalist':
                return this.onDatalist(item);
                break;

            case 'reference':
                return this.onReference(item);
                break;

            case 'input':
                return this.onInput(item);
                break;

            case 'select':
                return this.onSelect(item);
                break;

            case 'textarea':
                return this.onTextarea(item);
                break;
        }
    },

    onForm: function(item){
        // create form panel
        this.method = item.method;
        this.action = item.action;

        return Ext.create('Ext.form.Panel', {
            url: item.action,
            cls: 'wb-content-form',
            items: [this.parseElements(item.item)],
            border: false,
            region: 'center',
            fieldDefaults: {
                labelWidth: 120,
                width: 340
            },
            buttons: [{
                text: 'Reset',
                scope: this,
                handler: function(){
                    this.fireEvent('reset', this);
                }
            },{
                text: 'Submit',
                formBind: true,
                disabled: true,
                scope: this,
                handler: function(){
                    this.fireEvent('submit', this);
                }
            }]
        });
    },

    onTabPanel: function(item){
        // create tab pane
        return Ext.create('Ext.tab.Panel', {
            title: item.label,
            border: false,
            items: this.buildChildren(item)
        });
    },

    onPanel: function(item){
        // create fieldset
        return Ext.create('Ext.panel.Panel', {
            title: item.label,
            border: false,
            bodyPadding: 5,
            items: this.buildChildren(item)
        });
    },

    onCaptcha: function(item){
        /*
        var val = item.value ? item.value : '';

        html+= '<p>';
        html+= '<label for="' + item.ref + '">' + item.label + '</label>';
        html+= '<img src="' + item.src + '" alt="Captcha" /><br />';
        html+= '<input type="text" name="' + item.ref + '" id="' + item.ref + '" value="' + val + '" />';
        html+= '</p>';
        */
        return null;
    },

    onDatalist: function(item){
        return null;
    },

    onReference: function(item){
        // build store
        var store = Ext.create('Ext.data.Store', {
            fields: [item.valueField, item.labelField],
            autoLoad: true,
            proxy: {
                type: 'ajax',
                url: item.src + '?fields=' + item.valueField + ',' + item.labelField + '&format=json',
                reader: {
                    type: 'json',
                    root: 'entry',
                    idProperty: 'id',
                    totalProperty: 'totalResults'
                }
            }
        });

        // create combobox
        return Ext.create('Ext.form.ComboBox', {
            name: item.ref,
            disabled: item.disabled,
            fieldLabel: item.label,
            store: store,
            value: item.value,
            queryMode: 'remote',
            displayField: item.labelField,
            valueField: item.valueField
        });
    },

    onInput: function(item){
        // check type
        switch (item.type) {
            case 'hidden':
                var input = {
                    xtype: 'hiddenfield',
                    name: item.ref,
                    value: item.value,
                };
                break;

            case 'date':
            case 'datetime':
            case 'datetime-local':
                var input = {
                    xtype: 'datefield',
                    name: item.ref,
                    disabled: item.disabled,
                    value: item.value,
                    fieldLabel: item.label,
                    format: 'Y-m-d H:i:s'
                };
                break;

            case 'file':
                var input = {
                    xtype: 'filefield',
                    name: item.ref,
                    disabled: item.disabled,
                    value: item.value,
                    fieldLabel: item.label
                };
                break;

            case 'number':
                var input = {
                    xtype: 'numberfield',
                    name: item.ref,
                    disabled: item.disabled,
                    value: item.value,
                    fieldLabel: item.label
                };
                break;

            default:
                var input = {
                    xtype: 'textfield',
                    name: item.ref,
                    disabled: item.disabled,
                    value: item.value,
                    fieldLabel: item.label,
                    inputType: item.type == 'password' ? 'password' : 'text'
                };
        }
        return input;
    },

    onSelect: function(item){
        var selectedValue = null;
        // build store
        var data = [];
        if (typeof item.children.item != 'undefined') {
            for (var j = 0; j < item.children.item.length; j++) {
                var opt = item.children.item[j];
                data.push({value: opt.value, name: opt.label});

                if (opt.value == item.value) {
                    selectedValue = opt.value;
                }
            }
        }

        var store = Ext.create('Ext.data.Store', {
            fields: ['value', 'name'],
            data: data
        });

        // create combobox
        return Ext.create('Ext.form.ComboBox', {
            name: item.ref,
            disabled: item.disabled,
            fieldLabel: item.label,
            store: store,
            value: selectedValue,
            queryMode: 'local',
            displayField: 'name',
            valueField: 'value'
        });
    },

    onTextarea: function(item){
        return {
            xtype: 'aceeditor',
            width: 640,
            height: 300,
            grow: true,
            name: item.ref,
            fieldLabel: item.label,
            value: item.value,
            disabled: item.disabled
        };
    }

});


Ext.define('Amun.service.content.page.Form', {
    extend: 'Amun.form.Form',

    formPanel: null,
    gadgetPanel: null,

    initComponent: function(){
        var me = this;
        me.callParent();

        me.on('formLoaded', function(el){
            // load group gadgets
            el.gadgetPanel.getStore().load();
        });
    },

    reload: function(){
        this.getForm().reset();
        this.gadgetPanel.getStore().load();
    },

    buildForm: function(form){
        // build form
        this.formPanel = this.parseElements(form);
        this.formPanel.add({
            xtype: 'hiddenfield',
            cls: 'wb-form-gadgets',
            name: 'gadgets',
            value: ''
        });

        // build gadgets
        this.gadgetPanel = null;
        var gadgetUri = Amun.xrds.Manager.findServiceUri('http://ns.amun-project.org/2011/amun/service/content/gadget');
        if (gadgetUri !== false) {
            var store = Ext.create('Amun.service.content.page.GadgetStore', {
                autoLoad: false,
                proxy: {
                    type: 'ajax',
                    url: gadgetUri + '?count=1024&fields=id,name&format=json',
                    reader: {
                        type: 'json',
                        root: 'entry',
                        idProperty: 'id',
                        totalProperty: 'totalResults'
                    }
                }
            });

            store.on('load', function(el, node){
                el.getRootNode().expand();

                if (this.recordId > 0) {
                    this.loadExistingGadgets();
                }
            }, this);

            this.gadgetPanel = Ext.create('Ext.tree.Panel', {
                title: 'Gadgets',
                region: 'east',
                margins: '0 0 0 5',
                border: false,
                width: 200,
                store: store,
                hideHeaders: true,
                useArrows: true,
                rootVisible: false,
                viewConfig: {
                    plugins: {
                        ptype: 'treeviewdragdrop',
                        containerScroll: true
                    }
                },
                listeners: {
                    scope: this,
                    checkchange: function(node, checked){
                        this.updateGadgets();
                    },
                    itemmove: function(node, checked){
                        this.updateGadgets();
                    }
                }
            });
        }

        var el = {
            layout: 'border',
            border: false,
            items: [this.formPanel, this.gadgetPanel]
        };
        el.formMethod = form.method;
        el.formAction = form.action;

        return el;
    },

    loadExistingGadgets: function(){
        var groupRightUri = Amun.xrds.Manager.findServiceUri('http://ns.amun-project.org/2011/amun/service/content/page/gadget');
        if (groupRightUri !== false) {
            Ext.Ajax.request({
                url: groupRightUri + '?fields=id,gadgetId,sort&count=1024&sortBy=sort&sortOrder=ascending&filterBy=pageId&filterOp=equals&filterValue=' + this.recordId + '&format=json',
                scope: this,
                success: function(response, opts){
                    var result = Ext.JSON.decode(response.responseText);
                    if (result.entry.length > 0) {
                        for (var i = 0; i < result.entry.length; i++) {
                            var node = this.gadgetPanel.getStore().getNodeById(result.entry[i].gadgetId);
                            if (node) {
                                node.set('checked', true);
                                node.set('sort', result.entry[i].sort);
                            }
                        }

                        // sort
                        this.gadgetPanel.getStore().sort('sort', 'ASC');

                        // update
                        this.updateGadgets();
                    }
                },
                failure: function(response){
                    Ext.Msg.alert('Error', response.responseText);
                }
            });
        }
    },

    updateGadgets: function(){
        var value = '';
        var rootNode = this.gadgetPanel.getStore().getRootNode();
        rootNode.eachChild(function(node){
            if (node.get('checked')) {
                value+= node.get('id') + ',';
            }
        }, this);

        var el = this.query('hidden[cls=wb-form-gadgets]')[0];
        el.setValue(value);
    }

});



Ext.define('Amun.service.content.page.Gadget', {
    extend: 'Ext.data.Model',
    fields: [
        { name: 'id', type: 'int' },
        { name: 'text', type: 'string', mapping: 'name' },
        { name: 'leaf', type: 'boolean', defaultValue: true },
        { name: 'sort', type: 'int', defaultValue: 0 },
        { name: 'checked', type: 'boolean', defaultValue: false }
    ]
});



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
        // grid
        this.grid.reload();
    },

    buildTree: function(){

        Ext.Ajax.request({
            url: url + 'api/page/tree?format=json',
            scope: this,
            success: function(response, opts){
                var tree = this.buildRecTree(JSON.parse(response.responseText));

                this.tree.setRootNode(tree);
                this.tree.getRootNode().expand();
            }
        });

        this.tree = Ext.create('Ext.tree.Panel', {
            region: 'west',
            margins: '0 5 0 0',
            header: false,
            border: false,
            width: 200,
            viewConfig: {
                plugins: {
                    ptype: 'treeviewdragdrop',
                    containerScroll: true
                },
                listeners: {
                    beforedrop: function(node, data, overModel, dropPosition) {
                        return data.records.length == 1 && dropPosition != 'append';
                    }
                }
            },
            hideHeaders: true,
            useArrows: true,
            rootVisible: true
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

        /*
        this.tree.on('celldblclick', function(el, td, index, rec){
            var serviceType = rec.raw.type;
            var service = Amun.xrds.Manager.findService(serviceType);
            if (service) {
                var uri = service.getUri() + '/form?method=update&id=' + rec.get('id');
                this.grid.loadForm(uri, serviceType);
            }
        }, this);
        */

        this.tree.on('select', function(el, rec){
            var rec = this.grid.getStore().getById(rec.get('id'));
            this.grid.getSelectionModel().select([rec]);
        }, this);

        return this.tree;
    },

    buildRecTree: function(result){
        var children = [];
        if (result.children && result.children.length > 0) {
            for (var i = 0; i < result.children.length; i++) {
                children.push(this.buildRecTree(result.children[i]));
            }
        }

        var node = {
            text: result.title,
            id: result.id,
            cls: result.status == 0 ? 'wb-tree-page-normal' : 'wb-tree-page-hidden',
            leaf: true,
            children: null
        };

        if (children.length > 0) {
            node.leaf = false;
            node.children = children;
        }

        return node;
    },

    buildGrid: function(){
        this.grid = Ext.create('Amun.Grid', {
            border: false,
            region: 'center',
            service: this.service,
            result: this.result,
            columnConfig: {
                id: 80,
                path: 200,
                title: 100,
                template: 100,
                serviceName: 100,
                date: 120,
                serviceType: 100
            }
        });

        this.grid.on('reload', function(){
            this.tree.getStore().load();
        }, this);

        this.grid.on('celldblclick', function(el){
            var rec = this.grid.getSelectionModel().getSelection()[0];
            var service = Amun.xrds.Manager.findService(rec.raw.serviceType);
            if (service) {
                var editor = null;
                var editorName = 'Amun.' + service.getNamespace() + '.Editor';
                if (Ext.ClassManager.get(editorName) != null) {
                    editor = Ext.create(editorName, {
                        service: service,
                        page: rec.raw
                    });
                }

                // as fallback use the default form
                if (editor == null) {
                    editor = Ext.create('Amun.Editor', {
                        service: service,
                        page: rec.raw
                    });
                }
                editor.show();
            }
        }, this);

        return this.grid;
    }

});



Ext.define('Amun.service.user.group.Form', {
    extend: 'Amun.form.Form',

    initComponent: function(){
        var me = this;
        me.callParent();

        // load group rights
        me.on('formLoaded', function(el){
            this.loadRights();
        });

        // show loading panel
        me.on('submit', function(el){
            var loadingPanel = Ext.create('Ext.window.Window', {
                title: 'Operation',
                height: 60,
                width: 280,
                modal: true,
                layout: 'fit',
                items: [Ext.create('Ext.ProgressBar', {
                    border: false,
                    text: 'Initialize ...'
                })]
            });
            loadingPanel.show();

            loadingPanel.getComponent(0).wait({
                interval: 200,
                increment: 15
            });

            var form = el.getForm();
            form.on('actioncomplete', function(el, action){
                loadingPanel.hide();
            });
        }, this);
    },

    reload: function(){
        this.getForm().reset();

        // load existing rights
        if (this.recordId > 0) {
            this.loadExistingRights();
        }
    },

    loadRights: function(){
        var rightUri = Amun.xrds.Manager.findServiceUri('http://ns.amun-project.org/2011/amun/service/user/right');
        if (rightUri !== false) {
            Ext.Ajax.request({
                url: rightUri + '?count=1024&fields=id,description&format=json',
                scope: this,
                success: function(response, opts){
                    var result = Ext.JSON.decode(response.responseText);
                    if (result.entry.length > 0) {
                        var items = [];
                        for (var i = 0; i < result.entry.length; i++) {
                            items.push({
                                xtype: 'checkbox',
                                boxLabel: result.entry[i].description,
                                itemId: 'right_' + result.entry[i].id,
                                name: 'right_' + result.entry[i].id,
                                inputValue: result.entry[i].id,
                                width: 200,
                                scope: this,
                                handler: function(){
                                    this.updateRights();
                                }
                            });
                        }

                        var comboGroup = [{
                            xtype: 'hiddenfield',
                            cls: 'wb-form-rights',
                            name: 'rights',
                            value: ''
                        },{
                            xtype: 'checkboxgroup',
                            fieldLabel: 'Rights',
                            columns: 3,
                            style: 'margin-left:5px;',
                            items: items
                        }];

                        this.getFormPanel().addBodyCls('wb-overflow');
                        this.getFormPanel().add(comboGroup);
                        this.doLayout();

                        // load existing rights
                        if (this.recordId > 0) {
                            // defer so the update layout has enough time
                            Ext.Function.defer(function(){
                                this.loadExistingRights();
                            }, 200, this);
                        }
                    } else {
                        //
                    }
                },
                failure: function(response){
                    Ext.Msg.alert('Error', response.responseText);
                }
            });
        }
    },

    loadExistingRights: function(){
        var groupRightUri = Amun.xrds.Manager.findServiceUri('http://ns.amun-project.org/2011/amun/service/user/group/right');
        if (groupRightUri !== false) {
            Ext.Ajax.request({
                url: groupRightUri + '?fields=id,rightId&count=1024&filterBy=groupId&filterOp=equals&filterValue=' + this.recordId + '&format=json',
                scope: this,
                success: function(response, opts){
                    var result = Ext.JSON.decode(response.responseText);
                    if (result.entry.length > 0) {
                        var checkboxGroup = this.query('checkboxgroup')[0];
                        for (var i = 0; i < result.entry.length; i++) {
                            var right = checkboxGroup.getComponent('right_' + result.entry[i].rightId);
                            if (right) {
                                right.setValue(true);
                            }
                        }

                        this.updateRights();
                    }
                },
                failure: function(response){
                    Ext.Msg.alert('Error', response.responseText);
                }
            });
        }
    },

    updateRights: function(){
        var value = '';
        var rights = this.query('checkbox');
        for (var i = 0; i < rights.length; i++) {
            if (rights[i].getRawValue()) {
                value+= rights[i].getSubmitValue() + ',';
            }
        }

        var el = this.query('hidden[cls=wb-form-rights]')[0];
        el.setValue(value);
    }

});


Ext.define('Amun.service.mail.Form', {
    extend: 'Amun.form.Form',

    initComponent: function(){
        var me = this;
        me.callParent();

        me.on('formLoaded', function(el){
        	this.getFormPanel().addBodyCls('wb-overflow');
        });
    }

});


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


Ext.define('Amun.Auth', {
    singleton: true,

    verify: function(options){
        options = options || {};
        var me = this,
            scope = options.scope || window;

        // find credentials service
        var uri = Amun.xrds.Manager.findServiceUri('http://ns.amun-project.org/2011/amun/service/my/verifyCredentials');
        if (uri != false) {
            Ext.Ajax.request({
                url: uri + '?format=json',
                success: function(response, opts) {
                    var data = Ext.JSON.decode(response.responseText);
                    var user = Ext.create('Amun.User', data);

                    Ext.callback(options.success, options.scope, [user]);
                },
                failure: function(response, opts) {
                    var msg = 'Could not find verifyCredentials service';
                    Ext.callback(options.failure, options.scope, [msg]);
                }
            });
        } else {
            var msg = 'Could not find verifyCredentials service';
            Ext.callback(options.failure, options.scope, [msg]);
        }
    }

});


Ext.define('Amun.Application', {

    user: null,
    services: null,

    constructor: function(config){
        config = config || {};
        Ext.apply(this, config);
    },

    start: function(){
        // discover services
        Amun.xrds.Manager.discover({
            scope: this,
            success: this.onServiceDiscovered,
            failure: function(msg){
                console.log(msg);
            }
        });
    },

    onServiceDiscovered: function(services){
        this.services = services;
        // check user auth
        Amun.Auth.verify({
            scope: this,
            success: this.onAuthentication,
            failure: function(msg){
                console.log(msg);
            }
        });
    },

    onAuthentication: function(user){
        this.user = user;
        if (user.loggedIn == true && user.status == 'Administrator') {
            // start application
            var viewport = Ext.create('Ext.container.Viewport', {
                layout: 'border',
                items: [{
                    region: 'north',
                    title: '<div class="wb-header"><div style="float:left;">Workbench (<a href="' + psx_url + '">' + psx_url + '</a>)</div><div style="float:right;">Logged in as: <a href="' + user.profileUrl + '">' + user.name + '</a><img src="' + user.thumbnailUrl + '" width="16" style="float:right;margin-left:4px" /></div></div>',
                    margins: '0 0 0 0',
                    border: false
                },{
                    region: 'west',
                    layout: 'fit',
                    width: 200,
                    minWidth: 175,
                    maxWidth: 400,
                    margins: '5 5 5 5',
                    items: [{
                        header: false,
                        xtype: 'navigation'
                    }]
                },{
                    region: 'center',
                    layout: 'fit',
                    margins: '5 5 5 0',
                    items: [{
                        header: false,
                        xtype: 'content'
                    }]
                }]
            });
        } else {
            Ext.Msg.alert('Information', 'Please <a href="' + psx_url + '">login</a> with an administrator account', function(){
                window.location = psx_url;
            });
        }
    }

});

Ext.define('Amun.ColumnConfig', {
    singleton: true,

    getByService: function(service){
        if (service.hasType('http://ns.amun-project.org/2011/amun/service/content/gadget')) {
            return {
                id: 80,
                name: 300,
                title: 300,
                date: 120
            };
        } else if (service.hasType('http://ns.amun-project.org/2011/amun/service/media')) {
            return {
                id: 80,
                name: 300,
                mimeType: 200,
                size: 100,
                date: 120
            };
        // user
        } else if (service.hasType('http://ns.amun-project.org/2011/amun/service/user/account')) {
            return {
                id: 80,
                name: 300,
                email: 200,
                countryTitle: 100,
                date: 120
            };
        } else if (service.hasType('http://ns.amun-project.org/2011/amun/service/user/activity')) {
            return {
                id: 80,
                authorName: 100,
                verb: 100,
                summary: 400,
                date: 120
            };
        // system
        } else if (service.hasType('http://ns.amun-project.org/2011/amun/service/core/service')) {
            return {
                id: 80,
                name: 200,
                source: 200,
                license: 100,
                version: 100,
                date: 120
            };
        } else if (service.hasType('http://ns.amun-project.org/2011/amun/service/mail')) {
            return {
                id: 80,
                name: 200,
                from: 200,
                subject: 320
            };
        } else if (service.hasType('http://ns.amun-project.org/2011/amun/service/oauth')) {
            return {
                id: 80,
                status: 80,
                name: 120,
                email: 120,
                url: 280,
                date: 120
            };
        } else if (service.hasType('http://ns.amun-project.org/2011/amun/service/country')) {
            return {
                id: 80,
                title: 360,
                code: 120,
                longitude: 120,
                latitude: 120
            };
        // service
        } else if (service.hasType('http://ns.amun-project.org/2011/amun/service/comment')) {
            return {
                id: 80,
                authorName: 120,
                pageTitle: 120,
                text: 360,
                date: 120
            };
        } else if (service.hasType('http://ns.amun-project.org/2011/amun/service/file')) {
            return {
                id: 80,
                pageTitle: 120,
                contentType: 120,
                content: 360,
                date: 120
            };
        } else if (service.hasType('http://ns.amun-project.org/2011/amun/service/forum')) {
            return {
                id: 80,
                authorName: 120,
                pageTitle: 120,
                title: 360,
                date: 120
            };
        } else if (service.hasType('http://ns.amun-project.org/2011/amun/service/news')) {
            return {
                id: 80,
                authorName: 120,
                pageTitle: 120,
                title: 360,
                date: 120
            };
        } else if (service.hasType('http://ns.amun-project.org/2011/amun/service/page')) {
            return {
                id: 80,
                authorName: 120,
                pageTitle: 120,
                content: 360,
                date: 120
            };
        } else if (service.hasType('http://ns.amun-project.org/2011/amun/service/php')) {
            return {
                id: 80,
                authorName: 120,
                pageTitle: 120,
                content: 360,
                date: 120
            };
        } else if (service.hasType('http://ns.amun-project.org/2011/amun/service/phpinfo')) {
            return {
                key: 400,
                value: 400
            };
        } else if (service.hasType('http://ns.amun-project.org/2011/amun/service/pipe')) {
            return {
                id: 80,
                authorName: 120,
                pageTitle: 120,
                mediaName: 360,
                date: 120
            };
        } else if (service.hasType('http://ns.amun-project.org/2011/amun/service/redirect')) {
            return {
                id: 80,
                authorName: 120,
                pageTitle: 120,
                href: 360,
                date: 120
            };
        }

        return false;
    }

});



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

Ext.define('Amun.service.phpinfo.Store', {
    extend: 'Ext.data.Store',

    groupField: 'group'

});



Ext.define('Amun.Grid', {
    extend: 'Ext.grid.Panel',

    service: null,
    result: null,
    selectedRecordId: null,

    columnConfig: false,

    columns: null,
    searchColumns: null,
    store: null,

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
        // build columns
        this.buildColumns(result);

        // create store
        this.store = this.buildStore(service);

        // build grid
        return {
            store: this.store,
            columns: this.columns,
            border: false,
            cls: 'wb-content-grid',
            selModel: {
                listeners: {
                    scope: this,
                    selectionchange: this.onSelect
                }
            },
            listeners: {
                scope: this,
                celldblclick: this.onDblClick
            },
            tbar: this.getTbar(),
            bbar: this.getBbar()
        };
    },

    buildColumns: function(result){
        // columns
        this.columns = [];
        this.searchColumns = [];

        // check whether we have an config
        var config = this.getColumnConfig();
        if (typeof config == 'object') {
            for (var k in config) {
                this.columns.push({
                    text: k,
                    width: config[k],
                    dataIndex: k
                });
                this.searchColumns.push(k);
            }
        } else {
            // we have no config select all available fields
            for (var i = 0; i < result.length; i++) {
                this.columns.push({
                    text: result[i],
                    dataIndex: result[i]
                });
                this.searchColumns.push(result[i]);
            }
        }
        return fields;
    },

    buildStore: function(service){
        // define model
        var modelName = 'Amun.' + service.getNamespace() + '.Model';
        if (Ext.ClassManager.get(modelName) == null) {
            var fields = [];
            for (var i = 0; i < this.columns.length; i++) {
                fields.push({
                    name: this.columns[i].text,
                    type: 'string'
                });
            }

            Ext.define(modelName, {
                extend: 'Ext.data.Model',
                fields: fields,
                idProperty: 'id'
            });
        }

        // get fields
        var fields = '';
        for (var i = 0; i < this.searchColumns.length; i++) {
            fields+= this.searchColumns[i] + ',';
        }

        var storeName = 'Amun.' + service.getNamespace() + '.Store';
        if (Ext.ClassManager.get(storeName) == null) {
            storeName = 'Ext.data.Store';
        }

        return Ext.create(storeName, {
            model: modelName,
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
    },

    /**
     * Shows an create, update or delete form
     *
     * @param string type
     * @param Amun.Service service
     * @param Amun.Page page
     */
    showForm: function(type, service, page){
        // if we have an type try to use this form else load the type from 
        // the grid
        var form = null;
        var formName = 'Amun.' + service.getNamespace() + '.Form';
        if (Ext.ClassManager.get(formName) != null) {
            form = Ext.create(formName, {
                type: type,
                service: service,
                page: page,
                recordId: this.getSelectedRecordId()
            });
        }

        // as fallback use the default form
        if (form == null) {
            form = Ext.create('Amun.form.Form', {
                type: type,
                service: service,
                page: page,
                recordId: this.getSelectedRecordId()
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

        form.show();
    },

    getSelectedRecordId: function(){
        return this.selectedRecordId;
    },

    getTbar: function(){
        return [{
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

    getBbar: function(){
        return Ext.create('Ext.PagingToolbar', {
            store: this.store,
            displayInfo: true,
            displayMsg: 'Displaying record {0} - {1} of {2}',
            emptyMsg: 'No records to display',
        });
    },

    onSelect: function(el){
        if (this.getSelectionModel().hasSelection()) {
            var rec = this.getSelectionModel().getSelection()[0];

            if (this.query('button[cls=wb-content-edit]').length > 0) {
                this.query('button[cls=wb-content-edit]')[0].enable();
            }

            if (this.query('button[cls=wb-content-delete]').length > 0) {
                this.query('button[cls=wb-content-delete]')[0].enable();
            }

            this.selectedRecordId = rec.get('id');
        } else {
            if (this.query('button[cls=wb-content-edit]').length > 0) {
                this.query('button[cls=wb-content-edit]')[0].disable();
            }

            if (this.query('button[cls=wb-content-delete]').length > 0) {
                this.query('button[cls=wb-content-delete]')[0].disable();
            }

            this.selectedRecordId = null;
        }
    },

    onDblClick: function(el){
        /*
        var grid = el.findParentByType('grid');
        var rec = grid.getSelectionModel().getSelection()[0];
        var type = rec.raw.serviceType;

        var service = Amun.xrds.Manager.findService(type);
        if (service) {
            this.showForm('UPDATE', service);
        }
        Amun.Editor
        */
    },

    onAddClick: function(el, e, eOpts){
        this.showForm('CREATE', this.service, this.page);
    },

    onEditClick: function(el, e, eOpts){
        /*
        var grid = el.findParentByType('grid');
        var rec = grid.getSelectionModel().getSelection()[0];
        var uri = this.service.getUri() + '/form?method=update&id=' + rec.get('id');
        */

        this.showForm('UPDATE', this.service, this.page);
    },

    onDeleteClick: function(el, e, eOpts){
        this.showForm('DELETE', this.service, this.page);
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
            return Amun.ColumnConfig.getByService(this.service);
        } else {
            return this.columnConfig;
        }
    }

});


Ext.define('Amun.SplitEditor', {
    extend: 'Amun.Editor',

    record: null,

    initComponent: function(){
        var me = this;
        var el = {
            maximized: true
        };
        Ext.apply(me, el);

        me.callParent();

        me.on('boxready', function(){
            var editors = Ext.select('.amun-ace-editor');
            if (editors) {
                editors.setWidth(this.getEditorComponent().getWidth());
                editors.setHeight(this.getEditorComponent().getHeight());
            }

            this.getEditorComponent().on('resize', function(el, width, height, oldWidth, oldHeight, eOpts){
                var editors = Ext.select('.amun-ace-editor');
                if (editors) {
                    editors.setWidth(width);
                    editors.setHeight(height);
                }
            });

            // load page data
            me.requestPage();
        });
    },

    buildContainer: function(){
        return Ext.create('Ext.panel.Panel', {
            border: false,
            layout: 'border',
            bbar: this.getBbar(),
            items: [{
                region: 'west',
                layout: 'fit',
                width: '50%',
                border: false,
                split: true,
                minWidth: 200,
                collapsible: true,
                collapseMode: 'mini',
                preventHeader: true,
                items: [this.getEditorPanel()]
            },{
                region: 'center',
                layout: 'fit',
                width: '50%',
                border: false,
                items: [{
                    xtype: 'component',
                    autoEl: {
                        tag: 'iframe',
                        cls: 'wb-iframe-preview',
                        src: url + this.page.path
                    }
                }]
            }]
        });
    },

    reloadPreview: function(){
        if (frames[0]) {
            frames[0].location.reload();
        }
    },

    setEditorValue: function(value){
        var editors = this.query('aceeditor');
        if (editors[0]) {
            editors[0].editor.setValue(value, -1);
        }
    },

    getEditorValue: function(){
        var editors = this.query('aceeditor');
        if (editors[0]) {
            return editors[0].editor.getValue();
        }
        return '';
    },

    getEditorComponent: function(){
        return this.getComponent(0).getComponent(0);
    },

    getEditorPanel: function(){
        return {
            xtype: 'aceeditor',
            grow: true,
            name: 'content',
            value: ''
        };
    },

    submitPage: function(method, params){
        Ext.Ajax.request({
            url: this.service.getUri() + '?format=json',
            scope: this,
            headers: {
                'X-HTTP-Method-Override': method
            },
            params: params,
            success: function(response, opts){
                var result = Ext.JSON.decode(response.responseText);
                if (result.success) {
                    Ext.Msg.alert('Success', result.text);

                    if (!this.record) {
                        this.requestPage();
                    }

                    this.reloadPreview();
                } else {
                    Ext.Msg.alert('Error', result.text ? result.text : 'Unknown error occured');
                }
            },
            failure: function(response){
                Ext.Msg.alert('Error', response.responseText);
            }
        });
    },

    /**
     * Method wich should return the bottom bar of the editor
     *
     * @return array
     */
    getBbar: function(){
        return [];
    },

    /**
     * Method wich should load the record assigned to this page. The record 
     * should be stored in this.record
     */
    requestPage: function(){
    }

});


Ext.define('Amun.service.page.Editor', {
    extend: 'Amun.SplitEditor',

    getBbar: function(){
        return [{
            text: 'Preview',
            scope: this,
            handler: function(){
                if (frames[0]) {
                    var element = frames[0].document.querySelector('.amun-service-page-content');
                    if (element) {
                        element.innerHTML = this.getEditorValue();
                    }
                }
            }
        },{
            text: 'Publish',
            scope: this,
            handler: function(){
                this.savePage();
            }
        }];
    },

    requestPage: function(){
        Ext.Ajax.request({
            url: this.service.getUri() + '?fields=id,content&count=1&filterBy=pageId&filterOp=equals&filterValue=' + this.page.id + '&format=json',
            scope: this,
            success: function(response, opts){
                var result = Ext.JSON.decode(response.responseText);
                if (result.entry[0]) {
                    this.record = result.entry[0];

                    // set value
                    this.setEditorValue(this.record.content);
                }
            },
            failure: function(response){
                Ext.Msg.alert('Error', response.responseText);
            }
        });
    },

    savePage: function(){
        var method;
        var params;
        if (this.record) {
            method = 'PUT';
            params = {
                id: this.record.id,
                content: this.getEditorValue()
            };
        } else {
            method = 'POST';
            params = {
                pageId: this.page.id,
                content: this.getEditorValue()
            };
        }

        this.submitPage(method, params);
    }

});


Ext.define('Amun.Service', {

    uri: null,
    types: null,

    constructor: function(config){
        config = config || {};
        Ext.apply(this, config);
    },

    hasType: function(type){
        for (var i = 0; i < this.types.length; i++) {
            if (this.types[i] == type) {
                return true;
            }
        }
        return false;
    },

    hasTypeStartsWith: function(type){
        for (var i = 0; i < this.types.length; i++) {
            if (this.types[i].substr(0, type.length) == type) {
                return this.types[i];
            }
        }
        return false;
    },

    getFirstType: function(){
        var type = null;
        for (var i = 0; i < this.types.length; i++) {
            if (this.types[i] != 'http://ns.amun-project.org/2011/amun/data/1.0') {
                type = this.types[i];
            }
        }
        return type;
    },

    getTypes: function(){
        return this.types;
    },

    getUri: function(){
        return this.uri;
    },

    getName: function(){
        var name;
        var pos = this.uri.lastIndexOf('/');

        name = this.uri.substr(pos + 1);
        name = name.charAt(0).toUpperCase() + name.substr(1); // ucfirst

        return name;
    },

    getNamespace: function(){
        var ns = this.getFirstType().substring(37);
        ns = ns.replace(/\//g, '.');

        return ns;
    }

});


Ext.define('Amun.User', {

    name: null,
    thumbnailUrl: null,

    constructor: function(config){
        config = config || {};
        Ext.apply(this, config);
    }

});

Ext.define('Workbench.model.NavigationEntry', {
    extend: 'Ext.data.Model',
    fields: [
        { name: 'title', type: 'string' },
        { name: 'type', type: 'string' }
    ]
});


Ext.define('Workbench.view.Navigation', {
    extend: 'Ext.panel.Panel',

    alias: 'widget.navigation',

    title: 'Navigation',
    border: 0,
    cls: 'wb-nav',
    layout: 'accordion',
    items: []

});

Ext.define('Workbench.view.Content', {
    extend: 'Ext.tab.Panel',

    alias: 'widget.content',

    title: 'Content',
    border: false,
    cls: 'wb-content',
    minTabWidth: 64,
    items: [],

    initComponent: function(){
        /*
        var dashboard = {
            title: 'Dashboard',
            html: '<p>content</p>',
            border: false
        };

        this.items.push(dashboard);
        */

        this.callParent(arguments);
    }

});


Ext.define('Amun.service.content.page.GadgetStore', {
    extend: 'Ext.data.TreeStore',
    model: 'Amun.service.content.page.Gadget',
    root: {
        text: 'Gadgets',
        id: null,
        leaf: false
    }
});


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



Ext.define('Amun.service.core.service.Grid', {
    extend: 'Amun.Grid',

    getTbar: function(){
        return [{
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

    onDeleteClick: function(el, e, eOpts){
        var grid = el.findParentByType('grid');
        var rec = grid.getSelectionModel().getSelection()[0];

        Ext.Msg.confirm('Confirmation', 'Do you want uninstall the service?', function(btn) {
            if (btn == 'yes') {
                // send deinstallation request
                Ext.Ajax.request({
                    url: this.service.getUri() + '?format=json',
                    method: 'POST',
                    headers: {
                        'X-HTTP-Method-Override': 'DELETE',
                        'Accept': 'application/json'
                    },
                    params: {
                        id: this.selectedRecordId
                    },
                    scope: this,
                    success: function(response, opts) {
                        var data = Ext.JSON.decode(response.responseText);
                        if (data && data.success == true) {
                            Ext.Msg.alert('Success', data.text, function(){
                                // reload
                                location.reload();
                            }, this);
                        } else {
                            Ext.Msg.alert('Failed', data.text ? data.text : 'Unknown error occured');
                        }
                    },
                    failure: function(response, opts) {
                        Ext.Msg.alert('Failed', 'Could not download service');
                    }
                });

                return true;
            }
        }, this);
    }

});



Ext.define('Amun.service.file.Editor', {
    extend: 'Amun.SplitEditor',

    getEditorComponent: function(){
        return this.getComponent(0).getComponent(0).getComponent(0).getComponent(1);
    },

    getEditorPanel: function(){
        var contentTypes = Ext.create('Ext.data.Store', {
            fields: ['type'],
            data : [
                {'type': 'application/json'},
                {'type': 'application/xhtml+xml'},
                {'type': 'application/xml'},
                {'type': 'text/css'},
                {'type': 'text/html'},
                {'type': 'text/javascript'},
                {'type': 'text/plain'},
                {'type': 'text/xml'}
            ]
        });

        return {
            border: false,
            layout: {
                type: 'vbox',
                align: 'stretch',
                pack: 'start'
            },
            items: [{
                xtype: 'combobox',
                store: contentTypes,
                queryMode: 'local',
                displayField: 'type',
                valueField: 'type',
                value: 'text/plain',
                editable: false
            },{
                xtype: 'aceeditor',
                flex: 1,
                grow: true,
                name: 'content',
                value: '' 
            }]
        };
    },

    getBbar: function(){
        return [{
            text: 'Save',
            scope: this,
            handler: function(){
                this.savePage();
            }
        }];
    },

    requestPage: function(){
        Ext.Ajax.request({
            url: this.service.getUri() + '?fields=id,content&count=1&filterBy=pageId&filterOp=equals&filterValue=' + this.page.id + '&format=json',
            scope: this,
            success: function(response, opts){
                var result = Ext.JSON.decode(response.responseText);
                if (result.entry[0]) {
                    this.record = result.entry[0];

                    // set value
                    this.setEditorValue(this.record.content);
                }
            },
            failure: function(response){
                Ext.Msg.alert('Error', response.responseText);
            }
        });
    },

    savePage: function(){
        var contentType = this.down('combobox');
        var method;
        var params;
        if (this.record) {
            method = 'PUT';
            params = {
                id: this.record.id,
                contentType: contentType.getSubmitValue(),
                content: this.getEditorValue()
            };
        } else {
            method = 'POST';
            params = {
                pageId: this.page.id,
                contentType: contentType.getSubmitValue(),
                content: this.getEditorValue()
            };
        }

        this.submitPage(method, params);
    }

});


Ext.define('Amun.service.user.group.Grid', {
    extend: 'Amun.Grid',

    columnConfig: {
        id: 80,
        title: 600,
        date: 120
    }

});



Ext.define('Amun.service.mail.Grid', {
    extend: 'Amun.Grid',

    getTbar: function(){
        return [{
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



Ext.define('Workbench.controller.Content', {
    extend: 'Ext.app.Controller',

    views: ['Content'],
    refs: [{
        selector: 'panel[cls=wb-content]',
        ref: 'gridContainer'
    }],

    selectedService: null,

    statics: {
        recordId: null
    },

    init: function() {
        this.application.on({
            navclick: this.loadGrid,
            scope: this
        });

        this.control({
            'panel[cls=wb-content]': {
                render: this.onPanelRendered
            },
        });
    },

    onPanelRendered: function(){
        this.loadGrid('http://ns.amun-project.org/2011/amun/service/content/page');
    },

    getSelectedService: function(){
        return this.selectedService;
    },

    getActiveTab: function(){
        return this.getGridContainer().getActiveTab();
    },

    loadGrid: function(uri){
        // discover service
        var service = Amun.xrds.Manager.findService(uri);
        if (service != false) {
            // set selected service
            this.selectedService = service;

            // activate grid if available
            var grid = this.getGridContainer().getComponent(service.getUri());
            if (grid == undefined) {
                // request supported fields
                Ext.Ajax.request({
                    url: service.getUri() + '/@supportedFields?format=json',
                    scope: this,
                    success: function(response, opts){
                        var result = Ext.JSON.decode(response.responseText);
                        fields = result.item;

                        // build grid
                        this.buildGrid(service, fields);
                    },
                    failure: function(response){
                        Ext.Msg.alert('Error', response.responseText);
                    }
                });
            } else {
                this.getGridContainer().setActiveTab(grid);
                grid.reload();
            }
        } else {
            console.log('Unknown service ' + uri);
        }
    },

    buildGrid: function(service, result){
        // check whether we have a custom form class else we build the form 
        // based on the json we received
        var grid;
        var className = 'Amun.' + this.getSelectedService().getNamespace() + '.Grid';
        var extClass = Ext.ClassManager.get(className);

        var config = {
            itemId: service.getUri(),
            title: service.getName(),
            closable: true,
            service: service,
            result: result
        };

        if (extClass != null) {
            grid = Ext.create(className, config);
        } else {
            grid = Ext.create('Amun.Grid', config);
        }

        // add grid
        this.getGridContainer().add(grid);
        this.getGridContainer().setActiveTab(grid);
    }

});


Ext.define('Workbench.controller.Navigation', {
    extend: 'Ext.app.Controller',

    views: ['Navigation'],
    refs: [{
        selector: 'panel[cls=wb-nav]',
        ref: 'navContainer'
    }],
    models: ['NavigationEntry'],
    controllers: ['Content'],

    init: function() {
        this.control({
            'dataview[cls=wb-navigation]': {
                itemclick: this.onLinkClick
            },
            'panel[cls=wb-nav]': {
                render: this.onPanelRendered
            }
        });
    },

    onLinkClick: function(el, rec, item, index) {
        this.application.fireEvent('navclick', rec.get('type'));
    },

    onPanelRendered: function(){
        this.buildNavigation();
    },

    buildNavigation: function(){
        // content
        this.addNavItems('Content', [{
            title: 'Page',
            type: 'http://ns.amun-project.org/2011/amun/service/content/page'
        },{
            title: 'Gadget',
            type: 'http://ns.amun-project.org/2011/amun/service/content/gadget'
        },{
            title: 'Media',
            type: 'http://ns.amun-project.org/2011/amun/service/media'
        }]);

        // user
        this.addNavItems('User', [{
            title: 'Account',
            type: 'http://ns.amun-project.org/2011/amun/service/user/account'
        },{
            title: 'Group',
            type: 'http://ns.amun-project.org/2011/amun/service/user/group'
        },{
            title: 'Activity',
            type: 'http://ns.amun-project.org/2011/amun/service/user/activity'
        }]);

        // system
        this.addNavItems('System', [{
            title: 'Service',
            type: 'http://ns.amun-project.org/2011/amun/service/core/service'
        },{
            title: 'Registry',
            type: 'http://ns.amun-project.org/2011/amun/service/core/registry'
        },{
            title: 'Mail',
            type: 'http://ns.amun-project.org/2011/amun/service/mail'
        },{
            title: 'Oauth',
            type: 'http://ns.amun-project.org/2011/amun/service/oauth'
        },{
            title: 'Country',
            type: 'http://ns.amun-project.org/2011/amun/service/country'
        },{
            title: 'Phpinfo',
            type: 'http://ns.amun-project.org/2011/amun/service/phpinfo'
        }]);

        // services
        /*
        this.addNavItems('Service', [{
            title: 'Comment',
            type: 'http://ns.amun-project.org/2011/amun/service/comment'
        },{
            title: 'File',
            type: 'http://ns.amun-project.org/2011/amun/service/file'
        },{
            title: 'Forum',
            type: 'http://ns.amun-project.org/2011/amun/service/forum'
        },{
            title: 'News',
            type: 'http://ns.amun-project.org/2011/amun/service/news'
        },{
            title: 'Page',
            type: 'http://ns.amun-project.org/2011/amun/service/page'
        },{
            title: 'Php',
            type: 'http://ns.amun-project.org/2011/amun/service/php'
        },{
            title: 'Pipe',
            type: 'http://ns.amun-project.org/2011/amun/service/pipe'
        },{
            title: 'Redirect',
            type: 'http://ns.amun-project.org/2011/amun/service/redirect'
        }]);
        */
    },

    addNavItems: function(name, children){
        var services = Amun.xrds.Manager.getServices();
        var data = [];
        for (var j = 0; j < children.length; j++) {
            for (var i = 0; i < services.length; i++) {
                if (services[i].hasType(children[j].type)) {
                    data.push({
                        title: children[j].title,
                        type: children[j].type
                    });
                }
            }
        }

        var navigation = Ext.create('Ext.view.View', {
            store: Ext.create('Ext.data.Store', {
                model: 'Workbench.model.NavigationEntry',
                data: data
            }),
            trackOver: true,
            cls: 'wb-navigation',
            itemSelector: 'div.wb-navigation-item',
            overItemCls: 'wb-navigation-item-hover',
            tpl: '<tpl for="."><div class="wb-navigation-item">{title}</div></tpl>'
        });

        var panel = Ext.create('Ext.Panel', {
            title: name,
            iconCls: 'wb-icon-' + name.toLowerCase(),
            items: [navigation]
        });
        this.getNavContainer().add(panel);
    }

});


