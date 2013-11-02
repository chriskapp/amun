
Ext.define('Amun.service.page.Editor', {
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
                editors.setWidth(this.getComponent(0).getComponent(0).getWidth());
                editors.setHeight(this.getComponent(0).getComponent(0).getHeight());
            }

            this.getComponent(0).getComponent(0).on('resize', function(el, width, height, oldWidth, oldHeight, eOpts){
                var editors = Ext.select('.amun-ace-editor');
                if (editors) {
                    editors.setWidth(width);
                    editors.setHeight(height);
                }
            });

            me.requestPage();
        });
    },

    buildContainer: function(){
        return Ext.create('Ext.panel.Panel', {
            border: false,
            layout: 'border',
            items: [{
                region: 'west',
                width: '50%',
                border: false,
                split: true,
                minWidth: 200,
                layout: 'fit',
                items: [{
                    xtype: 'aceeditor',
                    grow: true,
                    name: 'content',
                    value: ''
                }]
            },{
                region: 'center',
                width: '50%',
                border: false,
                layout: 'fit',
                html: '<iframe src="' + url + this.page.path + '" width="100%" height="100%" />'
            }]
        });
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
                    var editors = this.query('aceeditor');
                    if (editors[0]) {
                        editors[0].editor.setValue(this.record.content, -1);
                    }
                }
            },
            failure: function(response){
                Ext.Msg.alert('Error', response.responseText);
            }
        });
    }

});
