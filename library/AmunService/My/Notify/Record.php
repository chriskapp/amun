<?php
/*
 *  $Id: Notify.php 683 2012-06-03 11:52:32Z k42b3.x@googlemail.com $
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
 * Amun_Service_My_Notify
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   Amun
 * @package    Amun_Service_My
 * @version    $Revision: 683 $
 */
class AmunService_My_Notify extends Amun_Data_RecordAbstract
{
	const ACTIVE   = 0x1;
	const DISABLED = 0x2;

	protected $_user;
	protected $_service;
	protected $_contact;
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

	public function setServiceId($serviceId)
	{
		$serviceId = $this->_validate->apply($serviceId, 'integer', array(new Amun_Filter_Id(Amun_Sql_Table_Registry::get('My_Notify_Service'))), 'serviceId', 'Service Id');

		if(!$this->_validate->hasError())
		{
			$this->serviceId = $serviceId;
		}
		else
		{
			throw new PSX_Data_Exception($this->_validate->getLastError());
		}
	}

	public function setContactId($contactId)
	{
		$contactId = $this->_validate->apply($contactId, 'integer', array(new Amun_Filter_Id(Amun_Sql_Table_Registry::get('My_Contact'))), 'contactId', 'Contact Id');

		if(!$this->_validate->hasError())
		{
			$this->contactId = $contactId;
		}
		else
		{
			throw new PSX_Data_Exception($this->_validate->getLastError());
		}
	}

	public function setStatus($status)
	{
		$status = $this->_validate->apply($status, 'integer', array(new AmunService_My_Notify_Filter_Status()), 'status', 'Status');

		if(!$this->_validate->hasError())
		{
			$this->status = $status;
		}
		else
		{
			throw new PSX_Data_Exception($this->_validate->getLastError());
		}
	}

	public function getId()
	{
		return $this->_base->getUrn('service', 'my', 'contact', $this->id);
	}

	public function getUser()
	{
		if($this->_user === null)
		{
			$this->_user = Amun_Sql_Table_Registry::get('Core_User_Account')->getRecord($this->userId);
		}

		return $this->_user;
	}

	public function getService()
	{
		if($this->_service === null)
		{
			$this->_service = Amun_Sql_Table_Registry::get('My_Notify_Service')->getRecord($this->serviceId);
		}

		return $this->_service;
	}

	public function getContact()
	{
		if($this->_contact === null)
		{
			$this->_contact = Amun_Sql_Table_Registry::get('My_Contact')->getRecord($this->contactId);
		}

		return $this->_contact;
	}

	public function getDate()
	{
		if($this->_date === null)
		{
			$this->_date = new DateTime($this->date, $this->_registry['core.default_timezone']);
		}

		return $this->_date;
	}

	public static function getStatus($status = false)
	{
		$s = array(

			self::ACTIVE   => 'Active',
			self::DISABLED => 'Disabled',

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


