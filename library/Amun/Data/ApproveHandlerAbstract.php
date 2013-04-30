<?php
/*
 *  $Id: HandlerAbstract.php 877 2012-10-01 18:14:57Z k42b3.x@googlemail.com $
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

namespace Amun\Data;

use Amun\Exception;
use AmunService\Core\Approval\Record;
use PSX\Data\HandlerInterface;
use PSX\Data\RecordInterface;
use PSX\DateTime;

/**
 * The data handler class offers a general concept of handling data. It 
 * abstracts all SQL handling from the API and application parts. The data
 * handler knows where and who wants to insert data. It can be used to CRUD
 * records. Here an example howto simply create a new record
 * <code>
 * $handler = $this->getHandler();
 *
 * $record = $handler->getRecord();
 * $record->setTitle('foor');
 * $record->setText('<p>bar</p>');
 *
 * $handler->create($record);
 * </code>
 *
 * And here an example howto read specific fields
 * <code>
 * $result = $this->getHandler()->getAll(array('id', 'title'));
 * </code>
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   Amun
 * @package    Amun_Data
 * @version    $Revision: 877 $
 */
abstract class ApproveHandlerAbstract extends HandlerAbstract
{
	protected $ignoreApprovement = false;

	/**
	 * Returns whether the record needs to be approved
	 *
	 * @param PSX_Data_RecordInterface $record
	 * @return boolean
	 */
	public function hasApproval(RecordInterface $record)
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
	`approval`.`table` LIKE "{$this->table->getName()}"
SQL;

			$result = $this->sql->getAll($sql);

			foreach($result as $row)
			{
				$field = $row['approvalField'];

				if(empty($field))
				{
					return true;
				}

				if(isset($record->$field) && $record->$field == $row['approvalValue'])
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
	 * @param PSX_Data_RecordInterface $record
	 * @return void
	 */
	public function approveRecord($type, RecordInterface $record)
	{
		$type = Record::getType($type);

		if($type !== false)
		{
			$date = new DateTime('NOW', $this->registry['core.default_timezone']);

			$this->sql->insert($this->registry['table.core_approval_record'], array(

				'userId' => $this->user->id,
				'type'   => $type,
				'table'  => $this->table->getName(),
				'record' => serialize($record->getFields()),
				'date'   => $date->format(DateTime::SQL),

			));
		}
		else
		{
			throw new Exception('Invalid approve record type');
		}
	}

	/**
	 * Sets whether the handler should ignore approvement
	 *
	 * @param boolean $approvement
	 * @return void
	 */
	public function setIgnoreApprovement($approvement)
	{
		$this->ignoreApprovement = (boolean) $approvement;
	}
}


