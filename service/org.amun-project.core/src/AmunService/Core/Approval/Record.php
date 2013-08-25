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

namespace AmunService\Core\Approval;

use Amun\DataFactory;
use Amun\Data\HandlerAbstract;
use Amun\Data\RecordAbstract;
use Amun\Exception;
use Amun\Filter as AmunFilter;
use Amun\Util;
use Amun\Registry;
use AmunService\Core\Approval\Filter as ApprovalFilter;
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
	const INSERT = 0x1;
	const UPDATE = 0x2;
	const DELETE = 0x3;

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

	public function setField($field)
	{
		$field = $this->_validate->apply($field, 'string', array(new ApprovalFilter\Field($this->_sql, $this->table)), 'field', 'Field');

		if(!$this->_validate->hasError())
		{
			$this->field = $field;
		}
		else
		{
			throw new Exception($this->_validate->getLastError());
		}
	}

	public function setValue($value)
	{
		$this->value = $value;
	}

	public function getId()
	{
		return $this->_base->getUrn('core', 'approval', $this->id);
	}

	public static function getType($status = false)
	{
		$s = array(

			self::INSERT => 'INSERT',
			self::UPDATE => 'UPDATE',
			self::DELETE => 'DELETE',

		);

		if($status !== false)
		{
			$status = intval($status);

			if(array_key_exists($status, $s))
			{
				return $s[$status];
			}
			else
			{
				return false;
			}
		}
		else
		{
			return $s;
		}
	}
}


