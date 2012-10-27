
Ext.define('Workbench.controller.Navigation', {
    extend: 'Ext.app.Controller',

    views: ['Navigation'],
    models: ['NavigationEntry'],
    controllers: ['Content'],

    init: function() {
        this.control({
            'dataview[cls=wb-navigation]': {
                itemclick: this.onLinkClick
            }
        });
    },

    onLinkClick: function(el, rec, item, index) {
        this.application.fireEvent('navclick', rec.get('type'));
    }

});
