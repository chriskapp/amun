<?php
/*
 *  $Id: Gadget.php 838 2012-08-27 20:20:36Z k42b3.x@googlemail.com $
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
 * AmunService_Core_Content_Gadget_Record
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   Amun
 * @package    Amun_Content_Gadget
 * @version    $Revision: 838 $
 */
class AmunService_Core_Content_Gadget_Record extends Amun_Data_RecordAbstract
{
	const STRING  = 0x1;
	const INTEGER = 0x2;
	const FLOAT   = 0x3;
	const BOOLEAN = 0x4;

	protected $_expire;
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

	public function setName($name)
	{
		$name = $this->_validate->apply($name, 'string', array(new PSX_Filter_Length(2, 32)), 'name', 'Name');

		if(!$this->_validate->hasError())
		{
			$this->name = $name;
		}
		else
		{
			throw new PSX_Data_Exception($this->_validate->getLastError());
		}
	}

	public function setTitle($title)
	{
		$title = $this->_validate->apply($title, 'string', array(new PSX_Filter_Length(2, 32), new PSX_Filter_Html()), 'title', 'Title');

		if(!$this->_validate->hasError())
		{
			$this->title = $title;
		}
		else
		{
			throw new PSX_Data_Exception($this->_validate->getLastError());
		}
	}

	public function setPath($path)
	{
		$path = $this->_validate->apply($path, 'string', array(new Amun_Content_Gadget_Filter_Path($this->_config, $this->_sql)), 'path', 'Path');

		if(!$this->_validate->hasError())
		{
			$this->path = $path;
		}
		else
		{
			throw new PSX_Data_Exception($this->_validate->getLastError());
		}
	}

	public function setParam($param)
	{
		if(empty($this->path))
		{
			throw new PSX_Data_Exception('No path specified');
		}

		$data   = array();
		$class  = pathinfo($this->path, PATHINFO_FILENAME);

		if(!class_exists($class, false))
		{
			include_once($this->_config['amun_service_path'] . '/' . $this->path);
		}

		$values = self::parseParamString($param);
		$params = self::parseAnnotations($class);

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

						throw new PSX_Data_Exception('Invalid type');
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
		$expire = $this->_validate->apply($expire, 'string', array(new Amun_Filter_DateInterval()), 'expire', 'Expire');

		if(!$this->_validate->hasError())
		{
			$this->expire = $expire;
		}
		else
		{
			throw new PSX_Data_Exception($this->_validate->getLastError());
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

	public static function parseAnnotations($class)
	{
		$ref     = new ReflectionClass($class);
		$methods = $ref->getMethods();

		foreach($methods as $m)
		{
			if($m->getName() == 'onLoad')
			{
				$docComment = $m->getDocComment();

				break;
			}
		}

		if(!empty($docComment))
		{
			return self::parseDocComment($docComment);
		}
		else
		{
			throw new PSX_Data_Exception('Could not found doc comment in class');
		}
	}

	public static function parseDocComment($docComment)
	{
		$lines  = explode("\n", $docComment);
		$params = array();

		foreach($lines as $line)
		{
			$pos = strpos($line, '@');

			if($pos !== false)
			{
				$line = substr($line, $pos + 1);
				$tps  = strpos($line, '(');
				$tpe  = strpos($line, ')');

				if($tps !== false && $tpe !== false && $tpe > $tps)
				{
					$name = trim(substr($line, 0, $tps));
					$type = trim(substr($line, $tps + 1, $tpe - $tps - 1));
					$type = self::getDataType($type);

					if(ctype_alnum($name) && $type !== null)
					{
						$params[$name] = $type;
					}
				}
			}
		}

		return $params;
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
}

