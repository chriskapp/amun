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

doBootstrap();

function doBootstrap()
{
	$container = getContainer();
	$config    = $container->getConfig();
	$sql       = $container->getSql();
	$registry  = $container->getRegistry();

	// set container
	Amun_DataFactory::initInstance($container);

	// set user
	$userId = $sql->getField('SELECT id FROM ' . $registry['table.user_account'] . ' WHERE status = ' . AmunService_User_Account_Record::ADMINISTRATOR . ' ORDER BY id ASC LIMIT 1');

	// get API credentials
	$consumerKey    = '';
	$consumerSecret = '';
	$token          = '';
	$tokenSecret    = '';
	$hasCredentials = false;

	$api = $sql->getRow('SELECT id, consumerKey, consumerSecret FROM ' . $registry['table.oauth'] . ' ORDER BY id ASC LIMIT 1');

	if(!empty($api))
	{
		$consumerKey    = $api['consumerKey'];
		$consumerSecret = $api['consumerSecret'];

		$req = $sql->getRow('SELECT token, tokenSecret FROM ' . $registry['table.oauth_request'] . ' WHERE apiId = ' . $api['id'] . ' AND status = ' . AmunService_Oauth_Record::ACCESS. ' LIMIT 1');

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

function getContainer()
{
	static $container;

	if($container === null)
	{
		$config = new PSX_Config('configuration.php');
		$config['amun_service_path'] = 'service';
		$config['psx_path_cache']    = 'cache';
		$config['psx_path_library']  = 'library';
		$config['psx_path_module']   = 'module';
		$config['psx_path_template'] = 'template';

		// bootstrap
		$bootstrap = new PSX_Bootstrap($config);
		$bootstrap->addIncludePath('tests');

		$container = new Amun_Dependency_Script($config, array(
			'script.userId' => 1,
		));

		echo 'Execute tests as user: ' . $container->getUser()->name . '' . PHP_EOL;
	}

	return $container;
}
