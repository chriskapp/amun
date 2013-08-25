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

namespace AmunService\Vcshook\Commit;

use DateTimeZone;
use Amun\DataFactory;
use Amun\Data\RecordAbstract;
use Amun\Filter\Id;
use Amun\Exception;
use PSX\DateTime;
use PSX\Filter;
use PSX\Data\ReaderResult;
use PSX\Data\ReaderInterface;
use PSX\Data\WriterResult;
use PSX\Data\WriterInterface;
use PSX\Sql;
use PSX\Sql\Condition;

/**
 * Record
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/gpl.html GPLv3
 * @link    http://amun.phpsx.org
 */
class Record extends RecordAbstract
{
	protected $_project;
	protected $_author;
	protected $_commitDate;
	protected $_date;

	public function setId($id)
	{
		$id = $this->_validate->apply($id, 'integer', array(new Id($this->_table)), 'id', 'Id');

		if(!$this->_validate->hasError())
		{
			$this->id = $id;
		}
		else
		{
			throw new Exception($this->_validate->getLastError());
		}
	}

	public function setProjectId($projectId)
	{
		$projectId = $this->_validate->apply($projectId, 'integer', array(new Id($this->_hm->getTable('AmunService\Vcshook'))), 'projectId', 'Project Id');

		if(!$this->_validate->hasError())
		{
			$this->projectId = $projectId;
		}
		else
		{
			throw new Exception($this->_validate->getLastError());
		}
	}

	public function setAuthor($author)
	{
		$con      = new Condition(array('name', '=', $author));
		$authorId = $this->_sql->select($this->_registry['table.vcshook_author'], array('id'), $con, Sql::SELECT_FIELD);

		if(!empty($authorId))
		{
			$this->authorId = (integer) $authorId;
		}
		else
		{
			// author doesnt exist we create a new entry assigned to the user 0
			$this->_sql->insert($this->_registry['table.vcshook_author'], array(
				'userId' => 0,
				'name'   => $author,
				'date'   => date(DateTime::SQL),
			));

			$this->authorId = $this->_sql->getLastInsertId();
		}
	}

	public function setUrl($url)
	{
		$url = $this->_validate->apply($url, 'string', array(new Filter\Length(3, 256), new Filter\Url()), 'url', 'Url');

		if(!$this->_validate->hasError())
		{
			$this->url = $url;
		}
		else
		{
			throw new Exception($this->_validate->getLastError());
		}
	}

	public function setMessage($message)
	{
		$message = $this->_validate->apply($message, 'string', array(new Filter\Length(1, 512), new Filter\Html()), 'message', 'Message');

		if(!$this->_validate->hasError())
		{
			$this->message = $message;
		}
		else
		{
			throw new Exception($this->_validate->getLastError());
		}
	}

	public function setTimestamp($timestamp)
	{
		$timestamp = $this->_validate->apply($timestamp, 'string', array(new Filter\Length(4, 32)), 'timestamp', 'Timestamp');

		if(!$this->_validate->hasError())
		{
			if(is_numeric($timestamp))
			{
				$timestamp = '@' . $timestamp;
			}

			$date = new DateTime($timestamp, new DateTimeZone('UTC'));
			$date->setTimezone($this->_registry['core.default_timezone']);

			$this->commitDate = $date->format(DateTime::SQL);
		}
		else
		{
			throw new Exception($this->_validate->getLastError());
		}
	}

	public function getProject()
	{
		if($this->_project === null)
		{
			$this->_project = $this->_hm->getHandler('AmunService\Vcshook')->getRecord($this->projectId);
		}

		return $this->_project;
	}

	public function getAuthor()
	{
		if($this->_author === null)
		{
			$this->_author = $this->_hm->getHandler('AmunService\Vcshook\Author')->getRecord($this->authorId);
		}

		return $this->_author;
	}

	public function getCommitDate()
	{
		if($this->_commitDate === null)
		{
			$this->_commitDate = new DateTime($this->commitDate, $this->_registry['core.default_timezone']);
		}

		return $this->_commitDate;
	}

	public function getDate()
	{
		if($this->_date === null)
		{
			$this->_date = new DateTime($this->date, $this->_registry['core.default_timezone']);
		}

		return $this->_date;
	}

	public function import(ReaderResult $result)
	{
		switch($result->getType())
		{
			case ReaderInterface::JSON:

				$data = (array) $result->getData();

				$this->setProjectId($result->getParam('projectId'));
				$this->setRevision($data['revision']);
				$this->setUrl($data['url']);
				$this->setAuthor($data['author']);
				$this->setTimestamp($data['timestamp']);
				$this->setMessage($data['message']);

				break;

			default:

				throw new Exception('Reader is not supported');

				break;
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

				$entry->setTitle('Revision ' . $this->revision . ': ' . $this->message);
				$entry->setId('urn:uuid:' . $this->globalId);
				$entry->setUpdated($this->getCommitDate());
				$entry->addAuthor($this->getAuthor()->name);
				$entry->addLink($this->url, 'alternate', 'text/html');

				return $entry;

				break;

			default:

				throw new Exception('Writer is not supported');

				break;
		}
	}
}


