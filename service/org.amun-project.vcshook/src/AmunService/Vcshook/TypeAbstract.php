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

namespace AmunService\Vcshook;

use Amun\Exception;
use PSX\Data\RecordInterface;
use PSX\DateTime;
use PSX\Http;
use PSX\Sql\Condition;

/**
 * TypeAbstract
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/gpl.html GPLv3
 * @link    http://amun.phpsx.org
 */
abstract class TypeAbstract
{
	protected $http;

	public function __construct(Http $http)
	{
		$this->http = $http;
	}

	/**
	 * Parses the request and returns an project containing all commits in the
	 * request
	 *
	 * @return Project
	 */
	abstract public function getRequest($payload);

	/**
	 * Returns whether a specific project exists
	 *
	 * @return boolean
	 */
	abstract public function hasProject($url);

	public static function factory($type)
	{
		$class = '\AmunService\Vcshook\Type\\' . ucfirst(strtolower($type));

		if(class_exists($class))
		{
			return new $class(new Http());
		}
		else
		{
			throw new Exception('Invalid project type');
		}
	}
}
