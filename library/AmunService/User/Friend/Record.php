<?php
/*
 *  $Id: Friend.php 683 2012-06-03 11:52:32Z k42b3.x@googlemail.com $
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
 * Amun_User_Friend
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   Amun
 * @package    Amun_User_Friend
 * @version    $Revision: 683 $
 */
class AmunService_User_Friend_Record extends Amun_Data_RecordAbstract
{
	const REQUEST = 0x1;
	const NORMAL  = 0x2;

	protected $_group;
	protected $_user;
	protected $_friend;
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

	public function setGroupId($groupId)
	{
		$groupId = $this->_validate->apply($groupId, 'integer', array(new Amun_Filter_Id(Amun_Sql_Table_Registry::get('User_Friend_Group'))), 'groupId', 'Group Id');

		if(!$this->_validate->hasError())
		{
			$this->groupId = $groupId;
		}
		else
		{
			throw new PSX_Data_Exception($this->_validate->getLastError());
		}
	}

	public function setFriendId($friendId)
	{
		$friendId = $this->_validate->apply($friendId, 'integer', array(new Amun_Filter_Id(Amun_Sql_Table_Registry::get('User_Friend'))), 'friendId', 'Friend Id');

		if(!$this->_validate->hasError())
		{
			$this->friendId = $friendId;
		}
		else
		{
			throw new PSX_Data_Exception($this->_validate->getLastError());
		}
	}

	public function getId()
	{
		return $this->_base->getUrn('user', 'friend', $this->id);
	}

	public function getGroup()
	{
		if($this->_group === null)
		{
			$this->_group = Amun_Sql_Table_Registry::get('User_Friend_Group')->getRecord($this->groupId);
		}

		return $this->_group;
	}

	public function getUser()
	{
		if($this->_user === null)
		{
			$this->_user = Amun_Sql_Table_Registry::get('User_Account')->getRecord($this->userId);
		}

		return $this->_user;
	}

	public function getFriend()
	{
		if($this->_friend === null)
		{
			$this->_friend = Amun_Sql_Table_Registry::get('User_Account')->getRecord($this->friendId);
		}

		return $this->_friend;
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

				$entry->setTitle($this->friendName);
				$entry->setId('urn:uuid:' . $this->friendGlobalId);
				$entry->setUpdated($this->getDate());
				$entry->addAuthor($this->authorName, $this->authorProfileUrl);
				$entry->addLink($this->friendProfileUrl, 'alternate', 'text/html');

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

			self::REQUEST => 'Request',
			self::NORMAL  => 'Normal',

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

