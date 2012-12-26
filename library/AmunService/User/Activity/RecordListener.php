<?php
/*
 *  $Id: Log.php 635 2012-05-01 19:46:37Z k42b3.x@googlemail.com $
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
 * AmunService_User_Activity_RecordListener
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   Amun
 * @package    Amun_Log
 * @version    $Revision: 635 $
 */
class AmunService_User_Activity_RecordListener extends Amun_Data_ListenerAbstract
{
	public function notify($type, Amun_Sql_TableInterface $table, PSX_Data_RecordInterface $record)
	{
		switch(true)
		{
			case $record instanceof AmunService_User_Activity_Record:

				// nothing here
				break;

			case $record instanceof AmunService_User_Account_Record:

				$this->handleUserAccount($type, $table, $record);
				break;

			case $record instanceof AmunService_User_Friend_Record:

				$this->handleUserFriend($type, $table, $record);
				break;

			default:

				$this->handleDefault($type, $table, $record);
				break;
		}
	}

	private function handleUserAccount($type, Amun_Sql_TableInterface $table, PSX_Data_RecordInterface $record)
	{
		if($type == Amun_Data_RecordAbstract::INSERT)
		{
			// insert activity
			$activity          = Amun_Sql_Table_Registry::get('User_Activity')->getRecord();
			$activity->refId   = $record->id;
			$activity->table   = $table->getName();
			$activity->verb    = 'join';
			$activity->summary = $record->name . ' has created an account';

			$handler = new AmunService_User_Activity_Handler(new Amun_User($record->id, $this->registry));
			$handler->create($activity);
		}
	}

	private function handleUserFriend($type, Amun_Sql_TableInterface $table, PSX_Data_RecordInterface $record)
	{
		if($type == Amun_Data_RecordAbstract::INSERT)
		{
			$date = new DateTime('NOW', $this->registry['core.default_timezone']);

			if($record->status == AmunService_User_Friend_Record::REQUEST)
			{
			}
			else if($record->status == AmunService_User_Friend_Record::NORMAL)
			{
				// insert activity for user who has accepted the friend request
				$activity          = Amun_Sql_Table_Registry::get('User_Activity')->getRecord();
				$activity->refId   = $record->id;
				$activity->table   = $table->getName();
				$activity->verb    = 'make-friend';
				$activity->summary = '<a href="' . $record->getUser()->profileUrl . '">' . $record->getUser()->name . '</a> and <a href="' . $record->getFriend()->profileUrl . '">' . $record->getFriend()->name . '</a> are now friends';

				$handler = new AmunService_User_Activity_Handler($this->user);
				$handler->create($activity);

				// insert activity for user who has requested the friendship
				/*
				$activity          = Amun_Sql_Table_Registry::get('User_Activity')->getRecord();
				$activity->refId   = $record->id;
				$activity->table   = $table->getName();
				$activity->verb    = 'make-friend';
				$activity->summary = '<a href="' . $record->getFriend()->profileUrl . '">' . $record->getFriend()->name . '</a> and <a href="' . $record->getUser()->profileUrl . '">' . $record->getUser()->name . '</a> are now friends';

				$handler = new AmunService_User_Activity_Handler(new Amun_User($record->getFriend()->id, $this->registry));
				$handler->create($activity);
				*/
			}
		}
	}

	private function handleDefault($type, Amun_Sql_TableInterface $table, PSX_Data_RecordInterface $record)
	{
		// get template message
		$sql = <<<SQL
SELECT

	`template`.`verb`,
	`template`.`path`,
	`template`.`summary`

	FROM {$this->registry['table.user_activity_template']} `template`

		WHERE `template`.`table` = ?

		AND `template`.`type` = ?
SQL;

		$row = $this->sql->getRow($sql, array($table->getName(), Amun_Data_RecordAbstract::getType($type)));

		if(!empty($row))
		{
			$objectUrl = $this->getObjectUrl($record, $this->substituteVars($record, $row['path']));

			// insert activity
			$activity          = Amun_Sql_Table_Registry::get('User_Activity')->getRecord();
			$activity->refId   = $record->id;
			$activity->table   = $table->getName();
			$activity->verb    = $row['verb'];
			$activity->summary = $this->substituteVars($record, $row['summary'], $objectUrl);

			$handler = new AmunService_User_Activity_Handler($this->user);
			$handler->create($activity);
		}
	}

	private function substituteVars(PSX_Data_RecordInterface $record, $content, $url = null)
	{
		// object fields
		if($url !== null && strpos($content, '{object.url') !== false)
		{
			$content = str_replace('{object.url}', $url, $content);
		}

		// user fields
		if(strpos($content, '{user.') !== false)
		{
			$fields = array('id', 'name', 'profileUrl', 'lastSeen', 'date');

			foreach($fields as $v)
			{
				$key = '{user.' . $v . '}';

				if(strpos($content, $key) !== false)
				{
					$content = str_replace($key, $this->user->$v, $content);
				}
			}
		}

		// record fields
		if(strpos($content, '{record.') !== false)
		{
			$fields = $record->getFields();

			foreach($fields as $k => $v)
			{
				$key = '{record.' . $k . '}';

				if(strpos($content, $key) !== false)
				{
					$v = strip_tags($v);
					$v = strlen($v) > 256 ? substr($v, 0, 253) . '...' : $v;

					$content = str_replace($key, $v, $content);
				}
			}
		}

		return $content;
	}

	private function getObjectUrl(PSX_Data_RecordInterface $record, $path = null)
	{
		if(isset($record->pageId))
		{
			$url = Amun_Page::getUrl($this->registry, $record->pageId);

			if(!empty($path))
			{
				$url.= '/' . $path;
			}

			return $url;
		}
		else
		{
			return '#';
		}
	}
}
