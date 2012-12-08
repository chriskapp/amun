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
 * Amun_User_Friend_Handler
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   Amun
 * @package    Amun_User_Friend
 * @version    $Revision: 880 $
 */
class AmunService_User_Friend_Handler extends Amun_Data_HandlerAbstract
{
	public function create(PSX_Data_RecordInterface $record)
	{
		if($record->hasFields('friendId'))
		{
			$record->userId = $this->user->id;


			// check id
			if($this->user->id == $record->friendId)
			{
				throw new PSX_Data_Exception('You can not establish a relation to yourself');
			}


			// determine status
			$record->status = $this->getStatus($record);


			// check whether friend is an remote account if yes send an
			// friend request to the remote host
			$this->handleRemoteSubscription($record);


			$date = new DateTime('NOW', $this->registry['core.default_timezone']);

			$record->date = $date->format(PSX_DateTime::SQL);


			$this->table->insert($record->getData());

			$friendId = $this->sql->getLastInsertId();


			// build relation
			if($record->status == AmunService_User_Friend_Record::NORMAL)
			{
				$con = new PSX_Sql_Condition();
				$con->add('userId', '=', $record->friendId);
				$con->add('friendId', '=', $this->user->id);

				$this->table->update(array(

					'status' => AmunService_User_Friend_Record::NORMAL,
					'date'   => $date->format(PSX_DateTime::SQL),

				), $con);
			}


			$record->id = $friendId;

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


			$this->notify(Amun_Data_RecordAbstract::DELETE, $record);


			return $record;
		}
		else
		{
			throw new PSX_Data_Exception('Missing field in record');
		}
	}

	public function remote(PSX_Data_RecordInterface $record)
	{
		if($record->hasFields('mode', 'host', 'name'))
		{
			switch($record->mode)
			{
				case 'request':
				case 'accept':
				case 'deny':

					$methodName = 'handle' . ucfirst($record->mode);
					$callback   = array($this, $methodName);

					if(is_callable($callback))
					{
						return call_user_func_array($callback, array($record));
					}
					else
					{
						throw new PSX_Data_Exception('Could not call method "' . $methodName . '"');
					}

					break;

				default:

					throw new PSX_Data_Exception('Invalid mode');
					break;
			}
		}
		else
		{
			throw new PSX_Data_Exception('Missing field in record');
		}
	}

	/**
	 * Determines the status of an friendship request. Its a bi-directional
	 * system so the friendship relation is only established if both users has
	 * accepted the relation
	 *
	 * @return integer
	 */
	protected function getStatus(PSX_Data_RecordInterface $record)
	{
		$con = new PSX_Sql_Condition();
		$con->add('userId', '=', $record->friendId);
		$con->add('friendId', '=', $record->userId);

		$count = $this->table->count($con);

		return $count > 0 ? AmunService_User_Friend_Record::NORMAL : AmunService_User_Friend_Record::REQUEST;
	}

	/**
	 * If the status of the friendhip request is "REQUEST" then we look whether
	 * the user wich receives the friendship request is a remote user. If its a
	 * remote user we send an notification to the website that the user has
	 * received a friendship request. If the status is "NORMAL" the request was
	 * accepted. If the user who has made the request was an remote user we send
	 * and notification to the website that the request was accepted.
	 *
	 * @param PSX_Data_RecordInterface $record
	 * @return void
	 */
	protected function handleRemoteSubscription(PSX_Data_RecordInterface $record)
	{
		$cred = null;

		switch($record->status)
		{
			// a remote user wants to request a user as friend. We must notify
			// the remote website about the friendship request
			case AmunService_User_Friend_Record::REQUEST:

				if($record->getUser()->status == AmunService_User_Account_Record::REMOTE)
				{
					$url  = new PSX_Url($record->getUser()->getHost()->url);
					$mode = Amun_Relation::REQUEST;
					$host = $this->base->getHost();
					$name = $record->getFriend()->name;
					$cred = $record->getUser()->getRemoteCredentials();
				}

				break;

			// a user accepted a friendship request where the initiator was an
			// remote user we must inform the remote website that the request
			// was accepted
			case AmunService_User_Friend_Record::NORMAL:

				if($record->getFriend()->status == AmunService_User_Account_Record::REMOTE)
				{
					$url  = new PSX_Url($record->getFriend()->getHost()->url);
					$mode = Amun_Relation::ACCEPT;
					$host = $this->base->getHost();
					$name = $record->getUser()->name;
					$cred = $record->getFriend()->getRemoteCredentials();
				}

				break;
		}

		if($cred instanceof PSX_Oauth_Provider_Data_Consumer)
		{
			$http = new PSX_Http(new PSX_Http_Handler_Curl());

			$relation = new Amun_Relation($http, $cred);
			$relation->request($url, $mode, $host, $name);
		}
	}

	/**
	 * Is called if an user has made a friendship request on an remote website.
	 * The website makes a call to the api/user/friend/relation inorder to
	 * inform us that the friendship request was made. We make an webfinger
	 * request to the host and check whether the user actually exists. If the
	 * user exists on the remote website we create the friend as remote user
	 * in our user account table and create a relation to this user.
	 *
	 * @param PSX_Data_RecordInterface $record
	 * @return boolean
	 */
	protected function handleRequest(PSX_Data_RecordInterface $record)
	{
		$sql = <<<SQL
SELECT

	`host`.`id`       AS `hostId`,
	`host`.`name`     AS `hostName`,
	`host`.`template` AS `hostTemplate`

	FROM {$this->registry['table.core_host']} `host`

		WHERE `host`.`name` = ?
SQL;

		$row = $this->sql->getRow($sql, array($record->host));

		if(!empty($row))
		{
			// request profile url
			$email    = $record->name . '@' . $row['hostName'];
			$profile  = $this->getAcctProfile($email, $row['hostTemplate']);
			$identity = PSX_OpenId::normalizeIdentifier($profile['url']);


			// create remote user if not exists
			$con      = new PSX_Sql_Condition(array('identity', '=', sha1(Amun_Security::getSalt() . $identity)));
			$friendId = $this->sql->select($this->registry['table.user_account'], array('id'), $con, PSX_Sql::SELECT_FIELD);

			if(empty($friendId))
			{
				$handler = new AmunService_User_Account_Handler($this->user);

				$account = Amun_Sql_Table_Registry::get('User_Account')->getRecord();
				$account->globalId = $profile['id'];
				$account->setGroupId($this->registry['core.default_user_group']);
				$account->setHostId($row['hostId']);
				$account->setStatus(AmunService_User_Account_Record::REMOTE);
				$account->setIdentity($identity);
				$account->setName($profile['name']);
				$account->setPw(Amun_Security::generatePw());

				$account  = $handler->create($account);
				$friendId = $account->id;
			}


			// create relation
			$friend = Amun_Sql_Table_Registry::get('User_Friend')->getRecord();
			$friend->friendId = $friendId;

			return $this->create($friend);
		}
		else
		{
			throw new PSX_Data_Exception('Invalid host');
		}
	}

	/**
	 * If a user on an remote website accepts our friendship request the website
	 * makes a call to the api/user/friend/relation inorder to inform us that
	 * the relation was accepted. If the user exists we add a relation and set
	 * the status
	 *
	 * @param PSX_Data_RecordInterface $record
	 * @return boolean
	 */
	protected function handleAccept(PSX_Data_RecordInterface $record)
	{
		$sql = <<<SQL
SELECT

	`account`.`id`    AS `accountId`,
	`host`.`id`       AS `hostId`,
	`host`.`name`     AS `hostName`,
	`host`.`template` AS `hostTemplate`

	FROM {$this->registry['table.user_account']} `account`

		INNER JOIN {$this->registry['table.core_host']} `host`

		ON `account`.`hostId` = `host`.`id`

			WHERE `account`.`name` = ?

			AND `host`.`name` = ?

				AND `account`.`status` = ?
SQL;

		$row = $this->sql->getRow($sql, array($record->name, $record->host, AmunService_User_Account_Record::REMOTE));

		if(!empty($row))
		{
			// create relation
			$date = new DateTime('NOW', $this->registry['core.default_timezone']);

			$this->table->insert(array(

				'status'   => AmunService_User_Friend_Record::NORMAL,
				'userId'   => $row['accountId'],
				'friendId' => $this->user->id,
				'date'     => $date->format(PSX_DateTime::SQL),

			));


			// update status
			$con = new PSX_Sql_Condition();
			$con->add('userId', '=', $this->user->id);
			$con->add('friendId', '=', $row['accountId']);

			$this->table->update(array(

				'status' => AmunService_User_Friend_Record::NORMAL,
				'date'   => $date->format(PSX_DateTime::SQL),

			), $con);


			return true;
		}
		else
		{
			throw new PSX_Data_Exception('Account does not exist');
		}
	}

	protected function handleDeny(PSX_Data_RecordInterface $record)
	{
		throw new PSX_Data_Exception('Not implemented yet');
	}

	protected function getAcctProfile($email, $lrddTemplate)
	{
		$http      = new PSX_Http(new PSX_Http_Handler_Curl());
		$webfinger = new PSX_Webfinger($http);


		// check subject
		$acct = 'acct:' . $email;
		$xrd  = $webfinger->getLrdd($acct, $lrddTemplate);

		if(strcmp($xrd->getSubject(), $acct) !== 0)
		{
			throw new PSX_Webfinger_Exception('Invalid subject');
		}


		// get properties
		$profile         = array();
		$profile['id']   = $xrd->getPropertyValue('http://ns.amun-project.org/2011/meta/id');
		$profile['name'] = $xrd->getPropertyValue('http://ns.amun-project.org/2011/meta/name');
		$profile['url']  = $xrd->getLinkHref('profile');


		// check data
		if(isset($profile['id']) && isset($profile['name']) && isset($profile['url']))
		{
			return $profile;
		}
		else
		{
			throw new PSX_Webfinger_Exception('Could not find profile with necessary data');
		}
	}
}


