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

namespace AmunService\User\Activity;

use Amun\DataFactory;
use Amun\Data\HandlerAbstract;
use Amun\Data\RecordAbstract;
use Amun\Exception;
use Amun\Security;
use AmunService\Core\Approval;
use AmunService\User\Activity\Receiver;
use PSX\Atom\Entry;
use PSX\DateTime;
use PSX\Data\RecordInterface;
use PSX\Data\ResultSet;
use PSX\Sql;
use PSX\Sql\Condition;
use PSX\Sql\Join;
use DOMElement;

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
class Handler extends HandlerAbstract
{
	public function getPrivateResultSet($userId, array $fields, $startIndex = 0, $count = 16, $sortBy = null, $sortOrder = null, Condition $con = null, $mode = 0, $class = null, array $args = array())
	{
		$startIndex = $startIndex !== null ? (integer) $startIndex : 0;
		$count      = !empty($count)       ? (integer) $count      : 16;
		$sortBy     = $sortBy     !== null ? $sortBy               : 'date';
		$sortOrder  = $sortOrder  !== null ? (integer) $sortOrder  : Sql::SORT_DESC;

		$select = $this->table
			->select(array('id', 'parentId', 'status', 'verb', 'summary', 'date'))
			->join(Join::INNER, DataFactory::getTable('User_Activity_Receiver')
				->select(array('id', 'status', 'activityId', 'userId', 'date'), 'receiver'),
				'1:n'
			)
			->join(Join::INNER, DataFactory::getTable('User_Account')
				->select(array('name', 'profileUrl', 'thumbnailUrl'), 'author')
			)
			->where('receiverUserId', '=', $userId)
			->where('parentId', '=', 0)
			->orderBy($sortBy, $sortOrder)
			->limit($startIndex, $count);

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

		$totalResults = $select->getTotalResults();
		$entries      = $select->getAll($mode, $class, $args);
		$resultSet    = new ResultSet($totalResults, $startIndex, $count, $entries);

		return $resultSet;
	}

	public function getPublicResultSet($userId, array $fields, $startIndex = 0, $count = 16, $sortBy = null, $sortOrder = null, Condition $con = null, $mode = 0, $class = null, array $args = array())
	{
		$startIndex = $startIndex !== null ? (integer) $startIndex : 0;
		$count      = !empty($count)       ? (integer) $count      : 16;
		$sortBy     = $sortBy     !== null ? $sortBy               : 'date';
		$sortOrder  = $sortOrder  !== null ? (integer) $sortOrder  : Sql::SORT_DESC;

		$select = DataFactory::getTable('User_Activity_Receiver')
			->select(array('id', 'status', 'activityId', 'userId', 'date'), 'receiver')
			->join(Join::INNER, DataFactory::getTable('User_Activity')
				->select(array('id', 'globalId', 'parentId', 'userId', 'refId', 'table', 'status', 'scope', 'verb', 'summary', 'date'))
				->join(Join::INNER, DataFactory::getTable('User_Account')
					->select(array('globalId', 'name', 'profileUrl', 'thumbnailUrl'), 'author')
				)
			)
			->where('receiverUserId', '=', $userId)
			->where('receiverStatus', '=', Receiver\Record::VISIBLE)
			->where('parentId', '=', 0)
			->where('scope', '=', 0)
			->where('userId', '=', $userId)
			->orderBy($sortBy, $sortOrder)
			->limit($startIndex, $count);

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

		$totalResults = $select->getTotalResults();
		$entries      = $select->getAll($mode, $class, $args);
		$resultSet    = new ResultSet($totalResults, $startIndex, $count, $entries);

		return $resultSet;
	}

	public function create(RecordInterface $record)
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

				$record->date = $date->format(DateTime::SQL);
			}


			$this->table->insert($record->getData());


			$record->id = $this->sql->getLastInsertId();

			$this->notify(RecordAbstract::INSERT, $record);


			$this->sendToReceiver($record);


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


			//$this->notify(RecordAbstract::DELETE, $record);


			return $record;
		}
		else
		{
			throw new Exception('Missing field in record');
		}
	}

	public function callback(Entry $entry)
	{
		$dom  = $entry->getDom();
		$verb = $dom->getElementsByTagNameNS('http://activitystrea.ms/spec/1.0/', 'verb')->item(0);

		if($verb instanceof DOMElement)
		{
			$activity = DataFactory::getTable('User_Activity')->getRecord();
			$activity->setVerb($verb->nodeValue);
			$activity->setSummary($entry->content);
			$activity->table = 'amun_user_activity';

			return $this->create($activity);
		}
		else
		{
			throw new Exception('Verb not set');
		}
	}

	protected function getDefaultSelect()
	{
		return $this->table
			->select(array('id', 'globalId', 'parentId', 'userId', 'title', 'summary', 'date'))
			->join(Join::INNER, DataFactory::getTable('User_Account')
				->select(array('name', 'profileUrl', 'thumbnailUrl'), 'author')
			);
	}

	private function sendToReceiver(RecordInterface $record)
	{
		$activityId = isset($record->id)    ? (integer) $record->id    : null;
		$scope      = isset($record->scope) ? (integer) $record->scope : 0;

		if(!empty($activityId))
		{
			$sql = <<<SQL
INSERT INTO 
	{$this->registry['table.user_activity_receiver']}
	(`activityId`, `userId`, `date`)
	SELECT
		{$activityId} AS `activityId`,
		`friendId`    AS `userId`,
		NOW()         AS `date`
	FROM
		{$this->registry['table.user_friend']} `friend`
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


