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

namespace AmunService\User\Account\Filter;

use PSX\FilterAbstract;

/**
 * Identity
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/gpl.html GPLv3
 * @link    http://amun.phpsx.org
 */
class Identity extends FilterAbstract
{
	public function apply($value)
	{
		// check length
		if(strlen($value) < 3 || strlen($value) > 255)
		{
			return false;
		}

		/*
		if(strpos($value, '@') === false && strpos($value, '.') !== false)
		{
			if(substr($value, 0, 7) != 'http://' && substr($value, 0, 8) != 'https://')
			{
				$value = 'http://' . $value;
			}
		}

		$email = filter_var($value, FILTER_VALIDATE_EMAIL) === false ? false : true;
		$url   = filter_var($value, FILTER_VALIDATE_URL)   === false ? false : true;
		*/

		return true;
	}

	public function getErrorMsg()
	{
		return '%s must be an email or url';
	}
}

