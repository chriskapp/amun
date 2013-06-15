<?php
/*
 * amun
 * A social content managment system based on the psx framework. For
 * the current version and informations visit <http://amun.phpsx.org>
 *
 * Copyright (c) 2010-2013 Christoph Kappestein <k42b3.x@gmail.com>
 *
 * This file is part of psx. psx is free software: you can
 * redistribute it and/or modify it under the terms of the
 * GNU General Public License as published by the Free Software
 * Foundation, either version 3 of the License, or any later version.
 *
 * psx is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with psx. If not, see <http://www.gnu.org/licenses/>.
 */

namespace Amun\Dependency;

use Amun\DataFactory;
use Amun\Page;
use Amun\Service;
use Amun\User;
use Amun\Navigation;
use Amun\Path;
use Amun\Gadget\Container;
use Amun\Html;
use PSX\Config;
use PSX\Template;

/**
 * Application
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/gpl.html GPLv3
 * @link    http://phpsx.org
 */
class Application extends Session
{
	protected $pageId;

	public function __construct(Config $config, array $params)
	{
		$this->pageId = isset($params['application.pageId']) ? $params['application.pageId'] : null;;

		parent::__construct($config, $params);
	}

	public function getPage()
	{
		return new Page($this->pageId, $this->get('registry'), $this->get('user'));
	}

	public function getService()
	{
		return new Service($this->get('page')->getServiceId(), $this->get('registry'));
	}

	public function getDataFactory()
	{
		return DataFactory::initInstance($this);
	}

	public function getNavigation()
	{
		return new Navigation($this->get('registry'), $this->get('user'), $this->get('page'));
	}

	public function getPath()
	{
		return new Path($this->get('registry'), $this->get('page'));
	}

	public function getGadgetContainer()
	{
		return new Container($this->get('registry'), $this->get('user'));
	}

	public function getHtmlJs()
	{
		return new Html\Js($this->get('config'));
	}

	public function getHtmlCss()
	{
		return new Html\Css($this->get('config'));
	}

	public function getHtmlContent()
	{
		return new Html\Content();
	}

	public function getTemplate()
	{
		return new Template();
	}
}

