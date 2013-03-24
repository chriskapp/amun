<?php
/*
 *  $Id: CountryTest.php 637 2012-05-01 19:58:47Z k42b3.x@googlemail.com $
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

/**
 * Amun_Api_System_CountryTest
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   tests
 * @version    $Revision: 637 $
 * @backupStaticAttributes disabled
 */
class CountryTest extends RestTest
{
	protected function setUp()
	{
		parent::setUp();

		if(!$this->hasService('org.amun-project.country'))
		{
			$this->markTestSkipped('Service country not installed');
		}
	}

	public function getDataSet()
	{
		return $this->createMySQLXMLDataSet('tests/amun.xml');
	}

	public function getEndpoint()
	{
		return $this->config['psx_url'] . '/' . $this->config['psx_dispatch'] . 'api/country';
	}

	public function getTable()
	{
		return DataFactory::getTable('Country');
	}

	public function testGet()
	{
		$this->assertResultSetResponse($this->get());
	}

	public function testPost()
	{
		$record = $this->getTable()->getRecord();
		$record->setTitle('foobar');
		$record->setCode('FB');
		$record->setLongitude(10.4);
		$record->setLatitude(-80.5);

		$this->assertPositiveResponse($this->post($record));

		$actual = $this->table->getRow(array('title', 'code', 'longitude', 'latitude'), new Condition(array('id', '=', 240)));
		$expect = array_map('strval', $record->getData());

		$this->assertEquals($expect, $actual);
	}
}

