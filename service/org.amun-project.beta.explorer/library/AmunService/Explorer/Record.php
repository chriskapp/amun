<?php
/*
 *  $Id: Page.php 880 2012-10-27 13:14:26Z k42b3.x@googlemail.com $
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

namespace AmunService\Explorer;

use Amun\DataFactory;
use Amun\Data\HandlerAbstract;
use Amun\Data\RecordAbstract;
use Amun\Exception;
use Amun\Filter as AmunFilter;
use Amun\Util;
use AmunService\Explorer\Filter as ExplorerFilter;
use PSX\Data\WriterInterface;
use PSX\Data\WriterResult;
use PSX\DateTime;
use PSX\Filter;
use PSX\Util\Markdown;

/**
 * Amun_Service_Page
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   Amun
 * @package    Amun_Service_Page
 * @version    $Revision: 880 $
 */
class Record extends RecordAbstract
{
	public $path;
	public $content;

	protected $_base;
	protected $_config;
	protected $_sql;
	protected $_registry;
	protected $_validate;
	protected $_user;

	public function __construct()
	{
		$ct = DataFactory::getInstance()->getContainer();

		$this->_base     = $ct->getBase();
		$this->_config   = $ct->getConfig();
		$this->_sql      = $ct->getSql();
		$this->_registry = $ct->getRegistry();
		$this->_validate = $ct->getValidate();
		$this->_user     = $ct->getUser();
	}

	public function getName()
	{
		return 'file';
	}

	public function getFields()
	{
		return array(
			'path'    => $this->path, 
			'content' => $this->content,
		);
	}

	public function setPath($path)
	{
		$path = $this->_validate->apply($path, 'string', array(new ExplorerFilter\Path($this->_registry)), 'path', 'Path');

		if(!$this->_validate->hasError())
		{
			$this->path = $path;
		}
		else
		{
			throw new Exception($this->_validate->getLastError());
		}
	}

	public function setContent($content)
	{
		$content = $this->_validate->apply($content, 'string', array(), 'content', 'Content');

		if(!$this->_validate->hasError())
		{
			$this->content = $content;
		}
		else
		{
			throw new Exception($this->_validate->getLastError());
		}
	}

	public function getPath()
	{
		return $this->path;
	}

	public function getContent()
	{
		return $this->content;
	}
}
