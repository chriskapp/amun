<?php
/*
 *  $Id: Bootstrap.php 762 2012-07-01 17:07:10Z k42b3.x@googlemail.com $
 *
 * amun
 * A social content managment system based on the psx framework. For
 * the current version and informations visit <http://amun.phpsx.org>
 *
 * Copyright (c) 2010-2012 Christoph Kappestein <k42b3.x@gmail.com>
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

require_once('library/PSX/Config.php');
require_once('library/PSX/Bootstrap.php');

$config = new PSX_Config('configuration.php');
$config['amun_service_path'] = 'service';
$config['psx_path_cache']    = 'cache';
$config['psx_path_library']  = 'library';
$config['psx_path_module']   = 'module';
$config['psx_path_template'] = 'template';

doBootstrap($config);

function doBootstrap(PSX_Config $config)
{
	$bootstrap = new PSX_Bootstrap($config);
	$bootstrap->addIncludePath('tests');

	$base = Amun_Base::initInstance($config);

	// set user
	$userId = $base->getSql()->getField('SELECT id FROM ' . Amun_Registry::get('table.user_account') . ' WHERE status = ' . Amun_User_Account::ADMINISTRATOR . ' ORDER BY id ASC LIMIT 1');

	$base->setUser($userId);

	// get API credentials
	$consumerKey    = '';
	$consumerSecret = '';
	$token          = '';
	$tokenSecret    = '';
	$hasCredentials = false;

	$api = $base->getSql()->getRow('SELECT id, consumerKey, consumerSecret FROM ' . Amun_Registry::get('table.system_api') . ' ORDER BY id ASC LIMIT 1');

	if(!empty($api))
	{
		$consumerKey    = $api['consumerKey'];
		$consumerSecret = $api['consumerSecret'];

		$req = $base->getSql()->getRow('SELECT token, tokenSecret FROM ' . Amun_Registry::get('table.system_api_request') . ' WHERE apiId = ' . $api['id'] . ' AND status = ' . Amun_System_Api::ACCESS. ' LIMIT 1');

		if(!empty($req))
		{
			$token       = $req['token'];
			$tokenSecret = $req['tokenSecret'];

			$hasCredentials = true;
		}
	}

	define('CONSUMER_KEY', $consumerKey);
	define('CONSUMER_SECRET', $consumerSecret);
	define('TOKEN', $token);
	define('TOKEN_SECRET', $tokenSecret);
	define('HAS_CREDENTIALS', $hasCredentials);
}

