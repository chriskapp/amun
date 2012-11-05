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
 * The data provider class offers a general concept of handling data. The data
 * provider knows where and who wants to insert the data. Here an example howto
 * simply create a new record in an application
 * <code>
 * $provider = $this->getDataProvider('News');
 *
 * $record = $provider->getTable()->getRecord();
 * $record->setTitle('foor');
 * $record->setText('<p>bar</p>');
 *
 * $provider->getHandler()->create($record);
 * </code>
 *
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
	protected $config;
	protected $registry;
	protected $user;

	public function __construct($name, Amun_Registry $registry, Amun_User $user)
	{
		$this->name     = $name;
		$this->config   = $registry->getConfig();
		$this->registry = $registry;
		$this->user     = $user;
	}

	public function getName()
	{
		return $this->name;
	}

	public function getTable()
	{
		return Amun_Sql_Table_Registry::get($this->getName());
	}

	public function getHandler()
	{
		$class = self::getClass($this->getName(), 'Handler');

		if($class !== null && $class->isSubclassOf('Amun_Data_HandlerAbstract'))
		{
			return $class->newInstance($this->user);
		}
	}

	public function getForm()
	{
		$class = self::getClass($this->getName(), 'Form');

		if($class !== null && $class->isSubclassOf('Amun_Data_FormAbstract'))
		{
			return $class->newInstance($this->getApiEndpoint());
		}
	}

	public function getApiEndpoint()
	{
		$path = strtolower(str_replace('_', '/', $this->name));

		return $this->config['psx_url'] . '/' . $this->config['psx_dispatch'] . 'api/' . $path;
	}

	public function hasRight($rightName)
	{
		return $this->user->hasRight($this->getRightName($rightName));
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
		try
		{
			$class = Amun_Registry::getClassName('AmunService_' . $namespace . '_' . $className);

			return new ReflectionClass($class);
		}
		catch(ReflectionException $e)
		{
			return null;
		}
	}
}
