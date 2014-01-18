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

use Amun\Exception\ForbiddenException;
use PSX\Sql\Condition;

/**
 * UserAbstract
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/gpl.html GPLv3
 * @link    http://amun.phpsx.org
 */
abstract class UserAbstract extends DefaultAbstract implements UserAwareInterface
{
	private $_rightName;

	protected $user;

	public function setContainer(ContainerInterface $container = null)
	{
		parent::setContainer($container);

		if(!isset($this->user))
		{
			$this->user = $container->get('user');
		}
	}

	public function setUser(User $user = null)
	{
		$this->user = $user;
	}

	/**
	 * Checks whether the user is the owner of the $record. He is the owner if
	 * the user is an administrator or if the record has an column $field which
	 * is equal to the user id
	 *
	 * @param PSX\Data\RecordInterface $record
	 * @param string $field
	 * @return boolean
	 */
	public function isOwner(RecordInterface $record, $field = 'userId')
	{
		if($this->user->isAdministrator())
		{
			return true;
		}

		$id = $record->getId();

		if(!empty($id))
		{
			$record = $this->getDefaultHandler()->getRecord($id);
			$method = 'get' . ucfirst($field);

			if(method_exists($record, $method))
			{
				return $record->$method() == $this->user->getId();
			}
		}

		return false;
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
		if($this->_rightName === null)
		{
			$this->_rightName = strtolower(implode('_', array_slice(explode('\\', get_class($this)), 1, -1)));
		}

		return $this->_rightName;
	}
}
