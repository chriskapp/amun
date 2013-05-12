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

namespace Amun\Api\My;

use Amun\Api\ApiTest;
use PSX\Sql\Condition;
use PSX\Url;
use PSX\Http\GetRequest;
use PSX\Json;

/**
 * DetermineLoginHandlerTest
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/gpl.html GPLv3
 * @link    http://amun.phpsx.org
 * @backupStaticAttributes disabled
 */
class DetermineLoginHandlerTest extends ApiTest
{
	protected function setUp()
	{
		parent::setUp();

		if(!$this->hasService('org.amun-project.my'))
		{
			$this->markTestSkipped('Service my not installed');
		}
	}

	public function getEndpoint()
	{
		return $this->config['psx_url'] . '/' . $this->config['psx_dispatch'] . 'api/my/determineLoginHandler';
	}

	public function testGet()
	{
		$result = array(
			// openid
			array(
				'identity' => 'http://google.com',
				'response' => array(
					'handler'      => 'openid',
					'needPassword' => false,
				)
			),
			// system
			array(
				'identity' => 'test@test.com',
				'response' => array(
					'handler'      => 'system',
					'needPassword' => true,
				)
			),
			// google
			array(
				'identity' => 'foo@gmail.com',
				'response' => array(
					'handler'      => 'google',
					'needPassword' => false,
				)
			),
			// yahoo
			array(
				'identity' => 'foo@yahoo.com',
				'response' => array(
					'handler'      => 'yahoo',
					'needPassword' => false,
				)
			)
		);

		foreach($result as $row)
		{
			$url      = new Url($this->getEndpoint() . '?identity=' . urlencode($row['identity']));
			$request  = new GetRequest($url);
			$response = $this->http->request($request);

			$this->assertEquals(200, $response->getCode());

			$resp = Json::decode($response->getBody());
			unset($resp['icon']);

			$this->assertEquals($resp, $row['response']);
		}
	}
}

