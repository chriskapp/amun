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

namespace AmunService\User\Activity\Receiver;

use Amun\Data\RecordAbstract;
use Amun\Filter;
use Amun\Exception;
use Amun\DataFactory;
use AmunService\User\Activity\Receiver;
use DateTime;

/**
 * Record
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/gpl.html GPLv3
 * @link    http://amun.phpsx.org
 */
class Record extends RecordAbstract
{
	const VISIBLE = 0x1;
	const HIDDEN  = 0x2;

	protected $_activity;
	protected $_user;
	protected $_date;

	public function setId($id)
	{
		$id = $this->_validate->apply($id, 'integer', array(new Filter\Id($this->_table)), 'id', 'Id');

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
		$status = $this->_validate->apply($status, 'integer', array(new Receiver\Filter\Status()), 'status', 'Status');

		if(!$this->_validate->hasError())
		{
			$this->status = $status;
		}
		else
		{
			throw new Exception($this->_validate->getLastError());
		}
	}

	public function setActivityId($activityId)
	{
		$activityId = $this->_validate->apply($activityId, 'integer', array(new Filter\Id($this->_hm->getTable('AmunService\User\Activity'))), 'activityId', 'Activity Id');

		if(!$this->_validate->hasError())
		{
			$this->activityId = $activityId;
		}
		else
		{
			throw new Exception($this->_validate->getLastError());
		}
	}

	public function setUserId($userId)
	{
		$userId = $this->_validate->apply($userId, 'integer', array(new Filter\Id($this->_hm->getTable('AmunService\User\Account'))), 'userId', 'User Id');

		if(!$this->_validate->hasError())
		{
			$this->userId = $userId;
		}
		else
		{
			throw new Exception($this->_validate->getLastError());
		}
	}

	public function getId()
	{
		return $this->_base->getUrn('user', 'activity', 'receiver', $this->id);
	}

	public function getActivity()
	{
		if($this->_activity === null)
		{
			$this->_activity = $this->_hm->getHandler('AmunService\User\Activity')->getRecord($this->activityId);
		}

		return $this->_activity;
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

	public static function getStatus($status = false)
	{
		$s = array(

			self::VISIBLE => 'Visible',
			self::HIDDEN  => 'Hidden',

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


