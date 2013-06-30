
Ext.define('Workbench.controller.Navigation', {
    extend: 'Ext.app.Controller',

    views: ['Navigation'],
    refs: [{
        selector: 'panel[cls=wb-nav]',
        ref: 'navContainer'
    }],
    models: ['NavigationEntry'],
    controllers: ['Content'],

    init: function() {
        this.control({
            'dataview[cls=wb-navigation]': {
                itemclick: this.onLinkClick
            },
            'panel[cls=wb-nav]': {
                render: this.onPanelRendered
            }
        });
    },

    onLinkClick: function(el, rec, item, index) {
        this.application.fireEvent('navclick', rec.get('type'));
    },

    onPanelRendered: function(){
        this.buildNavigation();
    },

    buildNavigation: function(){
        // content
        this.addNavItems('Content', [{
            title: 'Page',
            type: 'http://ns.amun-project.org/2011/amun/service/content/page'
        },{
            title: 'Gadget',
            type: 'http://ns.amun-project.org/2011/amun/service/content/gadget'
        },{
            title: 'Media',
            type: 'http://ns.amun-project.org/2011/amun/service/media'
        }]);

        // user
        this.addNavItems('User', [{
            title: 'Account',
            type: 'http://ns.amun-project.org/2011/amun/service/user/account'
        },{
            title: 'Group',
            type: 'http://ns.amun-project.org/2011/amun/service/user/group'
        },{
            title: 'Activity',
            type: 'http://ns.amun-project.org/2011/amun/service/user/activity'
        }]);

        // system
        this.addNavItems('System', [{
            title: 'Service',
            type: 'http://ns.amun-project.org/2011/amun/service/core/service'
        },{
            title: 'Registry',
            type: 'http://ns.amun-project.org/2011/amun/service/core/registry'
        },{
            title: 'Mail',
            type: 'http://ns.amun-project.org/2011/amun/service/mail'
        },{
            title: 'Oauth',
            type: 'http://ns.amun-project.org/2011/amun/service/oauth'
        },{
            title: 'Country',
            type: 'http://ns.amun-project.org/2011/amun/service/country'
        },{
            title: 'VCS Hooks',
            type: 'http://ns.amun-project.org/2011/amun/service/vcshook'
        },{
            title: '┗ Authors',
            type: 'http://ns.amun-project.org/2011/amun/service/vcshook/author'
        }]);

        // services
        this.addNavItems('Service', [{
            title: 'Comment',
            type: 'http://ns.amun-project.org/2011/amun/service/comment'
        },{
            title: 'File',
            type: 'http://ns.amun-project.org/2011/amun/service/file'
        },{
            title: 'Forum',
            type: 'http://ns.amun-project.org/2011/amun/service/forum'
        },{
            title: 'News',
            type: 'http://ns.amun-project.org/2011/amun/service/news'
        },{
            title: 'Page',
            type: 'http://ns.amun-project.org/2011/amun/service/page'
        },{
            title: 'Php',
            type: 'http://ns.amun-project.org/2011/amun/service/php'
        },{
            title: 'Pipe',
            type: 'http://ns.amun-project.org/2011/amun/service/pipe'
        },{
            title: 'Redirect',
            type: 'http://ns.amun-project.org/2011/amun/service/redirect'
        }]);
    },

    addNavItems: function(name, children){
        var services = Amun.xrds.Manager.getServices();
        var data = [];
        for (var j = 0; j < children.length; j++) {
            for (var i = 0; i < services.length; i++) {
                if (services[i].hasType(children[j].type)) {
                    data.push({
                        title: children[j].title,
                        type: children[j].type
                    });
                }
            }
        }

        var navigation = Ext.create('Ext.view.View', {
            store: Ext.create('Ext.data.Store', {
                model: 'Workbench.model.NavigationEntry',
                data: data
            }),
            trackOver: true,
            cls: 'wb-navigation',
            itemSelector: 'div.wb-navigation-item',
            overItemCls: 'wb-navigation-item-hover',
            tpl: '<tpl for="."><div class="wb-navigation-item">{title}</div></tpl>'
        });

        var panel = Ext.create('Ext.Panel', {
            title: name,
            iconCls: 'wb-icon-' + name.toLowerCase(),
            items: [navigation]
        });
        this.getNavContainer().add(panel);
    }

});
