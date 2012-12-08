<?php
/*
 *  $Id: message.php 880 2012-10-27 13:14:26Z k42b3.x@googlemail.com $
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

namespace user\api\activity;

use AmunService_User_Activity_Handler;
use Amun_Module_ApiAbstract;
use Amun_Sql_Table_Registry;
use Amun_User;
use Exception;
use PSX_Atom;
use PSX_Atom_Entry;
use PSX_Base;
use PSX_Data_Exception;
use PSX_Data_Message;
use PSX_Data_ReaderInterface;
use PSX_Data_WriterInterface;
use PSX_Data_Writer_Atom;
use PSX_DateTime;
use PSX_Sql;
use PSX_Sql_Condition;
use PSX_Urn;

/**
 * message
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   module
 * @package    admin
 * @subpackage user_activity
 * @version    $Revision: 880 $
 */
class message extends Amun_Module_ApiAbstract
{
	public function onGet()
	{
		$msg = new PSX_Data_Message('Method not allowed', false);

		$this->setResponse($msg, null, 405);
	}

	public function onPost()
	{
		if($this->getProvider()->hasViewRight())
		{
			try
			{
				$contentType = PSX_Base::getRequestHeader('Content-Type');

				switch($contentType)
				{
					case PSX_Data_Writer_Atom::$mime:

						$atom = new PSX_Atom();
						$atom->import($this->getRequest(PSX_Data_ReaderInterface::DOM));

						foreach($atom as $entry)
						{
							try
							{
								$this->insertEntry($entry);
							}
							catch(Exception $e)
							{
							}
						}

						break;

					default:

						throw new PSX_Data_Exception('Invalid content type');
						break;
				}


				$msg = new PSX_Data_Message('You have successful create a message', true);

				$this->setResponse($msg, PSX_Data_WriterInterface::XML);
			}
			catch(Exception $e)
			{
				$msg = new PSX_Data_Message($e->getMessage(), false);

				$this->setResponse($msg, PSX_Data_WriterInterface::XML);
			}
		}
		else
		{
			$msg = new PSX_Data_Message('Access not allowed', false);

			$this->setResponse($msg, PSX_Data_WriterInterface::XML, $this->user->isAnonymous() ? 401 : 403);
		}
	}

	private function insertEntry(PSX_Atom_Entry $entry)
	{
		// get global id
		$urn      = new PSX_Urn($entry->id);
		$globalId = $urn->getNss();

		// get author of the entry
		$author = current($entry->author);

		if(!empty($author))
		{
			$urn = new PSX_Urn($author['uri']);
			$con = new PSX_Sql_Condition();
			$con->add('globalId', '=', $urn->getNss());
			$con->add('name', '=', $author['name']);

			$userId  = $this->sql->select($this->registry['table.user_account'], array('id'), $con, PSX_Sql::SELECT_FIELD);
			$user    = new Amun_User($userId, $this->registry);
			$handler = new AmunService_User_Activity_Handler($user);
		}
		else
		{
			throw new PSX_Data_Exception('No author set');
		}

		// get threading extension
		$thread = $entry->getElement()->getElementsByTagNameNS('http://purl.org/syndication/thread/1.0', 'in-reply-to');
		$refId  = 0;

		if($thread->length > 0)
		{
			// search for referenced activity globalId
			$ref   = $thread->item(0)->getAttribute('ref');
			$urn   = new PSX_Urn($ref);

			$con   = new PSX_Sql_Condition(array('globalId', '=', $urn->getNss()));
			$refId = Amun_Sql_Table_Registry::get('User_Activity')->getField('id', $con);

			if(empty($refId))
			{
				throw new PSX_Data_Exception('Invalid referenced id');
			}
		}

		$activity           = Amun_Sql_Table_Registry::get('User_Activity')->getRecord();
		$activity->globalId = $globalId;
		$activity->parentId = $refId;
		$activity->table    = 'amun_user_activity';
		$activity->verb     = 'add';
		$activity->summary  = $entry->content;
		$activity->date     = $entry->updated->format(PSX_DateTime::SQL);

		$handler->create($activity);
	}
}


