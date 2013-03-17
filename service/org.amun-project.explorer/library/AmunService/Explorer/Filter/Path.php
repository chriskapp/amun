<?php
/*
 *  $Id: Template.php 635 2012-05-01 19:46:37Z k42b3.x@googlemail.com $
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

namespace AmunService\Explorer\Filter;

use Amun\Registry;
use PSX\FilterAbstract;

/**
 * AmunService_Core_Content_Page_Filter_Template
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   Amun
 * @package    Amun_Content_Page
 * @version    $Revision: 635 $
 */
class Path extends FilterAbstract
{
	private $registry;

	public function __construct(Registry $registry)
	{
		$this->registry = $registry;
	}

	public function apply($value)
	{
		if(strpos($value, '..') === false)
		{
			$value = str_replace('\\', '/', $value);
			$value = trim($value, '/');
			$parts = explode('/', $value);
			$path  = '';

			foreach($parts as $part)
			{
				if($this->containsAllowedChars($part))
				{
					$path.= '/' . $part;
				}
			}

			return $this->registry['explorer.path'] . $path;
		}
		else
		{
			return false;
		}
	}

	public function getErrorMsg()
	{
		return 'Invalid path';
	}

	private function containsAllowedChars($name)
	{
		if(empty($name) || $name == '.')
		{
			return false;
		}

		$len = strlen($name);

		for($i = 0; $i < $len; $i++)
		{
			$j = ord($name[$i]);

			if($j < 0x20 || $j == 0x7F)
			{
				return false;
			}
		}

		return true;
	}
}
