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

namespace Amun\Filter;

use DateTimeZone;
use InvalidArgumentException;
use PSX\FilterAbstract;

/**
 * DateTime
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/gpl.html GPLv3
 * @link    http://amun.phpsx.org
 */
class DateTime extends FilterAbstract
{
	protected $emptyAllowed;

	public function __construct($emptyAllowed = true)
	{
		$this->emptyAllowed = $emptyAllowed;
	}

	public function apply($value)
	{
		try
		{
			if(empty($value))
			{
				throw new InvalidArgumentException('Empty value');
			}

			$date = new \DateTime($value, new DateTimeZone('UTC'));

			return $date->format('Y-m-d H:i:s');
		}
		catch(\Exception $e)
		{
			return $this->emptyAllowed ? null : false;
		}
	}

	public function getErrorMsg()
	{
		return '%s is not a valid date time';
	}
}
