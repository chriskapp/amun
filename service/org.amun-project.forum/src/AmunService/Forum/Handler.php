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

namespace AmunService\Forum;

use Amun\DataFactory;
use Amun\Data\ApproveHandlerAbstract;
use Amun\Data\RecordAbstract;
use Amun\Exception;
use AmunService\Core\Approval;
use PSX\DateTime;
use PSX\Data\RecordInterface;
use PSX\Data\ResultSet;
use PSX\Sql;
use PSX\Sql\Condition;
use PSX\Sql\Join;

/**
 * Handler
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/gpl.html GPLv3
 * @link    http://amun.phpsx.org
 */
class Handler extends ApproveHandlerAbstract
{
	public function getThreadsByPageId($pageId)
	{
		$sql = <<<SQL
SELECT
	`forum`.`id`,
	`forum`.`sticky`,
	`forum`.`closed`,
	`forum`.`pageId`,
	`forum`.`title`,
	`forum`.`url`,
	`forum`.`date`,
	`author`.`name` AS `authorName`,
	`author`.`profileUrl` AS `authorProfileUrl`,
	(SELECT 
		COUNT(`id`) 
	FROM 
		{$this->registry['table.comment']} `comment`
	WHERE 
		`comment`.`pageId` = ?
	AND
		`comment`.`refId` = `forum`.`id`) AS `replyCount`,
	(SELECT 
		COUNT(`id`) 
	FROM 
		{$this->registry['table.comment']} `comment`
	INNER JOIN
		{$this->registry['table.user_account']} `commentAuthor`
		ON ``
	WHERE 
		`comment`.`pageId` = ?
	AND
		`comment`.`refId` = `forum`.`id`) AS `lastReply`
FROM
	{$this->registry['table.forum']} `forum`
INNER JOIN
	{$this->registry['table.user_account']} `author`
	ON `userId` = `author`.`id`
WHERE
	`forum`.`pageId` = ?
ORDER BY
	`forum`.`sticky` DESC, `forum`.`id` DESC
SQL;


	}

	public function create(RecordInterface $record)
	{
		if($record->hasFields('pageId', 'urlTitle', 'title', 'text'))
		{
			$record->globalId = $this->base->getUUID('service:forum:' . $record->pageId . ':' . uniqid());
			$record->userId   = $this->user->getId();

			$date = new DateTime('NOW', $this->registry['core.default_timezone']);

			$record->date = $date->format(DateTime::SQL);


			if(isset($record->sticky) && !$this->user->hasRight('forum_sticky'))
			{
				unset($record->sticky);
			}

			if(isset($record->closed) && !$this->user->hasRight('forum_close'))
			{
				unset($record->closed);
			}


			if(!$this->hasApproval($record))
			{
				$this->table->insert($record->getData());


				$record->id = $this->sql->getLastInsertId();

				$this->notify(RecordAbstract::INSERT, $record);
			}
			else
			{
				$this->approveRecord(Approval\Record::INSERT, $record);
			}

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
			if(!$this->hasApproval($record))
			{
				if(isset($record->sticky) && !$this->user->hasRight('forum_sticky'))
				{
					unset($record->sticky);
				}

				if(isset($record->closed) && !$this->user->hasRight('forum_close'))
				{
					unset($record->closed);
				}


				$con = new Condition(array('id', '=', $record->id));

				$this->table->update($record->getData(), $con);


				$this->notify(RecordAbstract::UPDATE, $record);
			}
			else
			{
				$this->approveRecord(Approval\Record::UPDATE, $record);
			}

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
			if(!$this->hasApproval($record))
			{
				$con = new Condition(array('id', '=', $record->id));

				$this->table->delete($con);


				$this->notify(RecordAbstract::DELETE, $record);
			}
			else
			{
				$this->approveRecord(Approval\Record::DELETE, $record);
			}

			return $record;
		}
		else
		{
			throw new Exception('Missing field in record');
		}
	}

	protected function getDefaultSelect()
	{
		return $this->table
			->select(array('id', 'globalId', 'pageId', 'userId', 'sticky', 'closed', 'urlTitle', 'title', 'text', 'date', 'replyUserId', 'replyCount', 'replyDate'))
			->join(Join::INNER, $this->hm->getTable('AmunService\User\Account')
				->select(array('name', 'profileUrl'), 'author')
			)
			->join(Join::INNER, $this->hm->getTable('AmunService\Content\Page')
				->select(array('path'), 'page')
			)
			->join(Join::LEFT, $this->hm->getTable('AmunService\User\Account')
				->select(array('name', 'profileUrl'), 'reply')
			, 'n:1', 'replyUserId')
			->orderBy('sticky', Sql::SORT_DESC);
	}
}
