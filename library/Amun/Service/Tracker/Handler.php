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
 * Amun_Service_Tracker_Handler
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   Amun
 * @package    Amun_Service_Tracker
 * @version    $Revision: 880 $
 */
class Amun_Service_Tracker_Handler extends Amun_Data_HandlerAbstract
{
	public function create(PSX_Data_RecordInterface $record)
	{
		if($record->hasFields('pageId', 'urlTitle', 'title', 'name', 'length', 'infoHash', 'torrent'))
		{
			$record->globalId = $this->base->getUUID('service:tracker:' . $record->pageId . ':' . uniqid());
			$record->userId   = $this->user->id;

			// move torrent file
			if($record->torrent instanceof PSX_Upload_File)
			{
				$name = md5($record->torrent->getName()) . '.torrent';
				$path = $this->registry['tracker.upload_path'] . '/' . $name;

				if(is_file($path))
				{
					throw new PSX_Data_Exception('File already exists');
				}

				if($record->torrent->move($path))
				{
					$record->torrent = $name;
				}
				else
				{
					throw new PSX_Data_Exception('Could not move file');
				}
			}
			else
			{
				throw new PSX_Data_Exception('No torrent file');
			}

			$date = new DateTime('NOW', $this->registry['core.default_timezone']);

			$record->date = $date->format(PSX_DateTime::SQL);

			if(!$this->hasApproval($record))
			{
				$this->table->insert($record->getData());


				$record->id = $this->sql->getLastInsertId();

				$this->notify(Amun_Data_RecordAbstract::INSERT, $record);
			}
			else
			{
				$this->approveRecord(Amun_System_Approval_Record::INSERT, $record);
			}

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
			if(!$this->hasApproval($record))
			{
				$con = new PSX_Sql_Condition(array('id', '=', $record->id));

				$this->table->update($record->getData(), $con);


				$this->notify(Amun_Data_RecordAbstract::UPDATE, $record);
			}
			else
			{
				$this->approveRecord(Amun_System_Approval_Record::UPDATE, $record);
			}

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
			if(!$this->hasApproval($record))
			{
				$con = new PSX_Sql_Condition(array('id', '=', $record->id));

				$this->table->delete($con);


				$this->notify(Amun_Data_RecordAbstract::DELETE, $record);
			}
			else
			{
				$this->approveRecord(Amun_System_Approval_Record::DELETE, $record);
			}

			return $record;
		}
		else
		{
			throw new PSX_Data_Exception('Missing field in record');
		}
	}
}


