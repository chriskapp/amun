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

use Amun_Base;
use Amun_Module_RestAbstract;
use Amun_Sql_Table_Registry;
use Exception;
use PSX_Data_Array;
use PSX_Data_Exception;
use PSX_Data_Message;
use PSX_Data_WriterInterface;
use PSX_Data_WriterResult;
use PSX_Sql;
use PSX_Sql_Condition;
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
	private $userId;

	public function onGet()
	{
		if($this->getProvider()->hasViewRight())
		{
			try
			{
				$table = Amun_Sql_Table_Registry::get('Core_User_Activity')
					->select(array('id', 'globalId', 'parentId', 'userId', 'refId', 'table', 'verb', 'summary', 'date'))
					->join(PSX_Sql_Join::INNER, Amun_Sql_Table_Registry::get('Core_User_Account')
						->select(array('globalId', 'name', 'profileUrl', 'thumbnailUrl'), 'author')
					)
					->where('scope', '=', 0);

				$fragments = $this->getUriFragments();
				$params    = $this->getRequestParams();

				// get user id
				if(isset($fragments[0]) && $fragments[0] != '@me')
				{
					if(!is_numeric($fragments[0]))
					{
						$con    = new PSX_Sql_Condition(array('name', '=', $fragments[0]));
						$userId = $this->sql->select($this->registry['table.core_user_account'], array('id'), $con, PSX_Sql::SELECT_FIELD);

						$this->userId = $userId;
					}
					else
					{
						$this->userId = (integer) $fragments[0];
					}
				}
				else
				{
					$this->userId = $this->user->id;
				}

				if(isset($fragments[0]) && $fragments[0] == '@supportedFields')
				{
					$array = new PSX_Data_Array($table->getSupportedFields());

					$this->setResponse($array);
				}
				else
				{
					$table->where('userId', '=', $this->userId);

					if(!empty($params['fields']))
					{
						$table->setColumns($params['fields']);
					}

					$resultSet = $table->getResultSet($params['startIndex'], $params['count'], $params['sortBy'], $params['sortOrder'], $params['filterBy'], $params['filterOp'], $params['filterValue'], $params['updatedSince'], PSX_Sql::FETCH_OBJECT, 'AmunService_My_Activity', array($table->getTable()));

					$this->setResponse($resultSet);
				}
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

				$account = Amun_Sql_Table_Registry::get('Core_User_Account')
					->select(array('id', 'globalId', 'name', 'profileUrl', 'thumbnailUrl', 'updated'))
					->where('id', '=', $this->userId)
					->getRow(PSX_Sql::FETCH_OBJECT);

				if($account instanceof AmunService_Core_User_Account_Record)
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

