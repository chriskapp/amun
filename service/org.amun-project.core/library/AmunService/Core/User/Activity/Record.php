<?php
/*
 *  $Id: Activity.php 880 2012-10-27 13:14:26Z k42b3.x@googlemail.com $
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
 * Amun_User_Activity
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   Amun
 * @package    Amun_User_Activity
 * @version    $Revision: 880 $
 */
class AmunService_Core_User_Activity_Record extends Amun_Data_RecordAbstract
{
	protected $_user;
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

	public function setParentId($parentId)
	{
		$parentId = $this->_validate->apply($parentId, 'integer', array(new Amun_Filter_Id(Amun_Sql_Table_Registry::get('User_Activity'), true)), 'parentId', 'Parent Id');

		if(!$this->_validate->hasError())
		{
			$this->parentId = $parentId;
		}
		else
		{
			throw new PSX_Data_Exception($this->_validate->getLastError());
		}
	}

	public function setScope($scope)
	{
		$scope = $this->_validate->apply($scope, 'integer', array(new Amun_Filter_Id(Amun_Sql_Table_Registry::get('User_Friend_Group'), true)), 'scope', 'Scope');

		if(!$this->_validate->hasError())
		{
			$this->scope = $scope;
		}
		else
		{
			throw new PSX_Data_Exception($this->_validate->getLastError());
		}
	}

	public function setVerb($verb)
	{
		$verb = $this->_validate->apply($verb, 'string', array(new Amun_User_Activity_Filter_Verb()), 'verb', 'Verb');

		if(!$this->_validate->hasError())
		{
			$this->verb = $verb;
		}
		else
		{
			throw new PSX_Data_Exception($this->_validate->getLastError());
		}
	}

	public function setSummary($summary)
	{
		$summary = PSX_Util_Markdown::decode($summary);
		$summary = $this->_validate->apply($summary, 'string', array(new PSX_Filter_Length(3, 4096), new Amun_Filter_Html($this->_config, $this->_base->getUser())), 'summary', 'Summary');

		if(!$this->_validate->hasError())
		{
			$this->summary = $summary;
		}
		else
		{
			throw new PSX_Data_Exception($this->_validate->getLastError());
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
			$this->_user = Amun_Sql_Table_Registry::get('User_Account')->getRecord($this->userId);
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
			->join(PSX_Sql_Join::INNER, Amun_Sql_Table_Registry::get('User_Account')
				->select(array('name', 'profileUrl', 'thumbnailUrl'), 'author')
			)
			->where('parentId', '=', $this->id)
			->orderBy('date', PSX_Sql::SORT_ASC)
			->getAll(PSX_Sql::FETCH_OBJECT);
	}

	public function getObject()
	{
		$table = Amun_Sql_Table_Registry::get($this->table);

		if($table instanceof Amun_User_Activity_Table)
		{
			$stream = new Amun_User_Activity_Stream($table, $this->id);

			return $stream->getObject();
		}
		else if($this->refId > 0)
		{
			$class = $table->getDefaultRecordClass() . '_Stream';
			$file  = PSX_PATH_LIBRARY . '/' . str_replace('_', '/', $class) . '.php';

			if(is_file($file) && class_exists($class))
			{
				$stream = new $class($table, $this->refId);

				return $stream->getObject();
			}
		}

		return null;
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

				$entry->setTitle(Amun_Util::stripAndTruncateHtml($this->summary));
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

				throw new PSX_Data_Exception('Writer is not supported');

				break;
		}
	}
}


