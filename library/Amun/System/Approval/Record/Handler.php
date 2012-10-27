<?php
/*
 *  $Id: Handler.php 801 2012-07-08 21:17:10Z k42b3.x@googlemail.com $
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
 * Amun_System_Approval_Record_Handler
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   Amun
 * @package    Amun_System_Approval
 * @version    $Revision: 801 $
 */
class Amun_System_Approval_Record_Handler extends Amun_Data_HandlerAbstract
{
	public function create(PSX_Data_RecordInterface $record)
	{
		throw new PSX_Data_Exception('You cant create a approval record');
	}

	public function update(PSX_Data_RecordInterface $record)
	{
		if($record->hasFields('id'))
		{
			$sql = <<<SQL
SELECT

	record.type   AS `recordType`,
	record.table  AS `recordTable`,
	record.record AS `recordRecord`

	FROM {$this->table->getName()} `record`

		WHERE id = ?
SQL;

			$row  = $this->sql->getRow($sql, array($record->id));
			$data = unserialize($row['recordRecord']);

			if(!empty($data) && is_array($data))
			{
				$className    = $this->registry->getClassNameFromTable($row['recordTable']);
				$classHandler = $className . '_Handler';

				if($className !== false && class_exists($classHandler))
				{
					$handler = new $classHandler($this->user);
					$handler->setIgnoreApprovement(true);


					$object = Amun_Sql_Table_Registry::get($className)->getRecord();

					foreach($data as $k => $v)
					{
						$object->$k = $v;
					}


					switch($record->type)
					{
						case 'INSERT':

							$handler->create($object);

							break;

						case 'UPDATE':

							$handler->update($object);

							break;

						case 'DELETE':

							$handler->delete($object);

							break;

						default:

							throw new PSX_Data_Exception('Invalid record type');
					}
				}
				else
				{
					throw new PSX_Data_Exception('Invalid record table');
				}
			}


			// delete the record
			$this->delete($record);


			return $record;
		}
		else
		{
			throw new PSX_Data_Exception('Missing field in record');
		}
	}

	public function delete(PSX_Data_RecordInterface $record)
	{
		if($record->hasFields('id'))
		{
			$con = new PSX_Sql_Condition(array('id', '=', $record->id));

			$this->table->delete($con);


			$this->notify(Amun_Data_RecordAbstract::DELETE, $record);


			return $record;
		}
		else
		{
			throw new PSX_Data_Exception('Missing field in record');
		}
	}
}

