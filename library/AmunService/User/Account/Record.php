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

namespace AmunService\User\Account;

use Amun\DataFactory;
use Amun\Data\HandlerAbstract;
use Amun\Data\RecordAbstract;
use Amun\Exception;
use Amun\Filter as AmunFilter;
use Amun\Util;
use AmunService\User\Account\Filter as AccountFilter;
use PSX\Data\WriterInterface;
use PSX\Data\WriterResult;
use PSX\DateTime;
use PSX\Filter;
use PSX\Util\Markdown;
use PSX\Sql\Condition;
use PSX\Oauth\Provider\Data\Consumer;
use DateTimeZone;

/**
 * Record
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/gpl.html GPLv3
 * @link    http://amun.phpsx.org
 */
class Record extends RecordAbstract
{
	const BANNED        = 0x1;
	const ANONYMOUS     = 0x2;
	const NOT_ACTIVATED = 0x3;
	const NORMAL        = 0x4;
	const ADMINISTRATOR = 0x5;
	const RECOVER       = 0x6;
	const REMOTE        = 0x7;

	protected $_group;
	protected $_host;
	protected $_country;
	protected $_timezone;
	protected $_lastSeen;
	protected $_updated;
	protected $_date;
	protected $_karma;

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

	public function setGroupId($groupId)
	{
		$groupId = $this->_validate->apply($groupId, 'integer', array(new AmunFilter\Id(DataFactory::getTable('User_Group'))), 'groupId', 'Group Id');

		if(!$this->_validate->hasError())
		{
			$this->groupId = $groupId;
		}
		else
		{
			throw new Exception($this->_validate->getLastError());
		}
	}

	public function setHostId($hostId)
	{
		$hostId = $this->_validate->apply($hostId, 'integer', array(new AmunFilter\Id(DataFactory::getTable('Core_Host'), true)), 'hostId', 'Host Id');

		if(!$this->_validate->hasError())
		{
			$this->hostId = $hostId;
		}
		else
		{
			throw new Exception($this->_validate->getLastError());
		}
	}

	public function setCountryId($countryId)
	{
		$countryId = $this->_validate->apply($countryId, 'integer', array(new AmunFilter\Id(DataFactory::getTable('Country'))), 'countryId', 'Country Id');

		if(!$this->_validate->hasError())
		{
			$this->countryId = $countryId;
		}
		else
		{
			throw new Exception($this->_validate->getLastError());
		}
	}

	public function setStatus($status)
	{
		$status = $this->_validate->apply($status, 'integer', array(new AccountFilter\Status()), 'status', 'Status');

		if(!$this->_validate->hasError())
		{
			$this->status = $status;
		}
		else
		{
			throw new Exception($this->_validate->getLastError());
		}
	}

	public function setIdentity($identity)
	{
		$identity = $this->_validate->apply($identity, 'string', array(new AccountFilter\Identity(), new AmunFilter\Salt()), 'identity', 'Identity');

		if(!$this->_validate->hasError())
		{
			$this->identity = $identity;
		}
		else
		{
			throw new Exception($this->_validate->getLastError());
		}
	}

	public function setName($name)
	{
		$name = $this->_validate->apply($name, 'string', array(new AccountFilter\Name()), 'name', 'Name');

		if(!$this->_validate->hasError())
		{
			$this->name = $name;
		}
		else
		{
			throw new Exception($this->_validate->getLastError());
		}
	}

	public function setPw($pw)
	{
		$pw = $this->_validate->apply($pw, 'string', array(new AccountFilter\Pw(), new AmunFilter\Salt()), 'pw', 'Password');

		if(!$this->_validate->hasError())
		{
			$this->pw = $pw;
		}
		else
		{
			throw new Exception($this->_validate->getLastError());
		}
	}

	public function setEmail($email)
	{
		$email = trim($email);

		if(!empty($email))
		{
			$email = $this->_validate->apply($email, 'string', array(new Filter\Length(3, 64), new Filter\Email()), 'email', 'Email');

			if(!$this->_validate->hasError())
			{
				$this->email = $email;
			}
			else
			{
				throw new Exception($this->_validate->getLastError());
			}
		}
		else
		{
			$this->email = '';
		}
	}

	public function setToken($token)
	{
		$token = $this->_validate->apply($token, 'string', array(new Filter\Length(40, 40), new Filter\Xdigit()), 'token', 'Token');

		if(!$this->_validate->hasError())
		{
			$this->token = $token;
		}
		else
		{
			throw new Exception($this->_validate->getLastError());
		}
	}

	public function setGender($gender)
	{
		$gender = $this->_validate->apply($gender, 'string', array(new AccountFilter\Gender()), 'gender', 'Gender');

		if(!$this->_validate->hasError())
		{
			$this->gender = $gender;
		}
		else
		{
			throw new Exception($this->_validate->getLastError());
		}
	}

	public function setTimezone($timezone)
	{
		$timezone = $this->_validate->apply($timezone, 'string', array(new AccountFilter\Timezone()), 'timezone', 'Timezone');

		if(!$this->_validate->hasError())
		{
			$this->timezone = $timezone;
		}
		else
		{
			throw new Exception($this->_validate->getLastError());
		}
	}

	public function setLongitude($longitude)
	{
		$longitude = $this->_validate->apply($longitude, 'float', array(new Filter\Length(-180, 180)), 'longitude', 'Longitude');

		if(!$this->_validate->hasError())
		{
			$this->longitude = $longitude;
		}
		else
		{
			throw new Exception($this->_validate->getLastError());
		}
	}

	public function setLatitude($latitude)
	{
		$latitude = $this->_validate->apply($latitude, 'float', array(new Filter\Length(-90, 90)), 'latitude', 'Latitude');

		if(!$this->_validate->hasError())
		{
			$this->latitude = $latitude;
		}
		else
		{
			throw new Exception($this->_validate->getLastError());
		}
	}

	public function getId()
	{
		return $this->_base->getUrn('user', 'account', $this->id);
	}

	public function getGroup()
	{
		if($this->_group === null)
		{
			$this->_group = DataFactory::getTable('User_Group')->getRecord($this->groupId);
		}

		return $this->_group;
	}

	public function getHost()
	{
		if($this->_host === null && $this->hostId > 0)
		{
			$this->_host = DataFactory::getTable('Core_Host')->getRecord($this->hostId);
		}

		return $this->_host;
	}

	public function getCountry()
	{
		if($this->_country === null)
		{
			$this->_country = DataFactory::getTable('Country')->getRecord($this->countryId);
		}

		return $this->_country;
	}

	public function getTimezone()
	{
		if($this->_timezone === null)
		{
			$this->_timezone = new DateTimeZone($this->timezone);
		}

		return $this->_timezone;
	}

	public function getLastSeen()
	{
		if($this->_lastSeen === null)
		{
			$this->_lastSeen = new DateTime($this->lastSeen, $this->_registry['core.default_timezone']);
		}

		return $this->_lastSeen;
	}

	public function getUpdated()
	{
		if($this->_updated === null)
		{
			$this->_updated = new DateTime($this->updated, $this->_registry['core.default_timezone']);
		}

		return $this->_updated;
	}

	public function getDate()
	{
		if($this->_date === null)
		{
			$this->_date = new DateTime($this->date, $this->_registry['core.default_timezone']);
		}

		return $this->_date;
	}

	public function getKarma()
	{
		if($this->_karma === null)
		{
			$con = new Condition(array('userId', '=', $this->id));

			$this->_karma = DataFactory::getTable('User_Activity')->count($con);
		}

		return $this->_karma;
	}

	public function getRemoteCredentials()
	{
		if($this->status == self::REMOTE)
		{
			$sql = <<<SQL
SELECT
	`host`.`consumerKey`,
	`host`.`consumerSecret`,
	`request`.`token`,
	`request`.`tokenSecret`
FROM 
	{$this->_registry['table.core_host_request']} `request`
INNER JOIN 
	{$this->_registry['table.core_host']} `host`
	ON `request`.`hostId` = `host`.`id`
WHERE 
	`request`.`hostId` = {$this->hostId}
AND 
	`request`.`userId` = {$this->id}
ORDER BY 
	`request`.`date` DESC
SQL;

			$row = $this->_sql->getRow($sql);

			if(!empty($row))
			{
				$consumer = new Consumer($row['consumerKey'], $row['consumerSecret'], $row['token'], $row['tokenSecret']);

				return $consumer;
			}
			else
			{
				return false;
			}
		}
		else
		{
			throw new Exception('User is not an remote account');
		}
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
				$entry->setId('urn:uuid:' . $this->globalId);
				$entry->setUpdated($this->getUpdated());
				$entry->addAuthor($this->name, $this->profileUrl);
				$entry->addLink($this->profileUrl, 'alternate', 'text/html');
				$entry->setContent($this, 'application/xml');

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

			self::BANNED        => 'Banned',
			self::ANONYMOUS     => 'Anonymous',
			self::NOT_ACTIVATED => 'Not Activated',
			self::NORMAL        => 'Normal',
			self::ADMINISTRATOR => 'Administrator',
			self::RECOVER       => 'Recover',
			self::REMOTE        => 'Remote',

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

	public static function getGender($gender = false)
	{
		$g = array(

			'undisclosed' => 'Undisclosed',
			'male'        => 'Male',
			'female'      => 'Female',

		);

		if($gender !== false)
		{
			if(array_key_exists($gender, $g))
			{
				return $g[$gender];
			}
			else
			{
				return false;
			}
		}
		else
		{
			return $g;
		}
	}
}
