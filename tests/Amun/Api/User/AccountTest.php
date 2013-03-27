<?php
/*
 *  $Id: AccountTest.php 637 2012-05-01 19:58:47Z k42b3.x@googlemail.com $
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

namespace Amun\Api\User;

use Amun\Api\RestTest;
use Amun\DataFactory;
use Amun\Security;
use AmunService\User\Account;
use PSX\Sql\Condition;

/**
 * Amun_Api_User_AccountTest
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   tests
 * @version    $Revision: 637 $
 * @backupStaticAttributes disabled
 */
class AccountTest extends RestTest
{
	protected function setUp()
	{
		parent::setUp();

		if(!$this->hasService('org.amun-project.user'))
		{
			$this->markTestSkipped('Service user not installed');
		}
	}

	public function getDataSet()
	{
		return $this->createMySQLXMLDataSet('tests/amun.xml');
	}

	public function getEndpoint()
	{
		return $this->config['psx_url'] . '/' . $this->config['psx_dispatch'] . 'api/user/account';
	}

	public function getTable()
	{
		return DataFactory::getTable('User_Account');
	}

	public function testGet()
	{
		$this->assertResultSetResponse($this->get());
	}

	public function testPost()
	{
		$record = $this->getTable()->getRecord();
		$record->setGroupId(1);
		$record->setStatus(Account\Record::NORMAL);
		$record->identity = 'bar@bar.com';
		$record->setName('bar');
		$record->pw = 'test123';

		$this->assertPositiveResponse($this->post($record));

		$actual = $this->table->getRow(array('groupId', 'status', 'identity', 'name', 'pw'), new Condition(array('id', '=', 2)));
		$record->identity = sha1(Security::getSalt() . $record->identity);
		$record->pw = sha1(Security::getSalt() . $record->pw);
		$expect = array_map('strval', $record->getData());

		$this->assertEquals($expect, $actual);
	}
}

