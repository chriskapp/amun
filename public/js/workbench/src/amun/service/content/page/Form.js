
Ext.require('Amun.service.content.page.GadgetStore');

Ext.define('Amun.service.content.page.Form', {
    extend: 'Amun.Form',

    formPanel: null,
    gadgetPanel: null,

    initComponent: function(){
        var me = this;
        me.callParent();

        // load group gadgets
        this.gadgetPanel.getStore().load();
    },

    reload: function(){
        this.getForm().reset();
        this.gadgetPanel.getStore().load();
    },

    buildForm: function(form){
        // build form
        this.formPanel = this.parseElements(form);
        this.formPanel.items.push({
            xtype: 'hiddenfield',
            cls: 'wb-form-gadgets',
            name: 'gadgets',
            value: ''
        });
        this.formPanel.region = 'center';

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

