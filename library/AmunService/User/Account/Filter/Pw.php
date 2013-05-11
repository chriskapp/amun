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

use Amun\Security;
use PSX\FilterAbstract;

/**
 * Pw
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/gpl.html GPLv3
 * @link    http://amun.phpsx.org
 */
class Pw extends FilterAbstract
{
	public function apply($value)
	{
		$len = strlen($value);

		if($len < Security::getMinPwLength() || $len > Security::getMaxPwLength())
		{
			return false;
		}
		else
		{
			$alpha   = 0;
			$numeric = 0;
			$special = 0;

			for($i = 0; $i < $len; $i++)
			{
				$ascii = ord($value[$i]);

				# alpha (A - Z / a - z)
				if(($ascii >= 0x41 && $ascii <= 0x5A) || ($ascii >= 0x61 && $ascii <= 0x7A))
				{
					$alpha++;
				}
				# numeric (0 - 9)
				elseif($ascii >= 0x30 && $ascii <= 0x39)
				{
					$numeric++;
				}
				# special
				else
				{
					$special++;
				}
			}

			# verify complexity
			if($alpha < Security::getPwAlphaCount())
			{
				return false;
			}

			if($numeric < Security::getPwNumericCount())
			{
				return false;
			}

			if($special < Security::getPwSpecialCount())
			{
				return false;
			}
		}

		return true;
	}

	public function getErrorMsg()
	{
		return '%s must have at least ' . Security::getPwAlphaCount() . ' alpha, ' . Security::getPwNumericCount() . ' numeric and ' . Security::getPwSpecialCount() . ' special signs';
	}
}

