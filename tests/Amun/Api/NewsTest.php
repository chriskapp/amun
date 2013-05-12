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

namespace Amun\Api;

use Amun\DataFactory;
use PSX\Sql\Condition;
use PSX\Http\GetRequest;
use PSX\Json;
use PSX\Url;

/**
 * NewsTest
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/gpl.html GPLv3
 * @link    http://amun.phpsx.org
 * @backupStaticAttributes disabled
 */
class NewsTest extends RestTest
{
	protected function setUp()
	{
		parent::setUp();

		if(!$this->hasService('org.amun-project.news'))
		{
			$this->markTestSkipped('Service news not installed');
		}
	}

	public function getEndpoint()
	{
		return $this->config['psx_url'] . '/' . $this->config['psx_dispatch'] . 'api/news';
	}

	public function getHandler()
	{
		return DataFactory::get('News');
	}

	public function testGet()
	{
		$this->assertResultSetResponse($this->get());
	}

	public function testPost()
	{
		$record = $this->getHandler()->getRecord();
		$record->setPageId(1);
		$record->setTitle('foobar');
		$record->setText('<p>bar</p>');

		$this->assertPositiveResponse($this->post($record));

		$actual = $this->table->getRow(array('pageId', 'urlTitle', 'title', 'text'), new Condition(array('id', '=', 2)));
		$expect = array_map('strval', $record->getData());

		$this->assertEquals($expect, $actual);
	}

	public function testPut()
	{
		$record = $this->getHandler()->getRecord();
		$record->setId(1);
		$record->setPageId(1);
		$record->setTitle('bar');
		$record->setText('<p>foo</p>');

		$this->assertPositiveResponse($this->put($record));

		$actual = $this->table->getRow(array('id', 'pageId', 'urlTitle', 'title', 'text'), new Condition(array('id', '=', 1)));
		$expect = array_map('strval', $record->getData());

		$this->assertEquals($expect, $actual);
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

