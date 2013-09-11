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

use Amun\DbTest;
use PSX\Http;
use PSX\Http\Response;
use PSX\Oauth;
use PSX\Url;
use PSX\Json;
use PSX\Sql;
use InvalidArgumentException;

/**
 * ApiTest
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/gpl.html GPLv3
 * @link    http://amun.phpsx.org
 * @backupStaticAttributes disabled
 */
abstract class ApiTest extends DbTest
{
	const ONLINE  = 0x1;
	const OFFLINE = 0x2;

	protected $config;
	protected $sql;
	protected $registry;
	protected $user;
	protected $http;
	protected $oauth;
	protected $table;

	protected $consumerKey;
	protected $consumerSecret;
	protected $token;
	protected $tokenSecret;

	protected function setUp()
	{
		// check whether webserver is started
		if(!HTTP_SERVER)
		{
			$this->markTestSkipped('Webserver not available');
		}

		// call parent
		parent::setUp();

		// get api credentials
		$this->config   = getContainer()->get('config');
		$this->sql      = getContainer()->get('sql');
		$this->registry = getContainer()->get('registry');
		$this->user     = getContainer()->get('user');
		$this->http     = new Http();
		$this->oauth    = new Oauth($this->http);

		if(!$this->user->isAnonymous())
		{
			// check whether we have API credentials get API credentials
			$api    = $this->sql->getRow('SELECT id, consumerKey, consumerSecret FROM `amun_oauth` ORDER BY id ASC LIMIT 1');
			$status = \AmunService\Oauth\Record::ACCESS;

			if(!empty($api))
			{
				$this->consumerKey    = $api['consumerKey'];
				$this->consumerSecret = $api['consumerSecret'];

				$sql = <<<SQL
SELECT 
	`token`, 
	`tokenSecret` 
FROM 
	`amun_oauth_request`
WHERE 
	`apiId` = {$api['id']}
AND 
	`userId` = {$this->user->getId()}
AND 
	`status` = {$status} 
LIMIT 1
SQL;

				$request = $this->sql->getRow($sql);

				if(!empty($request))
				{
					$this->token       = $request['token'];
					$this->tokenSecret = $request['tokenSecret'];
				}
				else
				{
					$this->markTestSkipped('No oauth request available');
				}
			}
			else
			{
				$this->markTestSkipped('We have no oauth application');
			}
		}
	}

	protected function tearDown()
	{
		parent::tearDown();

		unset($this->config);
		unset($this->sql);
		unset($this->registry);
		unset($this->user);
		unset($this->http);
		unset($this->oauth);
	}

	protected function signedRequest($type, $url, array $header = array(), $body = null)
	{
		if(!$url instanceof Url)
		{
			$url = new Url($url);
		}

		if(!isset($header['Authorization']) && !$this->user->isAnonymous())
		{
			$header['Authorization'] = $this->oauth->getAuthorizationHeader($url, $this->consumerKey, $this->consumerSecret, $this->token, $this->tokenSecret, 'HMAC-SHA1', $type);
		}

		switch($type)
		{
			case 'GET':
				$request = new Http\GetRequest($url, $header);
				break;

			case 'POST':
				$request = new Http\PostRequest($url, $header, $body);
				break;

			case 'PUT':
				$request = new Http\PutRequest($url, $header, $body);
				break;

			case 'DELETE':
				$request = new Http\DeleteRequest($url, $header, $body);
				break;

			default:
				throw new InvalidArgumentException('Invalid type only GET, POST, PUT and DELETE is allowed');
		}

		return $this->http->request($request);
	}

	protected function assertResultSetResponse(Response $response)
	{
		$this->assertEquals(200, $response->getCode(), $response->getBody());

		$result = Json::decode($response->getBody());

		$this->assertEquals(true, isset($result['totalResults']), $response->getBody());
		$this->assertEquals(true, isset($result['startIndex']), $response->getBody());
		$this->assertEquals(true, isset($result['itemsPerPage']), $response->getBody());
	}

	protected function assertPositiveResponse(Response $response)
	{
		$this->assertEquals(200, $response->getCode(), $response->getBody());

		$resp = Json::decode($response->getBody());

		$this->assertEquals(true, isset($resp['success']), $response->getBody());
		$this->assertEquals(true, isset($resp['text']), $response->getBody());
		$this->assertEquals(true, $resp['success'], $response->getBody());
	}

	protected function assertNegativeResponse(Response $response)
	{
		//$this->assertEquals(200, $response->getCode(), $response->getBody());

		$resp = Json::decode($response->getBody());

		$this->assertEquals(true, isset($resp['text']), $response->getBody());
		$this->assertEquals(true, isset($resp['success']), $response->getBody());
		$this->assertEquals(false, $resp['success'], $response->getBody());
	}

	protected function hasService($name)
	{
		return $this->registry->hasService($name);
	}

	protected function getServiceId($name)
	{
		$sql       = "SELECT `id` FROM `" . $this->registry['table.core_service'] . "` WHERE `name` = ?";
		$serviceId = $this->sql->getField($sql, array($name));

		return $serviceId;
	}
}

