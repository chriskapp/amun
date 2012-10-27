<?php
/*
 *  $Id: Record.php 743 2012-06-26 19:31:26Z k42b3.x@googlemail.com $
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
 * Amun_System_Approval_Record
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   Amun
 * @package    Amun_System_Approval
 * @version    $Revision: 743 $
 */
class Amun_System_Approval_Record extends Amun_Data_RecordAbstract
{
	const INSERT = 0x1;
	const UPDATE = 0x2;
	const DELETE = 0x3;

	protected $_user;
	protected $_record;
	protected $_date;

	public function setId($id)
	{
		$id = $this->_validate->apply($id, 'integer', array(new Amun_Filter_Id($this->_table)), 'id', 'Id');

		if(!$this->_validate->hasError())
		{
			$this->id = $id;
		}
		else
		{
			throw new PSX_Data_Exception($this->_validate->getLastError());
		}
	}

	public function setType($type)
	{
		$type = $this->_validate->apply($type, 'string', array(new Amun_System_Approval_Record_Filter_Type()), 'type', 'Type');

		if(!$this->_validate->hasError())
		{
			$this->type = $type;
		}
		else
		{
			throw new PSX_Data_Exception($this->_validate->getLastError());
		}
	}

	public function setTable($table)
	{
		$table = $this->_validate->apply($table, 'string', array(new Amun_Filter_Table($this->_sql)), 'table', 'Table');

		if(!$this->_validate->hasError())
		{
			$this->table = $table;
		}
		else
		{
			throw new PSX_Data_Exception($this->_validate->getLastError());
		}
	}

	public function setRecord($record)
	{
		$this->record = $record;
	}

	public function getId()
	{
		return $this->_base->getUrn('system', 'approval', 'record', $this->id);
	}

	public function getUser()
	{
		if($this->_user === null)
		{
			$this->_user = Amun_Sql_Table_Registry::get('User_Account')->getRecord($this->userId);
		}

		return $this->_user;
	}

	public function getRecord()
	{
		if($this->_record === null && !empty($this->record))
		{
			$fields = unserialize($this->record);
			$class  = Amun_Registry::getClassName($this->table);
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

	public function export(PSX_Data_WriterResult $result)
	{
		switch($result->getType())
		{
			case PSX_Data_WriterInterface::JSON:
			case PSX_Data_WriterInterface::XML:

				$fields = parent::export($result);

				if(isset($fields['record']))
				{
					$fields['record'] = unserialize($fields['record']);
				}

				return $fields;

				break;

			case PSX_Data_WriterInterface::ATOM:

				$entry = $result->getWriter()->createEntry();

				$entry->setTitle($this->table);
				$entry->setId($this->getId());
				$entry->setUpdated($this->getDate());
				$entry->addAuthor($this->authorName);
				$entry->setContent($this->getRecord(), 'application/xml');

				return $entry;

				break;

			default:

				throw new PSX_Data_Exception('Writer is not supported');

				break;
		}
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


