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
			if(is_dir($path . '/' . $service) && $service[0] != '.')
			{
				echo 'Update: ' . $service . "\n";

				updateService($service);
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
	// read config
	$config = new DOMDocument();
	$file   = AMUN_PATH . '/service/' . $service . '/config.xml';

	if(is_file($file))
	{
		$config->load($file);
	}
	else
	{
		throw new InvalidArgumentException('Could not find config ' . $file);
	}

	// library
	$library = $config->getElementsByTagName('library');

	if($library->length > 0)
	{
		copyFiles(AMUN_PATH . '/library', AMUN_PATH . '/service/' . $service . '/library', $library->item(0));
	}
}


// copy files to the service folder
function copyFiles($src, $dest, DomNode $el)
{
	if(!is_dir($src))
	{
		throw new RuntimeException('Invalid source path ' . $src);
	}

	if(!is_dir($dest))
	{
		if(!mkdir($dest, 0755))
		{
			throw new RuntimeException('Could not create folder ' . $dest);
		}

		echo 'A ' . $dest . "\n";
	}

	for($i = 0; $i < $el->childNodes->length; $i++)
	{
		$e = $el->childNodes->item($i);

		if($e instanceof DOMElement)
		{
			if($e->nodeName == 'dir')
			{
				copyFiles($src . '/' . $e->getAttribute('name'), $dest . '/' . $e->getAttribute('name'), $e);
			}

			if($e->nodeName == 'file')
			{
				$srcFile  = $src . '/' . $e->getAttribute('name');
				$destFile = $dest . '/' . $e->getAttribute('name');

				if(!is_file($srcFile))
				{
					throw new RuntimeException('Invalid source file ' . $srcFile);
				}

				if(!is_file($destFile))
				{
					file_put_contents($destFile, file_get_contents($srcFile));

					echo 'A ' . $destFile . "\n";
				}
				else
				{
					// file has been changes
					if(md5_file($srcFile) != md5_file($destFile))
					{
						file_put_contents($destFile, file_get_contents($srcFile));

						echo 'M ' . $destFile . "\n";
					}
				}
			}
		}
	}
}


// exception error handler
function exceptionErrorHandler($errno, $errstr, $errfile, $errline)
{
	throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
}

