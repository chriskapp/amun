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

namespace Amun\Api\Content;

use Amun\Api\RestTest;
use Amun\DataFactory;
use AmunService\Content\Page;
use AmunService\Core\Service;
use PSX\Sql\Condition;
use PSX\Util\Uuid;
use PSX\DateTime;
use PSX\Http\GetRequest;
use PSX\Json;
use PSX\Url;

/**
 * PageTest
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/gpl.html GPLv3
 * @link    http://amun.phpsx.org
 * @backupStaticAttributes disabled
 */
class PageTest extends RestTest
{
	protected function setUp()
	{
		parent::setUp();

		if(!$this->hasService('amun/content'))
		{
			$this->markTestSkipped('Service content not installed');
		}
	}

	public function getEndpoint()
	{
		return $this->config['psx_url'] . '/' . $this->config['psx_dispatch'] . 'api/content/page';
	}

	public function getHandler()
	{
		return getContainer()->get('handlerManager')->getHandler('AmunService\Content\Page');
	}

	public function testGet()
	{
		$this->assertResultSetResponse($this->get());
	}

	public function testPost()
	{
		$record = $this->getHandler()->getRecord();
		$record->setParentId(1);
		$record->setServiceId(3);
		$record->setRightId(0);
		$record->setStatus(Page\Record::HIDDEN);
		$record->setLoad(Page\Record::NAV | Page\Record::PATH | Page\Record::GADGET);
		$record->setTitle('bar');
		$record->setTemplate('page.tpl');
		$record->setCache(1);
		$record->setExpire('PT1H');

		$this->assertPositiveResponse($this->post($record));

		$actual = $this->table->getRow(array('parentId', 'serviceId', 'rightId', 'status', 'load', 'urlTitle', 'title', 'template', 'cache', 'expire'), new Condition(array('id', '=', 12)));
		$expect = array_map('strval', $record->getData());

		$this->assertEquals($expect, $actual);
	}

	public function testMinimalPost()
	{
		$record = $this->getHandler()->getRecord();
		$record->setParentId(1);
		$record->setServiceId(3);
		$record->setStatus(Page\Record::HIDDEN);
		$record->setTitle('bar');

		$this->assertPositiveResponse($this->post($record));

		$actual = $this->table->getRow(array('parentId', 'serviceId', 'status', 'urlTitle', 'title'), new Condition(array('id', '=', 12)));
		$expect = array_map('strval', $record->getData());

		$this->assertEquals($expect, $actual);
	}

	public function testPut()
	{
		$record = $this->getHandler()->getRecord();
		$record->setId(2);
		$record->setTitle('foo');
		$record->setTemplate('page.tpl');

		$this->assertPositiveResponse($this->put($record));

		$actual = $this->table->getRow(array('id', 'urlTitle', 'title', 'template'), new Condition(array('id', '=', 2)));
		$expect = array_map('strval', $record->getData());

		$this->assertEquals($expect, $actual);
	}

	public function testDelete()
	{
		$record = $this->getHandler()->getRecord();
		$record->setId(2);

		$this->assertPositiveResponse($this->delete($record));

		$actual = $this->table->getRow(array('id'), new Condition(array('id', '=', 2)));

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

