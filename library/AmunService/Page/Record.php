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

namespace AmunService\Page;

use Amun\DataFactory;
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
		$pageId = $this->_validate->apply($pageId, 'integer', array(new AmunFilter\Id(DataFactory::getTable('Content_Page'))), 'pageId', 'Page Id');

		if(!$this->_validate->hasError())
		{
			$this->pageId = $pageId;
		}
		else
		{
			throw new Exception($this->_validate->getLastError());
		}
	}

	public function setContent($content)
	{
		$content = $this->_validate->apply($content, 'string', array(new Filter\Length(3, 65536), new AmunFilter\Html($this->_config, $this->_user)), 'content', 'Content');

		if(!$this->_validate->hasError())
		{
			$this->content = $content;
		}
		else
		{
			throw new Exception($this->_validate->getLastError());
		}
	}

	public function getId()
	{
		return $this->_base->getUrn('page', $this->id);
	}

	public function getPage()
	{
		if($this->_page === null)
		{
			$this->_page = DataFactory::getTable('Content_Page')->getRecord($this->pageId);
		}

		return $this->_page;
	}

	public function getUser()
	{
		if($this->_user === null)
		{
			$this->_user = DataFactory::getTable('User_Account')->getRecord($this->userId);
		}

		return $this->_user;
	}

	public function getContent()
	{
		return htmlspecialchars($this->content, ENT_NOQUOTES, 'UTF-8');
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
		return $this->_config['psx_url'] . '/' . $this->_config['psx_dispatch'] . $this->pagePath;
	}
}


