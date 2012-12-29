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
 * This script opens each file in the given directory and tries to import all
 * used classes in the script. This script can be dangerous because it 
 * overwrites all content between the page and class comment. Use this only on
 * files wich have the amun standard format
 *
 * > php organize-imports.php comment
 */
try
{
	// config values
	define('AMUN_SERVICE', isset($_SERVER['argv'][1]) ? $_SERVER['argv'][1] : false);
	define('AMUN_PATH', isset($_SERVER['argv'][2]) ? $_SERVER['argv'][2] : '..');

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
				organize($path . '/' . $service);
			}
		}
	}
	else if($service[0] != '.')
	{
		if(in_array($service, $services))
		{
			organize($path . '/' . $service);
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


function organize($path)
{
	// api
	$api = $path . '/api';

	if(is_dir($api))
	{
		echo 'Organize import: ' . $api . "\n";
		organizeImports($api);
	}

	// gadgets
	$app = $path . '/gadget';

	if(is_dir($app))
	{
		echo 'Organize import: ' . $app . "\n";
		organizeImports($app);
	}

	/*
	$app = $path . '/application';

	if(is_dir($app))
	{
		echo 'Organize import: ' . $app . "\n";
		organizeImports($app);
	}
	*/
}


function organizeImports($path)
{
	$files = scandir($path);

	foreach($files as $file)
	{
		$item = $path . '/' . $file;

		if($file[0] != '.')
		{
			if(is_dir($item))
			{
				organizeImports($item);
			}

			if(is_file($item))
			{
				$info = pathinfo($item);

				if(isset($info['extension']) && $info['extension'] == 'php')
				{
					// check used classes
					$classes = getUsedClasses($item);

					writeUsedClasses($item, $classes);
				}
			}
		}
	}
}


function writeUsedClasses($file, array $classes)
{
	$lines = file($file);

	// find start and endline of block
	$start = 0;
	$end   = 0;

	foreach($lines as $k => $line)
	{
		$line = trim($line);

		if($line == '*/')
		{
			$start = $k;
			break;
		}
	}

	foreach($lines as $k => $line)
	{
		$line = trim($line);

		if($line == '/**')
		{
			$end = $k;
			break;
		}
	}

	if(empty($start) || empty($end))
	{
		throw new Exception('Found no start and end block delimiters ' . $start . '/' . $end);
	}

	if($start > 32)
	{
		throw new Exception('Looks like we found not the right start line ' . $start);
	}

	if($end > 64)
	{
		throw new Exception('Looks like we found not the right end line ' . $end);
	}

	echo 'Found start / end delimiter ' . $start . '/' . $end . "\n";

	// find namespace declaration
	$ns = 0;

	foreach($lines as $k => $line)
	{
		if(substr($line, 0, 9) == 'namespace')
		{
			$ns = $k;
			break;
		}
	}

	if(empty($ns))
	{
		throw new Exception('Found no namespace declaration');
	}

	echo 'Found namespace declaration ' . $ns . "\n";

	// build formatted block
	$formatted = array();
	$formatted[] = '';
	$formatted[] = trim($lines[$ns]); // namespace declaration
	$formatted[] = '';

	foreach($classes as $class)
	{
		$formatted[] = 'use ' . $class . ';';
	}

	// remove existing block
	$result = array();
	$added  = false;

	foreach($lines as $k => $line)
	{
		if($k > $start && $k < $end - 1)
		{
			if($added === false)
			{
				foreach($formatted as $l)
				{
					$result[] = $l . PHP_EOL;
				}

				$added = true;
			}
		}
		else
		{
			$result[] = $line;
		}
	}

	// write file
	file_put_contents($file, implode("", $result));
}


function getUsedClasses($file)
{
	$result  = array();
	$classes = array();

	if(!is_file($file))
	{
		throw new Exception('Is not a file: ' . $file);
	}

	echo 'Organize: ' . $file . "\n";

	// parse content
	$source = file_get_contents($file);
	$tokens = token_get_all($source);
	$count  = count($tokens);

	for($i = 2; $i < $count; $i++)
	{
		// class definition
		if($tokens[$i - 2][0] == T_CLASS && $tokens[$i - 1][0] == T_WHITESPACE && $tokens[$i][0] == T_STRING)
		{
			//$classes[] = $tokens[$i][1];
		}
		// type hinting class in method or catch exceptions
		else if($tokens[$i - 2][0] == T_STRING && $tokens[$i - 1][0] == T_WHITESPACE && $tokens[$i][0] == T_VARIABLE)
		{
			$classes[] = $tokens[$i - 2][1];
		}
		// exceptions or class calls
		else if($tokens[$i - 2][0] == T_NEW && $tokens[$i - 1][0] == T_WHITESPACE && $tokens[$i][0] == T_STRING)
		{
			if(!in_array($tokens[$i][1], array('self')))
			{
				$classes[] = $tokens[$i][1];
			}
		}
		// extends
		else if($tokens[$i - 2][0] == T_EXTENDS && $tokens[$i - 1][0] == T_WHITESPACE && $tokens[$i][0] == T_STRING)
		{
			$classes[] = $tokens[$i][1];
		}
		// implements
		else if($tokens[$i - 2][0] == T_IMPLEMENTS && $tokens[$i - 1][0] == T_WHITESPACE && $tokens[$i][0] == T_STRING)
		{
			$classes[] = $tokens[$i][1];
		}
		// static calls
		else if($tokens[$i - 1][0] == T_STRING && $tokens[$i][0] == T_DOUBLE_COLON)
		{
			if(!in_array($tokens[$i - 1][1], array('self', 'parent')))
			{
				$classes[] = $tokens[$i - 1][1];
			}
		}
		// instance of
		else if($tokens[$i - 2][0] == T_INSTANCEOF && $tokens[$i - 1][0] == T_WHITESPACE && $tokens[$i][0] == T_STRING)
		{
			$classes[] = $tokens[$i][1];
		}
	}

	$classes = array_unique($classes);

	sort($classes);

	echo 'Found ' . count($classes) . ' used classes' . "\n";

	return $classes;
}


// exception error handler
function exceptionErrorHandler($errno, $errstr, $errfile, $errline)
{
	throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
}
