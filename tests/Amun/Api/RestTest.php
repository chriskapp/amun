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
abstract class Amun_Api_RestTest extends PHPUnit_Framework_TestCase
{
	protected $config;
	protected $http;
	protected $oauth;
	protected $sql;

	protected function setUp()
	{
		// check whether we have API credentials
		if(HAS_CREDENTIALS)
		{
			$this->config = Amun_Base::getInstance()->getConfig();
			$this->http   = new PSX_Http(new PSX_Http_Handler_Curl());
			$this->oauth  = new PSX_Oauth($this->http);
			$this->sql    = Amun_Base::getInstance()->getSql();
			$this->table  = $this->getTable();
		}
		else
		{
			$this->markTestSkipped('We have no API credentials');
		}
	}

	protected function tearDown()
	{
		unset($this->table);
		unset($this->sql);
		unset($this->oauth);
		unset($this->http);
		unset($this->config);
	}

	protected function get()
	{
		return $this->sendSignedRequest('GET');
	}

	protected function post(Amun_Data_RecordAbstract $record)
	{
		return $this->sendSignedRequest('POST', $record);
	}

	protected function put(Amun_Data_RecordAbstract $record)
	{
		return $this->sendSignedRequest('PUT', $record);
	}

	protected function delete(Amun_Data_RecordAbstract $record)
	{
		return $this->sendSignedRequest('DELETE', $record);
	}

	protected function sendSignedRequest($type, Amun_Data_RecordAbstract $record = null)
	{
		$url    = new PSX_Url($this->getEndpoint());
		$body   = $record !== null ? PSX_Json::encode($record->getFields()) : null;
		$header = array(

			'Content-Type'  => 'application/json',
			'Accept'        => 'application/json',
			'Authorization' => $this->oauth->getAuthorizationHeader($url, CONSUMER_KEY, CONSUMER_SECRET, TOKEN, TOKEN_SECRET, 'HMAC-SHA1', $type),

		);

		switch($type)
		{
			case 'GET':
				$request = new PSX_Http_GetRequest($url, $header);
				break;

			case 'POST':
				$request = new PSX_Http_PostRequest($url, $header, $body);
				break;

			case 'PUT':
				$request = new PSX_Http_PutRequest($url, $header, $body);
				break;

			case 'DELETE':
				$request = new PSX_Http_DeleteRequest($url, $header, $body);
				break;

			default:
				throw new InvalidArgumentException('Invalid type only GET, POST, PUT and DELETE is allowed');
		}

		return $this->http->request($request);
	}

	protected function assertResultSetResponse(PSX_Http_Response $response)
	{
		$this->assertEquals(200, $response->getCode(), $response->getBody());

		$result = PSX_Json::decode($response->getBody());

		$this->assertEquals(true, isset($result['totalResults']), $response->getBody());
		$this->assertEquals(true, isset($result['startIndex']), $response->getBody());
		$this->assertEquals(true, isset($result['itemsPerPage']), $response->getBody());

		//$this->assertEquals($this->table->count(), $result['totalResults']);
	}

	protected function assertPositiveResponse(PSX_Http_Response $response)
	{
		$this->assertEquals(200, $response->getCode(), $response->getBody());

		$resp = PSX_Json::decode($response->getBody());

		$this->assertEquals(true, isset($resp['success']), $response->getBody());
		$this->assertEquals(true, isset($resp['text']), $response->getBody());
		$this->assertEquals(true, $resp['success'], $response->getBody());
	}

	protected function assertNegativeResponse(PSX_Http_Response $response)
	{
		$this->assertEquals(200, $response->getCode(), $response->getBody());

		$resp = PSX_Json::decode($response->getBody());

		$this->assertEquals(true, isset($resp['text']), $response->getBody());
		$this->assertEquals(true, isset($resp['success']), $response->getBody());
		$this->assertEquals(false, $resp['success'], $response->getBody());
	}

	protected function getLastInsertedRecord()
	{
		return $this->table->getRow(array_keys($this->table->getColumns()), null, $this->table->getPrimaryKey(), PSX_Sql::SORT_DESC);
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

