<?php
/*
 *  $Id: GadgetTest.php 867 2012-09-29 19:22:56Z k42b3.x@googlemail.com $
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
 * Amun_Api_Content_GadgetTest
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   tests
 * @version    $Revision: 867 $
 * @backupStaticAttributes disabled
 */
class Amun_Api_Content_GadgetTest extends Amun_Api_RestTest
{
	public function getEndpoint()
	{
		return $this->config['psx_url'] . '/' . $this->config['psx_dispatch'] . 'api/content/gadget';
	}

	public function getTable()
	{
		return Amun_Sql_Table_Registry::get('Content_Gadget');
	}

	public function testGet()
	{
		$this->assertResultSetResponse($this->get());
	}

	public function testPost()
	{
		$record = new Amun_Content_Gadget($this->table);
		$record->setName('foo');
		$record->setTitle('bar');
		$record->setPath('page/gadget/textBox.php');
		$record->setCache(1);
		$record->setExpire('PT1H');

		$this->assertPositiveResponse($this->post($record));

		$row = $this->getLastInsertedRecord();

		$this->table->delete(new PSX_Sql_Condition(array('id', '=', $row['id'])));

		$record->globalId = $row['globalId'];

		unset($row['id']);
		unset($row['param']);
		unset($row['date']);

		$this->assertEquals($row, $record->getData());
	}

	public function testMinimalPost()
	{
		$record = new Amun_Content_Gadget($this->table);
		$record->setName('bar');
		$record->setTitle('foo');
		$record->setPath('news/gadget/latestNews.php');

		$this->assertPositiveResponse($this->post($record));

		$row = $this->getLastInsertedRecord();

		$this->table->delete(new PSX_Sql_Condition(array('id', '=', $row['id'])));

		$record->globalId = $row['globalId'];
		$record->cache    = $row['cache'];
		$record->expire   = $row['expire'];

		unset($row['id']);
		unset($row['param']);
		unset($row['date']);

		$this->assertEquals($row, $record->getData());
	}

	public function testWrongPathPost()
	{
		$record = new Amun_Content_Gadget($this->table);
		$record->title = 'blub';
		$record->path = 'test.php';

		$this->assertNegativeResponse($this->post($record));
	}

	public function testPut()
	{
		$globalId = PSX_Util_Uuid::pseudoRandom();
		$userId   = 1;
		$date     = date(PSX_DateTime::SQL);

		$this->table->insert(array(

			'globalId' => $globalId,
			'userId'   => $userId,
			'title'    => 'foobar',
			'path'     => 'news/gadget/latestNews.php',
			'date'     => $date,

		));

		$id = $this->sql->getLastInsertId();

		$record = new Amun_Content_Gadget($this->table);
		$record->setId($id);
		$record->setName('foo');
		$record->setTitle('bar');

		$this->assertPositiveResponse($this->put($record));

		$record->globalId = $globalId;
		$record->userId   = $userId;
		$record->path     = 'news/gadget/latestNews.php';
		$record->param    = '';
		$record->cache    = 0;
		$record->expire   = '';
		$record->date     = $date;

		$row = $this->table->getRow(array_keys($this->table->getColumns()), new PSX_Sql_Condition(array('id', '=', $id)));

		$this->assertEquals($row, $record->getData());

		$this->table->delete(new PSX_Sql_Condition(array('id', '=', $id)));
	}

	public function testDelete()
	{
		$globalId = PSX_Util_Uuid::pseudoRandom();
		$userId   = 1;
		$date     = date(PSX_DateTime::SQL);

		$this->table->insert(array(

			'globalId' => $globalId,
			'userId'   => $userId,
			'name'     => 'foo',
			'title'    => 'foobar',
			'path'     => 'news/gadget/latestNews.php',
			'cache'    => '0',
			'expire'   => '',
			'date'     => $date,

		));

		$id = $this->sql->getLastInsertId();

		$record = new Amun_Content_Gadget($this->table);
		$record->setId($id);

		$this->assertPositiveResponse($this->delete($record));

		$row = $this->table->getRow(array_keys($this->table->getColumns()), new PSX_Sql_Condition(array('id', '=', $id)));

		$this->assertEquals(true, empty($row));
	}
}

