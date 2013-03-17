<?php
/*
 *  $Id: Util.php 635 2012-05-01 19:46:37Z k42b3.x@googlemail.com $
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

namespace Amun;

/**
 * Amun_Util
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   Amun
 * @package    Amun_Util
 * @version    $Revision: 635 $
 */
class Util
{
	/**
	 * Strip html tags and truncate the string to 64 signs if required
	 *
	 * @param string $str
	 * @return string
	 */
	public static function stripAndTruncateHtml($str)
	{
		$str = trim(str_replace(array("\r\n", "\n", "\r"), ' ', strip_tags($str)));

		return strlen($str) > 64 ? substr($str, 0, 61) . '...' : $str;
	}
}

