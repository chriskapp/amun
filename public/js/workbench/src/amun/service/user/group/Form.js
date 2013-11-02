
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
