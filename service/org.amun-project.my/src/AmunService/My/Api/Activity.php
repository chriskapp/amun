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

namespace AmunService\My\Api;

use AmunService\User\Account;
use Amun\Base;
use Amun\Module\RestAbstract;
use Amun\Sql\Table\Registry;
use Amun\Exception;
use PSX\Data\Message;
use PSX\Data\WriterInterface;
use PSX\Data\WriterResult;
use PSX\Sql;

/**
 * Activity
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/gpl.html GPLv3
 * @link    http://amun.phpsx.org
 */
class Activity extends RestAbstract
{
	protected $userId;

	/**
	 * Returns informations about the current loggedin user
	 *
	 * @httpMethod GET
	 * @path /{userId}
	 * @nickname getRecords
	 * @responseClass PSX_Data_ResultSet
	 */
	public function getRecords()
	{
		if($this->user->hasRight('my_view'))
		{
			try
			{
				$userId = $this->getUriFragments('userId');

				if(!empty($userId))
				{
					$this->userId = $userId == '@me' ? $this->user->getId() : intval($userId);
				}
				else
				{
					$this->userId = $this->user->getId();
				}

				$params    = $this->getRequestParams();
				$fields    = (array) $params['fields'];
				$resultSet = $this->getHandler('AmunService\User\Activity')->getPublicResultSet($this->userId,
					array('id', 'globalId', 'parentId', 'userId', 'scope', 'verb', 'object', 'summary', 'date', 'receiverId', 'receiverStatus', 'receiverActivityId', 'receiverUserId', 'authorGlobalId', 'authorName', 'authorProfileUrl', 'authorThumbnailUrl'),
					$params['startIndex'], 
					$params['count'], 
					$params['sortBy'], 
					$params['sortOrder'], 
					$this->getRequestCondition(),
					Sql::FETCH_OBJECT);

				$this->setResponse($resultSet);
			}
			catch(Exception $e)
			{
				$msg = new Message($e->getTraceAsString(), false);

				$this->setResponse($msg);
			}
		}
		else
		{
			$msg = new Message('Access not allowed', false);

			$this->setResponse($msg, null, $this->user->isAnonymous() ? 401 : 403);
		}
	}

	/**
	 * Insert a new record
	 *
	 * @httpMethod POST
	 * @path /
	 * @nickname insertRecord
	 * @responseClass PSX_Data_Message
	 */
	public function insertRecord()
	{
		$msg = new Message('Create a activity record is not possible', false);

		$this->setResponse($msg, null, 500);
	}

	/**
	 * Update an existing record
	 *
	 * @httpMethod PUT
	 * @path /
	 * @nickname updateRecord
	 * @responseClass PSX_Data_Message
	 */
	public function updateRecord()
	{
		$msg = new Message('Update a activity record is not possible', false);

		$this->setResponse($msg, null, 500);
	}

	/**
	 * Delete an existing record
	 *
	 * @httpMethod DELETE
	 * @path /
	 * @nickname deleteRecord
	 * @responseClass PSX_Data_Message
	 */
	public function deleteRecord()
	{
		$msg = new Message('Delete a activity record is not possible', false);

		$this->setResponse($msg, null, 500);
	}

	protected function getHandler($table = null)
	{
		return parent::getHandler($table === null ? 'AmunService\User\Activity' : $table);
	}

	protected function setWriterConfig(WriterResult $writer)
	{
		switch($writer->getType())
		{
			case WriterInterface::ATOM:

				$account = $this->getHandler('AmunService\User\Account')->getOneById($this->userId, 
					array('id', 'globalId', 'name', 'profileUrl', 'thumbnailUrl', 'updated'), 
					Sql::FETCH_OBJECT);

				if($account instanceof Account\Record)
				{
					$writer = $writer->getWriter();
					$writer->setConfig($account->name . ' activities', 'urn:uuid:' . $account->globalId, $account->getUpdated());
					$writer->setGenerator('amun ' . Base::getVersion());
					$writer->addAuthor($account->name, $account->profileUrl);
					$writer->addLink($account->profileUrl, 'alternate', 'text/html');
					$writer->addLink($account->thumbnailUrl, 'avatar');
					$writer->setLogo($account->thumbnailUrl);

					if(!empty($this->config['amun_hub']))
					{
						$writer->addLink($this->config['amun_hub'], 'hub');
					}
				}
				else
				{
					throw new Exception('Invalid user account');
				}

				break;
		}
	}
}

