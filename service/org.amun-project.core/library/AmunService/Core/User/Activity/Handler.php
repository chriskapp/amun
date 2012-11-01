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
 * Amun_User_Activity_Handler
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   Amun
 * @package    Amun_User_Activity
 * @version    $Revision: 880 $
 */
class AmunService_Core_User_Activity_Handler extends Amun_Data_HandlerAbstract
{
	public function create(PSX_Data_RecordInterface $record)
	{
		if($record->hasFields('summary'))
		{
			if(!isset($record->globalId))
			{
				$record->globalId = $this->base->getUUID('user:activity:' . $record->summary . ':' . uniqid());
			}

			$record->userId = $this->user->id;
			$record->verb   = isset($record->verb) ? $record->verb : 'post';

			if(!isset($record->date))
			{
				$date = new DateTime('NOW', $this->registry['core.default_timezone']);

				$record->date = $date->format(PSX_DateTime::SQL);
			}


			$this->table->insert($record->getData());


			$record->id = $this->sql->getLastInsertId();

			$this->notify(Amun_Data_RecordAbstract::INSERT, $record);


			$this->sendToReceiver($record);


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


			//$this->notify(Amun_Data_RecordAbstract::UPDATE, $record);


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


			//$this->notify(Amun_Data_RecordAbstract::DELETE, $record);


			return $record;
		}
		else
		{
			throw new PSX_Data_Exception('Missing field in record');
		}
	}

	public function callback(PSX_Atom_Entry $entry)
	{
		$dom  = $entry->getDom();
		$verb = $dom->getElementsByTagNameNS('http://activitystrea.ms/spec/1.0/', 'verb')->item(0);

		if($verb instanceof DomElement)
		{
			$activity = Amun_Sql_Table_Registry::get('User_Activity')->getRecord();
			$activity->setVerb($verb->nodeValue);
			$activity->setSummary($entry->content);
			$activity->table = 'amun_user_activity';

			return $this->create($activity);
		}
		else
		{
			throw new PSX_Data_Exception('Verb not set');
		}
	}

	private function sendToReceiver(PSX_Data_RecordInterface $record)
	{
		$activityId = isset($record->id)    ? (integer) $record->id    : null;
		$scope      = isset($record->scope) ? (integer) $record->scope : 0;

		if(!empty($activityId))
		{
			$sql = <<<SQL
INSERT INTO {$this->registry['table.core_user_activity_receiver']}
	(`activityId`, `userId`, `date`)
	SELECT
		{$activityId} AS `activityId`,
		`friendId`    AS `userId`,
		NOW()         AS `date`
	FROM
		{$this->registry['table.core_user_friend']} `friend`
	WHERE
		`friend`.`userId` = {$this->user->id}
SQL;

			if($scope > 0)
			{
				$sql.= ' AND
							(`friend`.`friendId` = ' . $this->user->id . ' OR `friend`.`groupId` = ' . $scope . ')';
			}

			$this->sql->query($sql);
		}
	}
}


