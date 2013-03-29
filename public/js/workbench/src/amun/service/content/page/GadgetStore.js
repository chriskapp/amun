
Ext.require('Amun.service.content.page.Gadget');

Ext.define('Amun.service.content.page.GadgetStore', {
    extend: 'Ext.data.TreeStore',
    model: 'Amun.service.content.page.Gadget',
    reader: {
        type: 'json',
        root: 'entry',
        idProperty: 'id',
        totalProperty: 'totalResults'
    },
    root: {
        text: 'Gadgets',
        id: null,
        leaf: false
    }
});
