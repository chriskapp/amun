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

namespace AmunService\Openid;

use Amun\DataFactory;
use Amun\Data\HandlerAbstract;
use Amun\Data\RecordAbstract;
use Amun\Exception;
use Amun\Filter as AmunFilter;
use Amun\Util;
use AmunService\Openid\Filter as OpenidFilter;
use PSX\Data\WriterInterface;
use PSX\Data\WriterResult;
use PSX\DateTime;
use PSX\Filter;
use PSX\Util\Markdown;

/**
 * Record
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/gpl.html GPLv3
 * @link    http://amun.phpsx.org
 */
class Record extends RecordAbstract
{
	const NORMAL   = 0x1;
	const APPROVED = 0x2;
	const DENIED   = 0x3;

	protected $_user;
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

	public function setUserId($userId)
	{
		$userId = $this->_validate->apply($userId, 'integer', array(new AmunFilter\Id($this->_hm->getTable('AmunService\User\Account'))), 'userId', 'User Id');

		if(!$this->_validate->hasError())
		{
			$this->userId = $userId;
		}
		else
		{
			throw new Exception($this->_validate->getLastError());
		}
	}

	public function setStatus($status)
	{
		$status = $this->_validate->apply($status, 'integer', array(new OpenidFilter\Status()), 'status', 'Status');

		if(!$this->_validate->hasError())
		{
			$this->status = $status;
		}
		else
		{
			throw new Exception($this->_validate->getLastError());
		}
	}

	public function setClaimedId($claimedId)
	{
		$claimedId = $this->_validate->apply($claimedId, 'string', array(new Filter\Length(3, 256), new Filter\Url()), 'claimedId', 'Claimed Id');

		if(!$this->_validate->hasError())
		{
			$this->claimedId = $claimedId;
		}
		else
		{
			throw new Exception($this->_validate->getLastError());
		}
	}

	public function setIdentity($identity)
	{
		$identity = $this->_validate->apply($identity, 'string', array(new Filter\Length(3, 256), new Filter\Url()), 'identity', 'Identity');

		if(!$this->_validate->hasError())
		{
			$this->identity = $identity;
		}
		else
		{
			throw new Exception($this->_validate->getLastError());
		}
	}

	public function setReturnTo($returnTo)
	{
		$returnTo = $this->_validate->apply($returnTo, 'string', array(new Filter\Length(3, 256), new Filter\Url()), 'returnTo', 'Return To');

		if(!$this->_validate->hasError())
		{
			$this->returnTo = $returnTo;
		}
		else
		{
			throw new Exception($this->_validate->getLastError());
		}
	}

	public function setResponseNonce($responseNonce)
	{
		$this->responseNonce = $responseNonce;
	}

	public function setAssocHandle($assocHandle)
	{
		$this->assocHandle = $assocHandle;
	}

	public function setSig($sig)
	{
		$this->sig = $sig;
	}

	public function setSecret($secret)
	{
		$this->secret = $secret;
	}

	public function setExpire($expire)
	{
		$this->expire = $expire;
	}

	public function getId()
	{
		return $this->_base->getUrn('openid', $this->id);
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

			self::TEMPORARY => 'Temporary',
			self::APPROVED  => 'Approved',
			self::ACCESS    => 'Access',

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


