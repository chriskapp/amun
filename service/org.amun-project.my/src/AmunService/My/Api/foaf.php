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

namespace my\api;

use AmunService\User\Friend;
use Amun\Exception;
use Amun\DataFactory;
use Amun\Module\ApiAbstract;
use Amun\Sql\Table\Registry;
use PSX\Sql\Join;
use XMLWriter;

/**
 * foaf
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/gpl.html GPLv3
 * @link    http://amun.phpsx.org
 */
class foaf extends ApiAbstract
{
	/**
	 * Returns FOAF informations about an user
	 *
	 * @httpMethod GET
	 * @path /{userName}
	 * @nickname getFoaf
	 * @responseClass PSX_Data_ResultSet
	 */
	public function getFoaf()
	{
		header('Content-type: application/rdf+xml');

		$account = $this->getAccount();
		$friends = $this->getFriends($account['id']);

		$this->writer = new XMLWriter();
		$this->writer->openURI('php://output');
		$this->writer->setIndent(true);
		$this->writer->startDocument('1.0', 'UTF-8');

		$this->writer->startElementNS('rdf', 'RDF', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#');
		$this->writer->writeAttribute('xmlns:foaf', 'http://xmlns.com/foaf/0.1/');

		// person
		$this->writer->startElementNS('foaf', 'Person', null);

		$this->writer->writeAttributeNS('rdf', 'ID', null, $account['globalId']);
		$this->writer->writeElementNS('foaf', 'name', null, $account['name']);
		$this->writer->writeElementNS('foaf', 'gender', null, $account['gender']);
		$this->writer->writeElementNS('foaf', 'thumbnail', null, $account['thumbnailUrl']);
		$this->writer->startElementNS('foaf', 'page', null);
		$this->writer->startElementNS('foaf', 'Document', null);
		$this->writer->writeAttributeNS('rdf', 'about', null, $account['profileUrl']);
		$this->writer->endElement();
		$this->writer->endElement();

		// friends
		if(!empty($friends))
		{
			$this->writer->startElementNS('foaf', 'knows', null);

			foreach($friends as $friend)
			{
				$this->writer->startElementNS('foaf', 'Person', null);
				$this->writer->writeAttributeNS('rdf', 'ID', null, $friend['friendGlobalId']);
				$this->writer->writeElementNS('foaf', 'name', null, $friend['friendName']);
				$this->writer->writeElementNS('foaf', 'gender', null, $friend['friendGender']);
				$this->writer->writeElementNS('foaf', 'thumbnail', null, $friend['friendThumbnailUrl']);
				$this->writer->endElement();
			}

			$this->writer->endElement();
		}

		$this->writer->endElement();
		$this->writer->endElement();
		$this->writer->endDocument();
	}

	private function getAccount()
	{
		// get user id
		$userName = $this->getUriFragments('userName');

		if(!empty($userName) && $userName != '@me')
		{
			if(!is_numeric($userName))
			{
				$col = 'name';
				$val = $userName;
			}
			else
			{
				$col = 'id';
				$val = (integer) $userName;
			}
		}
		else
		{
			$col = 'id';
			$val = $this->user->getId();
		}

		$account = $this->hm->getTable('AmunService\User\Account')
			->select(array('id', 'globalId', 'name', 'gender', 'profileUrl', 'thumbnailUrl'))
			->where($col, '=', $val)
			->getRow();

		if(!empty($account))
		{
			return $account;
		}
		else
		{
			throw new Amun_Exception('Invalid account');
		}
	}

	private function getFriends($userId)
	{
		return $this->hm->getTable('AmunService\User\Friend')
			->select(array('id', 'status', 'date'))
			->join(Join::INNER, $this->hm->getTable('AmunService\User\Account')
				->select(array('id', 'globalId', 'name'), 'author'),
				'n:1',
				'userId'
			)
			->join(Join::INNER, $this->hm->getTable('AmunService\User\Account')
				->select(array('id', 'globalId', 'name', 'gender', 'profileUrl', 'thumbnailUrl'), 'friend'),
				'n:1',
				'friendId'
			)
			->where('authorId', '=', $userId)
			->where('status', '=', Friend\Record::NORMAL)
			->getAll();
	}
}
