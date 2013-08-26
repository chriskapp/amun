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

namespace Amun\Api\User;

use Amun\Api\RestTest;
use Amun\DataFactory;
use PSX\Sql\Condition;
use PSX\Http\GetRequest;
use PSX\Json;
use PSX\Url;

/**
 * GroupTest
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/gpl.html GPLv3
 * @link    http://amun.phpsx.org
 * @backupStaticAttributes disabled
 */
class GroupTest extends RestTest
{
	protected function setUp()
	{
		parent::setUp();

		if(!$this->hasService('amun/user'))
		{
			$this->markTestSkipped('Service user not installed');
		}
	}

	public function getEndpoint()
	{
		return $this->config['psx_url'] . '/' . $this->config['psx_dispatch'] . 'api/user/group';
	}

	public function getHandler()
	{
		return getContainer()->get('handlerManager')->getHandler('AmunService\User\Group');
	}

	public function testGet()
	{
		$this->assertResultSetResponse($this->get());
	}

	public function testPost()
	{
		$rightIds = array(1,2,3,4,5,6,7,8);

		$record = $this->getHandler()->getRecord();
		$record->setTitle('foo');
		$record->rights = implode(',', $rightIds);

		$this->assertPositiveResponse($this->post($record));

		$actual = $this->table->getRow(array('title'), new Condition(array('id', '=', 4)));
		unset($record->rights);
		$expect = array_map('strval', $record->getData());

		$table = getContainer()->get('handlerManager')->getTable('User_Group_Right');

		$this->assertEquals($expect, $actual);
		$this->assertEquals($rightIds, $table->getCol('rightId', new Condition(array('groupId', '=', 4))));
	}

	public function testPut()
	{
		$rightIds = array(1,2,3,4);

		$record = $this->getHandler()->getRecord();
		$record->setId(1);
		$record->setTitle('foobar');
		$record->rights = implode(',', $rightIds);

		$this->assertPositiveResponse($this->put($record));

		$actual = $this->table->getRow(array('id', 'title'), new Condition(array('id', '=', 1)));
		unset($record->rights);
		$expect = array_map('strval', $record->getData());

		$table = getContainer()->get('handlerManager')->getTable('User_Group_Right');

		$this->assertEquals($expect, $actual);
		$this->assertEquals($rightIds, $table->getCol('rightId', new Condition(array('groupId', '=', 1))));
	}

	public function testDelete()
	{
		$record = $this->getHandler()->getRecord();
		$record->setId(1);

		$this->assertPositiveResponse($this->delete($record));

		$actual = $this->table->getRow(array('id'), new Condition(array('id', '=', 1)));

		$this->assertEquals(true, empty($actual));
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

