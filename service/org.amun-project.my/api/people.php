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

namespace my\api;

use Amun\Base;
use Amun\Module\RestAbstract;
use Amun\Sql\Table\Registry;
use Amun\Exception;
use Amun\DataFactory;
use PSX\DateTime;
use PSX\Data\Message;
use PSX\Data\WriterInterface;
use PSX\Data\WriterResult;
use PSX\Sql;

/**
 * people
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/gpl.html GPLv3
 * @link    http://amun.phpsx.org
 */
class people extends RestAbstract
{
	/**
	 * Returns informations about the current loggedin user
	 *
	 * @httpMethod GET
	 * @path /{userId}
	 * @nickname getPeople
	 * @responseClass PSX_Data_ResultSet
	 */
	public function getPeople()
	{
		if($this->user->hasRight('my_view'))
		{
			try
			{
				$userId = $this->getUriFragments('userId');

				if(!empty($userId))
				{
					$this->userId = $userId == '@me' ? $this->user->id : intval($userId);
				}
				else
				{
					$this->userId = $this->user->id;
				}

				$params = $this->getRequestParams();
				$con    = $this->getRequestCondition();
				$con->add('userId', '=', $this->userId);

				$resultSet = $this->getHandler('User_Friend')->getResultSet(array(), 
					$params['startIndex'], 
					$params['count'], 
					$params['sortBy'], 
					$params['sortOrder'], 
					$con,
					Sql::FETCH_OBJECT, 
					'\AmunService\My\People', 
					array(DataFactory::getTable('User_Friend'), $this->getContainer()));

				$this->setResponse($resultSet);
			}
			catch(\Exception $e)
			{
				$msg = new Message($e->getMessage(), false);

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
		$msg = new Message('Create a person record is not possible', false);

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
		$msg = new Message('Update a person record is not possible', false);

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
		$msg = new Message('Delete a person record is not possible', false);

		$this->setResponse($msg, null, 500);
	}

	protected function setWriterConfig(WriterResult $writer)
	{
		switch($writer->getType())
		{
			case WriterInterface::ATOM:

				$updated = $this->sql->getField('SELECT `date` FROM ' . $this->registry['table.user_friend'] . ' ORDER BY `date` DESC LIMIT 1');

				$title   = 'Friend';
				$id      = 'urn:uuid:' . $this->base->getUUID('user:friend');
				$updated = new DateTime($updated, $this->registry['core.default_timezone']);

				$writer = $writer->getWriter();
				$writer->setConfig($title, $id, $updated);
				$writer->setGenerator('amun ' . Base::getVersion());

				if(!empty($this->config['amun_hub']))
				{
					$writer->addLink($this->config['amun_hub'], 'hub');
				}

				break;
		}
	}
}
