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

use Amun\Data\RecordAbstract;
use Amun\DataFactory;
use Amun\Exception;
use Amun\Filter;
use PSX\ActivityStream;
use PSX\DateTime;
use PSX\Data\WriterResult;
use PSX\Data\WriterInterface;

/**
 * Record
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/gpl.html GPLv3
 * @link    http://amun.phpsx.org
 */
class Record extends RecordAbstract
{
	const REQUEST = 0x1;
	const NORMAL  = 0x2;

	protected $_group;
	protected $_account;
	protected $_friend;
	protected $_date;

	public function setId($id)
	{
		$id = $this->_validate->apply($id, 'integer', array(new Filter\Id($this->_table)), 'id', 'Id');

		if(!$this->_validate->hasError())
		{
			$this->id = $id;
		}
		else
		{
			throw new Exception($this->_validate->getLastError());
		}
	}

	public function setGroupId($groupId)
	{
		$groupId = $this->_validate->apply($groupId, 'integer', array(new Filter\Id($this->_hm->getTable('AmunService\User\Friend\Group'))), 'groupId', 'Group Id');

		if(!$this->_validate->hasError())
		{
			$this->groupId = $groupId;
		}
		else
		{
			throw new Exception($this->_validate->getLastError());
		}
	}

	public function setFriendId($friendId)
	{
		$friendId = $this->_validate->apply($friendId, 'integer', array(new Filter\Id($this->_hm->getTable('AmunService\User\Friend'))), 'friendId', 'Friend Id');

		if(!$this->_validate->hasError())
		{
			$this->friendId = $friendId;
		}
		else
		{
			throw new Exception($this->_validate->getLastError());
		}
	}

	public function getId()
	{
		return $this->_base->getUrn('user', 'friend', $this->id);
	}

	public function getGroup()
	{
		if($this->_group === null)
		{
			$this->_group = $this->_hm->getHandler('AmunService\User\Friend\Group')->getRecord($this->groupId);
		}

		return $this->_group;
	}

	public function getUser()
	{
		if($this->_account === null)
		{
			$this->_account = $this->_hm->getHandler('AmunService\User\Account')->getRecord($this->userId);
		}

		return $this->_account;
	}

	public function getFriend()
	{
		if($this->_friend === null)
		{
			$this->_friend = $this->_hm->getHandler('AmunService\User\Account')->getRecord($this->friendId);
		}

		return $this->_friend;
	}

	public function getDate()
	{
		if($this->_date === null)
		{
			$this->_date = new DateTime($this->date, $this->_registry['core.default_timezone']);
		}

		return $this->_date;
	}

	public function export(WriterResult $result)
	{
		switch($result->getType())
		{
			case WriterInterface::ATOM:
				$entry = $result->getWriter()->createEntry();

				$entry->setTitle($this->friendName);
				$entry->setId('urn:uuid:' . $this->friendGlobalId);
				$entry->setUpdated($this->getDate());
				$entry->addAuthor($this->authorName, $this->authorProfileUrl);
				$entry->addLink($this->friendProfileUrl, 'alternate', 'text/html');

				return $entry;
				break;

			case WriterInterface::JAS:
				$image = new ActivityStream\MediaLink();
				$image->setUrl($this->authorThumbnailUrl);

				$actor = new ActivityStream\Object();
				$actor->setObjectType('person');
				$actor->setDisplayName($this->authorName);
				$actor->setUrl($this->authorProfileUrl);
				$actor->setImage($image);

				$object = new ActivityStream\Object();
				$object->setObjectType('person');
				$object->setId('urn:uuid:' . $this->friendGlobalId);
				$object->setDisplayName($this->friendName);
				$object->setUrl($this->friendProfileUrl);
				$object->setPublished($this->getDate());

				$activity = new ActivityStream\Activity();
				$activity->setActor($actor);
				$activity->setVerb('make-friend');
				$activity->setObject($object);

				return $activity;
				break;

			default:
				return parent::export($result);
				break;
		}
	}

	public static function getStatus($status = false)
	{
		$s = array(

			self::REQUEST => 'Request',
			self::NORMAL  => 'Normal',

		);

		if($status !== false)
		{
			$status = intval($status);

			if(array_key_exists($status, $s))
			{
				return $s[$status];
			}
			else
			{
				return false;
			}
		}
		else
		{
			return $s;
		}
	}
}

