<?php
/*
 *  $Id: Registry.php 683 2012-06-03 11:52:32Z k42b3.x@googlemail.com $
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

namespace AmunService\Core\Registry;

use Amun\DataFactory;
use Amun\Data\HandlerAbstract;
use Amun\Data\RecordAbstract;
use Amun\Exception;
use Amun\Filter as AmunFilter;
use Amun\Util;
use AmunService\Core\Registry\Filter as RegistryFilter;
use PSX\Data\WriterInterface;
use PSX\Data\WriterResult;
use PSX\DateTime;
use PSX\Filter;
use PSX\Util\Markdown;
use PSX\Url;

/**
 * Amun_System_Registry
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   Amun
 * @package    Amun_System_Registry
 * @version    $Revision: 683 $
 */
class Record extends RecordAbstract
{
	const STRING  = 0x1;
	const INTEGER = 0x2;
	const FLOAT   = 0x3;
	const BOOLEAN = 0x4;

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

	public function setName($name)
	{
		$name = $this->_validate->apply($name, 'string', array(new Filter\Length(3, 64), new RegistryFilter\Name()), 'name', 'Name');

		if(!$this->_validate->hasError())
		{
			$this->name = $name;
		}
		else
		{
			throw new Exception($this->_validate->getLastError());
		}
	}

	public function setType($type)
	{
		$type = $this->_validate->apply($type, 'string', array(new Filter\InArray(self::getType())), 'type', 'Type');

		if(!$this->_validate->hasError())
		{
			$this->type = $type;
		}
		else
		{
			throw new Exception($this->_validate->getLastError());
		}
	}

	public function setClass($class)
	{
		$class = $this->_validate->apply($class, 'string', array(new RegistryFilter\ClassName()), 'class', 'Class');

		if(!$this->_validate->hasError())
		{
			$this->class = $class;
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
		return $this->_base->getUrn('core', 'registry', $this->id);
	}

	public static function getType($status = false)
	{
		$s = array(

			self::STRING  => 'STRING',
			self::INTEGER => 'INTEGER',
			self::FLOAT   => 'FLOAT',
			self::BOOLEAN => 'BOOLEAN',

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


