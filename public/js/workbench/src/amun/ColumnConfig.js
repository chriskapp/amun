
Ext.define('Amun.ColumnConfig', {
    singleton: true,

    getByService: function(service){
        if (service.hasType('http://ns.amun-project.org/2011/amun/service/content/gadget')) {
            return {
                id: 80,
                name: 300,
                title: 300,
                date: 120
            };
        } else if (service.hasType('http://ns.amun-project.org/2011/amun/service/media')) {
            return {
                id: 80,
                name: 300,
                mimeType: 200,
                size: 100,
                date: 120
            };
        // user
        } else if (service.hasType('http://ns.amun-project.org/2011/amun/service/user/account')) {
            return {
                id: 80,
                name: 300,
                email: 200,
                countryTitle: 100,
                date: 120
            };
        } else if (service.hasType('http://ns.amun-project.org/2011/amun/service/user/activity')) {
            return {
                id: 80,
                authorName: 100,
                verb: 100,
                summary: 400,
                date: 120
            };
        // system
        } else if (service.hasType('http://ns.amun-project.org/2011/amun/service/core/service')) {
            return {
                id: 80,
                name: 200,
                source: 200,
                license: 100,
                version: 100,
                date: 120
            };
        } else if (service.hasType('http://ns.amun-project.org/2011/amun/service/mail')) {
            return {
                id: 80,
                name: 200,
                from: 200,
                subject: 320
            };
        } else if (service.hasType('http://ns.amun-project.org/2011/amun/service/oauth')) {
            return {
                id: 80,
                status: 80,
                name: 120,
                email: 120,
                url: 280,
                date: 120
            };
        } else if (service.hasType('http://ns.amun-project.org/2011/amun/service/country')) {
            return {
                id: 80,
                title: 360,
                code: 120,
                longitude: 120,
                latitude: 120
            };
        // service
        } else if (service.hasType('http://ns.amun-project.org/2011/amun/service/comment')) {
            return {
                id: 80,
                authorName: 120,
                pageTitle: 120,
                text: 360,
                date: 120
            };
        } else if (service.hasType('http://ns.amun-project.org/2011/amun/service/file')) {
            return {
                id: 80,
                pageTitle: 120,
                contentType: 120,
                content: 360,
                date: 120
            };
        } else if (service.hasType('http://ns.amun-project.org/2011/amun/service/forum')) {
            return {
                id: 80,
                authorName: 120,
                pageTitle: 120,
                title: 360,
                date: 120
            };
        } else if (service.hasType('http://ns.amun-project.org/2011/amun/service/news')) {
            return {
                id: 80,
                authorName: 120,
                pageTitle: 120,
                title: 360,
                date: 120
            };
        } else if (service.hasType('http://ns.amun-project.org/2011/amun/service/page')) {
            return {
                id: 80,
                authorName: 120,
                pageTitle: 120,
                content: 360,
                date: 120
            };
        } else if (service.hasType('http://ns.amun-project.org/2011/amun/service/php')) {
            return {
                id: 80,
                authorName: 120,
                pageTitle: 120,
                content: 360,
                date: 120
            };
        } else if (service.hasType('http://ns.amun-project.org/2011/amun/service/pipe')) {
            return {
                id: 80,
                authorName: 120,
                pageTitle: 120,
                mediaName: 360,
                date: 120
            };
        } else if (service.hasType('http://ns.amun-project.org/2011/amun/service/redirect')) {
            return {
                id: 80,
                authorName: 120,
                pageTitle: 120,
                href: 360,
                date: 120
            };
        }

        return false;
    }

});

