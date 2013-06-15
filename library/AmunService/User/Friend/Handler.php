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

namespace AmunService\User\Friend;

use Amun\DataFactory;
use Amun\Data\HandlerAbstract;
use Amun\Data\RecordAbstract;
use Amun\Exception;
use Amun\Relation;
use Amun\Security;
use AmunService\Core\Approval;
use AmunService\User\Account;
use PSX\DateTime;
use PSX\Data\RecordInterface;
use PSX\Data\ResultSet;
use PSX\Sql;
use PSX\Sql\Condition;
use PSX\Sql\Join;
use PSX\Url;
use PSX\Http;
use PSX\OpenId;
use PSX\Webfinger;
use PSX\Oauth\Provider\Data\Consumer;

/**
 * Handler
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/gpl.html GPLv3
 * @link    http://amun.phpsx.org
 */
class Handler extends HandlerAbstract
{
	public function getRequestResultSet($userId, array $fields, $startIndex = 0, $count = 16, $sortBy = null, $sortOrder = null, Condition $con = null, $mode = 0, $class = null, array $args = array())
	{
		$startIndex = $startIndex !== null ? (integer) $startIndex : 0;
		$count      = !empty($count)       ? (integer) $count      : 16;
		$sortBy     = $sortBy     !== null ? $sortBy               : 'date';
		$sortOrder  = $sortOrder  !== null ? (integer) $sortOrder  : Sql::SORT_DESC;

		$select = $this->table
			->select(array('id', 'status', 'date'))
			->join(Join::INNER, DataFactory::getTable('User_Account')
				->select(array('id', 'globalId', 'name', 'profileUrl', 'thumbnailUrl', 'updated', 'date'), 'author'),
				'n:1',
				'userId'
			)
			->join(Join::INNER, DataFactory::getTable('User_Account')
				->select(array('id', 'globalId', 'name', 'profileUrl', 'thumbnailUrl', 'updated', 'date'), 'friend'),
				'n:1',
				'friendId'
			)
			->where('friendId', '=', $userId)
			->where('status', '=', Record::REQUEST);

		if(!empty($fields))
		{
			$select->select($fields);
		}

		if($con !== null && $con->hasCondition())
		{
			$values = $con->toArray();

			foreach($values as $row)
			{
				$select->where($row[0], $row[1], $row[2]);
			}
		}

		if($mode == Sql::FETCH_OBJECT && $class === null)
		{
			$class = $this->getClassName();
		}

		if($mode == Sql::FETCH_OBJECT && empty($args))
		{
			$args = $this->getClassArgs();
		}

		$totalResults = $select->getTotalResults();
		$entries      = $select->getAll($mode, $class, $args);
		$resultSet    = new ResultSet($totalResults, $startIndex, $count, $entries);

		return $resultSet;
	}

	public function getPendingResultSet($userId, array $fields, $startIndex = 0, $count = 16, $sortBy = null, $sortOrder = null, Condition $con = null, $mode = 0, $class = null, array $args = array())
	{
		$startIndex = $startIndex !== null ? (integer) $startIndex : 0;
		$count      = !empty($count)       ? (integer) $count      : 16;
		$sortBy     = $sortBy     !== null ? $sortBy               : 'date';
		$sortOrder  = $sortOrder  !== null ? (integer) $sortOrder  : Sql::SORT_DESC;

		$select = $this->table
			->select(array('id', 'status', 'date'))
			->join(Join::INNER, DataFactory::getTable('User_Account')
				->select(array('id', 'globalId', 'name', 'profileUrl', 'thumbnailUrl', 'updated', 'date'), 'author'),
				'n:1',
				'userId'
			)
			->join(Join::INNER, DataFactory::getTable('User_Account')
				->select(array('id', 'globalId', 'name', 'profileUrl', 'thumbnailUrl', 'updated', 'date'), 'friend'),
				'n:1',
				'friendId'
			)
			->where('userId', '=', $userId)
			->where('status', '=', Record::REQUEST);

		if(!empty($fields))
		{
			$select->select($fields);
		}

		if($con !== null && $con->hasCondition())
		{
			$values = $con->toArray();

			foreach($values as $row)
			{
				$select->where($row[0], $row[1], $row[2]);
			}
		}

		if($mode == Sql::FETCH_OBJECT && $class === null)
		{
			$class = $this->getClassName();
		}

		if($mode == Sql::FETCH_OBJECT && empty($args))
		{
			$args = $this->getClassArgs();
		}

		$totalResults = $select->getTotalResults();
		$entries      = $select->getAll($mode, $class, $args);
		$resultSet    = new ResultSet($totalResults, $startIndex, $count, $entries);

		return $resultSet;
	}

	public function create(RecordInterface $record)
	{
		if($record->hasFields('friendId'))
		{
			$record->userId = $this->user->getId();


			// check id
			if($this->user->getId() == $record->friendId)
			{
				throw new Exception('You can not establish a relation to yourself');
			}


			// determine status
			$record->status = $this->getStatus($record);


			// check whether friend is an remote account if yes send an
			// friend request to the remote host
			$this->handleRemoteSubscription($record);


			$date = new DateTime('NOW', $this->registry['core.default_timezone']);

			$record->date = $date->format(DateTime::SQL);


			$this->table->insert($record->getData());

			$friendId = $this->sql->getLastInsertId();


			// build relation
			if($record->status == Record::NORMAL)
			{
				$con = new Condition();
				$con->add('userId', '=', $record->friendId);
				$con->add('friendId', '=', $this->user->getId());

				$this->table->update(array(

					'status' => Record::NORMAL,
					'date'   => $date->format(DateTime::SQL),

				), $con);
			}


			$record->id = $friendId;

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


			//$this->notify(RecordAbstract::UPDATE, $record);


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


			$this->notify(RecordAbstract::DELETE, $record);


			return $record;
		}
		else
		{
			throw new Exception('Missing field in record');
		}
	}

	public function remote(RecordInterface $record)
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
						throw new Exception('Could not call method "' . $methodName . '"');
					}

					break;

				default:

					throw new Exception('Invalid mode');
					break;
			}
		}
		else
		{
			throw new Exception('Missing field in record');
		}
	}

	protected function getDefaultSelect()
	{
		return $this->table
			->select(array('id', 'status', 'date'))
			->join(Join::INNER, DataFactory::getTable('User_Account')
				->select(array('id', 'globalId', 'name', 'profileUrl', 'thumbnailUrl', 'updated', 'date'), 'author'),
				'n:1',
				'userId'
			)
			->join(Join::INNER, DataFactory::getTable('User_Account')
				->select(array('id', 'globalId', 'name', 'profileUrl', 'thumbnailUrl', 'updated', 'date'), 'friend'),
				'n:1',
				'friendId'
			)
			->where('status', '=', Record::NORMAL);
	}

	/**
	 * Determines the status of an friendship request. Its a bi-directional
	 * system so the friendship relation is only established if both users has
	 * accepted the relation
	 *
	 * @return integer
	 */
	protected function getStatus(RecordInterface $record)
	{
		$con = new Condition();
		$con->add('userId', '=', $record->friendId);
		$con->add('friendId', '=', $record->userId);

		$count = $this->table->count($con);

		return $count > 0 ? Record::NORMAL : Record::REQUEST;
	}

	/**
	 * If the status of the friendhip request is "REQUEST" then we look whether
	 * the user wich receives the friendship request is a remote user. If its a
	 * remote user we send an notification to the website that the user has
	 * received a friendship request. If the status is "NORMAL" the request was
	 * accepted. If the user who has made the request was an remote user we send
	 * and notification to the website that the request was accepted.
	 *
	 * @param RecordInterface $record
	 * @return void
	 */
	protected function handleRemoteSubscription(RecordInterface $record)
	{
		$cred = null;

		switch($record->status)
		{
			// a remote user wants to request a user as friend. We must notify
			// the remote website about the friendship request
			case Record::REQUEST:

				if($record->getUser()->status == Account\Record::REMOTE)
				{
					$url  = new Url($record->getUser()->getHost()->url);
					$mode = Relation::REQUEST;
					$host = $this->base->getHost();
					$name = $record->getFriend()->name;
					$cred = $record->getUser()->getRemoteCredentials();
				}

				break;

			// a user accepted a friendship request where the initiator was an
			// remote user we must inform the remote website that the request
			// was accepted
			case Record::NORMAL:

				if($record->getFriend()->status == Account\Record::REMOTE)
				{
					$url  = new Url($record->getFriend()->getHost()->url);
					$mode = Relation::ACCEPT;
					$host = $this->base->getHost();
					$name = $record->getUser()->name;
					$cred = $record->getFriend()->getRemoteCredentials();
				}

				break;
		}

		if($cred instanceof Consumer)
		{
			$http     = new Http();
			$relation = new Relation($http, $cred);
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
	 * @param RecordInterface $record
	 * @return boolean
	 */
	protected function handleRequest(RecordInterface $record)
	{
		$sql = <<<SQL
SELECT
	`host`.`id`       AS `hostId`,
	`host`.`name`     AS `hostName`,
	`host`.`template` AS `hostTemplate`
FROM 
	{$this->registry['table.core_host']} `host`
WHERE 
	`host`.`name` = ?
SQL;

		$row = $this->sql->getRow($sql, array($record->host));

		if(!empty($row))
		{
			// request profile url
			$email    = $record->name . '@' . $row['hostName'];
			$profile  = $this->getAcctProfile($email, $row['hostTemplate']);
			$identity = OpenId::normalizeIdentifier($profile['url']);


			// create remote user if not exists
			$con      = new Condition(array('identity', '=', sha1(Security::getSalt() . $identity)));
			$friendId = $this->sql->select($this->registry['table.user_account'], array('id'), $con, Sql::SELECT_FIELD);

			if(empty($friendId))
			{
				$handler = DataFactory::get('User_Account', $this->user);

				$account = $handler->getRecord();
				$account->globalId = $profile['id'];
				$account->setGroupId($this->registry['core.default_user_group']);
				$account->setHostId($row['hostId']);
				$account->setStatus(Account\Record::REMOTE);
				$account->setIdentity($identity);
				$account->setName($profile['name']);
				$account->setPw(Security::generatePw());

				$account  = $handler->create($account);
				$friendId = $account->id;
			}


			// create relation
			$friend = DataFactory::getTable('User_Friend')->getRecord();
			$friend->friendId = $friendId;

			return $this->create($friend);
		}
		else
		{
			throw new Exception('Invalid host');
		}
	}

	/**
	 * If a user on an remote website accepts our friendship request the website
	 * makes a call to the api/user/friend/relation inorder to inform us that
	 * the relation was accepted. If the user exists we add a relation and set
	 * the status
	 *
	 * @param RecordInterface $record
	 * @return boolean
	 */
	protected function handleAccept(RecordInterface $record)
	{
		$sql = <<<SQL
SELECT
	`account`.`id`    AS `accountId`,
	`host`.`id`       AS `hostId`,
	`host`.`name`     AS `hostName`,
	`host`.`template` AS `hostTemplate`
FROM 
	{$this->registry['table.user_account']} `account`
INNER JOIN 
	{$this->registry['table.core_host']} `host`
	ON `account`.`hostId` = `host`.`id`
WHERE 
	`account`.`name` = ?
AND 
	`host`.`name` = ?
AND 
	`account`.`status` = ?
SQL;

		$row = $this->sql->getRow($sql, array($record->name, $record->host, Account\Record::REMOTE));

		if(!empty($row))
		{
			// create relation
			$date = new DateTime('NOW', $this->registry['core.default_timezone']);

			$this->table->insert(array(

				'status'   => Record::NORMAL,
				'userId'   => $row['accountId'],
				'friendId' => $this->user->getId(),
				'date'     => $date->format(DateTime::SQL),

			));


			// update status
			$con = new Condition();
			$con->add('userId', '=', $this->user->getId());
			$con->add('friendId', '=', $row['accountId']);

			$this->table->update(array(

				'status' => Record::NORMAL,
				'date'   => $date->format(DateTime::SQL),

			), $con);


			return true;
		}
		else
		{
			throw new Exception('Account does not exist');
		}
	}

	protected function handleDeny(RecordInterface $record)
	{
		throw new Exception('Not implemented yet');
	}

	protected function getAcctProfile($email, $lrddTemplate)
	{
		$http      = new Http();
		$webfinger = new Webfinger($http);

		// check subject
		$acct = 'acct:' . $email;
		$xrd  = $webfinger->getLrdd($acct, $lrddTemplate);

		if(strcmp($xrd->getSubject(), $acct) !== 0)
		{
			throw new Exception('Invalid subject');
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
			throw new Exception('Could not find profile with necessary data');
		}
	}
}


