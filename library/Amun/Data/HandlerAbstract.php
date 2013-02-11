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
abstract class Amun_Data_HandlerAbstract implements PSX_Data_HandlerInterface
{
	protected $base;
	protected $config;
	protected $sql;
	protected $registry;
	protected $event;
	protected $table;
	protected $user;

	protected $ignoreApprovement = false;

	protected $_select;

	public function __construct(Amun_User $user = null)
	{
		$ct = Amun_DataFactory::getContainer();

		$this->base     = $ct->getBase();
		$this->config   = $ct->getConfig();
		$this->sql      = $ct->getSql();
		$this->registry = $ct->getRegistry();
		$this->event    = $ct->getEvent();
		$this->table    = $this->getTableInstance();
		$this->user     = $user === null ? $ct->getUser() : $user;
	}

	/*
	public function getTable()
	{
		return $this->table;
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
	*/

	public function getUser()
	{
		return $this->user;
	}

	public function getAll(array $fields, $startIndex = 0, $count = 16, $sortBy = null, $sortOrder = null, PSX_Sql_Condition $con = null, $mode = 0, $class = null, array $args = array())
	{
		$startIndex = $startIndex !== null ? (integer) $startIndex : 0;
		$count      = !empty($count)       ? (integer) $count      : 16;
		$sortBy     = $sortBy     !== null ? $sortBy               : $this->table->getPrimaryKey();
		$sortOrder  = $sortOrder  !== null ? (integer) $sortOrder  : PSX_Sql::SORT_DESC;

		$select = $this->getSelect();
		$fields = array_intersect($fields, $select->getSupportedFields());

		if(!empty($fields))
		{
			$select->select($fields);
		}

		$select->orderBy($sortBy, $sortOrder)
			->limit($startIndex, $count);

		if($con !== null && $con->hasCondition())
		{
			$values = $con->toArray();

			foreach($values as $row)
			{
				$select->where($row[0], $row[1], $row[2]);
			}
		}

		return $select->getAll($mode, $class, $args);
	}

	/**
	 * Returns an resultset wich can easily displayed on an html page with a 
	 * pagination or exported as XML or JSON for an API
	 *
	 * @return PSX_Data_ResultSet
	 */
	public function getResultSet(array $fields, $startIndex = 0, $count = 16, $sortBy = null, $sortOrder = null, PSX_Sql_Condition $con = null, $mode = 0, $class = null, array $args = array())
	{
		$startIndex = $startIndex !== null ? (integer) $startIndex : 0;
		$count      = !empty($count)       ? (integer) $count      : 16;
		$sortOrder  = $sortOrder  !== null ? (strcasecmp($sortOrder, 'ascending') == 0 ? PSX_Sql::SORT_ASC : PSX_Sql::SORT_DESC) : null;

		$totalResults = $this->getCount();
		$entries      = $this->getAll($fields, $startIndex, $count, $sortBy, $sortOrder, $con, $mode, $class, $args);
		$resultSet    = new PSX_Data_ResultSet($totalResults, $startIndex, $count, $entries);

		return $resultSet;
	}

	/**
	 * Returns an row as associatve array or record object 
	 *
	 * @return array|PSX_Data_RecordInterface
	 */
	public function getById($id, $mode = 0, $class = null, array $args = array())
	{
		return $this->getSelect()
			->where('id', '=', $id)
			->getRow($mode, $class, $args);
	}

	/**
	 * Returns an array wich contains all columns wich are supported by the 
	 * default select
	 *
	 * @return array
	 */
	public function getSupportedFields()
	{
		return $this->getSelect()->getSupportedFields();
	}

	/**
	 * Returns the count of rows regarding to the condition
	 *
	 * @return integer
	 */
	public function getCount()
	{
		return $this->getSelect()->getTotalResults();
	}

	/**
	 * Returns a new record if the $id is not defined ot an existing record
	 *
	 * @param integer $id
	 * @return PSX_Data_RecordInterface
	 */
	public function getRecord($id = null)
	{
		return $this->table->getRecord($id);
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

	FROM {$this->registry['table.core_approval']} `approval`

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
		$type = AmunService_Core_Approval_Record::getType($type);

		if($type !== false)
		{
			$date = new DateTime('NOW', $this->registry['core.default_timezone']);

			$this->sql->insert($this->registry['table.core_approval_record'], array(

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
	 * updated or deleted. It notifies all listeners of the core.record_change 
	 * event
	 *
	 * @param integer $type
	 * @param PSX_Data_RecordInterface $record
	 * @return void
	 */
	public function notify($type, PSX_Data_RecordInterface $record)
	{
		if(Amun_Data_RecordAbstract::getType($type) === false)
		{
			throw new Amun_Exception('Invalid notification type');
		}

		$this->event->notifyListener('core.record_change', array($type, $this->table, $record), $this->user);
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

		return Amun_DataFactory::getProvider($className)->getTable();
	}

	protected function getSelect()
	{
		if($this->_select === null)
		{
			$this->_select = $this->getDefaultSelect();
		}

		return $this->_select;
	}

	/**
	 * Returns the default select object
	 *
	 * @return PSX_Sql_Table_SelectInterface
	 */
	protected function getDefaultSelect()
	{
		return $this->table
			->select(array('*'));
	}
}


