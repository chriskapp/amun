
Ext.define('Amun.service.core.service.Service', {
    extend: 'Ext.data.Model',
    fields: [
        { name: 'source', type: 'string' },
        { name: 'name', type: 'string' },
        { name: 'description', type: 'string' },
        { name: 'link', type: 'string' },
        { name: 'license', type: 'string' },
        { name: 'version', type: 'string' },
        { name: 'installed', type: 'boolean' }
    ]
});

