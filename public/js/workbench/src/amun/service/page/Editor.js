
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
