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

namespace AmunService\Vcshook;

use Amun\Data\RecordAbstract;
use Amun\DataFactory;
use Amun\Filter\Id;
use Amun\Exception;
use PSX\DateTime;
use PSX\Filter;

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
	protected $_date;

	public function setId($id)
	{
		$id = $this->_validate->apply($id, 'integer', array(new Id($this->_table)), 'id', 'Id');

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

	public function setUrl($url)
	{
		$url = $this->_validate->apply($url, 'string', array(new Filter\Length(3, 512), new Filter\Url()), 'url', 'Url');

		if(!$this->_validate->hasError())
		{
			$this->url = $url;
		}
		else
		{
			throw new Exception($this->_validate->getLastError());
		}
	}

	public function getUser()
	{
		if($this->_user === null)
		{
			$this->_user = $this->_hm->getHandler('AmunService\User\Account')->getRecord($this->userId);
		}

		return $this->_user;
	}

	public function getDate()
	{
		if($this->_date === null)
		{
			$this->_date = new DateTime($this->date, $this->_registry['core.default_timezone']);
		}

		return $this->_date;
	}

	public static function getType($type = false)
	{
		$t = array(

			'github'        => 'GitHub',
			'googleproject' => 'GoogleProject',

		);

		if($type !== false)
		{
			if(array_key_exists($type, $t))
			{
				return $t[$type];
			}
			else
			{
				return false;
			}
		}
		else
		{
			return $t;
		}
	}
}


