<?php
/*
 *  $Id: User.php 880 2012-10-27 13:14:26Z k42b3.x@googlemail.com $
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
 * Amun_User
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   Amun
 * @package    Amun_User
 * @version    $Revision: 880 $
 */
class Amun_User
{
	public $id;
	public $groupId;
	public $hostId;
	public $countryId;
	public $status;
	public $name;
	public $profileUrl;
	public $timezone;
	public $updated;
	public $date;
	public $group;
	public $rights = array();

	// optional the assigned request id
	// if logged in via oauth
	public $requestId;

	protected $config;
	protected $sql;
	protected $registry;

	public function __construct($id, Amun_Registry $registry)
	{
		$this->config   = $registry->getConfig();
		$this->sql      = $registry->getSql();
		$this->registry = $registry;


		$status = Amun_User_Account::BANNED;
		$sql    = <<<EOD
SELECT

	account.id         AS `accountId`,
	account.groupId    AS `accountGroupId`,
	account.hostId     AS `accountHostId`,
	account.countryId  AS `accountCountryId`,
	account.status     AS `accountStatus`,
	account.name       AS `accountName`,
	account.email      AS `accountEmail`,
	account.profileUrl AS `accountProfileUrl`,
	account.timezone   AS `accountTimezone`,
	account.updated    AS `accountUpdated`,
	account.date       AS `accountDate`,
	group.title        AS `groupTitle`

	FROM {$this->registry['table.user_account']} `account`

		INNER JOIN {$this->registry['table.user_group']} `group`

		ON `account`.`groupId` = `group`.`id`

			WHERE `account`.`id` = ?

			AND `account`.`status` NOT IN ({$status})
EOD;

		$row = $this->sql->getRow($sql, array($id));

		if(!empty($row))
		{
			$this->id         = $row['accountId'];
			$this->groupId    = $row['accountGroupId'];
			$this->hostId     = $row['accountHostId'];
			$this->group      = $row['groupTitle'];
			$this->countryId  = $row['accountCountryId'];
			$this->status     = $row['accountStatus'];
			$this->name       = $row['accountName'];
			$this->email      = $row['accountEmail'];
			$this->profileUrl = $row['accountProfileUrl'];
			$this->updated    = $row['accountUpdated'];
			$this->date       = $row['accountDate'];

			// set timezone
			$this->setTimezone($row['accountTimezone']);

			// update the last seen field
			$now = new DateTime('NOW', $this->registry['core.default_timezone']);
			$con = new PSX_Sql_Condition(array('id', '=', $this->id));

			$this->sql->update($this->registry['table.user_account'], array(

				'lastSeen' => $now->format(PSX_DateTime::SQL),

			), $con);

			// set user rights
			$this->setRights($this->groupId);
		}
		else
		{
			throw new Amun_User_Exception('Unknown user id');
		}
	}

	public function setRights($groupId)
	{
		$this->rights = array();

		$groupId = intval($groupId);
		$sql     = <<<SQL
SELECT

	`right`.`id`,
	`right`.`name`

	FROM {$this->registry['table.user_group_right']} `groupRight`

		INNER JOIN {$this->registry['table.user_right']} `right`

		ON `right`.`id` = `groupRight`.`rightId`

			WHERE `groupRight`.`groupId` = {$groupId}
SQL;

		$result = $this->sql->getAll($sql);

		foreach($result as $row)
		{
			$this->rights[$row['id']] = $row['name'];
		}
	}

	public function hasRight($key)
	{
		return in_array($key, $this->rights);
	}

	public function hasRightId($id)
	{
		return isset($this->rights[$id]);
	}

	public function isAdministrator()
	{
		return $this->status == Amun_User_Account::ADMINISTRATOR;
	}

	public function isRemote()
	{
		return $this->status == Amun_User_Account::REMOTE;
	}

	public function isAnonymous()
	{
		return $this->registry['core.anonymous_user'] == $this->id;
	}

	/**
	 * This method tries to figure out whether a user tries to abuse the system.
	 * Every user can insert, update or delete "security.input_limit" records
	 * in the last "security.input_interval" minutes without entering an captcha
	 * After this the user has to solve an captcha
	 *
	 * @return boolean
	 */
	public function hasInputExceeded()
	{
		if($this->isAdministrator())
		{
			return false;
		}

		$now = new DateTime('NOW', $this->registry['core.default_timezone']);
		$now->sub(new DateInterval($this->registry['security.input_interval']));

		$con = new PSX_Sql_Condition();
		$con->add('userId', '=', $this->id);
		$con->add('date', '>=', $now->format(PSX_DateTime::SQL));

		$count = $this->sql->count($this->registry['table.system_log'], $con);

		if($count > $this->registry['security.input_limit'])
		{
			$expire       = time() - $now->getTimestamp();
			$percentage   = ceil(($count * 100) / ($this->registry['security.input_limit'] * 2));
			$expire       = $expire - ($expire * ($percentage / 100));

			$lastVerified = isset($_SESSION['captcha_verified']) ? $_SESSION['captcha_verified'] : 0;
			$diff         = time() - $lastVerified;

			if($diff > $expire)
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * Returns whether the user is the owner of the $record
	 *
	 * @param PSX_Data_RecordInterface $record
	 * @return boolean
	 */
	public function isOwner(PSX_Data_RecordInterface $record)
	{
		if(!$this->isAdministrator())
		{
			return isset($record->userId) && $record->userId == $this->id;
		}
		else
		{
			return true;
		}
	}

	/**
	 * Returns whether the user is in the same group as the owner of the
	 * $record
	 *
	 * @param PSX_Data_RecordInterface $record
	 * @return boolean
	 */
	public function isGroup(PSX_Data_RecordInterface $record)
	{
		if(isset($record->userId))
		{
			$con = new PSX_Sql_Condition();
			$con->add('id', '=', $record->userId);

			$value = $this->sql->select($this->registry['table.user_account'], array('groupId'), $con, PSX_Sql::SELECT_FIELD);

			return $value == $this->groupId;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Sets the timezone for the current user. Accepts as $timezone an valid
	 * timezone identifier
	 *
	 * @param string $timezone
	 * @return void
	 */
	public function setTimezone($timezone)
	{
		if(!empty($timezone))
		{
			try
			{
				$this->timezone = new DateTimeZone($timezone);
			}
			catch(Exception $e)
			{
				$this->timezone = new DateTimeZone('UTC');
			}
		}
		else
		{
			$this->timezone = new DateTimeZone('UTC');
		}
	}

	public function getTimezone()
	{
		return $this->timezone;
	}

	public function getAccount()
	{
		return Amun_Sql_Table_Registry::get('User_Account')->getRecord($this->id);
	}

	public function hasFriend(Amun_User_Account $account)
	{
		$con = new PSX_Sql_Condition();
		$con->add('userId', '=', $this->id);
		$con->add('friendId', '=', $account->id);
		$con->add('status', '=', Amun_User_Friend::NORMAL);

		$count = $this->sql->count($this->registry['table.user_friend'], $con);

		return $count > 0;
	}

	public static function getId(PSX_Session $session, Amun_Registry $registry)
	{
		$id       = $session->get('amun_id');
		$lastSeen = $session->get('amun_t');
		$aId      = $registry['core.anonymous_user'];

		if($aId === false)
		{
			throw new Amun_User_Exception('Unknown anonymous user');
		}
		else
		{
			$aId = intval($aId);
		}

		if($id !== false && $lastSeen !== false)
		{
			$now    = time();
			$expire = $registry['core.session_expire'];

			if(($now - $lastSeen) > $expire)
			{
				$id = $aId;
			}
			else
			{
				$session->set('amun_t', time());
			}
		}
		else
		{
			$id = $aId;
		}

		return $id;
	}
}

