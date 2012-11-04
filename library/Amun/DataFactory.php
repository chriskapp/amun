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
 * Amun_DataFactory
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   Amun
 * @package    Amun_DataFactory
 * @version    $Revision: 635 $
 */
class Amun_DataFactory
{
	private $name;
	private $service;

	public function __construct($name, Amun_Service $service)
	{
		$this->name    = $name;
		$this->service = $service;
	}

	public function getTable()
	{
		return Amun_Sql_Table_Registry::get($this->name);
	}

	public function getHandler()
	{
		$class = self::getClass($this->name, 'Handler');

		if($class !== null && $class->isSubclassOf('Amun_Data_HandlerAbstract'))
		{
			return $class->newInstance($this->getTable());
		}
	}

	public function getForm()
	{
		$class = self::getClass($this->name, 'Form');

		if($class !== null && $class->isSubclassOf('Amun_Data_FormAbstract'))
		{
			return $class->newInstance($this->service->getApiEndpoint());
		}
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
