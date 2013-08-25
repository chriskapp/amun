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

namespace AmunService\Content\Page\Option;

use Amun\DataFactory;
use Amun\Data\HandlerAbstract;
use Amun\Data\RecordAbstract;
use Amun\Exception;
use Amun\Filter as AmunFilter;
use Amun\Util;
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
	protected $_srcPage;
	protected $_destPage;

	public function getName()
	{
		return 'option';
	}

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

	public function setOptionId($optionId)
	{
		$optionId = $this->_validate->apply($optionId, 'integer', array(new AmunFilter\Id($this->_hm->getTable('AmunService\Core\Service\Option'))), 'optionId', 'Option Id');

		if(!$this->_validate->hasError())
		{
			$this->optionId = $optionId;
		}
		else
		{
			throw new Exception($this->_validate->getLastError());
		}
	}

	public function setRightId($rightId)
	{
		$rightId = $this->_validate->apply($rightId, 'integer', array(new AmunFilter\Id($this->_hm->getTable('AmunService\User\Right'))), 'rightId', 'Right Id');

		if(!$this->_validate->hasError())
		{
			$this->rightId = $rightId;
		}
		else
		{
			throw new Exception($this->_validate->getLastError());
		}
	}

	public function setSrcPageId($srcPageId)
	{
		$srcPageId = $this->_validate->apply($srcPageId, 'integer', array(new AmunFilter\Id($this->_hm->getTable('AmunService\Content\Page'))), 'srcPageId', 'Source Page Id');

		if(!$this->_validate->hasError())
		{
			$this->srcPageId = $srcPageId;
		}
		else
		{
			throw new Exception($this->_validate->getLastError());
		}
	}

	public function setDestPageId($destPageId)
	{
		$destPageId = $this->_validate->apply($destPageId, 'integer', array(new AmunFilter\Id($this->_hm->getTable('AmunService\Content\Page'))), 'destPageId', 'Destination Page Id');

		if(!$this->_validate->hasError())
		{
			$this->destPageId = $destPageId;
		}
		else
		{
			throw new Exception($this->_validate->getLastError());
		}
	}

	public function setName($name)
	{
		$name = $this->_validate->apply($name, 'string', array(new Filter\Length(2, 32)), 'name', 'Name');

		if(!$this->_validate->hasError())
		{
			$this->name = $name;
		}
		else
		{
			throw new Exception($this->_validate->getLastError());
		}
	}

	public function setHref($href)
	{
		$href = $this->_validate->apply($href, 'string', array(new Filter\Length(0, 256)), 'href', 'Href');

		if(!$this->_validate->hasError())
		{
			$this->href = $href;
		}
		else
		{
			throw new Exception($this->_validate->getLastError());
		}
	}

	public function getId()
	{
		return $this->_base->getUrn('content', 'page', 'option', $this->id);
	}

	public function getSrcPage()
	{
		if($this->_srcPage === null)
		{
			$this->_srcPage = $this->_hm->getHandler('AmunService\Content\Page')->getRecord($this->srcPageId);
		}

		return $this->_srcPage;
	}

	public function getDestPage()
	{
		if($this->_destPage === null)
		{
			$this->_destPage = $this->_hm->getHandler('AmunService\Content\Page')->getRecord($this->destPageId);
		}

		return $this->_destPage;
	}
}



