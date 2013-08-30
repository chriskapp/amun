
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

