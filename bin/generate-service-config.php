<?php
/*
 *  $Id: generate-service-config.php 840 2012-09-11 22:19:37Z k42b3.x@googlemail.com $
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
 * This script is for generating all hashes in the config.xml file of a service
 * It updates the md5 hashes for each provided file and calculates the hmac
 * signature for the config. You can not install the service if the signature is
 * invalid so do not edit the config.xml after you have generated the signature
 * If you change the config you have to run the script again. The first argument
 * of the script is the service name i.e.:
 *
 * > php generate-service-config.php comment
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

	// scan services
	$path     = AMUN_PATH . '/service';
	$services = scandir($path);

	if($service == '*')
	{
		foreach($services as $service)
		{
			if(is_dir($path . '/' . $service) && $service[0] != '.')
			{
				echo 'Generate config for service ' . $service . "\n";

				generateServiceConfig($service);
			}
		}
	}
	else
	{
		if(in_array($service, $services))
		{
			generateServiceConfig($service);
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


// generates the service config
function generateServiceConfig($service)
{
	// read config
	$config = new DOMDocument();
	$file   = AMUN_PATH . '/service/' . $service . '/config.xml';

	if(is_file($file))
	{
		$config->load($file, LIBXML_NOBLANKS);
	}
	else
	{
		throw new InvalidArgumentException('Could not find config ' . $file);
	}

	// reset signature
	$config->documentElement->setAttribute('signature', '');

	// library
	$library = $config->getElementsByTagName('library');

	if($library->length > 0)
	{
		generateHashes(AMUN_PATH . '/service/' . $service . '/library', $library->item(0));
	}

	// build signature
	$config->preserveWhiteSpace = false;
	$config->formatOutput = true;

	$signature = hash_hmac('sha1', $config->saveXML(), AMUN_SECRET);

	$config->documentElement->setAttribute('signature', $signature);

	// write to file
	$config->save($file);
}


// generate md5 file hashes
function generateHashes($path, DOMNode $el)
{
	if(!is_dir($path))
	{
		throw new RuntimeException('Invalid path ' . $path);
	}

	// check for include tag
	$include = false;

	for($i = 0; $i < $el->childNodes->length; $i++)
	{
		$e = $el->childNodes->item($i);

		if($e instanceof DOMElement && $e->nodeName == 'include')
		{
			$el->removeChild($e);

			$include = true;
		}
	}

	// generate md5 hash for files or include complete file structure
	if(!$include)
	{
		for($i = 0; $i < $el->childNodes->length; $i++)
		{
			$e = $el->childNodes->item($i);

			if($e instanceof DOMElement)
			{
				if($e->nodeName == 'dir')
				{
					generateHashes($path . '/' . $e->getAttribute('name'), $e);
				}

				if($e->nodeName == 'file')
				{
					$file = $path . '/' . $e->getAttribute('name');

					if(!is_file($file))
					{
						throw new RuntimeException('Invalid file ' . $file);
					}

					$e->setAttribute('md5', md5_file($file));
				}
			}
		}
	}
	else
	{
		includeFiles($path, $el);
	}
}


// append all files to the node
function includeFiles($path, DOMNode $el)
{
	$files = scandir($path);

	foreach($files as $f)
	{
		if($f[0] != '.')
		{
			$file = $path . '/' . $f;

			if(is_dir($file))
			{
				$e = $el->ownerDocument->createElement('dir');
				$e->setAttribute('name', $f);

				includeFiles($file, $el->appendChild($e));
			}

			if(is_file($file))
			{
				$e = $el->ownerDocument->createElement('file');
				$e->setAttribute('name', $f);
				$e->setAttribute('md5', md5_file($file));

				$el->appendChild($e);
			}
		}
	}
}


// exception error handler
function exceptionErrorHandler($errno, $errstr, $errfile, $errline)
{
	throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
}

