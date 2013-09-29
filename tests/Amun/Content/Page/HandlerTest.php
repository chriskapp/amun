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

namespace Amun\Content\Page;

use PSX\Sql\Condition;

/**
 * HandlerTest
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/gpl.html GPLv3
 * @link    http://amun.phpsx.org
 * @backupStaticAttributes disabled
 */
class HandlerTest extends \Amun\HandlerTest
{
	public function testDefaultSelect()
	{
		$handler = $this->getHandler('AmunService\Content\Page');
		$actual  = $handler->getOneById(1);
		$expect  = array(
			'id' => '1',
			'date' => '2013-04-12 20:51:10',
			'title' => 'test',
			'parentId' => '0',
			'globalId' => '5aa63a03-b140-59b4-922a-a3e91b5266fe',
			'status' => '1',
			'load' => '3',
			'path' => '',
			'template' => '',
			'serviceId' => '26',
			'serviceType' => 'http://ns.amun-project.org/2011/amun/service/page',
		);

		$this->assertEquals($expect, $actual);
	}

	public function testBuildPath()
	{
		$handler = $this->getHandler('AmunService\Content\Page');

		// create foo under home page
		$record = $handler->getRecord();
		$record->setParentId(2);
		$record->setServiceId(19);
		$record->setStatus(\AmunService\Content\Page\Record::NORMAL);
		$record->setTitle('foo');

		$record = $this->getHandler('AmunService\Content\Page')->create($record);
		$parentId = $record->id;

		// check path and parent id of the page
		$row = $handler->getTable()->getRow(array('id', 'parentId', 'path'), new Condition(array('id', '=', $parentId)));

		$this->assertEquals(2, $row['parentId']);
		$this->assertEquals('home/foo', $row['path']);

		// create two sub pages under foo
		$record = $handler->getRecord();
		$record->setParentId($parentId);
		$record->setServiceId(19);
		$record->setStatus(\AmunService\Content\Page\Record::NORMAL);
		$record->setTitle('bar');

		$record = $this->getHandler('AmunService\Content\Page')->create($record);

		$row = $handler->getTable()->getRow(array('id', 'parentId', 'path'), new Condition(array('id', '=', $record->id)));

		$this->assertEquals($parentId, $row['parentId']);
		$this->assertEquals('home/foo/bar', $row['path']);


		$record = $handler->getRecord();
		$record->setParentId($parentId);
		$record->setServiceId(19);
		$record->setStatus(\AmunService\Content\Page\Record::NORMAL);
		$record->setTitle('foo');

		$record = $this->getHandler('AmunService\Content\Page')->create($record);

		$row = $handler->getTable()->getRow(array('id', 'parentId', 'path'), new Condition(array('id', '=', $record->id)));

		$this->assertEquals($parentId, $row['parentId']);
		$this->assertEquals('home/foo/foo', $row['path']);

	}

	public function testReparentPath()
	{
		$handler = $this->getHandler('AmunService\Content\Page');

		// create two sub pages under home
		$record = $handler->getRecord();
		$record->setParentId(2);
		$record->setServiceId(19);
		$record->setStatus(\AmunService\Content\Page\Record::NORMAL);
		$record->setTitle('bar');

		$page1 = $this->getHandler('AmunService\Content\Page')->create($record);

		$record = $handler->getRecord();
		$record->setParentId(2);
		$record->setServiceId(19);
		$record->setStatus(\AmunService\Content\Page\Record::NORMAL);
		$record->setTitle('foo');

		$page2 = $this->getHandler('AmunService\Content\Page')->create($record);

		// move bar page to another parent
		$record = $handler->getRecord();
		$record->setId($page1->id);
		$record->setParentId(7);

		$this->getHandler('AmunService\Content\Page')->update($record);

		// check path and parent id of the page
		$row = $handler->getTable()->getRow(array('id', 'parentId', 'path'), new Condition(array('id', '=', $page1->id)));

		$this->assertEquals(7, $row['parentId']);
		$this->assertEquals('news/bar', $row['path']);

		$row = $handler->getTable()->getRow(array('id', 'parentId', 'path'), new Condition(array('id', '=', $page2->id)));

		$this->assertEquals(2, $row['parentId']);
		$this->assertEquals('home/foo', $row['path']);

		$row = $handler->getTable()->getRow(array('id', 'parentId', 'path'), new Condition(array('id', '=', 2)));

		$this->assertEquals(1, $row['parentId']);
		$this->assertEquals('home', $row['path']);
	}

	public function testRebuildPath()
	{
		$handler = $this->getHandler('AmunService\Content\Page');

		// create two sub pages under home
		$record = $handler->getRecord();
		$record->setParentId(2);
		$record->setServiceId(19);
		$record->setStatus(\AmunService\Content\Page\Record::NORMAL);
		$record->setTitle('bar');

		$page1 = $this->getHandler('AmunService\Content\Page')->create($record);

		$record = $handler->getRecord();
		$record->setParentId(2);
		$record->setServiceId(19);
		$record->setStatus(\AmunService\Content\Page\Record::NORMAL);
		$record->setTitle('foo');

		$page2 = $this->getHandler('AmunService\Content\Page')->create($record);

		// rename home
		$record = $handler->getRecord();
		$record->setId(2);
		$record->setTitle('test');

		$this->getHandler('AmunService\Content\Page')->update($record);

		// check path and parent id of the page
		$row = $handler->getTable()->getRow(array('id', 'parentId', 'path'), new Condition(array('id', '=', $page1->id)));

		$this->assertEquals(2, $row['parentId']);
		$this->assertEquals('test/bar', $row['path']);

		$row = $handler->getTable()->getRow(array('id', 'parentId', 'path'), new Condition(array('id', '=', $page2->id)));

		$this->assertEquals(2, $row['parentId']);
		$this->assertEquals('test/foo', $row['path']);

		$row = $handler->getTable()->getRow(array('id', 'parentId', 'path'), new Condition(array('id', '=', 2)));

		$this->assertEquals(1, $row['parentId']);
		$this->assertEquals('test', $row['path']);
	}
}

