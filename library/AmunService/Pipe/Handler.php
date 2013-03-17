<?php
/*
 *  $Id: Handler.php 683 2012-06-03 11:52:32Z k42b3.x@googlemail.com $
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

namespace AmunService\Pipe;

use Amun\DataFactory;
use Amun\Data\HandlerAbstract;
use Amun\Data\RecordAbstract;
use Amun\Exception;
use Amun\Security;
use AmunService\Core\Approval;
use PSX\DateTime;
use PSX\Data\RecordInterface;
use PSX\Data\ResultSet;
use PSX\Sql;
use PSX\Sql\Condition;
use PSX\Sql\Join;

/**
 * Amun_Service_Pipe_Handler
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   Amun
 * @package    Amun_Service_Pipe
 * @version    $Revision: 683 $
 */
class Handler extends HandlerAbstract
{
	public function getByPageId($pageId, $mode = 0, $class = null, array $args = array())
	{
		return $this->table
			->select(array('id', 'date'))
			->join(Join::INNER, DataFactory::getTable('Media')
				->select(array('rightId', 'name', 'path', 'mimeType'), 'media')
			)
			->join(Join::INNER, DataFactory::getTable('User_Account')
				->select(array('name', 'profileUrl'), 'author')
			)
			->where('pageId', '=', $pageId)
			->getRow($mode, $class, $args);
	}

	public function create(RecordInterface $record)
	{
		if($record->hasFields('pageId', 'mediaId'))
		{
			$record->globalId = $this->base->getUUID('service:pipe:' . $record->pageId . ':' . uniqid());
			$record->userId   = $this->user->id;

			$date = new DateTime('NOW', $this->registry['core.default_timezone']);

			$record->date = $date->format(DateTime::SQL);

			if(!$this->hasApproval($record))
			{
				$this->table->insert($record->getData());


				$record->id = $this->sql->getLastInsertId();

				$this->notify(RecordAbstract::INSERT, $record);
			}
			else
			{
				$this->approveRecord(Approval\Record::INSERT, $record);
			}

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
			if(!$this->hasApproval($record))
			{
				$con = new Condition(array('id', '=', $record->id));

				$this->table->update($record->getData(), $con);


				$this->notify(RecordAbstract::UPDATE, $record);
			}
			else
			{
				$this->approveRecord(Approval\Record::UPDATE, $record);
			}

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
			if(!$this->hasApproval($record))
			{
				$con = new Condition(array('id', '=', $record->id));

				$this->table->delete($con);


				$this->notify(RecordAbstract::DELETE, $record);
			}
			else
			{
				$this->approveRecord(Approval\Record::DELETE, $record);
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
			->select(array('id', 'globalId', 'pageId', 'mediaId', 'date'))
			->join(Join::INNER, DataFactory::getTable('User_Account')
				->select(array('name', 'profileUrl'), 'author')
			)
			->join(Join::INNER, DataFactory::getTable('Content_Page')
				->select(array('path'), 'page')
			)
			->join(Join::INNER, DataFactory::getTable('Media')
				->select(array('path'), 'media')
			);
	}
}
