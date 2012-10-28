
Ext.define('Amun.ColumnConfig', {
    extend: 'Ext.util.HashMap',

    constructor: function(config){
        this.callParent(arguments);
        this.loadConfig();
    },

    loadConfig: function(){
        this.add('http://ns.amun-project.org/2011/amun/content/page', {
            id: '60',
            parentId: '100',
            sort: '100',
            title: '400',
            template: '200',
            date: '200'
        });

        this.add('http://ns.amun-project.org/2011/amun/content/gadget', {
            id: '60',
            name: '260',
            title: '316',
            date: '120'
        });

        this.add('http://ns.amun-project.org/2011/amun/content/media', {
            id: '60',
            path: '328',
            size: '80',
            mimeType: '160',
            date: '120'
        });

        this.add('http://ns.amun-project.org/2011/amun/content/service', {
            id: '60',
            name: '328',
            license: '120',
            version: '120',
            date: '120'
        });

        this.add('http://ns.amun-project.org/2011/amun/user/account', {
            id: '60',
            status: '30',
            name: '418',
            lastSeen: '120',
            date: '120'
        });

        this.add('http://ns.amun-project.org/2011/amun/user/group', {
            id: '60',
            title: '584',
            date: '120'
        });

        this.add('http://ns.amun-project.org/2011/amun/user/right', {
            id: '60',
            name: '360',
            description: '344'
        });

        this.add('http://ns.amun-project.org/2011/amun/system/log', {
            id: '60',
            type: '100',
            table: '260',
            authorName: '208',
            date: '120'
        });

        this.add('http://ns.amun-project.org/2011/amun/system/mail', {
            id: '60',
            name: '200',
            from: '160',
            subject: '336'
        });

        this.add('http://ns.amun-project.org/2011/amun/system/registry', {
            id: '60',
            name: '200',
            value: '416'
        });

        this.add('http://ns.amun-project.org/2011/amun/system/api', {
            id: '60',
            status: '30',
            title: '338',
            url: '200',
            date: '120'
        });

        this.add('http://ns.amun-project.org/2011/amun/system/host', {
            id: '60',
            status: '30',
            url: '338',
            template: '200',
            date: '120'
        });

        this.add('http://ns.amun-project.org/2011/amun/service/comment', {
            id: '60',
            text: '584',
            date: '120'
        });

        this.add('http://ns.amun-project.org/2011/amun/service/page', {
            id: '60',
            pageTitle: '584',
            date: '120'
        });

        this.add('http://ns.amun-project.org/2011/amun/service/news', {
            id: '60',
            title: '584',
            date: '120'
        });

        this.add('http://ns.amun-project.org/2011/amun/service/forum', {
            id: '60',
            title: '584',
            date: '120'
        });

        this.add('http://ns.amun-project.org/2011/amun/service/plugin', {
            id: '60',
            title: '584',
            date: '120'
        });

        this.add('http://ns.amun-project.org/2011/amun/service/redirect', {
            id: '60',
            pageTitle: '584',
            date: '120'
        });

        this.add('http://ns.amun-project.org/2011/amun/service/tracker', {
            id: '60',
            title: '584',
            date: '120'
        });

        this.add('http://ns.amun-project.org/2011/amun/service/php', {
            id: '60',
            pageTitle: '584',
            date: '120'
        });

        this.add('http://ns.amun-project.org/2011/amun/service/pipe', {
            id: '60',
            mediaPath: '584',
            date: '120'
        });
    },

    getConfigByType: function(type){
        if (this.containsKey(type)) {
            return this.get(type);
        } else {
            return null;
        }
    }

});
