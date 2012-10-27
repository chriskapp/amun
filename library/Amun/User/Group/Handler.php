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

/**
 * Amun_User_Group_Handler
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   Amun
 * @package    Amun_User_Group
 * @version    $Revision: 880 $
 */
class Amun_User_Group_Handler extends Amun_Data_HandlerAbstract
{
	public function create(PSX_Data_RecordInterface $record)
	{
		if($record->hasFields('title'))
		{
			$date = new DateTime('NOW', $this->registry['core.default_timezone']);

			$record->date = $date->format(PSX_DateTime::SQL);


			$this->table->insert($record->getData());


			$record->id = $this->sql->getLastInsertId();


			// insert rights if available
			$rights = isset($record->rights) ? $record->rights : null;

			if(!empty($rights))
			{
				$handler = new Amun_User_Group_Right_Handler($this->user);

				foreach($rights as $rightId)
				{
					$rightRecord = Amun_Sql_Table_Registry::get('User_Group_Right')->getRecord();
					$rightRecord->groupId = $record->id;
					$rightRecord->rightId = $rightId;

					$handler->create($rightRecord);
				}
			}


			$this->notify(Amun_Data_RecordAbstract::INSERT, $record);


			return $record;
		}
		else
		{
			throw new PSX_Data_Exception('Missing field in record');
		}
	}

	public function update(PSX_Data_RecordInterface $record)
	{
		if($record->hasFields('id'))
		{
			$con = new PSX_Sql_Condition(array('id', '=', $record->id));

			$this->table->update($record->getData(), $con);


			// update rights if available
			$rights    = isset($record->rights) ? $record->rights : null;
			$handler   = new Amun_User_Group_Right_Handler($this->user);
			$con       = new PSX_Sql_Condition(array('groupId', '=', $record->id));
			$oldRights = Amun_Sql_Table_Registry::get('User_Group_Right')->getCol('id', $con);

			// delete old rights
			foreach($oldRights as $id)
			{
				$rightRecord = Amun_Sql_Table_Registry::get('User_Group_Right')->getRecord();
				$rightRecord->id = $id;

				$handler->delete($rightRecord);
			}

			if(!empty($rights))
			{
				// create new rights
				foreach($rights as $rightId)
				{
					$rightRecord = Amun_Sql_Table_Registry::get('User_Group_Right')->getRecord();
					$rightRecord->groupId = $record->id;
					$rightRecord->rightId = $rightId;

					$handler->create($rightRecord);
				}
			}


			$this->notify(Amun_Data_RecordAbstract::UPDATE, $record);


			return $record;
		}
		else
		{
			throw new PSX_Data_Exception('Missing field in record');
		}
	}

	public function delete(PSX_Data_RecordInterface $record)
	{
		if($record->hasFields('id'))
		{
			$con = new PSX_Sql_Condition(array('id', '=', $record->id));

			$this->table->delete($con);


			// delete assigned rights
			$handler   = new Amun_User_Group_Right_Handler($this->user);
			$con       = new PSX_Sql_Condition(array('groupId', '=', $record->id));
			$oldRights = Amun_Sql_Table_Registry::get('User_Group_Right')->getCol('id', $con);

			foreach($oldRights as $id)
			{
				$rightRecord = Amun_Sql_Table_Registry::get('User_Group_Right')->getRecord();
				$rightRecord->id = $id;

				$handler->delete($rightRecord);
			}


			$this->notify(Amun_Data_RecordAbstract::DELETE, $record);


			return $record;
		}
		else
		{
			throw new PSX_Data_Exception('Missing field in record');
		}
	}
}
