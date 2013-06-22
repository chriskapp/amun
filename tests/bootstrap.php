<?php
/*
 * amun
 * A social content managment system based on the psx framework. For
 * the current version and informations visit <http://amun.phpsx.org>
 *
 * Copyright (c) 2010-2013 Christoph Kappestein <k42b3.x@gmail.com>
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

$loader = require_once('vendor/autoload.php');
$loader->add('Amun', 'tests');

// defines the user id under wich the tests gets executed
define('USER_ID', 1);

doBootstrap();

function doBootstrap()
{
	$container = getContainer();
	$bootstrap = new PSX\Bootstrap($container->get('config'));

	// check whether http server is available
	$server = false;
	try
	{
		$config   = $container->get('config');
		$http     = $container->get('http');
		$request  = new PSX\Http\GetRequest($config['psx_url'] . '/');
		$response = $http->request($request);
		$body     = $response->getBody();

		if($response->getCode() == 200 && !empty($body) && strpos($body, 'http-equiv="X-XRDS-Location"') !== false)
		{
			echo 'Found webserver and amun instance at ' . $config['psx_url'] . "\n";
			$server = true;
		}
		else
		{
			echo 'Webserver not running or amun instance not available at ' . $config['psx_url'] . "\n";
			echo $body . "\n";
		}
	}
	catch(\Exception $e)
	{
		echo 'Webserver not running: ' . $e->getMessage() . "\n";
	}

	define('HTTP_SERVER', $server);
}

function getContainer()
{
	static $container;

	if($container === null)
	{
		$container = new Amun\Dependency\Container();
		$container->setParameter('config.file', 'configuration.php');
		$container->setParameter('user.id', USER_ID);

		$config = $container->get('config');
		$config['psx_path_cache']    = 'cache';
		$config['psx_path_library']  = 'library';
		$config['psx_path_module']   = 'module';
		$config['psx_path_template'] = 'template';
	}

	return $container;
}

