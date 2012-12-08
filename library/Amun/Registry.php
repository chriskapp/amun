<?php
/*
 *  $Id: Registry.php 801 2012-07-08 21:17:10Z k42b3.x@googlemail.com $
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
 * Amun_Registry
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   Amun
 * @package    Amun_Registry
 * @version    $Revision: 801 $
 */
class Amun_Registry extends ArrayObject
{
	protected static $_instance;

	protected $container = array();
	protected $config;
	protected $sql;

	public function __construct(PSX_Config $config, PSX_Sql $sql)
	{
		parent::__construct($this->container, parent::ARRAY_AS_PROPS);

		$this->config = $config;
		$this->sql    = $sql;

		$this->load();
	}

	public function getConfig()
	{
		return $this->config;
	}

	public function getSql()
	{
		return $this->sql;
	}

	public function load()
	{
		$table  = $this->config['amun_table_prefix'] . $this->config['amun_table_registry'];
		$result = $this->sql->getAll('SELECT `name`, `type`, `class`, `value` FROM `' . $table . '`');

		foreach($result as $row)
		{
			switch($row['type'])
			{
				case 'STRING':
					$value = (string) $row['value'];
					break;

				case 'INTEGER':
					$value = (integer) $row['value'];
					break;

				case 'FLOAT':
					$value = (float) $row['value'];
					break;

				case 'BOOLEAN':
					$value = (boolean) $row['value'];
					break;
			}

			if(!empty($row['class']) && class_exists($row['class'], false))
			{
				try
				{
					$class = $row['class'];
					$value = new $class($value);
				}
				catch(Exception $e)
				{
					$value = null;
				}
			}

			$this->offsetSet($row['name'], $value);
		}
	}

	public function getTableName($offset)
	{
		$offset = strtolower($offset);

		if(parent::offsetExists('table.' . $offset))
		{
			return $offset;
		}
		else
		{
			return false;
		}
	}

	public function getClassNameFromTable($table)
	{
		$it = $this->getIterator();

		while($it->valid())
		{
			if(substr($it->key(), 0, 6) == 'table.' && $it->current() == $table)
			{
				return self::getClassName(substr($it->key(), 6));
			}

			$it->next();
		}

		return false;
	}

	public function clear()
	{
		$this->exchangeArray($this->container = array());
	}

	public function getServices()
	{
		$serviceIds = $this->sql->getCol("SELECT id FROM " . $this->offsetGet('table.core_service'));
		$result     = array();

		foreach($serviceIds as $serviceId)
		{
			$result[] = new Amun_Service($serviceId, $this->registry);
		}

		return $result;
	}

	public function hasService($source)
	{
		$con   = new PSX_Sql_Condition(array('source', '=', $source));
		$count = $this->sql->count($this->offsetGet('table.core_service'), $con);

		return $count > 0;
	}

	public static function get($key)
	{
		return self::getInstance()->offsetGet($key);
	}

	public static function set($key, $value)
	{
		self::getInstance()->offsetSet($key, $value);
	}

	public static function has($key)
	{
		return self::getInstance()->offsetExists($key);
	}

	public static function initInstance(PSX_Config $config, PSX_Sql $sql)
	{
		return self::$_instance = new self($config, $sql);
	}

	public static function getInstance()
	{
		return self::$_instance;
	}

	public static function getClassName($table)
	{
		return implode('_', array_map('ucfirst', explode('_', $table)));
	}
}


