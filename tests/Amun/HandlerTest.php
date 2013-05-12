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

use Amun\DataFactory;
use PDOException;
use PSX\Sql;

/**
 * HandlerTest
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/gpl.html GPLv3
 * @link    http://amun.phpsx.org
 * @backupStaticAttributes disabled
 */
abstract class HandlerTest extends \PHPUnit_Extensions_Database_TestCase
{
	protected $config;
	protected $sql;
	protected $registry;
	protected $user;

	public function getConnection()
	{
		return $this->createDefaultDBConnection(getContainer()->getSql(), getContainer()->getConfig()->offsetGet('psx_sql_db'));
	}

	public function getDataSet()
	{
		return $this->createMySQLXMLDataSet('tests/amun.xml');
	}

	protected function setUp()
	{
		parent::setUp();

		$this->config   = getContainer()->getConfig();
		$this->sql      = getContainer()->getSql();
		$this->registry = getContainer()->getRegistry();
		$this->user     = getContainer()->getUser();
	}

	protected function tearDown()
	{
		parent::tearDown();

		unset($this->config);
		unset($this->sql);
		unset($this->registry);
		unset($this->user);
	}

	protected function getHandler($table)
	{
		return DataFactory::getInstance()->getHandlerInstance($table);
	}
}
