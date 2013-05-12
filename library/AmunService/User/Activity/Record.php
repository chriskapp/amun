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

namespace AmunService\User\Activity;

use Amun\DataFactory;
use Amun\Data\HandlerAbstract;
use Amun\Data\RecordAbstract;
use Amun\Data\StreamAbstract;
use Amun\Exception;
use Amun\Filter as AmunFilter;
use Amun\Util;
use AmunService\User\Activity\Filter as ActivityFilter;
use PSX\Data\WriterInterface;
use PSX\Data\WriterResult;
use PSX\DateTime;
use PSX\Filter;
use PSX\Util\Markdown;
use PSX\Sql;
use PSX\Sql\Join;

/**
 * Record
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/gpl.html GPLv3
 * @link    http://amun.phpsx.org
 */
class Record extends RecordAbstract
{
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

	public function setParentId($parentId)
	{
		$parentId = $this->_validate->apply($parentId, 'integer', array(new AmunFilter\Id(DataFactory::getTable('User_Activity'), true)), 'parentId', 'Parent Id');

		if(!$this->_validate->hasError())
		{
			$this->parentId = $parentId;
		}
		else
		{
			throw new Exception($this->_validate->getLastError());
		}
	}

	public function setScope($scope)
	{
		$scope = $this->_validate->apply($scope, 'integer', array(new AmunFilter\Id(DataFactory::getTable('User_Friend_Group'), true)), 'scope', 'Scope');

		if(!$this->_validate->hasError())
		{
			$this->scope = $scope;
		}
		else
		{
			throw new Exception($this->_validate->getLastError());
		}
	}

	public function setVerb($verb)
	{
		$verb = $this->_validate->apply($verb, 'string', array(new ActivityFilter\Verb()), 'verb', 'Verb');

		if(!$this->_validate->hasError())
		{
			$this->verb = $verb;
		}
		else
		{
			throw new Exception($this->_validate->getLastError());
		}
	}

	public function setSummary($summary)
	{
		$summary = Markdown::decode($summary);
		$summary = $this->_validate->apply($summary, 'string', array(new Filter\Length(3, 4096), new AmunFilter\Html($this->_config, $this->_user, true)), 'summary', 'Summary');

		if(!$this->_validate->hasError())
		{
			$this->summary = $summary;
		}
		else
		{
			throw new Exception($this->_validate->getLastError());
		}
	}

	public function getId()
	{
		return $this->_base->getUrn('user', 'activity', $this->id);
	}

	public function getUser()
	{
		if($this->_user === null)
		{
			$this->_user = DataFactory::getTable('User_Account')->getRecord($this->userId);
		}

		return $this->_user;
	}

	public function getSummary()
	{
		return htmlspecialchars($this->summary, ENT_NOQUOTES, 'UTF-8');
	}

	public function getDate()
	{
		if($this->_date === null)
		{
			$this->_date = new DateTime($this->date, $this->_registry['core.default_timezone']);
		}

		return $this->_date;
	}

	public function getComments()
	{
		return $this->_table->select(array('id', 'refId', 'table', 'verb', 'summary', 'date'))
			->join(Join::INNER, DataFactory::getTable('User_Account')
				->select(array('name', 'profileUrl', 'thumbnailUrl'), 'author')
			)
			->where('parentId', '=', $this->id)
			->orderBy('date', Sql::SORT_ASC)
			->getAll(Sql::FETCH_OBJECT, '\AmunService\User\Activity\Record', array($this->_table, $this->_ct));
	}

	public function getObject()
	{
		if(empty($this->table))
		{
			$stream = DataFactory::getInstance()->getStreamInstance($this->_table->getName());

			return $stream->getObject($this->id);
		}
		else if($this->refId > 0)
		{
			$stream = DataFactory::getInstance()->getStreamInstance($this->table);

			if($stream instanceof StreamAbstract)
			{
				return $stream->getObject($this->refId);
			}
		}

		return null;
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

				$entry->setTitle(Util::stripAndTruncateHtml($this->summary));
				$entry->setId('urn:uuid:' . $this->globalId);
				$entry->setUpdated($this->getDate());
				$entry->addAuthor($this->authorName, 'urn:uuid:' . $this->authorGlobalId);
				$entry->setContent($this->summary, 'html');

				if($this->parentId > 0)
				{
					$writer = $result->getWriter()->writer;
					$parent = $this->_table->select(array('id', 'globalId', 'date'))
						->where('id', '=', $this->parentId)
						->getRow();

					$writer->startElementNS('thr', 'in-reply-to', 'http://purl.org/syndication/thread/1.0');
					$writer->writeAttribute('ref', 'urn:uuid:' . $parent['globalId']);
					$writer->endElement();
				}

				return $entry;
				break;

			default:
				throw new Exception('Writer is not supported');
				break;
		}
	}
}


