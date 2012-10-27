<?php
/*
 *  $Id: Activity.php 683 2012-06-03 11:52:32Z k42b3.x@googlemail.com $
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
 * Amun_Notify_Activity
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   Amun
 * @package    Amun_Notify
 * @version    $Revision: 683 $
 */
class Amun_Notify_Activity extends Amun_NotifyAbstract
{
	public function notify($type, PSX_Data_RecordInterface $record)
	{
		switch(true)
		{
			case $record instanceof Amun_User_Activity:

				// nothing here
				break;

			case $record instanceof Amun_User_Account:

				$this->handleUserAccount($type, $record);
				break;

			case $record instanceof Amun_User_Friend:

				$this->handleUserFriend($type, $record);
				break;

			default:

				$this->handleDefault($type, $record);
				break;
		}
	}

	private function handleUserAccount($type, PSX_Data_RecordInterface $record)
	{
		if($type == Amun_Data_RecordAbstract::INSERT)
		{
			// template
			$template = $this->applyTemplate($type, $record);

			// insert activity
			$activity          = Amun_Sql_Table_Registry::get('User_Activity')->getRecord();
			$activity->refId   = $record->id;
			$activity->table   = $this->table->getName();
			$activity->verb    = $template['verb'];
			$activity->summary = $template['summary'];

			$handler = new Amun_User_Activity_Handler(new Amun_User($record->id, $this->registry));
			$handler->create($activity);
		}
	}

	private function handleUserFriend($type, PSX_Data_RecordInterface $record)
	{
		if($type == Amun_Data_RecordAbstract::INSERT)
		{
			$date = new DateTime('NOW', $this->registry['core.default_timezone']);

			if($record->status == Amun_User_Friend::REQUEST)
			{

			}
			else if($record->status == Amun_User_Friend::NORMAL)
			{
				// insert activity for user who has accepted the friend request
				$activity          = Amun_Sql_Table_Registry::get('User_Activity')->getRecord();
				$activity->refId   = $record->id;
				$activity->table   = $this->table->getName();
				$activity->verb    = 'make-friend';
				$activity->summary = '<a href="' . $record->getUser()->profileUrl . '">' . $record->getUser()->name . '</a> and <a href="' . $record->getFriend()->profileUrl . '">' . $record->getFriend()->name . '</a> are now friends';

				$handler = new Amun_User_Activity_Handler($this->user);
				$handler->create($activity);

				// insert activity for user who has requested the friendship
				$activity          = Amun_Sql_Table_Registry::get('User_Activity')->getRecord();
				$activity->refId   = $record->id;
				$activity->table   = $this->table->getName();
				$activity->verb    = 'make-friend';
				$activity->summary = '<a href="' . $record->getFriend()->profileUrl . '">' . $record->getFriend()->name . '</a> and <a href="' . $record->getUser()->profileUrl . '">' . $record->getUser()->name . '</a> are now friends';

				$handler = new Amun_User_Activity_Handler(new Amun_User($record->getFriend()->id, $this->registry));
				$handler->create($activity);
			}
		}
	}

	private function handleDefault($type, PSX_Data_RecordInterface $record)
	{
		// template
		$template = $this->applyTemplate($type, $record);

		// insert activity
		$activity          = Amun_Sql_Table_Registry::get('User_Activity')->getRecord();
		$activity->refId   = $record->id;
		$activity->table   = $this->table->getName();
		$activity->verb    = $template['verb'];
		$activity->summary = $template['summary'];

		$handler = new Amun_User_Activity_Handler($this->user);
		$handler->create($activity);
	}

	private function applyTemplate($type, PSX_Data_RecordInterface $record)
	{
		// get template message
		$template = array();
		$sql      = <<<SQL
SELECT

	`template`.`verb`,
	`template`.`path`,
	`template`.`summary`

	FROM {$this->registry['table.user_activity_template']} `template`

		WHERE `template`.`table` = ?

		AND `template`.`type` = ?
SQL;

		$row = $this->sql->getRow($sql, array($this->table->getName(), Amun_Data_RecordAbstract::getType($type)));

		if(!empty($row))
		{
			$object = $this->getObjectUrl($record, $this->substituteVars($record, $row['path']));

			$template['verb']    = $row['verb'];
			$template['summary'] = $this->substituteVars($record, $row['summary'], $object);
		}
		else
		{
			$object = $this->getObjectUrl($record);
			$actor  = '<a href="' . $this->user->profileUrl . '">' . $this->user->name . '</a>';
			$verb   = $type == Amun_Data_RecordAbstract::INSERT ? 'add' : ($type == Amun_Data_RecordAbstract::UPDATE ? 'update' : 'delete');
			$target = '<a href="' . $object . '">' . $this->table->getDisplayName() . '</a>';

			$template['verb']    = $verb;
			$template['summary'] = '<p>' . $actor . ' has ' . $verb . ' a ' . $target . '</p>';
		}

		return $template;
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
			$fields = array('id', 'name', 'profileUrl', 'date');

			foreach($fields as $v)
			{
				$content = str_replace('{user.' . $v . '}', $this->user->$v, $content);
			}
		}

		// record fields
		if(strpos($content, '{record.') !== false)
		{
			$fields = $record->getFields();

			foreach($fields as $k => $v)
			{
				$v = strip_tags($v);
				$v = strlen($v) > 256 ? substr($v, 0, 253) . '...' : $v;

				$content = str_replace('{record.' . $k . '}', $v, $content);
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

	private function getGlobalId(PSX_Data_RecordInterface $record)
	{
		return $this->base->getUUID('user:activity:' . $record->id . ':' . uniqid());
	}

	private function sendToReceiver($activityId, $scope = 0)
	{
		$activityId = (integer) $activityId;
		$scope      = (integer) $scope;

		if(!empty($activityId))
		{
			$sql = <<<SQL
INSERT INTO {$this->registry['table.user_activity_receiver']}
	(`activityId`, `receiverId`, `date`)
	SELECT
		{$activityId} AS `activityId`,
		`friendId`    AS `receiverId`,
		NOW()         AS `date`
	FROM
		{$this->registry['table.user_friend']} `friend`
	WHERE
		`friend`.`userId` = {$this->user->id}
SQL;

			if($scope > 0)
			{
				$sql.= 'AND
							(`friend`.`friendId` = ' . $this->user->id . ' OR `friend`.`groupId` = ' . $scope . ')';
			}

			$this->sql->query($sql);
		}
	}
}

