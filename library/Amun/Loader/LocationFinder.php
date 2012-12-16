<?php
/*
 *  $Id: Base.php 818 2012-08-25 18:52:34Z k42b3.x@googlemail.com $
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
 * Amun_Loader_NamespaceStrategy
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   Amun
 * @package    Amun_Loader
 * @version    $Revision: 818 $
 */
class Amun_Loader_LocationFinder extends PSX_Loader_LocationFinder_FileSystem
{
	protected $config;
	protected $sql;
	protected $registry;

	public function __construct(Amun_Registry $registry)
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

		switch($parts[0])
		{
			case 'api':
				return $this->resolveApi($parts[1]);
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
					`id`,
					`source`,
					`path`,
					`namespace`
				FROM
					" . $this->registry['table.core_service'] . "
				WHERE
					`path` LIKE SUBSTRING(?, 1, CHAR_LENGTH(`path`))
				LIMIT 1";

		$service = $this->sql->getRow($sql, array('/' . $pathInfo));

		if(!empty($service))
		{
			// load module
			$pathInfo = substr($pathInfo, strlen($service['path']));
			$x        = $service['source'] . '/api/' . $pathInfo;

			$location = $this->getLocation($x);

			if($location !== false)
			{
				list($file, $path, $class) = $location;

				// include class
				require_once($file);

				// create class
				$namespace = $this->getApiNamespace($path, $service['source'], $service['namespace']);

				$class = new ReflectionClass($namespace . '\\' . $class);

				// remove path and class
				$path = substr($path, strlen($service['source']) + 5);
				$rest = $pathInfo;

				if(!empty($path))
				{
					$rest = self::removePathPart($path, $rest);
				}

				$rest = self::removePathPart($class->getShortName(), $rest);

				// return location
				return new Amun_Loader_Location(md5($file), $rest, $class, $service['id']);
			}
		}
		else
		{
			throw new PSX_Exception('Service not found', 404);
		}
	}

	protected function resolveApplication($pathInfo)
	{
		// get page
		$sql = "SELECT
					`page`.`id`,
					`page`.`path`,
					`service`.`source`
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
			$x        = $page['source'] . '/application/' . $pathInfo;

			$location = $this->getLocation($x);

			if($location !== false)
			{
				list($file, $path, $class) = $location;

				// include class
				require_once($file);

				// create class
				$class = new ReflectionClass('\\' . $class);

				// remove path and class
				$rest = $pathInfo;

				if(!empty($path))
				{
					$rest = self::removePathPart($path, $rest);
				}

				$rest = self::removePathPart($class->getShortName(), $rest);

				// return location
				return new Amun_Loader_Location(md5($file), $rest, $class, $page['id']);
			}
		}
		else
		{
			throw new PSX_Exception('Page not found', 404);
		}
	}

	protected function getApiNamespace($path, $source, $namespace)
	{
		// remove package name
		$path = substr($path, strlen($source) + 1);

		// build namespace
		if(empty($path))
		{
			$ns = '\\' . $namespace;
		}
		else
		{
			$ns = str_replace('/', '\\', $path);
			$ns = '\\' . $namespace . '\\' . $ns;
		}

		return $ns;
	}
}


