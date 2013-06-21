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

/**
 * Security
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/gpl.html GPLv3
 * @link    http://amun.phpsx.org
 */
class Security
{
	protected $config;
	protected $registry;

	public function __construct(Registry $registry)
	{
		$this->config   = $registry->getConfig();
		$this->registry = $registry;
	}

	public function getPwAlphaCount()
	{
		return isset($this->registry['core.pw_alpha']) ? $this->registry['core.pw_alpha'] : 4;
	}

	public function getPwNumericCount()
	{
		return isset($this->registry['core.pw_numeric']) ? $this->registry['core.pw_numeric'] : 2;
	}

	public function getPwSpecialCount()
	{
		return isset($this->registry['core.pw_special']) ? $this->registry['core.pw_special'] : 0;
	}

	public function generatePw($length = 16)
	{
		$pw      = '';
		$chars   = array(' ', '!', '"', '#', '$', '%', '&', '\'', '(', ')', '*', '+', ',', '-', '.', '/', '0', '1', '2', '3', '4', '5', '6', '7', '8', '9', ':', ';', '<', '=', '>', '?', '@', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', '[', '\\', ']', '^', '_', '`', 'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z', '{', '|', '}');
		$alpha   = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z');
		$numeric = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9');
		$special = array(' ', '!', '"', '#', '$', '%', '&', '\'', '(', ')', '*', '+', ',', '-', '.', '/', ':', ';', '<', '=', '>', '?', '@', '[', '\\', ']', '^', '_', '`', '{', '|', '}');


		// add alpha signs
		$count = $this->getPwAlphaCount();

		for($i = 0; $i < $count; $i++)
		{
			$pw.= $alpha[rand(0, count($alpha) - 1)];
		}

		// add numeric signs
		$count = $this->getPwNumericCount();

		for($i = 0; $i < $count; $i++)
		{
			$pw.= $numeric[rand(0, count($numeric) - 1)];
		}

		// add special signs
		$count = $this->getPwSpecialCount();

		for($i = 0; $i < $count; $i++)
		{
			$pw.= $special[rand(0, count($special) - 1)];
		}


		$minLength = $this->getMinPwLength();
		$diff      = $length - $minLength;

		if($diff >= 0)
		{
			// fill up pw to $length with rnd chars
			for($i = 0; $i < $diff; $i++)
			{
				$pw.= $chars[rand(0, count($chars) - 1)];
			}

			$pw = str_shuffle($pw);

			return $pw;
		}
		else
		{
			throw new Exception('Cannot generate pw length is to short to contain all required characters');
		}
	}

	public function getMinPwLength()
	{
		return $this->getPwAlphaCount() + $this->getPwNumericCount() + $this->getPwSpecialCount();
	}

	public function getMaxPwLength()
	{
		return 128;
	}

	public static function generateToken($length = false)
	{
		$token = sha1(uniqid(mt_rand(), true));

		if($length === false)
		{
			return $token;
		}
		else
		{
			if($length >= 40 || $length <= 0)
			{
				return $token;
			}
			else
			{
				return substr($token, 0, $length);
			}
		}
	}
}

