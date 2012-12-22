
Ext.define('Amun.service.content.page.Form', {
    extend: 'Amun.Form',

    initComponent: function(){
        var me = this;
        me.callParent();

        // load group gadgets
        this.loadGadgets();
    },

    reload: function(){
        this.getForm().reset();

        // load existing gadgets
        if (this.recordId > 0) {
            this.loadExistingGadgets();
        }
    },

    loadGadgets: function(){
        var gadgetUri = Amun.xrds.Manager.findServiceUri('http://ns.amun-project.org/2011/amun/service/content/gadget');
        if (gadgetUri !== false) {
            Ext.Ajax.request({
                url: gadgetUri + '?count=1024&fields=id,name&format=json',
                scope: this,
                success: function(response, opts){
                    var result = Ext.JSON.decode(response.responseText);
                    if (result.entry.length > 0) {
                        var items = [];
                        for (var i = 0; i < result.entry.length; i++) {
                            items.push({
                                xtype: 'checkbox',
                                boxLabel: result.entry[i].name,
                                itemId: 'gadget_' + result.entry[i].id,
                                name: 'gadget_' + result.entry[i].id,
                                inputValue: result.entry[i].id,
                                scope: this,
                                handler: function(){
                                    this.updateGadgets();
                                }
                            });
                        }

                        var comboGroup = [{
                            xtype: 'hiddenfield',
                            cls: 'wb-form-gadgets',
                            name: 'gadgets',
                            value: ''
                        },{
                            xtype: 'checkboxgroup',
                            fieldLabel: 'Gadgets',
                            columns: 3,
                            items: items
                        }];

                        this.add(comboGroup);
                        this.updateLayout();

                        // load existing gadgets
                        if (this.recordId > 0) {
                            // defer so the update layout has enough time
                            Ext.Function.defer(function(){
                                this.loadExistingGadgets();
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

    loadExistingGadgets: function(){
        var groupRightUri = Amun.xrds.Manager.findServiceUri('http://ns.amun-project.org/2011/amun/service/content/page/gadget');
        if (groupRightUri !== false) {
            Ext.Ajax.request({
                url: groupRightUri + '?fields=id,gadgetId&count=1024&filterBy=pageId&filterOp=equals&filterValue=' + this.recordId + '&format=json',
                scope: this,
                success: function(response, opts){
                    var result = Ext.JSON.decode(response.responseText);
                    if (result.entry.length > 0) {
                        var checkboxGroup = this.query('checkboxgroup')[0];
                        for (var i = 0; i < result.entry.length; i++) {
                            var gadget = checkboxGroup.getComponent('gadget_' + result.entry[i].gadgetId);
                            if (gadget) {
                                gadget.setValue(true);
                            }
                        }

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
        var gadgets = this.query('checkbox');
        for (var i = 0; i < gadgets.length; i++) {
            if (gadgets[i].getRawValue()) {
                value+= gadgets[i].getSubmitValue() + ',';
            }
        }

        var el = this.query('hidden[cls=wb-form-gadgets]')[0];
        el.setValue(value);
    }

});

