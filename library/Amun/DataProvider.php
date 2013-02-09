<?php
/*
 *  $Id: Exception.php 635 2012-05-01 19:46:37Z k42b3.x@googlemail.com $
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
 * The name wich must be passed as first argument to the constructor tells the
 * data provider where to look so i.e. if you pass as name "news" the data
 * provider gets the following informations:
 *
 * - The data is in the table [prefix]_news
 * - The api endpoint is /api/news
 * - The view, add, edit and delete rights are news_view, news_add, news_edit
 *   and news_delete
 * - The handler class is AmunService_News_Handler
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   Amun
 * @package    Amun_DataProvider
 * @version    $Revision: 635 $
 */
class Amun_DataProvider
{
	protected $name;
	protected $ct;

	protected $_table;
	protected $_handler;
	protected $_form;
	protected $_stream;

	public function __construct($name, PSX_DependencyAbstract $ct)
	{
		$this->name   = $name;
		$this->ct     = $ct;
	}

	public function getName()
	{
		return $this->name;
	}

	public function getTable()
	{
		if($this->_table !== null)
		{
			return $this->_table;
		}

		$tableName = $this->ct->getRegistry()->getTableName($this->getName());

		if($tableName !== false)
		{
			$class = self::getClass($tableName, 'Table');

			if($class !== null)
			{
				return $this->_table = new $class($this->ct->getRegistry());
			}
			else
			{
				throw new Amun_Exception('Table "' . $tableName . '" does not exist');
			}
		}
		else
		{
			throw new Amun_Exception('Table "' . $this->getName() . '" does not exist');
		}
	}

	public function getHandler()
	{
		if($this->_handler !== null)
		{
			return $this->_handler;
		}

		$class = self::getClass($this->getName(), 'Handler');

		if($class !== null)
		{
			return $this->_handler = new $class($this->ct->getUser());
		}
		else
		{
			throw new Amun_Exception('Handler "' . $this->getName() . '" does not exist');
		}
	}

	public function getForm()
	{
		if($this->_form !== null)
		{
			return $this->_form;
		}

		$class = self::getClass($this->getName(), 'Form');

		if($class !== null)
		{
			return $this->_form = new $class($this->getApiEndpoint());
		}
		else
		{
			throw new Amun_Exception('Form "' . $this->getName() . '" does not exist');
		}
	}

	public function getStream()
	{
		if($this->_stream !== null)
		{
			return $this->_stream;
		}

		$class = self::getClass($this->getName($this->getTable()), 'Stream');

		if($class !== null)
		{
			return $this->_stream = new $class();
		}
	}

	public function getApiEndpoint()
	{
		$config = $this->ct->getConfig();
		$path   = strtolower(str_replace('_', '/', $this->name));

		return $config['psx_url'] . '/' . $config['psx_dispatch'] . 'api/' . $path;
	}

	public function hasRight($rightName)
	{
		return $this->ct->getUser()->hasRight($this->getRightName($rightName));
	}

	public function hasViewRight()
	{
		return $this->hasRight('view');
	}

	public function hasAddRight()
	{
		return $this->hasRight('add');
	}

	public function hasEditRight()
	{
		return $this->hasRight('edit');
	}

	public function hasDeleteRight()
	{
		return $this->hasRight('delete');
	}

	protected function getRightName($rightName)
	{
		return strtolower($this->getName() . '_' . $rightName);
	}

	public static function getClass($namespace, $className)
	{
		$class = Amun_Registry::getClassName('AmunService_' . $namespace . '_' . $className);

		if(class_exists($class))
		{
			return $class;
		}
		else
		{
			return null;
		}
	}
}
