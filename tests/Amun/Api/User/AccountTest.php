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
		$record->identity = 'foo@bar.com';
		$record->setName('foo');
		$record->pw = 'test123';

		$this->assertPositiveResponse($this->post($record));

		$row = $this->getLastInsertedRecord();

		$this->table->delete(new Condition(array('id', '=', $row['id'])));

		unset($record->identity);
		unset($record->pw);

		unset($row['id']);
		unset($row['globalId']);
		unset($row['hostId']);
		unset($row['countryId']);
		unset($row['identity']);
		unset($row['pw']);
		unset($row['email']);
		unset($row['token']);
		unset($row['ip']);
		unset($row['gender']);
		unset($row['profileUrl']);
		unset($row['thumbnailUrl']);
		unset($row['longitude']);
		unset($row['latitude']);
		unset($row['timezone']);
		unset($row['lastSeen']);
		unset($row['updated']);
		unset($row['date']);

		$this->assertEquals($row, $record->getData());
	}
}

