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

use PSX\Data\RecordInterface;
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
abstract class RestTest extends ApiTest
{
	protected $table;

	protected function setUp()
	{
		parent::setUp();

		$this->table = $this->getTable();
	}

	protected function tearDown()
	{
		parent::tearDown();

		unset($this->table);
	}

	protected function get()
	{
		return $this->sendSignedRequest('GET');
	}

	protected function post(RecordInterface $record)
	{
		return $this->sendSignedRequest('POST', $record);
	}

	protected function put(RecordInterface $record)
	{
		return $this->sendSignedRequest('PUT', $record);
	}

	protected function delete(RecordInterface $record)
	{
		return $this->sendSignedRequest('DELETE', $record);
	}

	protected function sendSignedRequest($type, RecordInterface $record = null)
	{
		$url    = new Url($this->getEndpoint());
		$body   = $record !== null ? Json::encode($record->getFields()) : null;
		$header = array(
			'Content-Type' => 'application/json',
			'Accept'       => 'application/json',
		);

		return $this->signedRequest($type, $url, $header, $body);
	}

	protected function getLastInsertedRecord()
	{
		return $this->table->getRow(array_keys($this->table->getColumns()), null, $this->table->getPrimaryKey(), Sql::SORT_DESC);
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

	abstract public function testGet();
	abstract public function testPost();
	abstract public function testPut();
	abstract public function testDelete();
}

