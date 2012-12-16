<?php
/*
 *  $Id: UrlTitle.php 792 2012-07-08 02:59:37Z k42b3.x@googlemail.com $
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
 * Amun_Filter_UrlTitle
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   Amun
 * @package    Amun_Filter
 * @version    $Revision: 792 $
 */
class Amun_Filter_UrlTitle extends PSX_FilterAbstract
{
	public function apply($value)
	{
		return $this->encode($value);
	}

	private function encode($value)
	{
		$str   = '';
		$value = htmlspecialchars_decode(strtolower($value), ENT_NOQUOTES);
		$len   = strlen($value);

		$numeric = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9');
		$alpha   = array('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z');
		$mask    = array('-', '_', '.');
		$allowed = array_merge($numeric, $alpha, $mask);

		for($i = 0; $i < $len; $i++)
		{
			if(in_array($value[$i], $allowed))
			{
				$str.= $value[$i];
			}
			else if(ord($value[$i]) == 32)
			{
				$str.= '-';
			}
		}

		return !empty($str) ? $str : false;
	}
}
