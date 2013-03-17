<?php
/*
 *  $Id: Content.php 635 2012-05-01 19:46:37Z k42b3.x@googlemail.com $
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

namespace Amun\Html;

/**
 * Amun_Html_Content
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   Amun
 * @package    Amun_Html
 * @version    $Revision: 635 $
 */
class Content
{
	const META   = 0x1;
	const HEADER = 0x2;
	const FOOTER = 0x3;

	private $content = array();

	public function __construct()
	{
		$positions = self::getPosition();

		foreach($positions as $k => $v)
		{
			$this->content[$k] = array();
		}
	}

	public function get($position)
	{
		if(count($this->content[$position]) > 0)
		{
			return implode("\n", $this->content[$position]);
		}
		else
		{
			return '';
		}
	}

	public function add($position, $content)
	{
		$this->content[$position][] = $content;
	}

	public static function getPosition($position = false)
	{
		$p = array(

			self::META   => 'Meta',
			self::HEADER => 'Header',
			self::FOOTER => 'Footer',

		);

		if($position !== false)
		{
			$position = intval($position);

			if(array_key_exists($position, $p))
			{
				return $p[$position];
			}
			else
			{
				return false;
			}
		}
		else
		{
			return $p;
		}
	}
}

