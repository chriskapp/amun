
Ext.define('Amun.SplitEditor', {
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
                editors.setWidth(this.getEditorComponent().getWidth());
                editors.setHeight(this.getEditorComponent().getHeight());
            }

            this.getEditorComponent().on('resize', function(el, width, height, oldWidth, oldHeight, eOpts){
                var editors = Ext.select('.amun-ace-editor');
                if (editors) {
                    editors.setWidth(width);
                    editors.setHeight(height);
                }
            });

            // load page data
            me.requestPage();
        });
    },

    buildContainer: function(){
        return Ext.create('Ext.panel.Panel', {
            border: false,
            layout: 'border',
            bbar: this.getBbar(),
            items: [{
                region: 'west',
                layout: 'fit',
                width: '50%',
                border: false,
                split: true,
                minWidth: 200,
                collapsible: true,
                collapseMode: 'mini',
                preventHeader: true,
                items: [this.getEditorPanel()]
            },{
                region: 'center',
                layout: 'fit',
                width: '50%',
                border: false,
                items: [{
                    xtype: 'component',
                    autoEl: {
                        tag: 'iframe',
                        cls: 'wb-iframe-preview',
                        src: url + this.page.path
                    }
                }]
            }]
        });
    },

    reloadPreview: function(){
        if (frames[0]) {
            frames[0].location.reload();
        }
    },

    setEditorValue: function(value){
        var editors = this.query('aceeditor');
        if (editors[0]) {
            editors[0].editor.setValue(value, -1);
        }
    },

    getEditorValue: function(){
        var editors = this.query('aceeditor');
        if (editors[0]) {
            return editors[0].editor.getValue();
        }
        return '';
    },

    getEditorComponent: function(){
        return this.getComponent(0).getComponent(0);
    },

    getEditorPanel: function(){
        return {
            xtype: 'aceeditor',
            grow: true,
            name: 'content',
            value: ''
        };
    },

    submitPage: function(method, params){
        Ext.Ajax.request({
            url: this.service.getUri() + '?format=json',
            scope: this,
            headers: {
                'X-HTTP-Method-Override': method
            },
            params: params,
            success: function(response, opts){
                var result = Ext.JSON.decode(response.responseText);
                if (result.success) {
                    Ext.Msg.alert('Success', result.text);

                    if (!this.record) {
                        this.requestPage();
                    }

                    this.reloadPreview();
                } else {
                    Ext.Msg.alert('Error', result.text ? result.text : 'Unknown error occured');
                }
            },
            failure: function(response){
                Ext.Msg.alert('Error', response.responseText);
            }
        });
    },

    /**
     * Method wich should return the bottom bar of the editor
     *
     * @return array
     */
    getBbar: function(){
        return [];
    },

    /**
     * Method wich should load the record assigned to this page. The record 
     * should be stored in this.record
     */
    requestPage: function(){
    }

});
