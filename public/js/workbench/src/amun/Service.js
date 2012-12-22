
Ext.define('Amun.Service', {

    uri: null,
    types: null,

    constructor: function(config){
        config = config || {};
        Ext.apply(this, config);
    },

    hasType: function(type){
        for (var i = 0; i < this.types.length; i++) {
            if (this.types[i] == type) {
                return true;
            }
        }
        return false;
    },

    hasTypeStartsWith: function(type){
        for (var i = 0; i < this.types.length; i++) {
            if (this.types[i].substr(0, type.length) == type) {
                return this.types[i];
            }
        }
        return false;
    },

    getFirstType: function(){
        var type = null;
        for (var i = 0; i < this.types.length; i++) {
            if (this.types[i] != 'http://ns.amun-project.org/2011/amun/data/1.0') {
                type = this.types[i];
            }
        }
        return type;
    },

    getTypes: function(){
        return this.types;
    },

    getUri: function(){
        return this.uri;
    },

    getName: function(){
        var name;
        var pos = this.uri.lastIndexOf('/');

        name = this.uri.substr(pos + 1);
        name = name.charAt(0).toUpperCase() + name.substr(1); // ucfirst

        return name;
    },

    getNamespace: function(){
        var ns = this.getFirstType().substring(37);
        ns = ns.replace(/\//g, '.');

        return ns;
    }

});
