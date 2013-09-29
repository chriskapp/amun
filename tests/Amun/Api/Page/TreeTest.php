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
use PSX\Url;
use PSX\Http\GetRequest;

/**
 * XrdsTest
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/gpl.html GPLv3
 * @link    http://amun.phpsx.org
 * @backupStaticAttributes disabled
 */
class TreeTest extends ApiTest
{
	protected function setUp()
	{
		parent::setUp();

		if(!$this->hasService('amun/page'))
		{
			$this->markTestSkipped('Service page not installed');
		}
	}

	public function getEndpoint()
	{
		return $this->config['psx_url'] . '/' . $this->config['psx_dispatch'] . 'api/page/tree';
	}

	public function testGet()
	{
		$url      = new Url($this->getEndpoint());
		$request  = new GetRequest($url);
		$response = $this->http->request($request);

		$this->assertEquals(200, $response->getCode());

		$tree = json_decode($response->getBody(), true);

		$this->assertEquals(1, $tree['id']);
		$this->assertEquals('5aa63a03-b140-59b4-922a-a3e91b5266fe', $tree['globalId']);
		$this->assertEquals(0, $tree['sort']);
		$this->assertEquals('', $tree['path']);
		$this->assertEquals('test', $tree['title']);
		$this->assertEquals('test', $tree['urlTitle']);
		$this->assertEquals('http://ns.amun-project.org/2011/amun/service/page', $tree['type']);

		foreach($tree['children'] as $child)
		{
			$this->assertEquals(true, isset($tree['id']));
			$this->assertEquals(true, isset($tree['globalId']));
			$this->assertEquals(true, isset($tree['sort']));
			$this->assertEquals(true, isset($tree['path']));
			$this->assertEquals(true, isset($tree['title']));
			$this->assertEquals(true, isset($tree['urlTitle']));
			$this->assertEquals(true, isset($tree['type']));
		}
	}
}

