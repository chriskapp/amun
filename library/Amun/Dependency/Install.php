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

use DateTimeZone;
use PSX\Config;
use PSX\Loader;
use PSX\Sql;
use Amun\Registry;
use Amun\User;

/**
 * Install
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/gpl.html GPLv3
 * @link    http://phpsx.org
 */
class Install extends Container
{
	public function getConfig()
	{
		$config = new Config($this->getParameter('config.file'));
		$config->set('psx_module_default', 'install');

		return $config;
	}

	public function getLoader()
	{
		return new Loader($this);
	}

	public function getRegistry()
	{
		return new RegistryNoDb($this->get('config'), $this->get('sql'));
	}

	public function getUser()
	{
		return new UserNoDb($this->get('registry'));
	}
}

class RegistryNoDb extends Registry
{
	protected $container = array();
	protected $config;
	protected $sql;

	public function __construct(Config $config, Sql $sql)
	{
		$this->config = $config;
		$this->sql    = $sql;

		$this->exchangeArray(array(

			'table.core_approval'        => $this->config['amun_table_prefix'] . 'core_approval',
			'table.core_approval_record' => $this->config['amun_table_prefix'] . 'core_approval_record',
			'table.core_event'           => $this->config['amun_table_prefix'] . 'core_event',
			'table.core_event_listener'  => $this->config['amun_table_prefix'] . 'core_event_listener',
			'table.core_registry'        => $this->config['amun_table_prefix'] . 'core_registry',
			'table.core_service'         => $this->config['amun_table_prefix'] . 'core_service',
			'core.default_timezone'      => new DateTimeZone('UTC'),

		));
	}

	public function hasService($source)
	{
		return false;
	}
}

class UserNoDb extends User
{
	public $id      = 1;
	public $groupId = 1;
	public $name    = 'System';
	public $status  = User::ADMINISTRATOR;

	public function __construct(Registry $registry)
	{
		$this->registry = $registry;
		$this->config   = $registry->getConfig();
		$this->sql      = $registry->getSql();

		$this->id       = 1;
		$this->groupId  = 1;
		$this->name     = 'System';
	}

	public function hasRight($key)
	{
		return true;
	}

	public function isAdministrator()
	{
		return true;
	}

	public function isAnonymous()
	{
		return false;
	}
}
