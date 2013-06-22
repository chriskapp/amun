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

namespace AmunService\Login;

use Amun\Registry;
use PSX\DateTime;
use PSX\Sql;
use PSX\Sql\Condition;
use DateInterval;

/**
 * Attempt
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/gpl.html GPLv3
 * @link    http://amun.phpsx.org
 */
class Attempt
{
	const NONE   = 0x0;
	const TRYING = 0x1;
	const ABUSE  = 0x2;

	private $config;
	private $sql;
	private $registry;

	public function __construct(Registry $registry)
	{
		$this->config   = $registry->getConfig();
		$this->sql      = $registry->getSql();
		$this->registry = $registry;
	}

	public function getCount()
	{
		$date = new DateTime('NOW', $this->registry['core.default_timezone']);
		$date->sub(new DateInterval('PT30M'));

		$con = new Condition();
		$con->add('ip', '=', $_SERVER['REMOTE_ADDR']);
		$con->add('date', '>', $date->format(DateTime::SQL));

		$count = $this->sql->select($this->registry['table.login_attempt'], array('count'), $con, Sql::SELECT_FIELD);

		return (integer) $count;
	}

	public function getStage()
	{
		$count = $this->getCount();

		if($count == 0)
		{
			return self::NONE;
		}
		else if($count > 0 && $count <= $this->registry['login.max_wrong'])
		{
			return self::TRYING;
		}
		else
		{
			return self::ABUSE;
		}
	}

	public function clear()
	{
		$con = new Condition();
		$con->add('ip', '=', $_SERVER['REMOTE_ADDR']);

		$this->sql->delete($this->registry['table.login_attempt'], $con);
	}

	public function increase()
	{
		$date = new DateTime('NOW', $this->registry['core.default_timezone']);

		$this->sql->replace($this->registry['table.login_attempt'], array(

			'ip'    => $_SERVER['REMOTE_ADDR'],
			'count' => $this->getCount() + 1,
			'date'  => $date->format(DateTime::SQL),

		));
	}
}

