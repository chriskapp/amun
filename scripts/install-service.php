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

require_once('../vendor/autoload.php');

$service = isset($_SERVER['argv'][1]) ? $_SERVER['argv'][1] : null;

if(empty($service))
{
	echo <<<USAGE
NAME
	install-service.php - installs a specific service

SYNOPSIS
	install-service.php SERVICE

DESCRIPTION
	This script installs a specific service from the service folder. I.e.
	php install-service.php org.amun-project.asset
USAGE;
}
else
{
	try
	{
		$container = new Amun\Dependency\Container();
		$container->setParameter('config.file', '../configuration.php');
		$container->setParameter('user.id', 1);

		// bootstrap
		Bootstrap::setupEnvironment($container->get('config'));

		$logger = new Monolog\Logger('amun');
		$logger->pushHandler(new Amun\Logger\EchoHandler(Monolog\Logger::INFO));

		$container->set('logger', $logger);

		$handler = new AmunService\Core\Service\Handler($container);
		$record  = $handler->getRecord();
		$record->setSource($service);

		$handler->create($record);

		echo 'Installation successful';
		exit(0);
	}
	catch(\Exception $e)
	{
		echo 'Exception: ' . $e->getMessage() . "\n";
		exit(1);
	}
}
