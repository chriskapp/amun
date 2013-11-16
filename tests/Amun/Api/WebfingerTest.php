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

use PSX\Sql\Condition;
use PSX\Json;
use PSX\Url;
use PSX\Http\GetRequest;

/**
 * WebfingerTest
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/gpl.html GPLv3
 * @link    http://amun.phpsx.org
 * @backupStaticAttributes disabled
 */
class WebfingerTest extends ApiTest
{
	protected function setUp()
	{
		parent::setUp();

		if(!$this->hasService('amun/webfinger'))
		{
			$this->markTestSkipped('Service webfinger not installed');
		}
	}

	public function getEndpoint()
	{
		return $this->config['psx_url'] . '/' . $this->config['psx_dispatch'] . 'api/webfinger';
	}

	public function testGetPage()
	{
		$url      = new Url($this->getEndpoint());
		$url->addParam('resource', $this->config['psx_url'] . '/' . $this->config['psx_dispatch'] . 'news');
		$request  = new GetRequest($url);
		$response = $this->http->request($request);

		$this->assertEquals(200, $response->getCode());

		$document = Json::decode($response->getBody());

		$this->assertArrayHasKey('subject', $document);
		$this->assertEquals($this->config['psx_url'] . '/' . $this->config['psx_dispatch'] . 'news', $document['subject']);
	}

	public function testGetAccount()
	{
		$url      = new Url($this->getEndpoint());
		$url->addParam('resource', 'acct:test@test.com');
		$request  = new GetRequest($url);
		$response = $this->http->request($request);

		$this->assertEquals(200, $response->getCode());

		$document = Json::decode($response->getBody());

		$this->assertArrayHasKey('subject', $document);
		$this->assertEquals('test@127.0.0.1', $document['subject']);
	}

	public function testWellKnownLocation()
	{
		$url      = new Url($this->config['psx_url'] . '/' . $this->config['psx_dispatch'] . '.well-known/webfinger');
		$url->addParam('resource', $this->config['psx_url'] . '/' . $this->config['psx_dispatch'] . 'news');
		$request  = new GetRequest($url);
		$response = $this->http->request($request);

		$this->assertEquals(200, $response->getCode());

		$document = Json::decode($response->getBody());

		$this->assertArrayHasKey('subject', $document);
		$this->assertEquals($this->config['psx_url'] . '/' . $this->config['psx_dispatch'] . 'news', $document['subject']);
	}

	public function testResourceNotSet()
	{
		$url      = new Url($this->getEndpoint());
		$request  = new GetRequest($url);
		$response = $this->http->request($request);

		$this->assertEquals(400, $response->getCode());
	}

	public function testResourceNotFound()
	{
		$url      = new Url($this->getEndpoint());
		$url->addParam('resource', 'foobar');
		$request  = new GetRequest($url);
		$response = $this->http->request($request);

		$this->assertEquals(404, $response->getCode());
	}
}

