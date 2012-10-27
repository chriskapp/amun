<?php
/*
 *  $Id: GadgetTest.php 742 2012-06-25 20:47:21Z k42b3.x@googlemail.com $
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
 * Amun_Api_Content_Page_GadgetTest
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   tests
 * @version    $Revision: 742 $
 * @backupStaticAttributes disabled
 */
class Amun_Api_Content_Page_GadgetTest extends Amun_Api_RestTest
{
	private $pageId;
	private $gadgetId;

	protected function setUp()
	{
		parent::setUp();

		// check whether we have an page
		$this->pageId = Amun_Registry::getInstance()->getSql()->getField('SELECT id FROM ' . Amun_Registry::get('table.content_page') . ' ORDER BY id ASC LIMIT 1');

		if(empty($this->pageId))
		{
			$this->markTestSkipped('No page available');
		}

		// check whether we have an gadget
		$this->gadgetId = Amun_Registry::getInstance()->getSql()->getField('SELECT id FROM ' . Amun_Registry::get('table.content_gadget') . ' ORDER BY id ASC LIMIT 1');

		if(empty($this->gadgetId))
		{
			$this->markTestSkipped('No gadget available');
		}
	}

	public function getEndpoint()
	{
		return $this->config['psx_url'] . '/' . $this->config['psx_dispatch'] . 'api/content/page/gadget';
	}

	public function getTable()
	{
		return Amun_Sql_Table_Registry::get('Content_Page_Gadget');
	}

	public function testGet()
	{
		$this->assertResultSetResponse($this->get());
	}

	public function testPost()
	{
		$record = new Amun_Content_Page_Gadget($this->table);
		$record->setPageId($this->pageId);
		$record->setGadgetId($this->gadgetId);

		$this->assertPositiveResponse($this->post($record));

		$row = $this->getLastInsertedRecord();

		$this->table->delete(new PSX_Sql_Condition(array('id', '=', $row['id'])));

		unset($row['id']);
		unset($row['sort']);

		$this->assertEquals($row, $record->getData());
	}
}

