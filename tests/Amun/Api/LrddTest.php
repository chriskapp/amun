<?php
/*
 *  $Id: XrdsTest.php 637 2012-05-01 19:58:47Z k42b3.x@googlemail.com $
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

namespace Amun\Api;

use Amun\DataFactory;
use PSX\Sql\Condition;
use PSX\Url;
use PSX\Http\GetRequest;

/**
 * Amun_Api_XrdsTest
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   tests
 * @version    $Revision: 637 $
 * @backupStaticAttributes disabled
 */
class LrddTest extends ApiTest
{
	protected function setUp()
	{
		parent::setUp();

		if(!$this->hasService('org.amun-project.lrdd'))
		{
			$this->markTestSkipped('Service lrdd not installed');
		}
	}

	public function getEndpoint()
	{
		return $this->config['psx_url'] . '/' . $this->config['psx_dispatch'] . 'api/lrdd';
	}

	public function testGetHref()
	{
		$uri      = $this->config['psx_url'] . '/' . $this->config['psx_dispatch'] . 'home';
		$url      = new Url($this->getEndpoint());
		$url->addParam('uri', $uri);
		$request  = new GetRequest($url);
		$response = $this->http->request($request);

		$this->assertEquals(200, $response->getCode());

		$xml = simplexml_load_string($response->getBody());

		// check subject
		$this->assertEquals(true, isset($xml->Subject));
		$this->assertEquals($uri, (string) $xml->Subject);
	}

	public function testGetAcct()
	{
		$url      = new Url($this->getEndpoint());
		$url->addParam('uri', 'acct:test@localhost.com');
		$request  = new GetRequest($url);
		$response = $this->http->request($request);

		$this->assertEquals(200, $response->getCode());

		$xml = simplexml_load_string($response->getBody());

		// check subject
		$host = parse_url($this->config['psx_url'], PHP_URL_HOST);
		$this->assertEquals(true, isset($xml->Subject), $response->getBody());
		$this->assertEquals('test@' . $host, (string) $xml->Subject, $response->getBody());

		// check profile link
		$found = false;
		foreach($xml->Link as $link)
		{
			if($link['rel'] == 'profile')
			{
				$this->assertEquals('text/html', $link['type']);
				//$this->assertEquals($this->config['psx_url'] . '/' . $this->config['psx_dispatch'] . 'profile/test', $link['href']);
				$found = true;
			}
		}
		$this->assertEquals(true, $found);
	}
}

