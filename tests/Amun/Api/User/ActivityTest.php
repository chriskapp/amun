<?php
/*
 *  $Id: ActivityTest.php 637 2012-05-01 19:58:47Z k42b3.x@googlemail.com $
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
use PSX\Sql\Condition;
use PSX\Http\Response;
use PSX\Http\GetRequest;
use PSX\Json;
use PSX\Url;

/**
 * Amun_Api_User_ActivityTest
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   tests
 * @version    $Revision: 637 $
 * @backupStaticAttributes disabled
 */
class ActivityTest extends RestTest
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
		return $this->config['psx_url'] . '/' . $this->config['psx_dispatch'] . 'api/user/activity';
	}

	public function getTable()
	{
		return DataFactory::getTable('User_Activity');
	}

	protected function assertResultSetResponse(Response $response)
	{
		$this->assertEquals(200, $response->getCode(), $response->getBody());

		$result = Json::decode($response->getBody());

		$this->assertEquals(true, isset($result['totalResults']), $response->getBody());
		$this->assertEquals(true, isset($result['startIndex']), $response->getBody());
		$this->assertEquals(true, isset($result['itemsPerPage']), $response->getBody());

		$tblActivity = DataFactory::getTable('User_Activity')->getName();
		$tblAccount  = DataFactory::getTable('User_Account')->getName();
		$count       = $this->sql->getField('SELECT COUNT(*) FROM ' . $tblActivity . ' INNER JOIN ' . $tblAccount . ' ON ' . $tblActivity . '.userId = ' . $tblAccount . '.id');

		$this->assertEquals($count, $result['totalResults']);
	}

	public function testGet()
	{
		$this->assertResultSetResponse($this->get());
	}

	public function testPost()
	{
		$record = $this->getTable()->getRecord();
		$record->setSummary('bar');

		$this->assertPositiveResponse($this->post($record));

		$row = $this->getLastInsertedRecord();

		$this->table->delete(new Condition(array('id', '=', $row['id'])));

		unset($row['id']);
		unset($row['globalId']);
		unset($row['parentId']);
		unset($row['userId']);
		unset($row['refId']);
		unset($row['table']);
		unset($row['scope']);
		unset($row['verb']);
		unset($row['date']);

		$this->assertEquals($row, $record->getData());
	}

	public function testSupportedFields()
	{
		$url      = new Url($this->getEndpoint() . '/@supportedFields');
		$response = $this->signedRequest('GET', $url);

		$this->assertEquals(200, $response->getCode());

		$fields = Json::decode($response->getBody());

		$this->assertEquals(true, is_array($fields));
		$this->assertEquals(true, is_array($fields['item']));
	}

	public function testFormCreate()
	{
		$url      = new Url($this->getEndpoint() . '/form?method=create');
		$response = $this->signedRequest('GET', $url);

		$this->assertEquals(200, $response->getCode());

		$data = Json::decode($response->getBody());

		$this->assertEquals(true, is_array($data));
		$this->assertEquals('form', $data['class']);
		$this->assertEquals('POST', $data['method']);
	}
}

