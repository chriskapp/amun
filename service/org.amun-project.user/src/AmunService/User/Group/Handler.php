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

namespace AmunService\User\Group;

use Amun\DataFactory;
use Amun\Data\HandlerAbstract;
use Amun\Data\RecordAbstract;
use Amun\Exception;
use AmunService\Core\Approval;
use AmunService\User\Group\Right;
use PSX\DateTime;
use PSX\Data\RecordInterface;
use PSX\Data\ResultSet;
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
	public function create(RecordInterface $record)
	{
		if($record->hasFields('title'))
		{
			$date = new DateTime('NOW', $this->registry['core.default_timezone']);

			$record->date = $date->format(DateTime::SQL);


			$this->table->insert($record->getData());


			$record->id = $this->sql->getLastInsertId();


			// insert rights if available
			$rights = isset($record->rights) ? $record->rights : null;

			if(!empty($rights))
			{
				$handler = $this->hm->getHandler('AmunService\User\Group\Right', $this->user);

				foreach($rights as $rightId)
				{
					$rightRecord = $handler->getRecord();
					$rightRecord->groupId = $record->id;
					$rightRecord->rightId = $rightId;

					$handler->create($rightRecord);
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

			$this->table->update($record->getData(), $con);


			// update rights if available
			$rights    = isset($record->rights) ? $record->rights : null;
			$handler   = $this->hm->getHandler('AmunService\User\Group\Right', $this->user);
			$con       = new Condition(array('groupId', '=', $record->id));
			$oldRights = $this->hm->getTable('AmunService\User\Group\Right')->getCol('id', $con);

			// delete old rights
			foreach($oldRights as $id)
			{
				$rightRecord = $handler->getRecord();
				$rightRecord->id = $id;

				$handler->delete($rightRecord);
			}

			if(!empty($rights))
			{
				// create new rights
				foreach($rights as $rightId)
				{
					$rightRecord = $handler->getRecord();
					$rightRecord->groupId = $record->id;
					$rightRecord->rightId = $rightId;

					$handler->create($rightRecord);
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


			// delete assigned rights
			$handler = $this->hm->getHandler('AmunService\User\Group\Right', $this->user);
			$con       = new Condition(array('groupId', '=', $record->id));
			$oldRights = $this->hm->getTable('AmunService\User\Group\Right')->getCol('id', $con);

			foreach($oldRights as $id)
			{
				$rightRecord = $handler->getRecord();
				$rightRecord->id = $id;

				$handler->delete($rightRecord);
			}


			$this->notify(RecordAbstract::DELETE, $record);


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
			->select(array('id', 'title', 'date'));
	}
}
