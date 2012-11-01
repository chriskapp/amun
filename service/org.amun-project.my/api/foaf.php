<?php
/*
 *  $Id: foaf.php 875 2012-09-30 13:51:45Z k42b3.x@googlemail.com $
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
 * foaf
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   module
 * @package    api
 * @subpackage service_my
 * @version    $Revision: 875 $
 */
class foaf extends Amun_Module_ApiAbstract
{
	public function onLoad()
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
		$fragments = $this->getUriFragments();

		if(isset($fragments[0]) && $fragments[0] != '@me')
		{
			if(!is_numeric($fragments[0]))
			{
				$col = 'name';
				$val = $fragments[0];
			}
			else
			{
				$col = 'id';
				$val = (integer) $fragments[0];
			}
		}
		else
		{
			$col = 'id';
			$val = $this->user->id;
		}


		$account = Amun_Sql_Table_Registry::get('Core_User_Account')
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
		return Amun_Sql_Table_Registry::get('Core_User_Friend')
			->select(array('id', 'status', 'date'))
			->join(PSX_Sql_Join::INNER, Amun_Sql_Table_Registry::get('Core_User_Account')
				->select(array('id', 'globalId', 'name'), 'author'),
				'n:1',
				'userId'
			)
			->join(PSX_Sql_Join::INNER, Amun_Sql_Table_Registry::get('Core_User_Account')
				->select(array('id', 'globalId', 'name', 'gender', 'profileUrl', 'thumbnailUrl'), 'friend'),
				'n:1',
				'friendId'
			)
			->where('authorId', '=', $userId)
			->where('status', '=', Amun_User_Friend::NORMAL)
			->getAll();
	}
}
