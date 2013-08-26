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

namespace Amun\Api\Core;

use Amun\Api\RestTest;
use Amun\DataFactory;
use PSX\Sql\Condition;
use PSX\Http\GetRequest;
use PSX\Json;
use PSX\Url;

/**
 * RegistryTest
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/gpl.html GPLv3
 * @link    http://amun.phpsx.org
 * @backupStaticAttributes disabled
 */
class RegistryTest extends RestTest
{
	protected function setUp()
	{
		parent::setUp();

		if(!$this->hasService('amun/core'))
		{
			$this->markTestSkipped('Service core not installed');
		}
	}

	public function getEndpoint()
	{
		return $this->config['psx_url'] . '/' . $this->config['psx_dispatch'] . 'api/core/registry';
	}

	public function getHandler()
	{
		return getContainer()->get('handlerManager')->getHandler('AmunService\Core\Registry');
	}

	public function testGet()
	{
		$this->assertResultSetResponse($this->get());
	}

	public function testPost()
	{
		$record = $this->getHandler()->getRecord();
		$record->setName('bar');
		$record->setValue('foo');

		$this->assertNegativeResponse($this->post($record));
	}

	public function testPut()
	{
		$record = $this->getHandler()->getRecord();
		$record->setId(24);
		$record->setValue('foobar');

		$this->assertPositiveResponse($this->put($record));

		$actual = $this->table->getRow(array('id', 'value'), new Condition(array('name', '=', 'core.title')));
		$expect = array_map('strval', $record->getData());

		$this->assertEquals($expect, $actual);
	}

	public function testDelete()
	{
		$record = $this->getHandler()->getRecord();
		$record->setId(24);

		$this->assertNegativeResponse($this->delete($record));
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
}

