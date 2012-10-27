
Ext.define('Workbench.controller.Content', {
    extend: 'Ext.app.Controller',

    views: ['Content'],
    refs: [{
        selector: 'panel[cls=wb-content]',
        ref: 'gridContainer'
    }],

    selectedService: null,

    statics: {
        recordId: null,
        windowCache: {}
    },

    init: function() {
        this.application.on({
            navclick: this.loadGrid,
            scope: this
        });

        this.control({
            'panel[cls=wb-content]': {
                render: this.onPanelRendered
            },
        });
    },

    onPanelRendered: function(){
        this.loadGrid('http://ns.amun-project.org/2011/amun/content/page');
    },

    getSelectedService: function(){
        return this.selectedService;
    },

    getActiveTab: function(){
        return this.getGridContainer().getActiveTab();
    },

    loadGrid: function(uri){
        // discover service
        var service = Amun.xrds.Manager.findService(uri);
        if (service != false) {
            // set selected service
            this.selectedService = service;

            // activate grid if available
            var grid = this.getGridContainer().getComponent(service.getUri());
            if (grid == undefined) {
                // request supported fields
                Ext.Ajax.request({
                    url: service.getUri() + '/@supportedFields?format=json',
                    scope: this,
                    success: function(response, opts){
                        var result = Ext.JSON.decode(response.responseText);
                        fields = result.item;

                        // build grid
                        this.buildGrid(service, fields);
                    },
                    failure: function(response){
                        Ext.Msg.alert('Error', response.responseText);
                    }
                });
            } else {
                this.getGridContainer().setActiveTab(grid);
                grid.reload();
            }
        } else {
            console.log('Unknown service ' + uri);
        }
    },

    buildGrid: function(service, result){
        // check whether we have a custom form class else we build the form 
        // based on the json we received
        var grid;
        var className = 'Amun.' + this.getSelectedService().getNamespace() + '.Grid';
        var extClass = Ext.ClassManager.get(className);

        var config = {
            itemId: service.getUri(),
            title: service.getName(),
            closable: true,
            service: service,
            result: result
        };

        if (extClass != null) {
            grid = Ext.create(className, config);
        } else {
            grid = Ext.create('Amun.Grid', config);
        }

        // add grid
        this.getGridContainer().add(grid);
        this.getGridContainer().setActiveTab(grid);
    }

});
