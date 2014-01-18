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

use Amun\Exception;
use PSX\Data\RecordInterface;
use PSX\DateTime;
use PSX\Sql\TableInterface;

/**
 * Domain wich gives the option to add the record to an approve queue before 
 * CUD the record
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/gpl.html GPLv3
 * @link    http://amun.phpsx.org
 */
abstract class ApproveAbstract extends UserAbstract
{
	protected $ignoreApprovement = false;

	/**
	 * Sets whether the domain should ignore approvement
	 *
	 * @param boolean $approvement
	 * @return void
	 */
	public function setIgnoreApprovement($approvement)
	{
		$this->ignoreApprovement = (boolean) $approvement;
	}

	/**
	 * Returns whether the record needs to be approved
	 *
	 * @param PSX\Data\RecordInterface $record
	 * @return boolean
	 */
	protected function hasApproval(RecordInterface $record)
	{
		if($this->ignoreApprovement === false)
		{
			$sql = <<<SQL
SELECT
	`approval`.`field` AS `approvalField`,
	`approval`.`value` AS `approvalValue`
FROM 
	{$this->registry['table.core_approval']} `approval`
WHERE 
	`approval`.`class` LIKE ?
SQL;

			$result = $this->sql->getAll($sql, array(__CLASS__));

			foreach($result as $row)
			{
				$field = $row['approvalField'];

				if(empty($field))
				{
					return true;
				}

				$method = 'get' . ucfirst($field);

				if(method_exists($record, $method) && $record->$method() == $row['approvalValue'])
				{
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Inserts an record for approval
	 *
	 * @param integer $type
	 * @param PSX\Data\RecordInterface $record
	 * @return void
	 */
	protected function approveRecord($type, RecordInterface $record)
	{
		if(in_array($type, array('INSERT', 'UPDATE', 'DELETE')))
		{
			$date = new DateTime('NOW', $this->registry['core.default_timezone']);

			$this->sql->insert($this->registry['table.core_approval_record'], array(
				'userId' => $this->user->getId(),
				'type'   => $type,
				'class'  => __CLASS__,
				'record' => serialize($record->getRecordInfo()->getFields()),
				'date'   => $date->format(DateTime::SQL),
			));
		}
		else
		{
			throw new Exception('Invalid approve record type');
		}
	}
}
