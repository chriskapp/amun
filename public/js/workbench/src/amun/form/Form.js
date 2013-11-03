
Ext.require('Amun.form.Editor');

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
