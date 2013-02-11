<?php
/*
 *  $Id: RestAbstract.php 835 2012-08-26 21:37:35Z k42b3.x@googlemail.com $
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

/**
 * Amun_Module_RestAbstract
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   Amun
 * @package    Amun_Module
 * @version    $Revision: 835 $
 */
abstract class Amun_Module_RestAbstract extends Amun_Module_ApiAbstract
{
	/**
	 * Returns the resultset
	 *
	 * @httpMethod GET
	 * @path /
	 * @nickname getRecords
	 * @parameter [query startIndex integer]
	 * @parameter [query count integer]
	 * @parameter [query sortBy integer]
	 * @parameter [query sortOrder string ascending|descending]
	 * @parameter [query filterBy integer]
	 * @parameter [query filterOp integer contains|equals|startsWith|present]
	 * @parameter [query filterValue string]
	 * @parameter [query updatedSince DateTime]
	 * @responseClass PSX_Data_ResultSet
	 */
	public function getRecords()
	{
		if($this->getProvider()->hasViewRight())
		{
			try
			{
				$params    = $this->getRequestParams();
				$fields    = (array) $params['fields'];
				$resultSet = $this->getHandler()->getResultSet($fields, 
					$params['startIndex'], 
					$params['count'], 
					$params['sortBy'], 
					$params['sortOrder'], 
					$this->getRequestCondition(),
					$this->getMode());

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

	/**
	 * Returns all available fields 
	 *
	 * @httpMethod GET
	 * @path /@supportedFields
	 * @nickname getSupportedFields
	 * @responseClass PSX_Data_Array
	 */
	public function getSupportedFields()
	{
		if($this->getProvider()->hasViewRight())
		{
			try
			{
				$array = new PSX_Data_Array($this->getHandler()->getSupportedFields());

				$this->setResponse($array);
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
		if($this->getProvider()->hasAddRight())
		{
			try
			{
				$record = $this->getHandler()->getRecord();
				$record->import($this->getRequest());

				// check captcha
				$this->handleCaptcha($record);

				// insert
				$this->getHandler()->create($record);


				$msg = new PSX_Data_Message('You have successful create a ' . $record->getName(), true);

				$this->setResponse($msg);
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
		if($this->getProvider()->hasEditRight())
		{
			try
			{
				$record = $this->getHandler()->getRecord();
				$record->import($this->getRequest());

				// check owner
				if(!$this->isOwner($record))
				{
					throw new PSX_Data_Exception('You are not the owner of the record');
				}

				// check captcha
				$this->handleCaptcha($record);

				// update
				$this->getHandler()->update($record);


				$msg = new PSX_Data_Message('You have successful edit a ' . $record->getName(), true);

				$this->setResponse($msg);
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
		if($this->getProvider()->hasDeleteRight())
		{
			try
			{
				$record = $this->getHandler()->getRecord();
				$record->import($this->getRequest());

				// check owner
				if(!$this->isOwner($record))
				{
					throw new PSX_Data_Exception('You are not the owner of the record');
				}

				// check captcha
				$this->handleCaptcha($record);

				// delete
				$this->getHandler()->delete($record);


				$msg = new PSX_Data_Message('You have successful delete a ' . $record->getName(), true);

				$this->setResponse($msg);
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

	protected function isOwner(Amun_Data_RecordAbstract $record)
	{
		return $this->getHandler()->isOwner($record);
	}

	protected function handleCaptcha(Amun_Data_RecordAbstract $record)
	{
		if($this->user->isAnonymous() || $this->user->hasInputExceeded())
		{
			$captcha = Amun_Captcha::factory($this->config['amun_captcha']);

			if($captcha->verify($record->captcha))
			{
				$this->session->set('captcha_verified', time());
			}
			else
			{
				throw new PSX_Data_Exception('Invalid captcha');
			}
		}
	}

	protected function getRequestCondition()
	{
		$con          = new PSX_Sql_Condition();
		$params       = $this->getRequestParams();
		$filterBy     = $params['filterBy'];
		$filterOp     = $params['filterOp'];
		$filterValue  = $params['filterValue'];
		$updatedSince = $params['updatedSince'];

		if(!empty($filterBy) && !empty($filterOp) && !empty($filterValue))
		{
			switch($filterOp)
			{
				case 'contains':
					$con->add($filterBy, 'LIKE', '%' . $filterValue . '%');
					break;

				case 'equals':
					$con->add($filterBy, '=', $filterValue);
					break;

				case 'startsWith':
					$con->add($filterBy, 'LIKE', $filterValue . '%');
					break;

				case 'present':
					$con->add($filterBy, 'IS NOT', 'NULL', 'AND');
					$con->add($filterBy, 'NOT LIKE', '');
					break;
			}
		}

		if(!empty($updatedSince))
		{
			$datetime = new PSX_DateTime($updatedSince);

			$con->add('date', '>', $datetime->format(PSX_DateTime::SQL));
		}

		return $con;
	}

	protected function getMode()
	{
		$format = isset($_GET['format']) ? $_GET['format'] : null;

		switch($format)
		{
			case 'atom':
				return PSX_Sql::FETCH_OBJECT;
				break;

			case 'xml':
			case 'json':
			default:
				return PSX_Sql::FETCH_ASSOC;
				break;
		}
	}

	protected function getRequestParams()
	{
		$params = parent::getRequestParams();

		if(!empty($params['fields']))
		{
			$params['fields'] = array_diff($params['fields'], $this->getRestrictedFields());
		}

		return $params;
	}

	protected function getRestrictedFields()
	{
		return array();
	}
}

