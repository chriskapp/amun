
Ext.define('Workbench.view.Navigation', {
    extend: 'Ext.panel.Panel',

    alias: 'widget.navigation',

    title: 'Navigation',
    border: 0,
    layout: 'accordion',
    items: [],

    initComponent: function() {
        var nav = this.getNavigation();
        for (var i = 0; i < nav.length; i++) {
            var navigation = Ext.create('Ext.view.View', {
                store: nav[i].store,
                trackOver: true,
                cls: 'wb-navigation',
                itemSelector: 'div.wb-navigation-item',
                overItemCls: 'wb-navigation-item-hover',
                tpl: '<tpl for="."><div class="wb-navigation-item">{title}</div></tpl>'
            });

            var panel = Ext.create('Ext.Panel', {
                title:  nav[i].title,
                iconCls: 'wb-icon-' + nav[i].title.toLowerCase(),
                items: [navigation]
            });

            this.items.push(panel);
        }

        this.callParent(arguments);
    },

    getNavigation: function(){
        var nav = [];
        var children = [];
        
        // content
        children = [];
        if (Amun.xrds.Manager.findServiceUri('http://ns.amun-project.org/2011/amun/content/page')) {
            children.push({
                title: 'Pages',
                type: 'http://ns.amun-project.org/2011/amun/content/page'
            });
        }

        if (Amun.xrds.Manager.findServiceUri('http://ns.amun-project.org/2011/amun/content/gadget')) {
            children.push({
                title: 'Gadgets',
                type: 'http://ns.amun-project.org/2011/amun/content/gadget'
            });
        }

        if (Amun.xrds.Manager.findServiceUri('http://ns.amun-project.org/2011/amun/content/media')) {
            children.push({
                title: 'Media',
                type: 'http://ns.amun-project.org/2011/amun/content/media'
            });
        }

        nav.push({
            title: 'Content',
            store: Ext.create('Ext.data.Store', {
                model: 'Workbench.model.NavigationEntry',
                data: children
            })
        });

        // user
        children = [];

        if (Amun.xrds.Manager.findServiceUri('http://ns.amun-project.org/2011/amun/user/account')) {
            children.push({
                title: 'Account',
                type: 'http://ns.amun-project.org/2011/amun/user/account'
            });
        }

        if (Amun.xrds.Manager.findServiceUri('http://ns.amun-project.org/2011/amun/user/group')) {
            children.push({
                title: 'Groups',
                type: 'http://ns.amun-project.org/2011/amun/user/group'
            });
        }

        if (Amun.xrds.Manager.findServiceUri('http://ns.amun-project.org/2011/amun/user/right')) {
            children.push({
                title: 'Rights',
                type: 'http://ns.amun-project.org/2011/amun/user/right'
            });
        }

        nav.push({
            title: 'User',
            store: Ext.create('Ext.data.Store', {
                model: 'Workbench.model.NavigationEntry',
                data: children
            })
        });

        // system
        children = [];

        if (Amun.xrds.Manager.findServiceUri('http://ns.amun-project.org/2011/amun/content/service')) {
            children.push({
                title: 'Services',
                type: 'http://ns.amun-project.org/2011/amun/content/service'
            });
        }

        if (Amun.xrds.Manager.findServiceUri('http://ns.amun-project.org/2011/amun/system/registry')) {
            children.push({
                title: 'Registry',
                type: 'http://ns.amun-project.org/2011/amun/system/registry'
            });
        }

        if (Amun.xrds.Manager.findServiceUri('http://ns.amun-project.org/2011/amun/system/mail')) {
            children.push({
                title: 'Mail',
                type: 'http://ns.amun-project.org/2011/amun/system/mail'
            });
        }

        if (Amun.xrds.Manager.findServiceUri('http://ns.amun-project.org/2011/amun/system/api')) {
            children.push({
                title: 'API',
                type: 'http://ns.amun-project.org/2011/amun/system/api'
            });
        }

        nav.push({
            title: 'System',
            store: Ext.create('Ext.data.Store', {
                model: 'Workbench.model.NavigationEntry',
                data: children
            })
        });

        // service
        var services = Amun.xrds.Manager.getServices();
        children = [];
        for (var i = 0; i < services.length; i++) {
            if (services[i].hasType('http://ns.amun-project.org/2011/amun/data/1.0') && services[i].hasTypeStartsWith('http://ns.amun-project.org/2011/amun/service')) {
                children.push({
                    title: services[i].getName(),
                    type: services[i].getFirstType()
                });
            }
        }

        nav.push({
            title: 'Service',
            store: Ext.create('Ext.data.Store', {
                model: 'Workbench.model.NavigationEntry',
                data: children
            })
        });

        return nav;
    }

});