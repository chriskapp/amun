<?php
/*
 * amun
 * A social content managment system based on the psx framework. For
 * the current version and informations visit <http://amun.phpsx.org>
 *
 * Copyright (c) 2010-2013 Christoph Kappestein <k42b3.x@gmail.com>
 *
 * This file is part of psx. psx is free software: you can
 * redistribute it and/or modify it under the terms of the
 * GNU General Public License as published by the Free Software
 * Foundation, either version 3 of the License, or any later version.
 *
 * psx is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with psx. If not, see <http://www.gnu.org/licenses/>.
 */

namespace Amun\Dependency;

use Amun\Base;
use Amun\Registry;
use Amun\Event;
use Amun\User;
use Amun\DataFactory;
use PSX\DependencyAbstract;
use PSX\Sql;
use PSX\Validate;
use PSX\Input;
use PSX\Template;

/**
 * Install
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/gpl.html GPLv3
 * @link    http://phpsx.org
 */
class Install extends Request
{
	public function getRegistry()
	{
		try
		{
			return Registry::initInstance($this->config, $this->get('sql'));
		}
		catch(\Exception $e)
		{
			return new \RegistryNoDb($this->config, $this->get('sql'));
		}
	}

	public function getSession()
	{
		$session = new \PSX\Session('amun_' . md5($this->config['psx_url']));
		$session->start();

		return $session;
	}

	public function getDataFactory()
	{
		return DataFactory::initInstance($this);
	}

	public function getUser()
	{
		try
		{
			$userId = User::findUserId($this->get('session'), $this->get('registry'));

			return new User($userId, $this->get('registry'));
		}
		catch(\Exception $e)
		{
			return new \UserNoDb($this->get('registry'));
		}
	}

	public function getTemplate()
	{
		return new Template($this->config);
	}
}
