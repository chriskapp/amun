<?php
/*
 *  $Id: Commit.php 880 2012-10-27 13:14:26Z k42b3.x@googlemail.com $
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
 * Amun_Service_Googleproject_Commit
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   Amun
 * @package    Amun_Service_Googleproject
 * @version    $Revision: 880 $
 */
class Amun_Service_Googleproject_Commit extends Amun_Data_RecordAbstract
{
	protected $_project;
	protected $_author;
	protected $_commitDate;
	protected $_date;

	public function setId($id)
	{
		$id = $this->_validate->apply($id, 'integer', array(new Amun_Filter_Id($this->_table)), 'id', 'Id');

		if(!$this->_validate->hasError())
		{
			$this->id = $id;
		}
		else
		{
			throw new PSX_Data_Exception($this->_validate->getLastError());
		}
	}

	public function setProjectId($projectId)
	{
		$projectId = $this->_validate->apply($projectId, 'integer', array(new Amun_Filter_Id(Amun_Sql_Table_Registry::get('Service_Googleproject'))), 'projectId', 'Project Id');

		if(!$this->_validate->hasError())
		{
			$this->projectId = $projectId;
		}
		else
		{
			throw new PSX_Data_Exception($this->_validate->getLastError());
		}
	}

	public function setAuthor($author)
	{
		$con      = new PSX_Sql_Condition(array('name', '=', $author));
		$authorId = $this->_sql->select($this->_registry['table.service_googleproject_author'], array('id'), $con, PSX_Sql::SELECT_FIELD);

		if(!empty($authorId))
		{
			$this->authorId = (integer) $authorId;
		}
		else
		{
			throw new PSX_Data_Exception('Author is not assigned to an user');
		}
	}

	public function setRevision($revision)
	{
		$this->revision = (integer) $revision;
	}

	public function setUrl($url)
	{
		$url = $this->_validate->apply($url, 'string', array(new PSX_Filter_Length(3, 256), new PSX_Filter_Url()), 'url', 'Url');

		if(!$this->_validate->hasError())
		{
			$this->url = $url;
		}
		else
		{
			throw new PSX_Data_Exception($this->_validate->getLastError());
		}
	}

	public function setMessage($message)
	{
		$message = $this->_validate->apply($message, 'string', array(new PSX_Filter_Length(1, 512), new PSX_Filter_Html()), 'message', 'Message');

		if(!$this->_validate->hasError())
		{
			$this->message = $message;
		}
		else
		{
			throw new PSX_Data_Exception($this->_validate->getLastError());
		}
	}

	public function setTimestamp($timestamp)
	{
		$timestamp = $this->_validate->apply($timestamp, 'string', array(new PSX_Filter_Length(8, 14)), 'timestamp', 'Timestamp');

		if(!$this->_validate->hasError())
		{
			$date = new DateTime(date(PSX_DateTime::SQL, $timestamp), new DateTimeZone('UTC'));
			$date->setTimezone($this->_registry['core.default_timezone']);

			$this->commitDate = $date->format(PSX_DateTime::SQL);
		}
		else
		{
			throw new PSX_Data_Exception($this->_validate->getLastError());
		}
	}

	public function getProject()
	{
		if($this->_project === null)
		{
			$this->_project = Amun_Sql_Table_Registry::get('Service_Googleproject')->getRecord($this->projectId);
		}

		return $this->_project;
	}

	public function getAuthor()
	{
		if($this->_author === null)
		{
			$this->_author = Amun_Sql_Table_Registry::get('Service_Googleproject_Author')->getRecord($this->authorId);
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

	public function import(PSX_Data_ReaderResult $result)
	{
		switch($result->getType())
		{
			case PSX_Data_ReaderInterface::JSON:

				$data = (array) $result->getData();

				$this->setProjectId($result->getParam('projectId'));
				$this->setRevision($data['revision']);
				$this->setUrl($data['url']);
				$this->setAuthor($data['author']);
				$this->setTimestamp($data['timestamp']);
				$this->setMessage($data['message']);

				break;

			default:

				throw new PSX_Data_Exception('Reader is not supported');

				break;
		}
	}

	public function export(PSX_Data_WriterResult $result)
	{
		switch($result->getType())
		{
			case PSX_Data_WriterInterface::JSON:
			case PSX_Data_WriterInterface::XML:

				return parent::export($result);

				break;

			case PSX_Data_WriterInterface::ATOM:

				$entry = $result->getWriter()->createEntry();

				$entry->setTitle('Revision ' . $this->revision . ': ' . $this->message);
				$entry->setId('urn:uuid:' . $this->globalId);
				$entry->setUpdated($this->getCommitDate());
				$entry->addAuthor($this->getAuthor()->name);
				$entry->addLink($this->url, 'alternate', 'text/html');

				return $entry;

				break;

			default:

				throw new PSX_Data_Exception('Writer is not supported');

				break;
		}
	}
}


