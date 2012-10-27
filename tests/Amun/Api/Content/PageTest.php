<?php
/*
 *  $Id: PageTest.php 867 2012-09-29 19:22:56Z k42b3.x@googlemail.com $
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
 * Amun_Api_Content_PageTest
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   tests
 * @version    $Revision: 867 $
 * @backupStaticAttributes disabled
 */
class Amun_Api_Content_PageTest extends Amun_Api_RestTest
{
	public function getEndpoint()
	{
		return $this->config['psx_url'] . '/' . $this->config['psx_dispatch'] . 'api/content/page';
	}

	public function getTable()
	{
		return Amun_Sql_Table_Registry::get('Content_Page');
	}

	public function testGet()
	{
		$this->assertResultSetResponse($this->get());
	}

	public function testPost()
	{
		$record = new Amun_Content_Page($this->table);
		$record->setParentId(1);
		$record->setServiceId(3);
		$record->setRightId(0);
		$record->setStatus(Amun_Content_Page::HIDDEN);
		$record->setLoad(15);
		$record->setTitle('bar');
		$record->setTemplate('page.tpl');
		$record->setCache(1);
		$record->setExpire('PT1H');

		$this->assertPositiveResponse($this->post($record));

		$row = $this->getLastInsertedRecord();

		$this->table->delete(new PSX_Sql_Condition(array('id', '=', $row['id'])));

		unset($row['id']);
		unset($row['globalId']);
		unset($row['sort']);
		unset($row['path']);
		unset($row['date']);

		$this->assertEquals($row, $record->getData());
	}

	public function testMinimalPost()
	{
		$record = new Amun_Content_Page($this->table);
		$record->setParentId(1);
		$record->setServiceId(3);
		$record->setStatus(Amun_Content_Page::HIDDEN);
		$record->setTitle('bar');

		$this->assertPositiveResponse($this->post($record));

		$row = $this->getLastInsertedRecord();

		$this->table->delete(new PSX_Sql_Condition(array('id', '=', $row['id'])));

		unset($row['id']);
		unset($row['globalId']);
		unset($row['rightId']);
		unset($row['sort']);
		unset($row['load']);
		unset($row['path']);
		unset($row['template']);
		unset($row['cache']);
		unset($row['expire']);
		unset($row['date']);

		$this->assertEquals($row, $record->getData());
	}

	public function testPut()
	{
		$globalId = PSX_Util_Uuid::nameBased(uniqid());
		$status   = Amun_Content_Service::NORMAL;
		$date     = date(PSX_DateTime::SQL);

		$this->table->insert(array(

			'parentId'  => 0,
			'globalId'  => $globalId,
			'serviceId' => 3,
			'status'    => $status,
			'load'      => 6,
			'sort'      => 0,
			'path'      => 'bar',
			'urlTitle'  => 'bar',
			'title'     => 'bar',
			'template'  => '',
			'cache'     => '',
			'expire'    => '',
			'date'      => $date,

		));

		$id = $this->sql->getLastInsertId();

		$record = new Amun_Content_Page($this->table);
		$record->setId($id);
		$record->setTitle('foo');
		$record->setTemplate('page.tpl');

		$this->assertPositiveResponse($this->put($record));

		$record->parentId  = 0;
		$record->globalId  = $globalId;
		$record->serviceId = 3;
		$record->rightId   = 0;
		$record->status    = $status;
		$record->load      = 6;
		$record->sort      = 0;
		$record->path      = 'foo';
		$record->urlTitle  = 'foo';
		$record->cache     = 0;
		$record->expire    = '';
		$record->date      = $date;

		$row = $this->table->getRow(array_keys($this->table->getColumns()), new PSX_Sql_Condition(array('id', '=', $id)));

		$this->assertEquals($row, $record->getData());

		$this->table->delete(new PSX_Sql_Condition(array('id', '=', $id)));
	}

	public function testDelete()
	{
		$globalId = PSX_Util_Uuid::nameBased(uniqid());
		$status   = Amun_Content_Service::NORMAL;
		$date     = date(PSX_DateTime::SQL);

		$this->table->insert(array(

			'parentId'  => 0,
			'globalId'  => $globalId,
			'serviceId' => 3,
			'status'    => $status,
			'load'      => 6,
			'sort'      => 0,
			'path'      => 'bar',
			'urlTitle'  => 'bar',
			'title'     => 'bar',
			'template'  => '',
			'cache'     => '',
			'expire'    => '',
			'date'      => $date,

		));

		$id = $this->sql->getLastInsertId();

		$record = new Amun_Content_Gadget($this->table);
		$record->setId($id);

		$this->assertPositiveResponse($this->delete($record));

		$row = $this->table->getRow(array_keys($this->table->getColumns()), new PSX_Sql_Condition(array('id', '=', $id)));

		$this->assertEquals(true, empty($row));
	}
}

