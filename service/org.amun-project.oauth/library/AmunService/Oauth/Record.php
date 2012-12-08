<?php
/*
 *  $Id: Api.php 683 2012-06-03 11:52:32Z k42b3.x@googlemail.com $
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
 * AmunService_Oauth_Record
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   Amun
 * @package    Amun_Oauth
 * @version    $Revision: 683 $
 */
class AmunService_Oauth_Record extends Amun_Data_RecordAbstract
{
	const NORMAL    = 0x1;
	const CLOSED    = 0x2;

	const TEMPORARY = 0x1;
	const APPROVED  = 0x2;
	const ACCESS    = 0x3;

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

	public function setStatus($status)
	{
		$status = $this->_validate->apply($status, 'integer', array(new AmunService_Oauth_Filter_Status()), 'status', 'Status');

		if(!$this->_validate->hasError())
		{
			$this->status = $status;
		}
		else
		{
			throw new PSX_Data_Exception($this->_validate->getLastError());
		}
	}

	public function setName($name)
	{
		$name = $this->_validate->apply($name, 'string', array(new PSX_Filter_Length(3, 64), new PSX_Filter_Html()), 'name', 'Name');

		if(!$this->_validate->hasError())
		{
			$this->name = $name;
		}
		else
		{
			throw new PSX_Data_Exception($this->_validate->getLastError());
		}
	}

	public function setEmail($email)
	{
		$email = $this->_validate->apply($email, 'string', array(new PSX_Filter_Length(3, 64), new PSX_Filter_Email()), 'email', 'Email');

		if(!$this->_validate->hasError())
		{
			$this->email = $email;
		}
		else
		{
			throw new PSX_Data_Exception($this->_validate->getLastError());
		}
	}

	public function setUrl($url)
	{
		$url = $this->_validate->apply($url, 'string', array(new PSX_Filter_Length(3, 256), new PSX_Filter_Url()), 'url', 'Url');

		if(!$this->_validate->hasError())
		{
			$this->url = $url;
		}
		else
		{
			throw new PSX_Data_Exception($this->_validate->getLastError());
		}
	}

	public function setTitle($title)
	{
		$title = $this->_validate->apply($title, 'string', array(new PSX_Filter_Length(3, 64), new PSX_Filter_Html()), 'title', 'Title');

		if(!$this->_validate->hasError())
		{
			$this->title = $title;
		}
		else
		{
			throw new PSX_Data_Exception($this->_validate->getLastError());
		}
	}

	public function setDescription($description)
	{
		$description = $this->_validate->apply($description, 'string', array(new PSX_Filter_Length(3, 512), new PSX_Filter_Html()), 'description', 'Description');

		if(!$this->_validate->hasError())
		{
			$this->description = $description;
		}
		else
		{
			throw new PSX_Data_Exception($this->_validate->getLastError());
		}
	}

	public function setCallback($callback)
	{
		$callback = $this->_validate->apply($callback, 'string', array(new PSX_Filter_Length(0, 256), new AmunService_Oauth_Filter_Callback()), 'callback', 'Callback');

		if(!$this->_validate->hasError())
		{
			$this->callback = $callback;
		}
		else
		{
			throw new PSX_Data_Exception($this->_validate->getLastError());
		}
	}

	public function getId()
	{
		return $this->_base->getUrn('system', 'api', $this->id);
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

				return parent::export($result);

				break;

			case PSX_Data_WriterInterface::ATOM:

				$entry = $result->getWriter()->createEntry();

				$entry->setTitle($this->title);
				$entry->setId('urn:uuid:' . $this->_base->getUUID('system:api:' . $this->id));
				$entry->setUpdated($this->getDate());
				$entry->addAuthor('System');
				$entry->addLink($this->url, 'alternate', 'text/html');
				$entry->setContent($this->description, 'text');

				return $entry;

				break;

			default:

				throw new PSX_Data_Exception('Writer is not supported');

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


