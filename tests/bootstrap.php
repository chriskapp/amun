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

$loader = require_once('vendor/autoload.php');
$loader->add('Amun', 'tests');

// defines the user id under wich the tests gets executed
define('USER_ID', 1);


doBootstrap();

function doBootstrap()
{
	$container = getContainer();

	// set container
	Amun\DataFactory::initInstance($container);

	// set logger
	PSX\Log::getLogger()->addHandler(new PSX\Log\Handler\File(PSX_PATH_CACHE . '/log.txt'));
	PSX\Log::getLogger()->setLevel(PSX\Log::INFO);
}

function getContainer()
{
	static $container;

	if($container === null)
	{
		$config = new PSX\Config('configuration.php');
		$config['amun_service_path'] = 'service';
		$config['psx_path_cache']    = 'cache';
		$config['psx_path_library']  = 'library';
		$config['psx_path_module']   = 'module';
		$config['psx_path_template'] = 'template';

		// bootstrap
		$bootstrap = new PSX\Bootstrap($config);
		$bootstrap->addIncludePath('tests');

		$container = new Amun\Dependency\Script($config, array(
			'script.userId' => USER_ID,
		));
	}

	return $container;
}
