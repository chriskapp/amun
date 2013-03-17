<?php
/*
 *  $Id: Request.php 683 2012-06-03 11:52:32Z k42b3.x@googlemail.com $
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

namespace AmunService\Oauth\Request;

use Amun\DataFactory;
use Amun\Data\HandlerAbstract;
use Amun\Data\RecordAbstract;
use Amun\Exception;
use Amun\Filter as AmunFilter;
use Amun\Util;
use PSX\Data\WriterInterface;
use PSX\Data\WriterResult;
use PSX\DateTime;
use PSX\Filter;
use PSX\Util\Markdown;
use DateInterval;

/**
 * AmunService_Oauth_Request_Record
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   Amun
 * @package    Amun_Oauth
 * @version    $Revision: 683 $
 */
class Record extends RecordAbstract
{
	protected $_api;
	protected $_user;
	protected $_expire;
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

	public function setApiId($apiId)
	{
		$apiId = $this->_validate->apply($apiId, 'integer', array(new AmunFilter\Id(DataFactory::getTable('Oauth'))), 'apiId', 'Api Id');

		if(!$this->_validate->hasError())
		{
			$this->apiId = $apiId;
		}
		else
		{
			throw new Exception($this->_validate->getLastError());
		}
	}

	public function setStatus($status)
	{
		$this->status = $status;
	}

	public function setIp($ip)
	{
		$this->ip = $ip;
	}

	public function setNonce($nonce)
	{
		$this->nonce = $nonce;
	}

	public function setCallback($callback)
	{
		$this->callback = $callback;
	}

	public function setToken($token)
	{
		$this->token = $token;
	}

	public function setTokenSecret($tokenSecret)
	{
		$this->tokenSecret = $tokenSecret;
	}

	public function setVerifier($verifier)
	{
		$this->verifier = $verifier;
	}

	public function setTimestamp($timestamp)
	{
		$this->timestamp = $timestamp;
	}

	public function setExpire($expire)
	{
		$this->expire = $expire;
	}

	public function getId()
	{
		return $this->_base->getUrn('oauth', 'request', $this->id);
	}

	public function getApi()
	{
		if($this->_api === null)
		{
			$this->_api = DataFactory::getTable('Oauth')->getRecord($this->apiId);
		}

		return $this->_api;
	}

	public function getUser()
	{
		if($this->_user === null)
		{
			$this->_user = DataFactory::getTable('User_Account')->getRecord($this->userId);
		}

		return $this->_user;
	}

	public function getExpire()
	{
		if($this->_expire === null)
		{
			$this->_expire = new DateInterval($this->expire);
		}

		return $this->_expire;
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


