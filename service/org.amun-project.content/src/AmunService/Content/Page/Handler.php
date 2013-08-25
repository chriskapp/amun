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

namespace AmunService\Content\Page;

use Amun\Data\HandlerAbstract;
use Amun\Data\RecordAbstract;
use Amun\Exception;
use Amun\DataFactory;
use AmunService\Content\Page\Gadget;
use PSX\Data\RecordInterface;
use PSX\DateTime;
use PSX\Sql;
use PSX\Sql\Condition;
use PSX\Sql\Join;

/**
 * Handler
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/gpl.html GPLv3
 * @link    http://amun.phpsx.org
 */
class Handler extends HandlerAbstract
{
	public function getOneByPath($path)
	{
		$class = $this->getClassName();
		$args  = $this->getClassArgs();

		$sql = "SELECT
					`page`.*
				FROM
					" . $this->registry['table.content_page'] . " `page`
				INNER JOIN
					" . $this->registry['table.core_service'] . " `service`
				ON
					`page`.`serviceId` = `service`.`id`
				WHERE
					`page`.`path` LIKE SUBSTRING(?, 1, CHAR_LENGTH(`page`.`path`))
				ORDER BY
					CHAR_LENGTH(`page`.`path`) DESC
				LIMIT 1";

		return $this->sql->getRow($sql, array($path), Sql::FETCH_OBJECT, $class, $args);
	}

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
				$handler = $this->hm->getHandler('AmunService\Content\Page\Gadget', $this->user);

				foreach($gadgets as $k => $gadgetId)
				{
					$gadgetRecord = $handler->getRecord();
					$gadgetRecord->pageId   = $record->id;
					$gadgetRecord->gadgetId = $gadgetId;
					$gadgetRecord->sort     = $k;

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


			// get parent id and url title because the values are probably not 
			// set
			$row = $this->table->getRow(array('parentId', 'urlTitle'), new Condition(array('id', '=', $record->id)));


			// if parent has changed rebuild path
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
			$handler    = $this->hm->getHandler('AmunService\Content\Page\Gadget', $this->user);
			$con        = new Condition(array('pageId', '=', $record->id));
			$oldGadgets = $this->hm->getTable('AmunService\Content\Page\Gadget')->getCol('id', $con);

			// delete old gadgets
			foreach($oldGadgets as $id)
			{
				$gadgetRecord = $handler->getRecord();
				$gadgetRecord->id = $id;

				$handler->delete($gadgetRecord);
			}

			if(!empty($gadgets))
			{
				// create new gadgets
				foreach($gadgets as $k => $gadgetId)
				{
					$gadgetRecord = $handler->getRecord();
					$gadgetRecord->pageId   = $record->id;
					$gadgetRecord->gadgetId = $gadgetId;
					$gadgetRecord->sort     = $k;

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
			$handler    = $this->hm->getHandler('AmunService\Content\Page\Gadget', $this->user);
			$con        = new Condition(array('pageId', '=', $record->id));
			$oldGadgets = $this->hm->getTable('AmunService\Content\Page\Gadget')->getCol('id', $con);

			foreach($oldGadgets as $id)
			{
				$gadgetRecord = $handler->getRecord();
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
			->join(Join::INNER, $this->hm->getTable('AmunService\Core\Service')
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
	`page`.`id` = ?
SQL;

			$path = $this->sql->getField($sql, array($record->parentId));

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
	`page`.`id` = ?
SQL;

		$row = $this->sql->getRow($sql, array($record->id));

		if(!empty($row))
		{
			$path    = $row['path'] . '%';
			$len     = strlen($path);
			$part    = substr($path, 0, strrpos($path, '/'));
			$newPath = (!empty($part) ? $part . '/' : '') . $record->urlTitle;

			$sql = <<<SQL
UPDATE 
	{$this->registry['table.content_page']} 
SET
	`path` = CONCAT(?, SUBSTRING(`path`, ?))
WHERE 
	`path` LIKE ?
SQL;

			$this->sql->execute($sql, array($newPath, $len, $path));
		}
	}

	private function reparentPath(RecordInterface $record)
	{
		$sql = <<<SQL
SELECT
	`page`.`urlTitle`,
	`page`.`path`
FROM 
	{$this->registry['table.content_page']} `page`
WHERE 
	`page`.`id` = ?
SQL;

		$parent = $this->sql->getRow($sql, array($record->parentId));

		if(!empty($parent))
		{
			$sql = <<<SQL
SELECT
	`page`.`urlTitle`,
	`page`.`path`
FROM 
	{$this->registry['table.content_page']} `page`
WHERE 
	`page`.`id` = ?
SQL;

			$row = $this->sql->getRow($sql, array($record->id));

			// check whether the new parent was parent of this node
			if(substr($parent['path'], 0, strlen($row['path'])) == $row['path'])
			{
				throw new Exception('New parent can not be a parent of the current node');
			}

			if(!empty($row))
			{
				$path    = $row['path'] . '%';
				$len     = strlen($path);
				$newPath = (!empty($parent['path']) ? $parent['path'] . '/' : '') . $row['urlTitle'];

				$sql = <<<SQL
UPDATE 
	{$this->registry['table.content_page']} 
SET
	`path` = CONCAT(?, SUBSTRING(`path`, ?))
WHERE 
	`path` LIKE ?
SQL;

				$this->sql->execute($sql, array($newPath, $len, $path));
			}
		}
	}
}

