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

namespace AmunService\Core\Approval\Record;

use Amun\DataFactory;
use Amun\Data\HandlerAbstract;
use Amun\Data\RecordAbstract;
use Amun\Exception;
use Amun\Filter as AmunFilter;
use Amun\Util;
use Amun\Registry;
use AmunService\Core\Approval\Record\Filter as ApprovalFilter;
use PSX\Data\WriterInterface;
use PSX\Data\WriterResult;
use PSX\DateTime;
use PSX\Filter;
use PSX\Util\Markdown;

/**
 * Record
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/gpl.html GPLv3
 * @link    http://amun.phpsx.org
 */
class Record extends RecordAbstract
{
	protected $_user;
	protected $_record;
	protected $_date;

	public function setId($id)
	{
		$id = $this->_validate->apply($id, 'integer', array(new AmunFilter\Id($this->_table)), 'id', 'Id');

		if(!$this->_validate->hasError())
		{
			$this->id = $id;
		}
		else
		{
			throw new Exception($this->_validate->getLastError());
		}
	}

	public function setType($type)
	{
		$type = $this->_validate->apply($type, 'string', array(new ApprovalFilter\Type()), 'type', 'Type');

		if(!$this->_validate->hasError())
		{
			$this->type = $type;
		}
		else
		{
			throw new Exception($this->_validate->getLastError());
		}
	}

	public function setTable($table)
	{
		$table = $this->_validate->apply($table, 'string', array(new AmunFilter\Table($this->_sql)), 'table', 'Table');

		if(!$this->_validate->hasError())
		{
			$this->table = $table;
		}
		else
		{
			throw new Exception($this->_validate->getLastError());
		}
	}

	public function setRecord($record)
	{
		$this->record = $record;
	}

	public function getId()
	{
		return $this->_base->getUrn('core', 'approval', 'record', $this->id);
	}

	public function getUser()
	{
		if($this->_user === null)
		{
			$this->_user = $this->_hm->getHandler('User_Account')->getRecord($this->userId);
		}

		return $this->_user;
	}

	public function getRecord()
	{
		if($this->_record === null && !empty($this->record))
		{
			$fields = unserialize($this->record);
			$class  = Registry::getClassName($this->table);
			$record = new $class($this->_table);

			foreach($fields as $k => $v)
			{
				$record->$k = $v;
			}

			$this->_record = $record;
		}

		return $this->_record;
	}

	public function getDate()
	{
		if($this->_date === null)
		{
			$this->_date = new DateTime($this->date, $this->_registry['core.default_timezone']);
		}

		return $this->_date;
	}
}


