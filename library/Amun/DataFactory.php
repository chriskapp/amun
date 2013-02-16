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
 * @package    Amun_DataProvider
 * @version    $Revision: 635 $
 */
class Amun_DataFactory
{
	private static $_instance;

	protected $ct;
	protected $_cache = array();

	private function __construct(PSX_DependencyAbstract $ct)
	{
		$this->ct = $ct;
	}

	public function getHandlerInstance($table)
	{
		$class = Amun_Registry::getClassName('AmunService_' . $table . '_Handler');

		if(isset($this->_cache[$class]))
		{
			return $this->_cache[$class];
		}

		if(class_exists($class))
		{
			return $this->_cache[$class] = new $class($this->ct->getUser());
		}
		else
		{
			throw new Amun_Exception('Handler "' . $class . '" does not exist');
		}
	}

	public function getFormInstance($table)
	{
		$class = Amun_Registry::getClassName('AmunService_' . $table . '_Form');

		if(isset($this->_cache[$class]))
		{
			return $this->_cache[$class];
		}

		if(class_exists($class))
		{
			$config = $this->ct->getConfig();
			$path   = strtolower(str_replace('_', '/', $table));

			$apiEndpoint = $config['psx_url'] . '/' . $config['psx_dispatch'] . 'api/' . $path;

			return $this->_cache[$class] = new $class($apiEndpoint);
		}
		else
		{
			throw new Amun_Exception('Form "' . $class . '" does not exist');
		}
	}

	public function getStreamInstance($table)
	{
		$class = Amun_Registry::getClassName('AmunService_' . $table . '_Stream');

		if(isset($this->_cache[$class]))
		{
			return $this->_cache[$class];
		}

		if(class_exists($class))
		{
			return $this->_cache[$class] = new $class($this->getHandlerInstance($table)->getTable());
		}
	}

	public function getContainer()
	{
		return $this->ct;
	}

	public static function getInstance()
	{
		return self::$_instance;
	}

	public static function initInstance(PSX_DependencyAbstract $ct)
	{
		return self::$_instance = new self($ct);
	}
}
