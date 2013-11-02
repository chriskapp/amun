/*
 *  $Id: workbench.js 842 2012-09-16 11:46:35Z k42b3.x@googlemail.com $
 *
 * amun
 * A social content managment system based on the psx framework. For
 * the current version and informations visit <http://amun.phpsx.org>
 *
 * Copyright (c) 2010 Christoph Kappestein <k42b3.x@gmail.com>
 *
 * This file is part of amun. amun is free software: you can
 * redistribute it and/or modify it under the terms of the
 * GNU General Public License as published by the Free Software
 * Foundation, either version 3 of the License, or any later version.
 *
 * amun is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with amun. If not, see <http://www.gnu.org/licenses/>.
 */

Ext.Loader.setConfig({
    enabled: true,
    paths: {
        'Amun': base_url + '/js/workbench/src/amun'
    }
});

Ext.require('Amun.form.Form');

Ext.require('Amun.service.content.page.Form');
Ext.require('Amun.service.content.page.Grid');

Ext.require('Amun.service.core.registry.Grid');
Ext.require('Amun.service.core.service.Grid');

Ext.require('Amun.service.user.group.Form');
Ext.require('Amun.service.user.group.Grid');

Ext.require('Amun.service.mail.Form');
Ext.require('Amun.service.mail.Grid');

Ext.require('Amun.service.page.Editor');

Ext.require('Amun.service.phpinfo.Grid');
Ext.require('Amun.service.phpinfo.Store');

Ext.require('Amun.ColumnConfig');
Ext.require('Amun.Editor');
Ext.require('Amun.Grid');

// start application
Ext.application({
    name: 'Workbench',
    appFolder: base_url + '/js/workbench/app',
    controllers: ['Navigation', 'Content'],
    launch: function() {
        var app = Ext.create('Amun.Application');
        app.start();
    }
});
