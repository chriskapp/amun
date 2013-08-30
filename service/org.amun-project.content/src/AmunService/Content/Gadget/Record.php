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

namespace AmunService\Content\Gadget;

use Amun\DataFactory;
use Amun\Data\HandlerAbstract;
use Amun\Data\RecordAbstract;
use Amun\Exception;
use Amun\Filter as AmunFilter;
use Amun\Util;
use AmunService\Core\Registry\Filter\Name as FilterName;
use PSX\Data\WriterInterface;
use PSX\Data\WriterResult;
use PSX\DateTime;
use PSX\Filter;
use PSX\Sql\Condition;
use PSX\Util\Annotation;
use PSX\Util\Markdown;
use ReflectionClass;
use ReflectionException;
use DateInterval;

/**
 * Record
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/gpl.html GPLv3
 * @link    http://amun.phpsx.org
 */
class Record extends RecordAbstract
{
	const STRING  = 0x1;
	const INTEGER = 0x2;
	const FLOAT   = 0x3;
	const BOOLEAN = 0x4;

	const INLINE = 'inline';
	const IFRAME = 'iframe';
	const AJAX   = 'ajax';

	protected $_expire;
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

	public function setRightId($rightId)
	{
		$rightId = $this->_validate->apply($rightId, 'integer', array(new AmunFilter\Id($this->_hm->getTable('AmunService\User\Right'), true)), 'rightId', 'Right Id');

		if(!$this->_validate->hasError())
		{
			$this->rightId = $rightId;
		}
		else
		{
			throw new Exception($this->_validate->getLastError());
		}
	}

	public function setType($type)
	{
		$type = $this->_validate->apply($type, 'string', array(new Filter\KeyExists(self::getType())), 'type', 'Type');

		if(!$this->_validate->hasError())
		{
			$this->type = $type;
		}
		else
		{
			throw new Exception($this->_validate->getLastError());
		}
	}

	public function setName($name)
	{
		$name = $this->_validate->apply($name, 'string', array(new Filter\Length(3, 64), new FilterName()), 'name', 'Name');

		if(!$this->_validate->hasError())
		{
			$this->name = $name;
		}
		else
		{
			throw new Exception($this->_validate->getLastError());
		}
	}

	public function setTitle($title)
	{
		$title = $this->_validate->apply($title, 'string', array(new Filter\Length(2, 32), new Filter\Html()), 'title', 'Title');

		if(!$this->_validate->hasError())
		{
			$this->title = $title;
		}
		else
		{
			throw new Exception($this->_validate->getLastError());
		}
	}

	public function setClass($class)
	{
		$class = new ReflectionClass($class);

		if($class->isSubclassOf('\Amun\Module\GadgetAbstract'))
		{
			$sql = <<<SQL
SELECT 
	`id`
FROM 
	{$this->_registry['table.core_service']}
WHERE 
	REPLACE(`namespace`, '\\\\', '-') LIKE SUBSTRING(?, 1, CHAR_LENGTH(`namespace`))
LIMIT 1
SQL;

			$className = str_replace('\\', '-', $class->getName());
			$serviceId = $this->_sql->getField($sql, array($className));

			if(!empty($serviceId))
			{
				$this->serviceId = $serviceId;
				$this->class     = $class->getName();
			}
			else
			{
				throw new Exception('Could not assign gadget to a service');
			}
		}
		else
		{
			throw new Exception('Gagdet must be an instanceof Amun\Module\GadgetAbstract');
		}
	}

	public function setParam($param)
	{
		if(empty($this->class))
		{
			throw new Exception('No class specified');
		}

		$data   = array();
		$values = self::parseParamString($param);
		$params = self::parseAnnotations($this->class);

		foreach($params as $name => $type)
		{
			if(isset($values[$name]))
			{
				switch($type)
				{
					case self::STRING:
						$value = (string) $values[$name];
						break;

					case self::INTEGER:
						$value = (integer) $values[$name];
						break;

					case self::FLOAT:
						$value = (float) $values[$name];
						break;

					case self::BOOLEAN:
						$value = (boolean) $values[$name];
						break;

					default:
						throw new Exception('Invalid type');
						break;
				}

				$data[$name] = $value;
			}
		}

		if(!empty($data))
		{
			$this->param = serialize($data);
		}
	}

	public function setCache($cache)
	{
		$this->cache = $cache ? 1 : 0;
	}

	public function setExpire($expire)
	{
		$expire = $this->_validate->apply($expire, 'string', array(new AmunFilter\DateInterval()), 'expire', 'Expire');

		if(!$this->_validate->hasError())
		{
			$this->expire = $expire;
		}
		else
		{
			throw new Exception($this->_validate->getLastError());
		}
	}

	public function getId()
	{
		return $this->_base->getUrn('content', 'gadget', $this->id);
	}

	public function getExpire()
	{
		if($this->_expire === null)
		{
			$this->_expire = new DateInterval($this->expire);
		}

		return $this->_expire;
	}

	public function getParam()
	{
		$param = '';

		if(!empty($this->param))
		{
			$param = http_build_query(unserialize($this->param), '', '&');
		}

		return $param;
	}

	public function getDate()
	{
		if($this->_date === null)
		{
			$this->_date = new DateTime($this->date, $this->_registry['core.default_timezone']);
		}

		return $this->_date;
	}

	public static function parseParamString($string)
	{
		$params = array();

		parse_str($string, $params);

		return $params;
	}

	public static function parseAnnotations($className)
	{
		$class   = new ReflectionClass($className);
		$method  = $class->getMethod('onLoad');
		$comment = $method->getDocComment();

		if(!empty($comment))
		{
			$doc = Annotation::parse($comment);

			$params = $doc->getAnnotation('param');
			$result = array();

			foreach($params as $param)
			{
				$parts = explode(' ', $param);
				$name  = isset($parts[0]) ? $parts[0] : null;
				$type  = isset($parts[1]) ? $parts[1] : null;
				$type  = self::getDataType($type);

				if(ctype_alnum($name) && $type !== null)
				{
					$result[$name] = $type;
				}
			}

			return $result;
		}
		else
		{
			throw new Exception('Empty doc comment');
		}
	}

	public static function getDataType($type)
	{
		switch($type)
		{
			case 'str':
			case 'string':
				return self::STRING;
				break;

			case 'int':
			case 'integer':
				return self::INTEGER;
				break;

			case 'double':
			case 'float':
				return self::FLOAT;
				break;

			case 'bool':
			case 'boolean':
				return self::BOOLEAN;
				break;
		}

		return null;
	}

	public static function getType($status = false)
	{
		$s = array(
			self::INLINE => 'Inline',
			self::AJAX   => 'Ajax',
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

