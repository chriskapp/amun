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

/**
 * Amun_Data_HandlerAbstract
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   Amun
 * @package    Amun_Data
 * @version    $Revision: 877 $
 */
abstract class Amun_Data_HandlerAbstract implements PSX_Data_HandlerInterface
{
	protected $table;
	protected $base;
	protected $config;
	protected $sql;
	protected $registry;
	protected $user;

	protected $ignoreApprovement = false;

	public function __construct(Amun_User $user)
	{
		$this->table    = $this->getTableInstance();
		$this->base     = Amun_Base::getInstance();
		$this->config   = $this->base->getConfig();
		$this->sql      = $this->base->getSql();
		$this->registry = $this->base->getRegistry();
		$this->user     = $user;
	}

	public function getTable()
	{
		return $this->table;
	}

	public function getBase()
	{
		return $this->base;
	}

	public function getConfig()
	{
		return $this->config;
	}

	public function getSql()
	{
		return $this->sql;
	}

	public function getRegistry()
	{
		return $this->registry;
	}

	public function getUser()
	{
		return $this->user;
	}

	/**
	 * Returns whether the record needs to be approved
	 *
	 * @param PSX_Data_RecordInterface $record
	 * @return boolean
	 */
	public function hasApproval(PSX_Data_RecordInterface $record)
	{
		if($this->ignoreApprovement === false)
		{
			$sql = <<<SQL
SELECT

	approval.field AS `approvalField`,
	approval.value AS `approvalValue`

	FROM {$this->registry['table.core_system_approval']} `approval`

		WHERE `approval`.`table` LIKE "{$this->table->getName()}"
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
	public function approveRecord($type, PSX_Data_RecordInterface $record)
	{
		$type = AmunService_Core_System_Approval_Record::getType($type);

		if($type !== false)
		{
			$date = new DateTime('NOW', $this->registry['core.default_timezone']);

			$this->sql->insert($this->registry['table.core_system_approval_record'], array(

				'userId' => $this->user->id,
				'type'   => $type,
				'table'  => $this->table->getName(),
				'record' => serialize($record->getFields()),
				'date'   => $date->format(PSX_DateTime::SQL),

			));
		}
		else
		{
			throw new Amun_Exception('Invalid approve record type');
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

	/**
	 * This method should be called by each handler if an record was inserted, 
	 * updated or deleted. It notifies all classes in the table 
	 * core_system_notify about the changes
	 *
	 * @param integer $type
	 * @param PSX_Data_RecordInterface $record
	 * @param integer $mode
	 * @return void
	 */
	public function notify($type, PSX_Data_RecordInterface $record)
	{
		if(Amun_Data_RecordAbstract::getType($type) === false)
		{
			throw new Amun_Exception('Invalid notification type');
		}

		$sql = <<<SQL
SELECT

	`notify`.`class`

	FROM {$this->registry['table.core_system_notify']} `notify`

		WHERE "{$this->table->getName()}" REGEXP `notify`.`table`

		ORDER BY `notify`.`priority` DESC
SQL;

		$result = $this->sql->getAll($sql);

		foreach($result as $row)
		{
			$class  = $row['class'];
			$notify = new $class($this->table, $this->user);
			$notify->notify($type, $record);
		}
	}

	/**
	 * Checks whether the user is the owner of the $record
	 *
	 * @return boolean
	 */
	public function isOwner(PSX_Data_RecordInterface $record, $field = 'userId')
	{
		if($this->user->isAdministrator())
		{
			return true;
		}

		$pk = $this->table->getPrimaryKey();

		if(isset($record->$pk) && array_key_exists($field, $this->table->getColumns()))
		{
			$con     = new PSX_Sql_Condition(array($pk, '=', $record->$pk));
			$ownerId = $this->sql->select($this->table->getName(), array($field), $con, PSX_Sql::SELECT_FIELD);

			return !empty($ownerId) && $ownerId == $this->user->id;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Returns the table instance on wich the handler operates
	 *
	 * @return Amun_Sql_TableInterface
	 */
	protected function getTableInstance()
	{
		$className = get_class($this);
		$className = substr($className, 0, -8); // remove _Handler
		$className = substr($className, 12); // remove AmunService_

		return Amun_Sql_Table_Registry::get($className);
	}
}


