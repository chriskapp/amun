<?php
/*
 *  $Id: generate-service-phar.php 645 2012-05-01 22:41:02Z k42b3.x@googlemail.com $
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

require_once('configuration.php');

try
{
	$service = isset($_SERVER['argv'][1]) ? $_SERVER['argv'][1] : false;

	if(empty($service))
	{
		throw new InvalidArgumentException('Please provide a service name as first argument');
	}


	$path     = 'amun/service';
	$services = scandir($path);

	if($service == '*')
	{
		foreach($services as $service)
		{
			if(is_dir($path . '/' . $service) && $service[0] != '.')
			{
				echo '--' . $service . "\n";

				generateServicePhar($service);
			}
		}
	}
	else if($service[0] != '.')
	{
		if(in_array($service, $services))
		{
			generateServicePhar($service);
		}
		else
		{
			throw new InvalidArgumentException('Invalid service');
		}
	}
}
catch(Exception $e)
{
	echo $e->getMessage() . "\n";
	echo $e->getTraceAsString();
}


// generates the service config
function generateServicePhar($service)
{
	$spath  = 'amun/service/' . $service;
	$config = $spath . '/config.xml';

	$dir    = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($spath), RecursiveIteratorIterator::SELF_FIRST);
	$phar   = new PharData(AMUN_PATH . '/service/' . $service . '.tar', 0, $service . '.tar');
	$phar->setMetadata(file_get_contents($config));

	foreach($dir as $file)
	{
		$path = (string) $file;
		$name = substr($path, strlen($spath) + 1);

		if($name != 'config.xml')
		{
			if($file->isFile())
			{
				$phar->addFromString($name, file_get_contents($path));
			}
			else if($file->isDir())
			{
				$phar->addEmptyDir($name);
			}

			echo 'A ' . $name . "\n";
		}
	}


	echo "\n";
	echo 'Generating service phar ' . $service . ' successful' . "\n";
}





