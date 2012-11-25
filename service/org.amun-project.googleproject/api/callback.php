<?php
/*
 *  $Id: callback.php 875 2012-09-30 13:51:45Z k42b3.x@googlemail.com $
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

namespace googleproject\api;

use AmunService_Googleproject_Commit_Handler;
use Amun_Module_ApiAbstract;
use Amun_Sql_Table_Registry;
use Amun_User;
use Exception;
use PSX_Base;
use PSX_Data_Exception;
use PSX_Data_Message;
use PSX_Data_ReaderInterface;
use PSX_Data_ReaderResult;
use PSX_Json;
use PSX_Sql;
use PSX_Sql_Condition;

/**
 * callback
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   module
 * @package    api
 * @subpackage service_googleproject
 * @version    $Revision: 875 $
 */
class callback extends Amun_Module_ApiAbstract
{
	public function onGet()
	{
		header('Allow: POST');

		$msg = new PSX_Data_Message('Invalid request method', false);

		$this->setResponse($msg, null, 405);
	}

	public function onPost()
	{
		try
		{
			// get project
			$count   = 0;
			$project = PSX_Json::decode(PSX_Base::getRawInput());

			if(isset($project['project_name']))
			{
				$con = new PSX_Sql_Condition(array('name', '=', $project['project_name']));
				$id  = $this->sql->select($this->registry['table.googleproject'], array('id'), $con, PSX_Sql::SELECT_FIELD);

				if(!empty($id))
				{
					$record      = Amun_Sql_Table_Registry::get('Googleproject')->getRecord($id);
					$foreignHash = PSX_Base::getRequestHeader('google-code-project-hosting-hook-hmac');
					$hash        = hash_hmac('md5', PSX_Base::getRawInput(), $record->secret);

					if(strcmp($hash, $foreignHash) === 0)
					{
						foreach($project['revisions'] as $commit)
						{
							try
							{
								$result = new PSX_Data_ReaderResult(PSX_Data_ReaderInterface::JSON, $commit);
								$result->addParam('projectId', $record->id);

								$record = Amun_Sql_Table_Registry::get('Googleproject_Commit')->getRecord();
								$record->import($result);

								$user = new Amun_User($record->getAuthor()->userId, $this->registry);

								$handler = new AmunService_Googleproject_Commit_Handler($user);
								$handler->create($record);

								$count++;
							}
							catch(Exception $e)
							{
								// import fails we go the next commit and
								// ignore the error
							}
						}
					}
					else
					{
						throw new PSX_Data_Exception('Invalid signature');
					}
				}
				else
				{
					throw new PSX_Data_Exception('Project doesnt exist');
				}
			}
			else
			{
				throw new PSX_Data_Exception('Project name not set');
			}

			if($count == 0)
			{
				throw new PSX_Data_Exception('No commits inserted');
			}


			$msg = new PSX_Data_Message('Inserted ' . $count . ' commits', true);

			$this->setResponse($msg);
		}
		catch(Exception $e)
		{
			$msg = new PSX_Data_Message($e->getMessage(), false);

			$this->setResponse($msg, null, 500);
		}
	}
}

