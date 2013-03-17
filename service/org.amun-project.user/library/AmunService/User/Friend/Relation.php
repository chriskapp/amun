<?php
/*
 *  $Id: Relation.php 635 2012-05-01 19:46:37Z k42b3.x@googlemail.com $
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

namespace AmunService\User\Friend;

use Amun\Exception;
use AmunService\Core\Host\Filter\Name as HostFilter;
use AmunService\User\Account\Filter\Name as AccountFilter;
use PSX\Data\RecordAbstract;
use PSX\Validate;
use PSX\Filter;
use PSX\Data\ReaderInterface;
use PSX\Data\ReaderResult;

/**
 * Amun_User_Friend_Relation
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   Amun
 * @package    Amun_User_Friend
 * @version    $Revision: 635 $
 */
class Relation extends RecordAbstract
{
	protected $_validate;

	public $mode;
	public $host;
	public $name;

	public function __construct()
	{
		$this->_validate = new Validate();
	}

	public function getName()
	{
		return 'relation';
	}

	public function getFields()
	{
		return array(

			'mode' => $this->mode,
			'host' => $this->host,
			'name' => $this->name,

		);
	}

	public function setMode($mode)
	{
		$mode = $this->_validate->apply($mode, 'string', array(new Filter\Length(1, 16)), 'mode', 'Mode');

		if(!$this->_validate->hasError())
		{
			$this->mode = $mode;
		}
		else
		{
			throw new Exception($this->_validate->getLastError());
		}
	}

	public function setHost($host)
	{
		$host = $this->_validate->apply($host, 'string', array(new HostFilter()), 'host', 'Host');

		if(!$this->_validate->hasError())
		{
			$this->host = $host;
		}
		else
		{
			throw new Exception($this->_validate->getLastError());
		}
	}

	public function setName($name)
	{
		$name = $this->_validate->apply($name, 'string', array(new AccountFilter()), 'name', 'Name');

		if(!$this->_validate->hasError())
		{
			$this->name = $name;
		}
		else
		{
			throw new Exception($this->_validate->getLastError());
		}
	}

	public function import(ReaderResult $result)
	{
		switch($result->getType())
		{
			case ReaderInterface::FORM:

				$params = $result->getData();

				$ns   = isset($params['relation_ns'])   ? $params['relation_ns']   : null;
				$mode = isset($params['relation_mode']) ? $params['relation_mode'] : null;
				$host = isset($params['relation_host']) ? $params['relation_host'] : null;
				$name = isset($params['relation_name']) ? $params['relation_name'] : null;

				if($ns != Amun_Relation::NS)
				{
					throw new Exception('Invalid namespace');
				}

				$this->setMode($mode);
				$this->setHost($host);
				$this->setName($name);

				break;

			default:

				throw new Exception('Can only import from form reader');

				break;
		}
	}
}

