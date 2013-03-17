<?php
/*
 *  $Id: Handler.php 880 2012-10-27 13:14:26Z k42b3.x@googlemail.com $
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

namespace AmunService\Content\Page;

use Amun\Data\HandlerAbstract;
use Amun\Data\RecordAbstract;
use Amun\Exception;
use Amun\DataFactory;
use AmunService\Content\Page\Gadget;
use PSX\Data\RecordInterface;
use PSX\DateTime;
use PSX\Sql\Condition;
use PSX\Sql\Join;

/**
 * AmunService_Core_Content_Page_Handler
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   Amun
 * @package    Amun_Content_Page
 * @version    $Revision: 880 $
 */
class Handler extends HandlerAbstract
{
	public function create(RecordInterface $record)
	{
		if($record->hasFields('parentId', 'serviceId', 'status', 'urlTitle', 'title'))
		{
			if(!isset($record->load))
			{
				$record->load = Record::NAV | Record::PATH;
			}


			// build path for node
			$record->path = $this->buildPath($record);


			// set global id
			$record->globalId = $this->base->getUUID('content:page:' . $record->path . ':' . uniqid());


			$date = new DateTime('NOW', $this->registry['core.default_timezone']);

			$record->date = $date->format(DateTime::SQL);


			$this->table->insert($record->getData());


			$pageId = $this->sql->getLastInsertId();

			if($pageId == 0)
			{
				throw new Exception('Couldnt insert page');
			}


			$record->id = $pageId;


			// set gadgets
			$gadgets = isset($record->gadgets) ? $record->gadgets : null;

			if(!empty($gadgets))
			{
				$handler = new Gadget\Handler($this->user);

				foreach($gadgets as $gadgetId)
				{
					$gadgetRecord = DataFactory::getTable('Content_Page_Gadget')->getRecord();
					$gadgetRecord->pageId = $record->id;
					$gadgetRecord->gadgetId = $gadgetId;

					$handler->create($gadgetRecord);
				}
			}


			$this->notify(RecordAbstract::INSERT, $record);


			return $record;
		}
		else
		{
			throw new Exception('Missing field in record');
		}
	}

	public function update(RecordInterface $record)
	{
		if($record->hasFields('id'))
		{
			$con = new Condition(array('id', '=', $record->id));


			$sql = 'SELECT parentId, urlTitle FROM ' . $this->table->getName() . ' WHERE id = ' . $record->id;

			$row = $this->sql->getRow($sql);


			// if parent change rebuild path
			if(isset($record->parentId) && $row['parentId'] != $record->parentId)
			{
				$this->reparentPath($record);
			}

			// if title changes rebuild path
			if(isset($record->urlTitle) && $row['urlTitle'] != $record->urlTitle)
			{
				$this->rebuildPath($record);
			}


			$con = new Condition(array('id', '=', $record->id));

			$this->table->update($record->getData(), $con);


			// update gadgets if available
			$gadgets    = isset($record->gadgets) ? $record->gadgets : null;
			$handler    = new Gadget\Handler($this->user);
			$con        = new Condition(array('pageId', '=', $record->id));
			$oldGadgets = DataFactory::getTable('Content_Page_Gadget')->getCol('id', $con);

			// delete old gadgets
			foreach($oldGadgets as $id)
			{
				$gadgetRecord = DataFactory::getTable('Content_Page_Gadget')->getRecord();
				$gadgetRecord->id = $id;

				$handler->delete($gadgetRecord);
			}

			if(!empty($gadgets))
			{
				// create new gadgets
				foreach($gadgets as $gadgetId)
				{
					$gadgetRecord = DataFactory::getTable('Content_Page_Gadget')->getRecord();
					$gadgetRecord->pageId = $record->id;
					$gadgetRecord->gadgetId = $gadgetId;

					$handler->create($gadgetRecord);
				}
			}


			$this->notify(RecordAbstract::UPDATE, $record);


			return $record;
		}
		else
		{
			throw new Exception('Missing field in record');
		}
	}

	public function delete(RecordInterface $record)
	{
		if($record->hasFields('id'))
		{
			$con = new Condition(array('id', '=', $record->id));

			$this->table->delete($con);


			$this->notify(RecordAbstract::DELETE, $record);


			// delete assigned gadgets
			$handler    = new Gadget\Handler($this->user);
			$con        = new Condition(array('pageId', '=', $record->id));
			$oldGadgets = DataFactory::getTable('Content_Page_Gadget')->getCol('id', $con);

			foreach($oldGadgets as $id)
			{
				$gadgetRecord = DataFactory::getTable('Content_Page_Gadget')->getRecord();
				$gadgetRecord->id = $id;

				$handler->delete($gadgetRecord);
			}


			return $record;
		}
		else
		{
			throw new Exception('Missing field in record');
		}
	}

	protected function getDefaultSelect()
	{
		return $this->table
			->select(array('id', 'parentId', 'globalId', 'status', 'load', 'path', 'title', 'template', 'date'))
			->join(Join::INNER, DataFactory::getTable('Core_Service')
				->select(array('id', 'type'), 'service')
			);
	}

	private function buildPath(RecordInterface $record)
	{
		if($record->parentId > 0)
		{
			$sql = <<<SQL
SELECT
	`page`.`path`
FROM 
	{$this->registry['table.content_page']} `page`
WHERE 
	`page`.`id` = {$record->parentId}
SQL;

			$path = $this->sql->getField($sql);

			if(!empty($path))
			{
				return $path . '/' . $record->urlTitle;
			}
			else
			{
				return $record->urlTitle;
			}
		}
		else
		{
			return '';
		}
	}

	private function rebuildPath(RecordInterface $record)
	{
		$sql = <<<SQL
SELECT
	`page`.`path`
FROM 
	{$this->registry['table.content_page']} `page`
WHERE 
	`page`.`id` = {$record->id}
SQL;

		$row = $this->sql->getRow($sql);

		if(!empty($row))
		{
			$path    = $row['path'];
			$len     = strlen($path) + 1;
			$part    = substr($path, 0, strrpos($path, '/'));
			$newPath = (!empty($part) ? $part . '/' : '') . $record->urlTitle;

			$sql = <<<SQL
UPDATE 
	{$this->registry['table.content_page']} 
SET
	`path` = CONCAT('{$newPath}', SUBSTRING(`path`, {$len}))
WHERE 
	`path` LIKE '{$path}%'
SQL;

			$this->sql->query($sql);
		}
	}

	public function reparentPath(RecordInterface $record)
	{
		$sql = <<<SQL
SELECT
	`page`.`urlTitle`,
	`page`.`path`
FROM 
	{$this->registry['table.content_page']} `page`
WHERE 
	`page`.`id` = {$record->parentId}
SQL;

		$parent = $this->sql->getRow($sql);

		if(!empty($parent))
		{
			$sql = <<<SQL
SELECT
	`page`.`urlTitle`,
	`page`.`path`
FROM 
	{$this->registry['table.content_page']} `page`
WHERE 
	`page`.`id` = {$record->id}
SQL;

			$row = $this->sql->getRow($sql);

			// check whether the new parent was parent of this node
			if(substr($parent['path'], 0, strlen($row['path'])) == $row['path'])
			{
				throw new Exception('New parent can not be a parent of the current node');
			}

			if(!empty($row))
			{
				$path    = $row['path'];
				$len     = strlen($path) + 1;
				$newPath = (!empty($parent['path']) ? $parent['path'] . '/' : '') . $row['urlTitle'];

				$sql = <<<SQL
UPDATE 
	{$this->registry['table.content_page']} 
SET
	`path` = CONCAT('{$newPath}', SUBSTRING(`path`, {$len}))
WHERE 
	`path` LIKE '{$path}%'
SQL;

				$this->sql->query($sql);
			}
		}
	}
}

