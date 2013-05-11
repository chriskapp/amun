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
	public function setup()
	{
		parent::setup();

		$this->getSql();
		$this->getValidate();
		$this->getGet();
		$this->getPost();
		$this->getSession();
		$this->getDataFactory();
		$this->getTemplate();

		$this->getEvent();
		$this->getRegistry();
		$this->getUser();
	}

	public function getRegistry()
	{
		if($this->has('registry'))
		{
			return $this->get('registry');
		}

		try
		{
			return $this->set('registry', Registry::initInstance($this->getConfig(), $this->getSql()));
		}
		catch(\Exception $e)
		{
			return $this->set('registry', new \RegistryNoDb($this->getConfig(), $this->getSql()));
		}
	}

	public function getSession()
	{
		if($this->has('session'))
		{
			return $this->get('session');
		}

		$session = new \PSX\Session('amun_' . md5($this->config['psx_url']));
		$session->start();

		return $this->set('session', $session);
	}

	public function getDataFactory()
	{
		if($this->has('dataFactory'))
		{
			return $this->get('dataFactory');
		}

		return $this->set('dataFactory', DataFactory::initInstance($this));
	}

	public function getUser()
	{
		if($this->has('user'))
		{
			return $this->get('user');
		}

		try
		{
			$userId = User::getId($this->getSession(), $this->getRegistry());

			return $this->set('user', new User($userId, $this->getRegistry()));
		}
		catch(\Exception $e)
		{
			return $this->set('user', new \UserNoDb($this->getRegistry()));
		}
	}

	public function getTemplate()
	{
		if($this->has('template'))
		{
			return $this->get('template');
		}

		return $this->set('template', new Template($this->config));
	}
}
