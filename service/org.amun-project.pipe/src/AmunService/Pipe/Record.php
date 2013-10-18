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

namespace AmunService\Pipe;

use Amun\Data\HandlerAbstract;
use Amun\Data\RecordAbstract;
use Amun\Exception;
use Amun\Filter as AmunFilter;
use Amun\Util;
use AmunService\Openid\Filter as OpenidFilter;
use PSX\Data\WriterInterface;
use PSX\Data\WriterResult;
use PSX\DateTime;
use PSX\Filter;
use PSX\File;
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
	protected $_account;
	protected $_media;
	protected $_date;
	protected $_file;

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

	public function setMediaId($mediaId)
	{
		$mediaId = $this->_validate->apply($mediaId, 'integer', array(new AmunFilter\Id($this->_hm->getTable('AmunService\Media'))), 'mediaId', 'Media Id');

		if(!$this->_validate->hasError())
		{
			$this->mediaId = $mediaId;
		}
		else
		{
			throw new Exception($this->_validate->getLastError());
		}
	}

	public function setProcessor($processor)
	{
		$proc = ProcessorAbstract::factory($processor);

		if($proc instanceof ProcessorInterface)
		{
			$this->processor = $processor;
		}
		else
		{
			throw new Exception('Invalid processor type');
		}
	}

	public function getId()
	{
		return $this->_base->getUrn('pipe', $this->id);
	}

	public function getContent()
	{
		$processor = ProcessorAbstract::factory($this->processor);

		if($processor instanceof ProcessorInterface)
		{
			return $processor->process($this->getFile());
		}
	}

	public function getLastModified()
	{
		$date = new DateTime();
		$date->setTimestamp(filemtime($this->getFile()));

		return $date;
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
		if($this->_account === null)
		{
			$this->_account = $this->_hm->getHandler('AmunService\User\Account')->getRecord($this->userId);
		}

		return $this->_account;
	}

	public function getMedia()
	{
		if($this->_media === null)
		{
			$this->_media = $this->_hm->getHandler('AmunService\Media')->getRecord($this->mediaId);
		}

		return $this->_media;
	}

	public function getDate()
	{
		if($this->_date === null)
		{
			$this->_date = new DateTime($this->date, $this->_registry['core.default_timezone']);
		}

		return $this->_date;
	}

	public function getFile()
	{
		if($this->_file === null)
		{
			// check whether we have an absolute or relative path
			if($this->mediaPath[0] == '/' || $this->mediaPath[1] == ':')
			{
				$file = $this->mediaPath;
			}
			else
			{
				$file = $this->_registry['media.path'] . '/' . $this->mediaPath;
			}

			if(!File::exists($file))
			{
				throw new Exception('File not found', 404);
			}

			$this->_file = $file;
		}

		return $this->_file;
	}

	public function getUrl()
	{
		return $this->_config['psx_url'] . '/' . $this->_config['psx_dispatch'] . $this->pagePath;
	}
}


