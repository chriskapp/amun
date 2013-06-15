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

namespace Amun;

use PSX\DependencyAbstract;

/**
 * DataFactory
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/gpl.html GPLv3
 * @link    http://amun.phpsx.org
 */
class DataFactory
{
	private static $_instance;

	protected $ct;
	protected $prefix;

	protected $_cache = array();

	private function __construct(DependencyAbstract $ct)
	{
		$this->ct     = $ct;
		$this->prefix = $this->ct->get('config')->offsetGet('amun_table_prefix');
	}

	public function getHandlerInstance($table, User $user = null)
	{
		$ns    = $this->getNamespace($table);
		$class = Registry::getClassName('\AmunService\\' . $ns . '\Handler');

		if(isset($this->_cache[$class]))
		{
			return $this->_cache[$class];
		}

		if(class_exists($class))
		{
			return $this->_cache[$class] = new $class($this->ct, $user);
		}
		else
		{
			throw new Exception('Handler "' . $class . '" does not exist');
		}
	}

	public function getFormInstance($table)
	{
		$ns    = $this->getNamespace($table);
		$class = Registry::getClassName('\AmunService\\' . $ns . '\Form');

		if(isset($this->_cache[$class]))
		{
			return $this->_cache[$class];
		}

		if(class_exists($class))
		{
			$config = $this->ct->getConfig();
			$path   = strtolower(str_replace('_', '/', $table));

			$apiEndpoint = $config['psx_url'] . '/' . $config['psx_dispatch'] . 'api/' . $path;

			return $this->_cache[$class] = new $class($this->ct, $apiEndpoint);
		}
		else
		{
			throw new Exception('Form "' . $class . '" does not exist');
		}
	}

	public function getContainer()
	{
		return $this->ct;
	}

	protected function getNamespace($table)
	{
		if(substr($table, 0, strlen($this->prefix)) == $this->prefix)
		{
			$table = substr($table, strlen($this->prefix));
		}

		$table = strtolower($table);
		$table = str_replace('_', '\\', $table);

		return $table;
	}

	public static function getInstance()
	{
		return self::$_instance;
	}

	public static function initInstance(DependencyAbstract $ct)
	{
		return self::$_instance = new self($ct);
	}

	public static function get($table, User $user = null)
	{
		return self::getInstance()->getHandlerInstance($table, $user);
	}

	public static function getTable($table)
	{
		return self::getInstance()->getHandlerInstance($table)->getTable();
	}
}
