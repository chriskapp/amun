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

namespace Amun\Api\Oauth;

use Amun\Api\RestTest;
use AmunService\Oauth;
use PSX\Http\GetRequest;
use PSX\Json;
use PSX\Url;
use PSX\Sql\Condition;

/**
 * RequestTest
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/gpl.html GPLv3
 * @link    http://amun.phpsx.org
 * @backupStaticAttributes disabled
 */
class RequestTest extends RestTest
{
	protected function setUp()
	{
		parent::setUp();

		if(!$this->hasService('amun/oauth'))
		{
			$this->markTestSkipped('Service oauth not installed');
		}
	}

	public function getEndpoint()
	{
		return $this->config['psx_url'] . '/' . $this->config['psx_dispatch'] . 'api/oauth/request';
	}

	public function getHandler()
	{
		return getContainer()->get('handlerManager')->getHandler('AmunService\Oauth\Request');
	}

	public function testGet()
	{
		$this->assertResultSetResponse($this->get());
	}

	public function testPost()
	{
		$record = $this->getHandler()->getRecord();
		$record->setApiId(1);
		$record->setStatus(Oauth\Record::ACCESS);
		$record->setIp('127.0.0.1');
		$record->setNonce(uniqid());
		$record->setCallback('oob');
		$record->setToken(sha1(uniqid()));
		$record->setTokenSecret(sha1(uniqid()));
		$record->setVerifier(md5(uniqid()));
		$record->setTimestamp(time());
		$record->setExpire('P1M');

		$this->assertNegativeResponse($this->post($record));
	}

	public function testPut()
	{
		$record = $this->getHandler()->getRecord();
		$record->setId(1);
		$record->setApiId(1);
		$record->setStatus(Oauth\Record::ACCESS);
		$record->setIp('127.0.0.1');
		$record->setNonce(uniqid());
		$record->setCallback('oob');
		$record->setToken(sha1(uniqid()));
		$record->setTokenSecret(sha1(uniqid()));
		$record->setVerifier(md5(uniqid()));
		$record->setTimestamp(time());
		$record->setExpire('P2M');

		$this->assertNegativeResponse($this->put($record));
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
}

