<?php
/*
 *  $Id: CommentTest.php 743 2012-06-26 19:31:26Z k42b3.x@googlemail.com $
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
 * Amun_Api_Service_CommentTest
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   tests
 * @version    $Revision: 743 $
 * @backupStaticAttributes disabled
 */
class Amun_Api_Service_CommentTest extends Amun_Api_RestTest
{
	protected function setUp()
	{
		if(!$this->hasService('org.amun-project.comment'))
		{
			$this->markTestSkipped('Service comment not installed');
		}
		else
		{
			parent::setUp();
		}
	}

	public function getEndpoint()
	{
		return $this->config['psx_url'] . '/' . $this->config['psx_dispatch'] . 'api/comment';
	}

	public function getTable()
	{
		return Amun_Sql_Table_Registry::get('Comment');
	}

	public function testGet()
	{
		$this->assertResultSetResponse($this->get());
	}

	public function testPost()
	{
		$record = $this->getTable()->getRecord();
		$record->setPageId(1);
		$record->setRefId(1);
		$record->text = 'foobar';

		$this->assertPositiveResponse($this->post($record));

		$row = $this->getLastInsertedRecord();

		$this->table->delete(new PSX_Sql_Condition(array('id', '=', $row['id'])));

		$record->text = '<p>foobar </p>' . "\n";

		unset($row['id']);
		unset($row['globalId']);
		unset($row['userId']);
		unset($row['date']);

		$row['refId'] = (integer) $row['refId'];

		$this->assertEquals($row, $record->getData());
	}
}

