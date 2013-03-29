
Ext.define('Amun.service.content.page.Gadget', {
    extend: 'Ext.data.Model',
    fields: [
        { name: 'id', type: 'int' },
        { name: 'text', type: 'string', mapping: 'name' },
        { name: 'leaf', type: 'boolean', defaultValue: true },
        { name: 'sort', type: 'int', defaultValue: 0 },
        { name: 'checked', type: 'boolean', defaultValue: false }
    ]
});

