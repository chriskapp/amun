
Ext.define('Workbench.view.Content', {
    extend: 'Ext.tab.Panel',

    alias: 'widget.content',

    title: 'Content',
    border: false,
    cls: 'wb-content',
    minTabWidth: 64,
    items: [],

    initComponent: function(){
        /*
        var dashboard = {
            title: 'Dashboard',
            html: '<p>content</p>',
            border: false
        };

        this.items.push(dashboard);
        */

        this.callParent(arguments);
    }

});
