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

namespace AmunService\User\Api\Activity;

use AmunService\User\Activity\Handler;
use Amun\Module\ApiAbstract;
use Amun\DataFactory;
use Amun\User;
use Amun\Exception;
use Amun\Base;
use PSX\Atom;
use PSX\Atom\Entry;
use PSX\Data;
use PSX\Data\ReaderInterface;
use PSX\Data\WriterInterface;
use PSX\Data\Writer;
use PSX\DateTime;
use PSX\Sql;
use PSX\Sql\Condition;
use PSX\Urn;

/**
 * Message
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/gpl.html GPLv3
 * @link    http://amun.phpsx.org
 */
class Message extends ApiAbstract
{
	public function onGet()
	{
		$msg = new Data\Message('Method not allowed', false);

		$this->setResponse($msg, null, 405);
	}

	public function onPost()
	{
		if($this->user->hasRight('user_activity_add'))
		{
			try
			{
				$contentType = Base::getRequestHeader('Content-Type');

				switch($contentType)
				{
					case Writer\Atom::$mime:

						$atom = new Atom();
						$atom->import($this->getRequest(ReaderInterface::DOM));

						foreach($atom as $entry)
						{
							try
							{
								$this->insertEntry($entry);
							}
							catch(\Exception $e)
							{
							}
						}

						break;

					default:
						throw new Exception('Invalid content type');
						break;
				}


				$msg = new Data\Message('You have successful create a message', true);

				$this->setResponse($msg, WriterInterface::XML);
			}
			catch(Exception $e)
			{
				$msg = new Data\Message($e->getMessage(), false);

				$this->setResponse($msg, WriterInterface::XML);
			}
		}
		else
		{
			$msg = new Data\Message('Access not allowed', false);

			$this->setResponse($msg, WriterInterface::XML, $this->user->isAnonymous() ? 401 : 403);
		}
	}

	private function insertEntry(Entry $entry)
	{
		// get global id
		$urn      = new Urn($entry->id);
		$globalId = $urn->getNss();

		// get author of the entry
		$author = current($entry->author);

		if(!empty($author))
		{
			$urn = new Urn($author['uri']);
			$con = new Condition();
			$con->add('globalId', '=', $urn->getNss());
			$con->add('name', '=', $author['name']);

			$userId  = $this->sql->select($this->registry['table.user_account'], array('id'), $con, Sql::SELECT_FIELD);
			$user    = new User($userId, $this->registry);
			$handler = new Handler($user);
		}
		else
		{
			throw new Exception('No author set');
		}

		// get threading extension
		$thread = $entry->getElement()->getElementsByTagNameNS('http://purl.org/syndication/thread/1.0', 'in-reply-to');
		$refId  = 0;

		if($thread->length > 0)
		{
			// search for referenced activity globalId
			$ref   = $thread->item(0)->getAttribute('ref');
			$urn   = new Urn($ref);

			$con   = new Condition(array('globalId', '=', $urn->getNss()));
			$refId = $this->hm->getTable('AmunService\User\Activity')->getField('id', $con);

			if(empty($refId))
			{
				throw new Exception('Invalid referenced id');
			}
		}

		$activity           = $this->hm->getTable('AmunService\User\Activity')->getRecord();
		$activity->globalId = $globalId;
		$activity->parentId = $refId;
		$activity->table    = 'amun_user_activity';
		$activity->verb     = 'add';
		$activity->summary  = $entry->content;
		$activity->date     = $entry->updated->format(DateTime::SQL);

		$handler->create($activity);
	}
}


