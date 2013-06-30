
Ext.define('Amun.service.core.service.Form', {
    extend: 'Amun.form.Form',

    formPanel: null,
    loadingPanel: null,
    providerCombobox: null,
    providerSearch: null,

    initComponent: function(){
        var me = this;
        me.callParent();

        // load services
        this.formPanel.getStore().load();
    },

    reload: function(){
        this.formPanel.getStore().load();
    },

    buildForm: function(form){
        // build form
        this.formPanel = null;
        var serviceUri = Amun.xrds.Manager.findServiceUri('http://ns.amun-project.org/2011/amun/service/core/service');
        if (serviceUri !== false) {
            var store = Ext.create('Amun.service.core.service.ServiceStore', {
                autoLoad: false,
                proxy: {
                    type: 'ajax',
                    url: serviceUri + '/discover?format=json',
                    reader: {
                        type: 'json',
                        root: 'entry',
                        idProperty: 'source',
                        totalProperty: 'totalResults'
                    }
                }
            });

            var providerStore = Ext.create('Amun.service.core.service.ProviderStore', {
                autoLoad: true,
                proxy: {
                    type: 'ajax',
                    url: serviceUri + '/provider?count=1024&fields=id,url&sortBy=id&sortOrder=ascending&format=json',
                    reader: {
                        type: 'json',
                        root: 'entry',
                        idProperty: 'id',
                        totalProperty: 'totalResults'
                    }
                }
            });

            this.providerCombobox = Ext.create('Ext.form.field.ComboBox', {
                hideLabel: true,
                store: providerStore,
                displayField: 'url',
                valueField: 'url',
                value: 'localhost',
                emptyText: 'Select a provider ...',
                selectOnFocus: true,
                width: 200,
                indent: true,
                disabled: true // not complete implemented
            });

            this.providerSearch = {            
                xtype: 'textfield',
                width: 200,
                listeners: {
                    scope: this,
                    specialkey: function(el, e){
                        if (e.getKey() == e.ENTER) {
                            var store = this.formPanel.getStore();
                            var search = el.getValue();

                            if (search != '') {
                                store.clearFilter(true);
                                store.filterBy(function(rec){
                                    search = search.toLowerCase();
                                    var name = rec.data.name.toLowerCase();
                                    var desc = rec.data.description.toLowerCase();

                                    return name.indexOf(search) != -1 || desc.indexOf(search) != -1;
                                });
                            } else {
                                store.clearFilter(false);
                            }
                        }
                    }
                }
            };

            this.formPanel = Ext.create('Ext.grid.Panel', {
                region: 'center',
                margins: '0 0 0 0',
                border: false,
                store: store,
                columns: [{
                    text: 'Service',
                    dataIndex: 'description',
                    flex: 1,
                    renderer: function renderTopic(value, p, record) {
                        if (record.data.installed) {
                            return Ext.String.format(
                                '<b><a href="{0}" target="_blank" style="color:#aaa;">{1}</a></b><p style="color:#aaa;">{2}</p>',
                                record.data.link,
                                record.data.name,
                                value
                            );
                        } else {
                            return Ext.String.format(
                                '<b><a href="{0}" target="_blank">{1}</a></b><p>{2}</p>',
                                record.data.link,
                                record.data.name,
                                value
                            );
                        }
                    },
                    sortable: false
                },{
                    text: 'Version',
                    dataIndex: 'version',
                    width: 100
                },{
                    text: 'License',
                    dataIndex: 'license',
                    width: 100
                }],
                tbar: ['Search:', this.providerSearch, '->', 'Provider:', this.providerCombobox, {
                    text: 'Refresh',
                    scope: this,
                    handler: function(){
                        var provider = this.providerCombobox.getRawValue();
                        this.loadServices(provider);
                    }
                }],
                buttons: [{
                    text: 'Cancel',
                    scope: this,
                    handler: function(){
                        this.close();
                    }
                },{
                    text: 'Submit',
                    formBind: true,
                    disabled: true,
                    scope: this,
                    handler: function(){
                        var records = this.formPanel.getSelectionModel().getSelection();
                        if (records && records.length > 0) {
                            var source = records[0].data.source;
                            var provider = this.providerCombobox.getRawValue();

                            // show loading panel
                            this.loadingPanel = Ext.create('Ext.window.Window', {
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
                            this.loadingPanel.show();

                            this.loadingPanel.getComponent(0).wait({
                                interval: 200,
                                increment: 15
                            });

                            if (provider == '' || provider == 'localhost') {
                                this.installService(source);
                            } else {
                                this.loadingPanel.getComponent(0).updateText('Downloading ...');
                                // we have to download the service from the 
                                // remote provider and then we can install it
                                Ext.Ajax.request({
                                    url: serviceUri + '/download?format=json',
                                    method: 'POST',
                                    params: {
                                        provider: provider,
                                        source: source
                                    },
                                    scope: this,
                                    success: function(response, opts) {
                                        var data = Ext.JSON.decode(response.responseText);
                                        if (data && data.success == true) {
                                            this.installService(data.source);
                                        } else {
                                            this.loadingPanel.hide();
                                            Ext.Msg.alert('Failed', data.text);
                                        }
                                    },
                                    failure: function(response, opts) {
                                        this.loadingPanel.hide();
                                        Ext.Msg.alert('Failed', 'Could not download service');
                                    }
                                });
                            }
                        }
                    }
                }],
            });
        }

        var el = {
            layout: 'border',
            border: false,
            items: [this.formPanel]
        };
        el.formMethod = form.method;
        el.formAction = form.action;

        return el;
    },

    installService: function(source){
        this.loadingPanel.getComponent(0).updateText('Installing ...');
        // we can simply install the service
        Ext.Ajax.request({
            url: this.formAction + '?format=json',
            method: 'POST',
            params: {
                source: source
            },
            scope: this,
            success: function(response, opts) {
                var data = Ext.JSON.decode(response.responseText);
                if (data && data.success == true) {
                    this.loadingPanel.getComponent(0).reset();
                    this.loadingPanel.hide();
                    Ext.Msg.alert('Success', data.text, function(){
                        // reload
                        location.reload();
                    }, this);
                } else {
                    this.loadingPanel.hide();
                    Ext.Msg.alert('Failed', data.text);
                }
            },
            failure: function(response, opts) {
                this.loadingPanel.hide();
                Ext.Msg.alert('Failed', 'Could not install service');
            }
        });
    },

    loadServices: function(provider){
        this.formPanel.getStore().removeAll();
        if (provider == '' || provider == 'localhost') {
            this.formPanel.getStore().load({
                params: {}
            });
        } else {
            this.formPanel.getStore().load({
                params: {
                    provider: provider
                }
            });
        }
    }

});

