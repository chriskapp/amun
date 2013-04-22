<?php
/*
 *  $Id: UrlTitleTest.php 792 2012-07-08 02:59:37Z k42b3.x@googlemail.com $
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

namespace Amun\Content\Page;

use PSX\Sql\Condition;

/**
 * Amun_Filter_UrlTitleTest
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   tests
 * @version    $Revision: 792 $
 * @backupStaticAttributes disabled
 */
class HandlerTest extends \Amun\HandlerTest
{
	public function testBuildPath()
	{
		$handler = $this->getHandler('Content_Page');

		// create foo under home page
		$record = $handler->getTable()->getRecord();
		$record->setParentId(2);
		$record->setServiceId(19);
		$record->setStatus(\AmunService\Content\Page\Record::NORMAL);
		$record->setTitle('foo');

		$this->getHandler('Content_Page')->create($record);

		// check path and parent id of the page
		$row = $handler->getTable()->getRow(array('id', 'parentId', 'path'), new Condition(array('id', '=', 11)));

		$this->assertEquals(2, $row['parentId']);
		$this->assertEquals('home/foo', $row['path']);

		// create two sub pages under foo
		$record = $handler->getTable()->getRecord();
		$record->setParentId(11);
		$record->setServiceId(19);
		$record->setStatus(\AmunService\Content\Page\Record::NORMAL);
		$record->setTitle('bar');

		$this->getHandler('Content_Page')->create($record);

		$record = $handler->getTable()->getRecord();
		$record->setParentId(11);
		$record->setServiceId(19);
		$record->setStatus(\AmunService\Content\Page\Record::NORMAL);
		$record->setTitle('foo');

		$this->getHandler('Content_Page')->create($record);

		// check pages
		$row = $handler->getTable()->getRow(array('id', 'parentId', 'path'), new Condition(array('id', '=', 12)));

		$this->assertEquals(11, $row['parentId']);
		$this->assertEquals('home/foo/bar', $row['path']);

		$row = $handler->getTable()->getRow(array('id', 'parentId', 'path'), new Condition(array('id', '=', 13)));

		$this->assertEquals(11, $row['parentId']);
		$this->assertEquals('home/foo/foo', $row['path']);
	}

	public function testReparentPath()
	{
		$handler = $this->getHandler('Content_Page');

		// create two sub pages under home
		$record = $handler->getTable()->getRecord();
		$record->setParentId(2);
		$record->setServiceId(19);
		$record->setStatus(\AmunService\Content\Page\Record::NORMAL);
		$record->setTitle('bar');

		$this->getHandler('Content_Page')->create($record);

		$record = $handler->getTable()->getRecord();
		$record->setParentId(2);
		$record->setServiceId(19);
		$record->setStatus(\AmunService\Content\Page\Record::NORMAL);
		$record->setTitle('foo');

		$this->getHandler('Content_Page')->create($record);

		// move bar page to another parent
		$record = $handler->getTable()->getRecord();
		$record->setId(11);
		$record->setParentId(7);

		$this->getHandler('Content_Page')->update($record);

		// check path and parent id of the page
		$row = $handler->getTable()->getRow(array('id', 'parentId', 'path'), new Condition(array('id', '=', 11)));

		$this->assertEquals(7, $row['parentId']);
		$this->assertEquals('news/bar', $row['path']);

		$row = $handler->getTable()->getRow(array('id', 'parentId', 'path'), new Condition(array('id', '=', 12)));

		$this->assertEquals(2, $row['parentId']);
		$this->assertEquals('home/foo', $row['path']);

		$row = $handler->getTable()->getRow(array('id', 'parentId', 'path'), new Condition(array('id', '=', 2)));

		$this->assertEquals(1, $row['parentId']);
		$this->assertEquals('home', $row['path']);
	}

	public function testRebuildPath()
	{
		$handler = $this->getHandler('Content_Page');

		// create two sub pages under home
		$record = $handler->getTable()->getRecord();
		$record->setParentId(2);
		$record->setServiceId(19);
		$record->setStatus(\AmunService\Content\Page\Record::NORMAL);
		$record->setTitle('bar');

		$this->getHandler('Content_Page')->create($record);

		$record = $handler->getTable()->getRecord();
		$record->setParentId(2);
		$record->setServiceId(19);
		$record->setStatus(\AmunService\Content\Page\Record::NORMAL);
		$record->setTitle('foo');

		$this->getHandler('Content_Page')->create($record);

		// rename home
		$record = $handler->getTable()->getRecord();
		$record->setId(2);
		$record->setTitle('test');

		$this->getHandler('Content_Page')->update($record);

		// check path and parent id of the page
		$row = $handler->getTable()->getRow(array('id', 'parentId', 'path'), new Condition(array('id', '=', 11)));

		$this->assertEquals(2, $row['parentId']);
		$this->assertEquals('test/bar', $row['path']);

		$row = $handler->getTable()->getRow(array('id', 'parentId', 'path'), new Condition(array('id', '=', 12)));

		$this->assertEquals(2, $row['parentId']);
		$this->assertEquals('test/foo', $row['path']);

		$row = $handler->getTable()->getRow(array('id', 'parentId', 'path'), new Condition(array('id', '=', 2)));

		$this->assertEquals(1, $row['parentId']);
		$this->assertEquals('test', $row['path']);
	}
}

