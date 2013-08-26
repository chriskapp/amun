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
use PSX\Url;
use PSX\Http\GetRequest;
use SimpleXMLElement;

/**
 * HostmetaTest
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/gpl.html GPLv3
 * @link    http://amun.phpsx.org
 * @backupStaticAttributes disabled
 */
class HostmetaTest extends ApiTest
{
	protected function setUp()
	{
		parent::setUp();

		if(!$this->hasService('amun/hostmeta'))
		{
			$this->markTestSkipped('Service hostmeta not installed');
		}
	}

	public function getEndpoint()
	{
		return $this->config['psx_url'] . '/' . $this->config['psx_dispatch'] . 'api/hostmeta';
	}

	public function testGet()
	{
		$url      = new Url($this->getEndpoint());
		$request  = new GetRequest($url);
		$response = $this->http->request($request);

		$this->assertEquals(200, $response->getCode());

		$xml = simplexml_load_string($response->getBody());

		$this->checkHostmetaXrd($xml);
	}

	public function testWellKnownLocation()
	{
		$url      = new Url($this->config['psx_url'] . '/' . $this->config['psx_dispatch'] . '.well-known/host-meta');
		$request  = new GetRequest($url);
		$response = $this->http->request($request);

		$this->assertEquals(200, $response->getCode());

		$xml = simplexml_load_string($response->getBody());

		$this->checkHostmetaXrd($xml);
	}

	protected function checkHostmetaXrd(SimpleXMLElement $xml)
	{
		// check subject
		$this->assertEquals(true, isset($xml->Subject));
		$this->assertEquals($this->config['psx_url'], (string) $xml->Subject);

		// check lrdd link in hostmeta
		$found = false;
		foreach($xml->Link as $link)
		{
			if($link['rel'] == 'lrdd')
			{
				$this->assertEquals('application/xrd+xml', $link['type']);
				$this->assertEquals($this->config['psx_url'] . '/' . $this->config['psx_dispatch'] . 'api/lrdd?uri={uri}', (string) $link['template']);
				$found = true;
			}
		}
		$this->assertEquals(true, $found);
	}
}

