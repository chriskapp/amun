
Ext.define('Amun.service.mail.Form', {
    extend: 'Amun.form.Form',

    initComponent: function(){
        var me = this;
        me.callParent();

        me.on('formLoaded', function(el){
        	this.getFormPanel().addBodyCls('wb-overflow');
        });
    }

});
