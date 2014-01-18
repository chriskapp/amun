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

namespace Amun\Domain;

use Amun\Event\RecordManipulationEvent;
use Amun\Exception\ForbiddenException;
use PSX\Data\RecordInterface;

/**
 * ManipulationAbstract
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/gpl.html GPLv3
 * @link    http://amun.phpsx.org
 */
abstract class ManipulationAbstract extends ApproveAbstract implements DispatchableInterface
{
	const INSERT = 'INSERT';
	const UPDATE = 'UPDATE';
	const DELETE = 'DELETE';

	public function create(RecordInterface $record)
	{
		if(!$this->user->hasRight($this->getAddRight()))
		{
			throw new ForbiddenException('Access not allowed');
		}

		$missingFields = $record->getRecordInfo()->getMissingFields($this->getRequiredFields());

		if(empty($missingFields))
		{
			$this->beforeValidation($record);

			$this->getFilterDefinition()->validate($record);

			$this->afterValidation($record);


			if(!$this->hasApproval($record))
			{
				$this->beforeInsert($record);

				$this->getDefaultHandler()->insert($record);

				$this->afterInsert($record);


				$this->dispatch('core.record_change', new RecordManipulationEvent(self::INSERT, $record));
			}
			else
			{
				$this->approveRecord(self::INSERT, $record);
			}

			return $record;
		}
		else
		{
			throw new Exception('Missing fields in record: ' . implode(', ', $missingFields));
		}
	}

	public function update(RecordInterface $record)
	{
		if(!$this->user->hasRight($this->getEditRight()))
		{
			throw new ForbiddenException('Access not allowed');
		}

		$missingFields = $record->getRecordInfo()->getMissingFields(array('id'));

		if(empty($missingFields))
		{
			$this->beforeValidation($record);

			$this->getFilterDefinition()->validate($record);

			$this->afterValidation($record);


			if(!$this->hasApproval($record))
			{
				$this->beforeUpdate($record);

				$this->getDefaultHandler()->update($record);

				$this->afterUpdate($record);


				$this->dispatch('core.record_change', new RecordManipulationEvent(self::UPDATE, $record));
			}
			else
			{
				$this->approveRecord(self::UPDATE, $record);
			}

			return $record;
		}
		else
		{
			throw new Exception('Missing fields in record: ' . implode(', ', $missingFields));
		}
	}

	public function delete(RecordInterface $record)
	{
		if(!$this->user->hasRight($this->getDeleteRight()))
		{
			throw new ForbiddenException('Access not allowed');
		}

		$missingFields = $record->getRecordInfo()->getMissingFields(array('id'));

		if(empty($missingFields))
		{
			$this->beforeValidation($record);

			$this->getFilterDefinition()->validate($record);

			$this->afterValidation($record);

			if(!$this->hasApproval($record))
			{
				$this->beforeDelete($record);

				$this->getDefaultHandler()->delete($record);

				$this->afterDelete($record);


				$this->dispatch('core.record_change', new RecordManipulationEvent(self::DELETE, $record));
			}
			else
			{
				$this->approveRecord(self::DELETE, $record);
			}

			return $record;
		}
		else
		{
			throw new Exception('Missing fields in record: ' . implode(', ', $missingFields));
		}
	}

	public function getRecord($id = null)
	{
		return $this->getDefaultHandler()->getRecord($id);
	}

	public function getDispatchableEvents()
	{
		return array('core.record_change');
	}

	protected function beforeValidate(RecordInterface $record)
	{
	}

	protected function afterValidate(RecordInterface $record)
	{
	}

	protected function beforeInsert(RecordInterface $record)
	{
	}

	protected function afterInsert(RecordInterface $record)
	{
	}

	protected function beforeUpdate(RecordInterface $record)
	{
	}

	protected function afterUpdate(RecordInterface $record)
	{
	}

	protected function beforeDelete(RecordInterface $record)
	{
	}

	protected function afterDelete(RecordInterface $record)
	{
	}

	/**
	 * Returns the fields which are required when creating an record
	 *
	 * @return array
	 */
	abstract protected function getRequiredFields()

	/**
	 * Returns the default handler on which the domain operates
	 *
	 * @return PSX\Handler\HandlerInterface
	 */
	abstract protected function getDefaultHandler();

	/**
	 * Returns the default filter definition howto validate each record field
	 *
	 * @return PSX\Filter\Definition
	 */
	abstract protected function getFilterDefinition();
}
