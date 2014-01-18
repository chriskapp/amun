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

namespace Amun\Module;

use Amun\Exception;
use Amun\Exception\ForbiddenException;
use Amun\Captcha;
use Amun\Data\RecordAbstract;
use Amun\Module\ApiAbstract;
use PSX\Data\Record;
use PSX\Data\Message;
use PSX\DateTime;
use PSX\Sql;
use PSX\Sql\Condition;

/**
 * RestAbstract
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/gpl.html GPLv3
 * @link    http://amun.phpsx.org
 */
abstract class RestAbstract extends ApiAbstract
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
	 * @parameter [query filterBy string]
	 * @parameter [query filterOp string contains|equals|startsWith|present]
	 * @parameter [query filterValue string]
	 * @parameter [query updatedSince DateTime]
	 * @responseClass PSX\Data\Collection
	 */
	public function getRecords()
	{
		try
		{
			if($this->isWriter(WriterInterface::ATOM))
			{
				$this->setResponse($this->getQueryDomain()->getAtom());
			}
			else
			{
				$params = $this->getRequestParams();

				$this->setResponse($this->getQueryDomain()->getCollection($params['fields'],
					$params['startIndex'], 
					$params['count'], 
					$params['sortBy'], 
					$params['sortOrder'], 
					$this->getRequestCondition()
				));
			}
		}
		catch(ForbiddenException $e)
		{
			$msg = new Message('Access not allowed', false);

			$this->setResponse($msg, null, $this->user->isAnonymous() ? 401 : 403);
		}
		catch(\Exception $e)
		{
			$msg = new Message($e->getMessage(), false);

			$this->setResponse($msg);
		}
	}

	/**
	 * Returns all available fields 
	 *
	 * @httpMethod GET
	 * @path /@supportedFields
	 * @nickname getSupportedFields
	 * @responseClass PSX\Data\Record
	 */
	public function getSupportedFields()
	{
		try
		{
			$array = new Record('fields', array(
				'items' => $this->getQueryDomain()->getSupportedFields(),
			));

			$this->setResponse($array);
		}
		catch(\Exception $e)
		{
			$msg = new Message($e->getMessage(), false);

			$this->setResponse($msg);
		}
	}

	/**
	 * Insert a new record
	 *
	 * @httpMethod POST
	 * @path /
	 * @nickname insertRecord
	 * @responseClass PSX\Data\Record
	 */
	public function insertRecord()
	{
		try
		{
			// import record
			$record = $this->import($this->getManipulationDomain()->getRecord());

			// check captcha
			$this->handleCaptcha($record);

			// insert
			$this->getManipulationDomain()->create($record);


			$msg = new Message('You have successful create a ' . $record->getName(), true);

			$this->setResponse($msg);
		}
		catch(ForbiddenException $e)
		{
			$msg = new Message('Access not allowed', false);

			$this->setResponse($msg, null, $this->user->isAnonymous() ? 401 : 403);
		}
		catch(\Exception $e)
		{
			$msg = new Message($e->getMessage(), false);

			$this->setResponse($msg);
		}
	}

	/**
	 * Update an existing record
	 *
	 * @httpMethod PUT
	 * @path /
	 * @nickname updateRecord
	 * @responseClass PSX\Data\Record
	 */
	public function updateRecord()
	{
		try
		{
			// import record
			$record = $this->import($this->getManipulationDomain()->getRecord());

			// check owner
			if(!$this->getManipulationDomain()->isOwner($record))
			{
				throw new Exception('You are not the owner of the record');
			}

			// check captcha
			$this->handleCaptcha($record);

			// update
			$this->getManipulationDomain()->update($record);


			$msg = new Message('You have successful edit a ' . $record->getName(), true);

			$this->setResponse($msg);
		}
		catch(ForbiddenException $e)
		{
			$msg = new Message('Access not allowed', false);

			$this->setResponse($msg, null, $this->user->isAnonymous() ? 401 : 403);
		}
		catch(\Exception $e)
		{
			$msg = new Message($e->getMessage(), false);

			$this->setResponse($msg);
		}
	}

	/**
	 * Delete an existing record
	 *
	 * @httpMethod DELETE
	 * @path /
	 * @nickname deleteRecord
	 * @responseClass PSX\Data\Record
	 */
	public function deleteRecord()
	{
		try
		{
			// import record
			$record = $this->import($this->getManipulationDomain()->getRecord());

			// check owner
			if(!$this->getManipulationDomain()->isOwner($record))
			{
				throw new Exception('You are not the owner of the record');
			}

			// check captcha
			$this->handleCaptcha($record);

			// delete
			$this->getManipulationDomain()->delete($record);


			$msg = new Message('You have successful delete a ' . $record->getName(), true);

			$this->setResponse($msg);
		}
		catch(ForbiddenException $e)
		{
			$msg = new Message('Access not allowed', false);

			$this->setResponse($msg, null, $this->user->isAnonymous() ? 401 : 403);
		}
		catch(\Exception $e)
		{
			$msg = new Message($e->getMessage(), false);

			$this->setResponse($msg);
		}
	}

	protected function handleCaptcha(RecordAbstract $record)
	{
		if($this->user->isAnonymous() || $this->user->hasInputExceeded())
		{
			$captcha = Captcha::factory($this->config['amun_captcha']);

			if($captcha->verify($record->captcha))
			{
				$this->session->set('captcha_verified', time());
			}
			else
			{
				throw new Exception('Invalid captcha');
			}
		}
	}

	/**
	 * Returns the query domain
	 *
	 * @return Amun\Domain\QueryAbstract
	 */
	abstract protected function getQueryDomain();

	/**
	 * Returns the manipulation domain
	 *
	 * @return Amun\Domain\ManipulationAbstract
	 */
	abstract protected function getManipulationDomain();
}

