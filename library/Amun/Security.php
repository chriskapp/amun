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
	/**
	 * Returns the salt from the config or an default salt if the parameter
	 * doesnt exist in the config
	 *
	 * @return string
	 */
	public static function getSalt()
	{
		$config = Registry::getInstance()->getConfig();
		$salt   = isset($config['amun_salt']) ? $config['amun_salt'] : '4ec656bfdee95a3596e31c3d36e49dda';

		return $salt;
	}

	public static function getPwAlphaCount()
	{
		$registry = Registry::getInstance();
		$count    = isset($registry['core.pw_alpha']) ? $registry['core.pw_alpha'] : 4;

		return $count;
	}

	public static function getPwNumericCount()
	{
		$registry = Registry::getInstance();
		$count    = isset($registry['core.pw_numeric']) ? $registry['core.pw_numeric'] : 2;

		return $count;
	}

	public static function getPwSpecialCount()
	{
		$registry = Registry::getInstance();
		$count    = isset($registry['core.pw_special']) ? $registry['core.pw_special'] : 0;

		return $count;
	}

	public static function generatePw($length = 16)
	{
		$pw      = '';
		$chars   = array(' ', '!', '"', '#', '$', '%', '&', '\'', '(', ')', '*', '+', ',', '-', '.', '/', '0', '1', '2', '3', '4', '5', '6', '7', '8', '9', ':', ';', '<', '=', '>', '?', '@', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', '[', '\\', ']', '^', '_', '`', 'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z', '{', '|', '}');
		$alpha   = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z');
		$numeric = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9');
		$special = array(' ', '!', '"', '#', '$', '%', '&', '\'', '(', ')', '*', '+', ',', '-', '.', '/', ':', ';', '<', '=', '>', '?', '@', '[', '\\', ']', '^', '_', '`', '{', '|', '}');


		// add alpha signs
		$count = self::getPwAlphaCount();

		for($i = 0; $i < $count; $i++)
		{
			$pw.= $alpha[rand(0, count($alpha) - 1)];
		}

		// add numeric signs
		$count = self::getPwNumericCount();

		for($i = 0; $i < $count; $i++)
		{
			$pw.= $numeric[rand(0, count($numeric) - 1)];
		}

		// add special signs
		$count = self::getPwSpecialCount();

		for($i = 0; $i < $count; $i++)
		{
			$pw.= $special[rand(0, count($special) - 1)];
		}


		$minLength = self::getMinPwLength();
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

	public static function getMinPwLength()
	{
		return self::getPwAlphaCount() + self::getPwNumericCount() + self::getPwSpecialCount();
	}

	public static function getMaxPwLength()
	{
		return 128;
	}
}

