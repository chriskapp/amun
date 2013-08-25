<?php
/*
 *  $Id: update-service.php 836 2012-08-26 21:54:07Z k42b3.x@googlemail.com $
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

/**
 * This script copies all library files from the amun instance into the service 
 * folder.
 *
 * > php update-service.php comment
 */
try
{
	// config values
	define('AMUN_SERVICE', isset($_SERVER['argv'][1]) ? $_SERVER['argv'][1] : false);
	define('AMUN_PATH', isset($_SERVER['argv'][2]) ? $_SERVER['argv'][2] : '..');
	define('AMUN_SECRET', 'd6b0c93c6f9b0a7917fdb402298ac692bf25fab8');

	// check config
	if(!is_dir(AMUN_PATH))
	{
		throw new InvalidArgumentException('Invalid dir ' . AMUN_PATH);
	}

	$service = AMUN_SERVICE;

	if(empty($service))
	{
		throw new InvalidArgumentException('Please provide a service name as first argument');
	}

	set_error_handler("exceptionErrorHandler");

	// update service
	$path     = AMUN_PATH . '/service';
	$services = scandir($path);

	if($service == '*')
	{
		foreach($services as $service)
		{
			try
			{
				if(is_dir($path . '/' . $service) && $service[0] != '.')
				{
					echo 'Update ' . $service . "\n";

					updateService($service);
				}
			}
			catch(InvalidPathException $e)
			{
				echo 'S ' . $e->getMessage() . "\n";
			}
		}
	}
	else if($service[0] != '.')
	{
		if(in_array($service, $services))
		{
			updateService($service);
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
	exit(1);
}

// update the service
function updateService($service)
{
	$spath  = AMUN_PATH . '/service/' . $service;
	$config = $spath . '/config.xml';

	$dir  = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($spath), RecursiveIteratorIterator::SELF_FIRST);
	$phar = new PharData(AMUN_PATH . '/service/' . $service . '.zip', 0, $service . '.zip', Phar::ZIP);

	foreach($dir as $file)
	{
		if($file->getFilename() != '.' && $file->getFilename() != '..')
		{
			$path = (string) $file;
			$name = substr($path, strlen($spath) + 1);

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
	echo 'Generating service zip ' . $service . ' successful' . "\n";
}

// exception error handler
function exceptionErrorHandler($errno, $errstr, $errfile, $errline)
{
	throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
}

// exceptions
class InvalidPathException extends Exception
{
}
