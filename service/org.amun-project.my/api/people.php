<?php
/*
 *  $Id: people.php 875 2012-09-30 13:51:45Z k42b3.x@googlemail.com $
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

namespace my\api;

use Amun_Base;
use Amun_Module_RestAbstract;
use Amun_Sql_Table_Registry;
use DateTime;
use Exception;
use PSX_Data_Message;
use PSX_Data_WriterInterface;
use PSX_Data_WriterResult;
use PSX_Sql;

/**
 * people
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   module
 * @package    api
 * @subpackage service_my
 * @version    $Revision: 875 $
 */
class people extends Amun_Module_RestAbstract
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
					PSX_Sql::FETCH_OBJECT, 
					'AmunService_My_People', 
					array(Amun_Sql_Table_Registry::get('User_Friend')));

				$this->setResponse($resultSet);
			}
			catch(Exception $e)
			{
				$msg = new PSX_Data_Message($e->getMessage(), false);

				$this->setResponse($msg);
			}
		}
		else
		{
			$msg = new PSX_Data_Message('Access not allowed', false);

			$this->setResponse($msg, null, $this->user->isAnonymous() ? 401 : 403);
		}
	}

	public function onPost()
	{
		$msg = new PSX_Data_Message('Create a person record is not possible', false);

		$this->setResponse($msg, null, 500);
	}

	public function onPut()
	{
		$msg = new PSX_Data_Message('Update a person record is not possible', false);

		$this->setResponse($msg, null, 500);
	}

	public function onDelete()
	{
		$msg = new PSX_Data_Message('Delete a person record is not possible', false);

		$this->setResponse($msg, null, 500);
	}

	protected function setWriterConfig(PSX_Data_WriterResult $writer)
	{
		switch($writer->getType())
		{
			case PSX_Data_WriterInterface::ATOM:

				$updated = $this->sql->getField('SELECT `date` FROM ' . $this->registry['table.user_friend'] . ' ORDER BY `date` DESC LIMIT 1');

				$title   = 'Friend';
				$id      = 'urn:uuid:' . $this->base->getUUID('user:friend');
				$updated = new DateTime($updated, $this->registry['core.default_timezone']);

				$writer = $writer->getWriter();
				$writer->setConfig($title, $id, $updated);
				$writer->setGenerator('amun ' . Amun_Base::getVersion());

				if(!empty($this->config['amun_hub']))
				{
					$writer->addLink($this->config['amun_hub'], 'hub');
				}

				break;
		}
	}
}
