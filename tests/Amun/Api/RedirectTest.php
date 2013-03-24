<?php
/*
 *  $Id: RedirectTest.php 743 2012-06-26 19:31:26Z k42b3.x@googlemail.com $
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
 * Amun_Api_Service_RedirectTest
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   tests
 * @version    $Revision: 743 $
 * @backupStaticAttributes disabled
 */
class RedirectTest extends RestTest
{
	protected function setUp()
	{
		parent::setUp();

		if(!$this->hasService('org.amun-project.redirect'))
		{
			$this->markTestSkipped('Service redirect not installed');
		}
	}

	public function getDataSet()
	{
		return $this->createMySQLXMLDataSet('tests/amun.xml');
	}

	public function getEndpoint()
	{
		return $this->config['psx_url'] . '/' . $this->config['psx_dispatch'] . 'api/redirect';
	}

	public function getTable()
	{
		return DataFactory::getTable('Redirect');
	}

	public function testGet()
	{
		$this->assertResultSetResponse($this->get());
	}

	public function testPost()
	{
		$record = $this->getTable()->getRecord();
		$record->setPageId(1);
		$record->setHref('http://google.de');

		$this->assertPositiveResponse($this->post($record));

		$actual = $this->table->getRow(array('pageId', 'href'), new Condition(array('id', '=', 1)));
		$expect = array_map('strval', $record->getData());

		$this->assertEquals($expect, $actual);
	}
}

