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

namespace AmunService\Forum;

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
	const NORMAL = 0x0;
	const STICKY = 0x1;
	const CLOSED = 0x2;

	protected $_page;
	protected $_user;
	protected $_date;
	protected $_replyDate;

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

	public function setSticky($sticky)
	{
		$sticky = $this->_validate->apply($sticky, 'boolean', array(), 'sticky', 'Sticky');

		if(!$this->_validate->hasError())
		{
			$this->sticky = $sticky ? '1' : '0';
		}
		else
		{
			throw new Exception($this->_validate->getLastError());
		}
	}

	public function setClosed($closed)
	{
		$closed = $this->_validate->apply($closed, 'boolean', array(), 'closed', 'Closed');

		if(!$this->_validate->hasError())
		{
			$this->closed = $closed ? '1' : '0';
		}
		else
		{
			throw new Exception($this->_validate->getLastError());
		}
	}

	public function setUrlTitle($urlTitle)
	{
		$urlTitle = $this->_validate->apply($urlTitle, 'string', array(new AmunFilter\UrlTitle(), new Filter\Length(3, 128)), 'urlTitle', 'Url Title');

		if(!$this->_validate->hasError())
		{
			$this->urlTitle = $urlTitle;
		}
		else
		{
			throw new Exception($this->_validate->getLastError());
		}
	}

	public function setTitle($title)
	{
		$title = $this->_validate->apply($title, 'string', array(new Filter\Length(3, 256), new Filter\Html()), 'title', 'Title');

		if(!$this->_validate->hasError())
		{
			$this->setUrlTitle($title);

			$this->title = $title;
		}
		else
		{
			throw new Exception($this->_validate->getLastError());
		}
	}

	public function setText($text)
	{
		$text = Markdown::decode($text);
		$text = $this->_validate->apply($text, 'string', array(new Filter\Length(3, 4096), new AmunFilter\Html($this->_registry, $this->_user)), 'text', 'Text');

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
		return $this->_base->getUrn('forum', $this->id);
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

	public function getUrl()
	{
		return $this->_config['psx_url'] . '/' . $this->_config['psx_dispatch'] . $this->pagePath . '/view/' . $this->id . '/' . $this->urlTitle;
	}

	public function isSticky()
	{
		return (boolean) $this->sticky;
	}

	public function isClosed()
	{
		return (boolean) $this->closed;
	}

	public function getReplyDate()
	{
		if($this->_replyDate === null)
		{
			$this->_replyDate = new DateTime($this->replyDate, $this->_registry['core.default_timezone']);
		}

		return $this->_replyDate;
	}

	public function export(WriterResult $result)
	{
		switch($result->getType())
		{
			case WriterInterface::ATOM:
				$entry = $result->getWriter()->createEntry();

				$entry->setTitle($this->title);
				$entry->setId('urn:uuid:' . $this->globalId);
				$entry->setUpdated($this->getDate());
				$entry->addAuthor($this->authorName, $this->authorProfileUrl);
				$entry->addLink($this->getUrl(), 'alternate', 'text/html');
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
				$object->setObjectType('article');
				$object->setId('urn:uuid:' . $this->globalId);
				$object->setDisplayName($this->title);
				$object->setUrl($this->getUrl());
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

	public static function getStatus($status = false)
	{
		$s = array(

			self::NORMAL => 'Normal',
			self::STICKY => 'Sticky',
			self::CLOSED => 'Closed',

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
}


