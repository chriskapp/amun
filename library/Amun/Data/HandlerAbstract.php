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

namespace Amun\Data;

use Amun\Dependency;
use Amun\Exception;
use Amun\User;
use Amun\Registry;
use AmunService\Core\Approval\Record;
use PSX\Data\HandlerInterface;
use PSX\Data\RecordInterface;
use PSX\Data\ResultSet;
use PSX\DateTime;
use PSX\Sql;
use PSX\Sql\Condition;

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
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/gpl.html GPLv3
 * @link    http://amun.phpsx.org
 */
abstract class HandlerAbstract extends \PSX\Data\HandlerAbstract
{
	protected $container;
	protected $base;
	protected $config;
	protected $sql;
	protected $registry;
	protected $event;
	protected $hm;
	protected $user;

	public function __construct($container, User $user = null)
	{
		$this->container = $container;
		$this->base      = $container->get('base');
		$this->config    = $container->get('config');
		$this->sql       = $container->get('sql');
		$this->registry  = $container->get('registry');
		$this->event     = $container->get('event');
		$this->hm        = $container->get('handlerManager');
		$this->user      = $user === null ? $container->get('user') : $user;

		parent::__construct($this->getTableInstance());
	}

	public function getTable()
	{
		return $this->table;
	}

	public function getUser()
	{
		return $this->user;
	}

	public function getClassName()
	{
		$className = get_class($this);
		$className = substr($className, 0, -8); // remove _Handler

		return $className . '\\Record';
	}

	protected function getClassArgs()
	{
		return array($this->table, $this->container);
	}

	/**
	 * This method should be called by each handler if an record was inserted, 
	 * updated or deleted. It notifies all listeners of the core.record_change 
	 * event
	 *
	 * @param integer $type
	 * @param PSX\Data\RecordInterface $record
	 * @return void
	 */
	public function notify($type, RecordInterface $record)
	{
		if(RecordAbstract::getType($type) === false)
		{
			throw new Exception('Invalid notification type');
		}

		$this->event->notifyListener('core.record_change', array($type, $this->table, $record), $this->user);
	}

	/**
	 * Checks whether the user is the owner of the $record
	 *
	 * @return boolean
	 */
	public function isOwner(RecordInterface $record, $field = 'userId')
	{
		if($this->user->isAdministrator())
		{
			return true;
		}

		$pk = $this->table->getPrimaryKey();

		if(isset($record->$pk) && array_key_exists($field, $this->table->getColumns()))
		{
			$con     = new Condition(array($pk, '=', $record->$pk));
			$ownerId = $this->sql->select($this->table->getName(), array($field), $con, Sql::SELECT_FIELD);

			return !empty($ownerId) && $ownerId == $this->user->getId();
		}
		else
		{
			return false;
		}
	}

	/**
	 * Returns the view right name for the handler
	 *
	 * @return string
	 */
	public function getViewRight()
	{
		return $this->getRightName() . '_view';
	}

	/**
	 * Returns the add right name for the handler
	 *
	 * @return string
	 */
	public function getAddRight()
	{
		return $this->getRightName() . '_add';
	}

	/**
	 * Returns the edit right name for the handler
	 *
	 * @return string
	 */
	public function getEditRight()
	{
		return $this->getRightName() . '_edit';
	}

	/**
	 * Returns the delete right name for the handler
	 *
	 * @return string
	 */
	public function getDeleteRight()
	{
		return $this->getRightName() . '_delete';
	}

	/**
	 * Returns the right name of this handler
	 *
	 * @return string
	 */
	protected function getRightName()
	{
		$className = get_class($this);
		$className = substr($className, 0, -8); // remove _Handler
		$className = substr($className, 12); // remove AmunService_
		$className = strtolower($className);
		$className = str_replace('\\', '_', $className);

		return $className;
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
		$className = $className . '\\Table';

		if(class_exists($className))
		{
			return new $className($this->registry);
		}
		else
		{
			throw new Exception('Table "' . $className . '" does not exist');
		}
	}

	protected function getSelect()
	{
		if($this->_select === null)
		{
			$this->_select = $this->getDefaultSelect();
		}

		$select = clone $this->_select;

		return $select;
	}

	/**
	 * Returns the default select object
	 *
	 * @return PSX\Sql\Table\SelectInterface
	 */
	protected function getDefaultSelect()
	{
		return $this->table
			->select(array('*'));
	}
}


