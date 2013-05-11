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

namespace AmunService\Core\Host;

use Amun\DataFactory;
use Amun\Data\HandlerAbstract;
use Amun\Data\RecordAbstract;
use Amun\Exception;
use Amun\Filter as AmunFilter;
use Amun\Util;
use AmunService\Core\Host\Filter as HostFilter;
use PSX\Data\WriterInterface;
use PSX\Data\WriterResult;
use PSX\DateTime;
use PSX\Filter;
use PSX\Util\Markdown;
use PSX\Url;

/**
 * Record
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/gpl.html GPLv3
 * @link    http://amun.phpsx.org
 */
class Record extends RecordAbstract
{
	const NORMAL = 0x1;
	const CLOSED = 0x2;

	protected $_url;
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

	public function setStatus($status)
	{
		$status = $this->_validate->apply($status, 'integer', array(new HostFilter\Status()), 'status', 'Status');

		if(!$this->_validate->hasError())
		{
			$this->status = $status;
		}
		else
		{
			throw new Exception($this->_validate->getLastError());
		}
	}

	public function setName($name)
	{
		$name = $this->_validate->apply($name, 'string', array(new Filter\Length(3, 64), new Filter\Html()), 'name', 'Name');

		if(!$this->_validate->hasError())
		{
			$this->name = $name;
		}
		else
		{
			throw new Exception($this->_validate->getLastError());
		}
	}

	public function setConsumerKey($consumerKey)
	{
		$consumerKey = $this->_validate->apply($consumerKey, 'string', array(new Filter\Length(6, 40)), 'consumerKey', 'Consumer Key');

		if(!$this->_validate->hasError())
		{
			$this->consumerKey = $consumerKey;
		}
		else
		{
			throw new Exception($this->_validate->getLastError());
		}
	}

	public function setConsumerSecret($consumerSecret)
	{
		$consumerSecret = $this->_validate->apply($consumerSecret, 'string', array(new Filter\Length(6, 40)), 'consumerSecret', 'Consumer Secret');

		if(!$this->_validate->hasError())
		{
			$this->consumerSecret = $consumerSecret;
		}
		else
		{
			throw new Exception($this->_validate->getLastError());
		}
	}

	public function setUrl($url)
	{
		$url = $this->_validate->apply($url, 'string', array(new Filter\Length(3, 256), new Filter\Url()), 'url', 'Url');

		if(!$this->_validate->hasError())
		{
			$this->url = $url;
		}
		else
		{
			throw new Exception($this->_validate->getLastError());
		}
	}

	public function getId()
	{
		return $this->_base->getUrn('core', 'host', $this->id);
	}

	public function getUrl()
	{
		if($this->_url === null)
		{
			$this->_url = new Url($this->url);
		}

		return $this->_url;
	}

	public function getDate()
	{
		if($this->_date === null)
		{
			$this->_date = new DateTime($this->date, $this->_registry['core.default_timezone']);
		}

		return $this->_date;
	}

	public function export(WriterResult $result)
	{
		switch($result->getType())
		{
			case WriterInterface::JSON:
			case WriterInterface::XML:

				return parent::export($result);

				break;

			case WriterInterface::ATOM:

				$entry = $result->getWriter()->createEntry();

				$entry->setTitle($this->name);
				$entry->setId('urn:uuid:' . $this->_base->getUUID('system:host:' . $this->id));
				$entry->setUpdated($this->getDate());
				$entry->addAuthor('System');
				$entry->addLink($this->url, 'alternate', 'text/html');

				return $entry;

				break;

			default:

				throw new Exception('Writer is not supported');

				break;
		}
	}

	public static function getStatus($status = false)
	{
		$s = array(

			self::NORMAL => 'Normal',
			self::CLOSED => 'Closed',

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


