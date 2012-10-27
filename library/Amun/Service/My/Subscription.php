<?php
/*
 *  $Id: Subscription.php 683 2012-06-03 11:52:32Z k42b3.x@googlemail.com $
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
 * Amun_Service_My_Subscription
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   Amun
 * @package    Amun_Service_My
 * @version    $Revision: 683 $
 */
class Amun_Service_My_Subscription extends Amun_Data_RecordAbstract
{
	protected $_user;
	protected $_hub;
	protected $_topic;
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

	public function setHub($hub)
	{
		$hub = $this->_validate->apply($hub, 'string', array(new PSX_Filter_Length(3, 256), new PSX_Filter_Url()), 'hub', 'Hub');

		if(!$this->_validate->hasError())
		{
			$this->hub = $hub;
		}
		else
		{
			throw new PSX_Data_Exception($this->_validate->getLastError());
		}
	}

	public function setTopic($topic)
	{
		$topic = $this->_validate->apply($topic, 'string', array(new PSX_Filter_Length(3, 256), new PSX_Filter_Url()), 'topic', 'Topic');

		if(!$this->_validate->hasError())
		{
			$this->topic = $topic;
		}
		else
		{
			throw new PSX_Data_Exception($this->_validate->getLastError());
		}
	}

	public function setSecret($secret)
	{
		$this->secret = $secret;
	}

	public function setVerifyToken($verifyToken)
	{
		$this->verifyToken = $verifyToken;
	}

	public function getId()
	{
		return $this->_base->getUrn('service', 'my', 'subscription', $this->id);
	}

	public function getUser()
	{
		if($this->_user === null)
		{
			$this->_user = Amun_Sql_Table_Registry::get('User_Account')->getRecord($this->userId);
		}

		return $this->_user;
	}

	public function getHub()
	{
		if($this->_hub === null)
		{
			$this->_hub = new PSX_Url($this->hub);
		}

		return $this->_hub;
	}

	public function getTopic()
	{
		if($this->_topic === null)
		{
			$this->_topic = new PSX_Url($this->topic);
		}

		return $this->_topic;
	}

	public function getDate()
	{
		if($this->_date === null)
		{
			$this->_date = new DateTime($this->date, $this->_registry['core.default_timezone']);
		}

		return $this->_date;
	}
}


