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

use AmunService\User\Account;
use Amun\Base;
use Amun\DataFactory;
use Amun\Module\RestAbstract;
use Amun\Sql\Table\Registry;
use Amun\Exception;
use PSX\Data\Message;
use PSX\Data\WriterInterface;
use PSX\Data\WriterResult;
use PSX\Sql;

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
class activity extends RestAbstract
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

				$params    = $this->getRequestParams();
				$fields    = (array) $params['fields'];
				$resultSet = $this->getHandler('User_Activity')->getPublicResultSet($this->userId, 
					array(), 
					$params['startIndex'], 
					$params['count'], 
					$params['sortBy'], 
					$params['sortOrder'], 
					$this->getRequestCondition(),
					Sql::FETCH_OBJECT, 
					'\AmunService\My\Activity', 
					array(DataFactory::getTable('User_Activity')));

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

	public function onPost()
	{
		$msg = new Message('Create a activity record is not possible', false);

		$this->setResponse($msg, null, 500);
	}

	public function onPut()
	{
		$msg = new Message('Update a activity record is not possible', false);

		$this->setResponse($msg, null, 500);
	}

	public function onDelete()
	{
		$msg = new Message('Delete a activity record is not possible', false);

		$this->setResponse($msg, null, 500);
	}

	protected function setWriterConfig(WriterResult $writer)
	{
		switch($writer->getType())
		{
			case WriterInterface::ATOM:

				$account = DataFactory::getTable('User_Account')
					->select(array('id', 'globalId', 'name', 'profileUrl', 'thumbnailUrl', 'updated'))
					->where('id', '=', $this->userId)
					->getRow(Sql::FETCH_OBJECT);

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

