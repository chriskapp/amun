<?php
/*
 *  $Id: RestTest.php 792 2012-07-08 02:59:37Z k42b3.x@googlemail.com $
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

use Amun\Data\RecordAbstract;
use PSX\Http;
use PSX\Http\Response;
use PSX\Oauth;
use PSX\Url;
use PSX\Json;
use PSX\Sql;
use InvalidArgumentException;

/**
 * Amun_Api_RestTest
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   tests
 * @version    $Revision: 792 $
 * @backupStaticAttributes disabled
 */
abstract class RestTest extends \PHPUnit_Extensions_Database_TestCase
{
	protected static $con;

	protected $config;
	protected $sql;
	protected $registry;
	protected $http;
	protected $oauth;

	public function getConnection()
	{
		$container = getContainer();
		$config    = $container->getConfig();

		if(self::$con === null)
		{
			try
			{
				self::$con = new Sql($config['psx_sql_host'],
					$config['psx_sql_user'],
					$config['psx_sql_pw'],
					$config['psx_sql_db']);
			}
			catch(PDOException $e)
			{
				$this->markTestSkipped($e->getMessage());
			}
		}

		if($this->sql === null)
		{
			$this->sql = self::$con;

			// create tables
			$queries = $this->getBeforeQueries();

			foreach($queries as $query)
			{
				$this->sql->exec($query);
			}
		}

		return $this->createDefaultDBConnection($this->sql, $config['psx_sql_db']);
	}

	public function getBeforeQueries()
	{
		return array();
	}

	protected function setUp()
	{
		parent::setUp();

		// check whether we have API credentials
		if(HAS_CREDENTIALS)
		{
			$this->config   = getContainer()->getConfig();
			$this->registry = getContainer()->getRegistry();
			$this->http     = new Http();
			$this->oauth    = new Oauth($this->http);
			$this->table    = $this->getTable();
		}
		else
		{
			$this->markTestSkipped('We have no API credentials');
		}
	}

	protected function tearDown()
	{
		parent::setUp();

		unset($this->sql);

		unset($this->config);
		unset($this->registry);
		unset($this->http);
		unset($this->oauth);
		unset($this->table);
	}

	protected function get()
	{
		return $this->sendSignedRequest('GET');
	}

	protected function post(RecordAbstract $record)
	{
		return $this->sendSignedRequest('POST', $record);
	}

	protected function put(RecordAbstract $record)
	{
		return $this->sendSignedRequest('PUT', $record);
	}

	protected function delete(RecordAbstract $record)
	{
		return $this->sendSignedRequest('DELETE', $record);
	}

	protected function sendSignedRequest($type, RecordAbstract $record = null)
	{
		$url    = new Url($this->getEndpoint());
		$body   = $record !== null ? Json::encode($record->getFields()) : null;
		$header = array(

			'Content-Type'  => 'application/json',
			'Accept'        => 'application/json',
			'Authorization' => $this->oauth->getAuthorizationHeader($url, CONSUMER_KEY, CONSUMER_SECRET, TOKEN, TOKEN_SECRET, 'HMAC-SHA1', $type),

		);

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

		//$this->assertEquals($this->table->count(), $result['totalResults']);
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

	protected function getLastInsertedRecord()
	{
		return $this->table->getRow(array_keys($this->table->getColumns()), null, $this->table->getPrimaryKey(), Sql::SORT_DESC);
	}

	protected function hasService($source)
	{
		return $this->registry->hasService($source);
	}

	protected function getServiceId($source)
	{
		$sql       = "SELECT `id` FROM `" . $this->registry['table.core_service'] . "` WHERE `source` = ?";
		$serviceId = $this->sql->getField($sql, array($source));

		return $serviceId;
	}

	/**
	 * Returns the endpoint url from the API
	 *
	 * @return string
	 */
	abstract public function getEndpoint();

	/**
	 * Returns the table on wich the endpoint operates
	 *
	 * @return Amun_Sql_TableAbstract
	 */
	abstract public function getTable();
}

