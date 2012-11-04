<?php
/*
 *  $Id: Registry.php 802 2012-07-08 21:51:11Z k42b3.x@googlemail.com $
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
 * Amun_Sql_Table_Registry
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   Amun
 * @package    Amun_Sql
 * @version    $Revision: 802 $
 */
class Amun_Sql_Table_Registry extends ArrayObject
{
	protected static $_instance;

	protected $container = array();
	protected $registry;

	public function __construct(Amun_Registry $registry)
	{
		parent::__construct($this->container, parent::ARRAY_AS_PROPS);

		$this->registry = $registry;
	}

	public function offsetGet($offset)
	{
		if(!parent::offsetExists($offset))
		{
			$offset = $this->registry->getTableName($offset);

			if($offset !== false)
			{
				$class = Amun_DataFactory::getClass($offset, 'Table');

				if($class instanceof ReflectionClass)
				{
					parent::offsetSet($offset, $class->newInstance($this->registry));
				}
				else
				{
					throw new Amun_Exception('Table "' . $offset . '" does not exist');
				}
			}
			else
			{
				throw new Amun_Exception('Invalid "' . $offset . '" table');
			}
		}

		return parent::offsetGet($offset);
	}

	public static function initInstance(Amun_Registry $registry)
	{
		return self::$_instance = new self($registry);
	}

	public static function getInstance()
	{
		return self::$_instance;
	}

	public static function get($key)
	{
		return self::getInstance()->offsetGet($key);
	}

	public static function set($key, $value)
	{
		self::getInstance()->offsetSet($key, $value);
	}
}

