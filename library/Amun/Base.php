<?php
/*
 *  $Id: Base.php 818 2012-08-25 18:52:34Z k42b3.x@googlemail.com $
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
 * Amun_Base
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   Amun
 * @package    Amun_Base
 * @version    $Revision: 818 $
 */
class Amun_Base extends PSX_Base
{
	const VERSION = '0.4 beta';

	protected $sql;
	protected $registry;
	protected $tableRegistry;
	protected $user;

	public function setup()
	{
		parent::setup();

		// sql
		$this->sql = new PSX_Sql($this->config['psx_sql_host'],
			$this->config['psx_sql_user'],
			$this->config['psx_sql_pw'],
			$this->config['psx_sql_db']);

		// init registry
		$this->registry = Amun_Registry::initInstance($this->config, $this->sql);

		// init table registry
		$this->tableRegistry = Amun_Sql_Table_Registry::initInstance($this->registry);

		// add routes
		$this->loader->addRoute('/.well-known/host-meta', 'api/meta/host');
	}

	public function setUser($userId)
	{
		return $this->user = new Amun_User($userId, $this->registry);
	}

	public function getSql()
	{
		return $this->sql;
	}

	public function getRegistry()
	{
		return $this->registry;
	}

	public function getTableRegistry()
	{
		return $this->tableRegistry;
	}

	public function getUser()
	{
		return $this->user;
	}

	public function getServices()
	{
		$serviceIds = $this->sql->getCol("SELECT id FROM " . $this->registry['table.core_content_service']);
		$result     = array();

		foreach($serviceIds as $serviceId)
		{
			$result[] = new Amun_Service($serviceId, $this->registry);
		}

		return $result;
	}

	public function hasService($source)
	{
		$con   = new PSX_Sql_Condition(array('source', '=', $source));
		$count = $this->sql->count($this->registry['table.core_content_service'], $con);

		return $count > 0;
	}

	public static function getVersion()
	{
		return self::VERSION;
	}
}

