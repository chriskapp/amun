
Ext.require('Amun.Editor');

Ext.define('Amun.Form', {
    extend: 'Ext.form.Panel',

    formMethod: 'POST',

    initComponent: function(){
        var me = this;
        me.addEvents('submit', 'reset');

        var el = this.parseElements(this.form);
        Ext.apply(me, el);

        me.callParent();
    },

    reload: function(){
        this.getForm().reset();
    },

    getAction: function(){
        return this.form.form.action;
    },

    getMethod: function(){
        return this.form.form.method;
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
        // build items
        var items = [];
        for (var i = 0; i < item.item.children.item.length; i++) {
            items.push(this.parseElements(item.item.children.item[i]));
        }

        // create form panel
        return {
            url: item.action,
            cls: 'wb-content-form',
            layout: 'anchor',
            items: items,
            autoScroll: true,
            bodyStyle: 'padding:5px 5px 0',
            border: false,
            buttons: [{
                text: 'Reset',
                scope: this,
                handler: function(){
                    this.fireEvent('reset', this);
                }
            }, {
                text: 'Submit',
                formBind: true,
                disabled: true,
                scope: this,
                handler: function(){
                    this.fireEvent('submit', this);
                }
            }],
        };
    },

    onPanel: function(item){
        // build items
        var items = [];
        for (var i = 0; i < item.children.item.length; i++) {
            items.push(this.parseElements(item.children.item[i]));
        }

        // create fieldset
        return {
            xtype: 'fieldset',
            title: item.label,
            layout: 'anchor',
            items: items
        };
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
        /*
        var val = item.value ? item.value : '';
        var disabled = item.disabled ? 'disabled="disabled"' : '';

        html+= '<p>';
        html+= '<label for="' + item.ref + '">' + item.label + '</label>';
        html+= '<input type="text" name="' + item.ref + '" id="' + item.ref + '" value="' + val + '" ' + disabled + ' />';
        html+= '</p>';
        */

        return {
            xtype: 'textfield',
            name: item.ref,
            disabled: item.disabled,
            value: item.value,
            fieldLabel: item.label
        };
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
                    fieldLabel: item.label
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
                    fieldLabel: item.label
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
            data : data
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
            grow: true,
            name: item.ref,
            fieldLabel: item.label,
            value: item.value,
            disabled: item.disabled
        };
    }

});
