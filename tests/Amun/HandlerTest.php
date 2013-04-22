<?php
/*
 *  $Id: UrlTitleTest.php 792 2012-07-08 02:59:37Z k42b3.x@googlemail.com $
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

namespace Amun;

use Amun\DataFactory;
use PDOException;
use PSX\Sql;

/**
 * Amun_Filter_UrlTitleTest
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   tests
 * @version    $Revision: 792 $
 * @backupStaticAttributes disabled
 */
abstract class HandlerTest extends \PHPUnit_Extensions_Database_TestCase
{
	protected static $con;

	protected $config;
	protected $sql;
	protected $registry;
	protected $user;

	public function getConnection()
	{
		$container = getContainer();
		$config    = $container->getConfig();

		if(self::$con === null)
		{
			try
			{
				self::$con = new Sql($config['psx_sql_host'],
					$config['psx_sql_user'],
					$config['psx_sql_pw'],
					$config['psx_sql_db']);
			}
			catch(PDOException $e)
			{
				$this->markTestSkipped($e->getMessage());
			}
		}

		if($this->sql === null)
		{
			$this->sql = self::$con;
		}

		return $this->createDefaultDBConnection($this->sql, $config['psx_sql_db']);
	}

	public function getDataSet()
	{
		return $this->createMySQLXMLDataSet('tests/amun.xml');
	}

	protected function setUp()
	{
		parent::setUp();

		$this->config   = getContainer()->getConfig();
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
