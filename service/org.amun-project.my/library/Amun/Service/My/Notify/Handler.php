<?php
/*
 *  $Id: Handler.php 638 2012-05-01 20:50:26Z k42b3.x@googlemail.com $
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
 * Amun_Service_My_Notify_Handler
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   Amun
 * @package    Amun_Service_My
 * @version    $Revision: 638 $
 */
class Amun_Service_My_Notify_Handler extends Amun_Data_HandlerAbstract
{
	public function create(PSX_Data_RecordInterface $record)
	{
		if($record->hasFields('serviceId', 'contactId'))
		{
			$record->userId = $this->user->id;
			$record->status = Amun_Service_My_Notify::ACTIVE;

			$date = new DateTime('NOW', $this->registry['core.default_timezone']);

			$record->date = $date->format(PSX_Time::SQL);


			$this->table->insert($record->getData());


			$record->id = $this->sql->getLastInsertId();

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


			$this->notify(Amun_Data_RecordAbstract::DELETE, $record);


			return $record;
		}
		else
		{
			throw new PSX_Data_Exception('Missing field in record');
		}
	}
}


