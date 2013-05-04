<?php
/*
 *  $Id: Handler.php 635 2012-05-01 19:46:37Z k42b3.x@googlemail.com $
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

namespace AmunService\Oauth\Access;

use Amun\DataFactory;
use Amun\Data\HandlerAbstract;
use Amun\Data\RecordAbstract;
use Amun\Exception;
use AmunService\Core\Approval;
use PSX\DateTime;
use PSX\Data\RecordInterface;
use PSX\Data\ResultSet;
use PSX\Sql;
use PSX\Sql\Condition;
use PSX\Sql\Join;

/**
 * AmunService_Oauth_Access_Handler
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   Amun
 * @package    Amun_Oauth
 * @version    $Revision: 635 $
 */
class Handler extends HandlerAbstract
{
	public function getAllowedApplication($applicationId, $userId)
	{
		return DataFactory::getTable('Oauth_Access')
			->select(array('id', 'date'))
			->join(Join::INNER, DataFactory::getTable('Oauth')
				->select(array('id', 'status', 'url', 'title', 'description'), 'api')
			)
			->join(Join::INNER, DataFactory::getTable('User_Account')
				->select(array('id', 'name'), 'author')
			)
			->where('id', '=', $applicationId)
			->where('authorId', '=', $userId)
			->where('allowed', '=', 1)
			->getRow(Sql::FETCH_OBJECT, $this->getClassName(), $this->getClassArgs());
	}

	public function isAllowed($apiId, $userId)
	{
		return DataFactory::getTable('Oauth_Access')
			->select(array('allowed'))
			->where('apiId', '=', $apiId)
			->where('userId', '=', $userId)
			->getField();
	}

	/**
	 * An efficent way to set rights for an $accessId. The method deletes all
	 * existing rights and inserts the defined $rights
	 *
	 * @param integer $accessId
	 * @param array $rights<integer>
	 * @return void
	 */
	public function setRights($accessId, array $rights)
	{
		$accessId = (integer) $accessId;

		if($accessId <= 0)
		{
			throw new Exception('Access id must be greater 0');
		}

		// delete existing rights
		$sql = <<<SQL
DELETE FROM 
	{$this->registry['table.oauth_access_right']} 
WHERE
	`accessId` = ?
SQL;

		$this->sql->execute($sql, array($accessId));

		// create sql query
		$parts = array();

		foreach($rights as $rightId)
		{
			$rightId = (integer) $rightId;
			$parts[] = '(' . $accessId . ',' . $rightId . ')';
		}

		if(!empty($parts))
		{
			$sql = implode(',', $parts);
			$sql = <<<SQL
INSERT INTO 
	{$this->registry['table.oauth_access_right']} (`accessId`, `rightId`)
VALUES
	{$sql}
SQL;

			$this->sql->execute($sql);
		}
	}

	public function create(RecordInterface $record)
	{
		throw new Exception('Access can not created');
	}

	public function update(RecordInterface $record)
	{
		throw new Exception('Access can not updated');
	}

	public function delete(RecordInterface $record)
	{
		if($record->hasFields('id'))
		{
			$con = new Condition(array('id', '=', $record->id));

			$this->table->delete($con);


			$con = new Condition(array('accessId', '=', $record->id));

			DataFactory::getTable('Oauth_Access_Right')->delete($con);


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
			->select(array('id', 'userId', 'allowed', 'date'))
			->join(Join::INNER, DataFactory::getTable('Oauth')
				->select(array('id', 'title'), 'api')
			)
			->join(Join::INNER, DataFactory::getTable('User_Account')
				->select(array('name', 'profileUrl'), 'author')
			);
	}
}

