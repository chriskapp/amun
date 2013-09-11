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

namespace AmunService\Comment;

use Amun\Data\HandlerAbstract;
use Amun\Data\RecordAbstract;
use Amun\Exception;
use Amun\Filter as AmunFilter;
use Amun\Util;
use PSX\ActivityStream;
use PSX\Data\WriterInterface;
use PSX\Data\WriterResult;
use PSX\DateTime;
use PSX\Filter;
use PSX\Util\Markdown;

/**
 * Record
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/gpl.html GPLv3
 * @link    http://amun.phpsx.org
 */
class Record extends RecordAbstract
{
	protected $_page;
	protected $_user;
	protected $_date;

	/**
	 * Sets the id
	 *
	 * @param integer $id
	 */
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

	/**
	 * Sets the page id
	 *
	 * @param integer $pageId
	 */
	public function setPageId($pageId)
	{
		$pageId = $this->_validate->apply($pageId, 'integer', array(new AmunFilter\Id($this->_hm->getTable('AmunService\Content\Page'))), 'pageId', 'Page Id');

		if(!$this->_validate->hasError())
		{
			$this->pageId = $pageId;
		}
		else
		{
			throw new Exception($this->_validate->getLastError());
		}
	}

	public function setRefId($refId)
	{
		$refId = $this->_validate->apply($refId, 'integer');

		if(!$this->_validate->hasError())
		{
			$this->refId = $refId;
		}
		else
		{
			throw new Exception($this->_validate->getLastError());
		}
	}

	public function setText($text)
	{
		$text = Markdown::decode($text);
		$text = $this->_validate->apply($text, 'string', array(new Filter\Length(3, 2048), new AmunFilter\Html($this->_registry, $this->_user, true)), 'text', 'Text');

		if(!$this->_validate->hasError())
		{
			$this->text = $text;
		}
		else
		{
			throw new Exception($this->_validate->getLastError());
		}
	}

	public function getId()
	{
		return $this->_base->getUrn('comment', $this->id);
	}

	public function getPage()
	{
		if($this->_page === null)
		{
			$this->_page = $this->_hm->getHandler('AmunService\Content\Page')->getRecord($this->pageId);
		}

		return $this->_page;
	}

	public function getUser()
	{
		if($this->_user === null)
		{
			$this->_user = $this->_hm->getHandler('AmunService\User\Account')->getRecord($this->userId);
		}

		return $this->_user;
	}

	public function getText()
	{
		return htmlspecialchars($this->text, ENT_NOQUOTES, 'UTF-8');
	}

	public function getDate()
	{
		if($this->_date === null)
		{
			$this->_date = new DateTime($this->date, $this->_registry['core.default_timezone']);
		}

		return $this->_date;
	}

	public function export(WriterResult $result)
	{
		switch($result->getType())
		{
			case WriterInterface::ATOM:
				$entry = $result->getWriter()->createEntry();

				$entry->setTitle(Util::stripAndTruncateHtml($this->text));
				$entry->setId('urn:uuid:' . $this->globalId);
				$entry->setUpdated($this->getDate());
				$entry->addAuthor($this->authorName, $this->authorProfileUrl);
				$entry->setContent($this->text, 'html');

				return $entry;
				break;

			case WriterInterface::JAS:
				$image = new ActivityStream\MediaLink();
				$image->setUrl($this->authorThumbnailUrl);

				$actor = new ActivityStream\Object();
				$actor->setObjectType('person');
				$actor->setDisplayName($this->authorName);
				$actor->setUrl($this->authorProfileUrl);
				$actor->setImage($image);

				$object = new ActivityStream\Object();
				$object->setObjectType('comment');
				$object->setId('urn:uuid:' . $this->globalId);
				$object->setDisplayName(Util::stripAndTruncateHtml($this->text));
				$object->setPublished($this->getDate());
				$object->setContent($this->text);

				$activity = new ActivityStream\Activity();
				$activity->setActor($actor);
				$activity->setVerb('post');
				$activity->setObject($object);

				return $activity;
				break;

			default:
				return parent::export($result);
				break;
		}
	}
}


