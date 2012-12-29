<?php
/*
 *  $Id: activity.php 875 2012-09-30 13:51:45Z k42b3.x@googlemail.com $
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

use AmunService_User_Account_Record;
use Amun_Base;
use Amun_Module_RestAbstract;
use Amun_Sql_Table_Registry;
use Exception;
use PSX_Data_Exception;
use PSX_Data_Message;
use PSX_Data_WriterInterface;
use PSX_Data_WriterResult;
use PSX_Sql;
use PSX_Sql_Join;

/**
 * activity
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   module
 * @package    api
 * @subpackage service_my
 * @version    $Revision: 875 $
 */
class activity extends Amun_Module_RestAbstract
{
	protected $userId;

	/**
	 * Returns informations about the current loggedin user
	 *
	 * @httpMethod GET
	 * @path /{userId}
	 * @nickname getActivity
	 * @responseClass PSX_Data_ResultSet
	 */
	public function getActivity()
	{
		if($this->getProvider()->hasViewRight())
		{
			try
			{
				$select    = $this->getSelection();
				$fragments = $this->getUriFragments();
				$params    = $this->getRequestParams();
				$userId    = $this->getUriFragments('userId');

				if(!empty($userId))
				{
					$this->userId = $userId == '@me' ? $this->user->id : intval($userId);
				}
				else
				{
					$this->userId = $this->user->id;
				}

				$select->where('userId', '=', $this->userId);

				if(!empty($params['fields']))
				{
					$select->setColumns($params['fields']);
				}

				$resultSet = $select->getResultSet($params['startIndex'], $params['count'], $params['sortBy'], $params['sortOrder'], $params['filterBy'], $params['filterOp'], $params['filterValue'], $params['updatedSince'], PSX_Sql::FETCH_OBJECT, 'AmunService_My_Activity', array($select->getTable()));

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

	protected function getSelection()
	{
		return Amun_Sql_Table_Registry::get('User_Activity')
			->select(array('id', 'globalId', 'parentId', 'userId', 'refId', 'table', 'verb', 'summary', 'date'))
			->join(PSX_Sql_Join::INNER, Amun_Sql_Table_Registry::get('User_Account')
				->select(array('globalId', 'name', 'profileUrl', 'thumbnailUrl'), 'author')
			)
			->where('scope', '=', 0);
	}

	public function onPost()
	{
		$msg = new PSX_Data_Message('Create a activity record is not possible', false);

		$this->setResponse($msg, null, 500);
	}

	public function onPut()
	{
		$msg = new PSX_Data_Message('Update a activity record is not possible', false);

		$this->setResponse($msg, null, 500);
	}

	public function onDelete()
	{
		$msg = new PSX_Data_Message('Delete a activity record is not possible', false);

		$this->setResponse($msg, null, 500);
	}

	protected function setWriterConfig(PSX_Data_WriterResult $writer)
	{
		switch($writer->getType())
		{
			case PSX_Data_WriterInterface::ATOM:

				$account = Amun_Sql_Table_Registry::get('User_Account')
					->select(array('id', 'globalId', 'name', 'profileUrl', 'thumbnailUrl', 'updated'))
					->where('id', '=', $this->userId)
					->getRow(PSX_Sql::FETCH_OBJECT);

				if($account instanceof AmunService_User_Account_Record)
				{
					$writer = $writer->getWriter();
					$writer->setConfig($account->name . ' activities', 'urn:uuid:' . $account->globalId, $account->getUpdated());
					$writer->setGenerator('amun ' . Amun_Base::getVersion());
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
					throw new PSX_Data_Exception('Invalid user account');
				}

				break;
		}
	}
}

