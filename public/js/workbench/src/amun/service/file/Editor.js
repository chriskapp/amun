
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
