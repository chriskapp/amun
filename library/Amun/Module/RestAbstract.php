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
	public function onGet()
	{
		if($this->getProvider()->hasViewRight())
		{
			try
			{
				$select    = $this->getSelection();
				$fragments = $this->getUriFragments();
				$params    = $this->getRequestParams();

				if(isset($fragments[0]) && $fragments[0] == '@supportedFields')
				{
					$array = new PSX_Data_Array($select->getSupportedFields());

					$this->setResponse($array);
				}
				else
				{
					if(!empty($params['fields']))
					{
						$select->setColumns($params['fields']);
					}

					$resultSet = $select->getResultSet($params['startIndex'], $params['count'], $params['sortBy'], $params['sortOrder'], $params['filterBy'], $params['filterOp'], $params['filterValue'], $params['updatedSince'], $this->getMode());

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
		if($this->getProvider()->hasAddRight())
		{
			try
			{
				$record = $this->getTable()->getRecord();
				$record->import($this->getRequest());

				// check captcha
				$this->handleCaptcha($record);

				// insert
				$this->getHandler()->create($record);


				$msg = new PSX_Data_Message('You have successful create a ' . $this->getTable()->getDisplayName(), true);

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

	public function onPut()
	{
		if($this->getProvider()->hasEditRight())
		{
			try
			{
				$record = $this->getTable()->getRecord();
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


				$msg = new PSX_Data_Message('You have successful edit a ' . $this->getTable()->getDisplayName(), true);

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

	public function onDelete()
	{
		if($this->getProvider()->hasDeleteRight())
		{
			try
			{
				$record = $this->getTable()->getRecord();
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


				$msg = new PSX_Data_Message('You have successful delete a ' . $this->getTable()->getDisplayName(), true);

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

	protected function getSelection()
	{
		return $this->getTable()->select(array('*'));
	}

	protected function isOwner(Amun_Data_RecordAbstract $record)
	{
		return $this->getHandler()->isOwner($record);
	}

	protected function getTable()
	{
		return $this->getProvider()->getTable();
	}

	protected function getHandler()
	{
		return $this->getProvider()->getHandler();
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

