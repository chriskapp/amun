
Ext.define('Amun.form.Editor', {
    extend: 'Ext.form.field.Text',

    alias: 'widget.aceeditor',
    editorId: null,
    editor: null,

    initComponent: function(){
        var me = this;
        me.editorId = Ext.id();

        var config = {
            width: 640,
            height: 300,
            fieldSubTpl: [
                '<div id="{id}" {inputAttrTpl}>',
                    '<div id="' + me.editorId + '" class="amun-ace-editor"></div>',
                '</div>',
                {
                    disableFormats: true
                }
            ]
        };
        Ext.apply(me, config);

        me.callParent();
    },

    afterRender: function(){
        var me = this;

        me.callParent(arguments);

        // ace editor
        me.editor = ace.edit(me.editorId);
        me.editor.setTheme('ace/theme/eclipse');
        me.editor.getSession().setMode('ace/mode/html');
        me.editor.setValue(me.rawValue, -1);
    },

    setRawValue: function(value){
        var me = this;
        me.rawValue = value;
        return value;
    },

    getRawValue: function(){
        var me = this;
        var value = me.editor != null ? me.editor.getValue() : '';
        return value;
    }

});
