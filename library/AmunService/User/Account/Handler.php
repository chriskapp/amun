<?php
/*
 *  $Id: Handler.php 880 2012-10-27 13:14:26Z k42b3.x@googlemail.com $
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

namespace AmunService\User\Account;

use Amun\Data\RecordAbstract;
use Amun\Data\HandlerAbstract;
use Amun\DataFactory;
use Amun\Exception;
use Amun\Security;
use AmunService\User\Friend;
use AmunService\Core\Host;
use PSX\DateTime;
use PSX\Http;
use PSX\Url;
use PSX\Webfinger;
use PSX\Sql;
use PSX\Sql\Condition;
use PSX\Sql\Join;
use PSX\Data\RecordInterface;

/**
 * Amun_User_Account_Handler
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   Amun
 * @package    Amun_User_Account
 * @version    $Revision: 880 $
 */
class Handler extends HandlerAbstract
{
	public function getByIdentity($identity)
	{
		return DataFactory::getTable('User_Account')
			->select(array('id', 'status', 'name', 'email'))
			->where('identity', '=', $identity)
			->getRow(Sql::FETCH_OBJECT);
	}

	public function getRecoverByToken($token)
	{
		return DataFactory::getTable('User_Account')
			->select(array('id', 'name', 'ip', 'email', 'date'))
			->where('token', '=', $token)
			->where('status', '=', Record::RECOVER)
			->getRow(Sql::FETCH_OBJECT);
	}

	public function getNotActivatedByToken($token)
	{
		return DataFactory::getTable('User_Account')
			->select(array('id', 'ip', 'date'))
			->where('token', '=', $token)
			->where('status', '=', Record::NOT_ACTIVATED)
			->getRow(Sql::FETCH_OBJECT);
	}

	public function create(RecordInterface $record)
	{
		if($record->hasFields('groupId', 'status', 'identity', 'name', 'pw'))
		{
			// check whether identity exists
			$con = new Condition(array('identity', '=', $record->identity));

			if($this->table->count($con) > 0)
			{
				throw new Exception('Identity already exists');
			}


			// default values
			if(!isset($record->countryId))
			{
				$record->setCountryId(1);
			}

			if(!isset($record->timezone))
			{
				$record->setTimezone('UTC');
			}


			$date = new DateTime('NOW', $this->registry['core.default_timezone']);

			$record->token    = Security::generateToken();
			$record->ip       = $_SERVER['REMOTE_ADDR'];
			$record->lastSeen = $date->format(DateTime::SQL);
			$record->updated  = $date->format(DateTime::SQL);
			$record->date     = $date->format(DateTime::SQL);


			// set host id if we have an remote host discover the profile url
			if(empty($record->hostId))
			{
				$record->hostId     = 0;
				$record->profileUrl = $this->config['psx_url']. '/' . $this->config['psx_dispatch'] . 'profile/' . $record->name;
			}
			else
			{
				$record->status     = Record::REMOTE;
				$record->profileUrl = $this->discoverProfileUrl($record->hostId, $record->name);
			}


			// set global id
			if(!isset($record->globalId))
			{
				$profileUrl = new Url($record->profileUrl);

				$record->globalId = $this->base->getUUID('user:account:' . $profileUrl->getHost() . ':' . $record->name . ':' . uniqid());
			}


			// set thumbnail if email available and thumbnail not set
			if(!isset($record->thumbnailUrl))
			{
				$default = $this->config['psx_url'] . '/img/avatar/no_image.png';

				if(!empty($record->email))
				{
					$record->thumbnailUrl = 'http://www.gravatar.com/avatar/' . md5(strtolower(trim($record->email))) . '?d=' . urlencode($default) . '&s=48';
				}
				else
				{
					$record->thumbnailUrl = $default;
				}
			}


			$this->table->insert($record->getData());


			$record->id = $this->sql->getLastInsertId();


			// insert relation to self
			$this->sql->insert($this->registry['table.user_friend'], array(

				'status'   => Friend\Record::NORMAL,
				'userId'   => $record->id,
				'friendId' => $record->id,
				'date'     => $date->format(DateTime::SQL),

			));


			$this->notify(RecordAbstract::INSERT, $record);


			return $record;
		}
		else
		{
			throw new Exception('Missing field in record');
		}
	}

	public function update(RecordInterface $record)
	{
		if($record->hasFields('id'))
		{
			$date = new DateTime('NOW', $this->registry['core.default_timezone']);

			$record->ip      = $_SERVER['REMOTE_ADDR'];
			$record->updated = $date->format(DateTime::SQL);


			// set host id if we have an remote host discover the profile url
			if(isset($record->hostId))
			{
				if(!isset($record->name))
				{
					$con  = new Condition(array('id', '=', $record->id));
					$name = $this->sql->select($this->table->getName(), array('name'), $con, Sql::SELECT_FIELD);
				}
				else
				{
					$name = $record->name;
				}

				if(empty($record->hostId))
				{
					$record->hostId     = 0;
					$record->profileUrl = $this->config['psx_url']. '/' . $this->config['psx_dispatch'] . 'profile.htm/' . $name;
				}
				else
				{
					$record->status     = Record::REMOTE;
					$record->profileUrl = $this->discoverProfileUrl($record->hostId, $name);
				}
			}


			// set thumbnail if email available and thumbnail not set
			if(!empty($record->email) && !isset($record->thumbnailUrl))
			{
				$default = $this->config['psx_url'] . '/img/avatar/no_image.png';

				$record->thumbnailUrl = 'http://www.gravatar.com/avatar/' . md5($record->email) . '.jpg?d=' . urlencode($default) . '&s=48';
			}


			$con = new Condition(array('id', '=', $record->id));

			$this->table->update($record->getData(), $con);


			$this->notify(RecordAbstract::UPDATE, $record);


			return $record;
		}
		else
		{
			throw new Exception('Missing field in record');
		}
	}

	public function delete(RecordInterface $record)
	{
		if($record->hasFields('id'))
		{
			$con = new Condition(array('id', '=', $record->id));

			$this->table->delete($con);


			$this->notify(RecordAbstract::DELETE, $record);


			return $record;
		}
		else
		{
			throw new Exception('Missing field in record');
		}
	}

	protected function getDefaultSelect()
	{
		return $this->table
			->select(array('id', 'globalId', 'groupId', 'status', 'name', 'updated', 'profileUrl', 'thumbnailUrl', 'date'))
			->join(Join::INNER, DataFactory::getTable('User_Group')
				->select(array('title'), 'group')
			)
			->join(Join::INNER, DataFactory::getTable('Country')
				->select(array('title'), 'country')
			);
	}

	private function discoverProfileUrl($hostId, $name)
	{
		if(empty($name))
		{
			throw new Exception('Need name to discover remote profile');
		}

		$sql = <<<SQL
SELECT
	`host`.`name`     AS `hostName`,
	`host`.`template` AS `hostTemplate`
FROM 
	{$this->registry['table.core_host']} `host`
WHERE 
	`host`.`id` = ?
AND 
	`host`.`status` = ?
SQL;

		$row = $this->sql->getRow($sql, array($hostId, Host\Record::NORMAL));

		if(!empty($row))
		{
			$http      = new Http();
			$webfinger = new Webfinger($http);
			$email     = $name . '@' . $row['hostName'];

			return $webfinger->getAcctProfile($email, $row['hostTemplate']);
		}
		else
		{
			throw new Exception('Invalid host id');
		}
	}
}


