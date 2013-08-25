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
 * HandlerManager
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/gpl.html GPLv3
 * @link    http://amun.phpsx.org
 */
class HandlerManager
{
	protected $container;

	protected $_cache = array();

	public function __construct($container)
	{
		$this->container = $container;
	}

	public function getHandler($name, User $user = null)
	{
		$name  = str_replace('_', '\\', $name);
		$name  = implode('\\', array_map('ucfirst', explode('\\', $name)));
		$class = $name . '\Handler';

		if(isset($this->_cache[$class]))
		{
			return $this->_cache[$class];
		}

		if(class_exists($class))
		{
			return $this->_cache[$class] = new $class($this->container, $user);
		}
		else
		{
			throw new Exception('Handler "' . $class . '" does not exist');
		}
	}

	public function getTable($name)
	{
		return $this->getHandler($name)->getTable();
	}
}
