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

namespace Amun\Loader;

use Amun\Registry;
use Amun\Exception;
use ReflectionClass;
use PSX\Loader\LocationFinder\FileSystem;

/**
 * LocationFinder
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/gpl.html GPLv3
 * @link    http://amun.phpsx.org
 */
class LocationFinder extends FileSystem
{
	protected $config;
	protected $sql;
	protected $registry;

	public function __construct(Registry $registry)
	{
		$this->config   = $registry->getConfig();
		$this->sql      = $registry->getSql();
		$this->registry = $registry;

		parent::__construct($this->config['amun_service_path']);
	}

	public function resolve($pathInfo)
	{
		$parts = explode('/', trim($pathInfo, '/'), 2);
		$type  = isset($parts[0]) ? $parts[0] : null;
		$path  = isset($parts[1]) ? $parts[1] : null;

		switch($parts[0])
		{
			case 'api':
				return $this->resolveApi($path);
				break;

			case 'gadget':
				return $this->resolveGadget($path);
				break;

			case 'install':
			case 'workbench':
				$this->path = PSX_PATH_MODULE;
				return parent::resolve($pathInfo);
				break;

			default:
				return $this->resolveApplication($pathInfo);
				break;
		}
	}

	protected function resolveApi($pathInfo)
	{
		// get service
		$sql = "SELECT
					`service`.`id`,
					`service`.`path`,
					`service`.`namespace`
				FROM
					" . $this->registry['table.core_service'] . " `service`
				WHERE
					`service`.`path` LIKE SUBSTRING(?, 1, CHAR_LENGTH(`service`.`path`))
				ORDER BY
					CHAR_LENGTH(`service`.`path`) DESC
				LIMIT 1";

		$service = $this->sql->getRow($sql, array('/' . $pathInfo));

		if(!empty($service))
		{
			// load module
			$pathInfo  = substr($pathInfo, strlen($service['path']));
			$path      = $service['namespace'] . '/api/' . $pathInfo;

			list($className, $rest) = $this->getClassByPath($path);

			if(class_exists($className))
			{
				$class = new ReflectionClass($className);

				return new Location(md5($className), $rest, $class, $service['id']);
			}
		}
		else
		{
			throw new Exception('Service not found', 404);
		}
	}

	protected function resolveGadget($pathInfo)
	{
		// get gadget
		$sql = "SELECT
					`gadget`.`id`,
					`gadget`.`class`
				FROM
					" . $this->registry['table.content_gadget'] . " `gadget`
				INNER JOIN
					" . $this->registry['table.core_service'] . " `service`
				ON
					`gadget`.`serviceId` = `service`.`id`
				WHERE
					`gadget`.`name` LIKE ?
				LIMIT 1";

		$gadget = $this->sql->getRow($sql, array($pathInfo));

		if(!empty($gadget))
		{
			if(class_exists($gadget['class']))
			{
				$class = new ReflectionClass($gadget['class']);

				// return location
				return new Location(md5(uniqid()), null, $class, $gadget['id']);
			}
			else
			{
				throw new Exception('Gadget file not found', 500);
			}
		}
		else
		{
			throw new Exception('Gadget not found', 404);
		}
	}

	protected function resolveApplication($pathInfo)
	{
		// get page
		$sql = "SELECT
					`page`.`id`,
					`page`.`path`,
					`service`.`namespace`
				FROM
					" . $this->registry['table.content_page'] . " `page`
				INNER JOIN
					" . $this->registry['table.core_service'] . " `service`
				ON
					`page`.`serviceId` = `service`.`id`
				WHERE
					`page`.`path` LIKE SUBSTRING(?, 1, CHAR_LENGTH(`page`.`path`))
				ORDER BY
					CHAR_LENGTH(`page`.`path`) DESC
				LIMIT 1";

		$page = $this->sql->getRow($sql, array($pathInfo));

		if(!empty($page))
		{
			// load module
			$pathInfo = substr($pathInfo, strlen($page['path']));
			$path     = $page['namespace'] . '/application/' . trim($pathInfo, '/');
			$path     = trim($path, '/');

			list($className, $rest) = $this->getClassByPath($path);

			if(class_exists($className))
			{
				$class = new ReflectionClass($className);

				// return location
				return new Location(md5($className), $rest, $class, $page['id']);
			}
		}
		else
		{
			throw new Exception('Page not found', 404);
		}
	}

	protected function getClassByPath($path)
	{
		$path  = str_replace('\\', '/', $path);
		$parts = array_map('ucfirst', explode('/', $path));
		$len   = count($parts);

		if($len > 16)
		{
			throw new Exception('Request uri to large', 414);
		}

		for($i = $len; $i > 0; $i--)
		{
			$class    = implode('\\', array_slice($parts, 0, $i));
			$rest     = implode('/', array_slice($parts, $i));
			$explicit = $class;
			$default  = $class . '\Index';

			if(class_exists($explicit))
			{
				return array($explicit, $rest);
			}
			else if(class_exists($default))
			{
				return array($default, $rest);
			}
		}
	}
}


