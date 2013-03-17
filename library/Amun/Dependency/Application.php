<?php
/*
 *  $Id: Application.php 818 2012-08-25 18:52:34Z k42b3.x@googlemail.com $
 *
 * psx
 * A object oriented and modular based PHP framework for developing
 * dynamic web applications. For the current version and informations
 * visit <http://phpsx.org>
 *
 * Copyright (c) 2010-2012 Christoph Kappestein <k42b3.x@gmail.com>
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
 * Amun_Dependency_Application
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://phpsx.org
 * @category   Amun
 * @package    Amun_Dependency
 * @version    $Revision: 818 $
 */
class Application extends Session
{
	protected $pageId;

	public function __construct(Config $config, array $params)
	{
		$this->pageId = isset($params['application.pageId']) ? $params['application.pageId'] : null;;

		parent::__construct($config, $params);
	}

	public function setup()
	{
		parent::setup();

		$this->getPage();
		$this->getService();
		$this->getDataFactory();
		$this->getNavigation();
		$this->getPath();
		$this->getGadgetContainer();
		$this->getHtmlJs();
		$this->getHtmlCss();
		$this->getHtmlContent();
		$this->getTemplate();
	}

	public function getPage()
	{
		if($this->has('page'))
		{
			return $this->get('page');
		}

		return $this->set('page', new Page($this->pageId, $this->getRegistry(), $this->getUser()));
	}

	public function getService()
	{
		if($this->has('service'))
		{
			return $this->get('service');
		}

		return $this->set('service', new Service($this->getPage()->getServiceId(), $this->getRegistry()));
	}

	public function getDataFactory()
	{
		if($this->has('dataFactory'))
		{
			return $this->get('dataFactory');
		}

		return $this->set('dataFactory', DataFactory::initInstance($this));
	}

	public function getNavigation()
	{
		if($this->has('navigation'))
		{
			return $this->get('navigation');
		}

		return $this->set('navigation', new Navigation($this->getRegistry(), $this->getUser(), $this->getPage()));
	}

	public function getPath()
	{
		if($this->has('path'))
		{
			return $this->get('path');
		}

		return $this->set('path', new Path($this->getRegistry(), $this->getPage()));
	}

	public function getGadgetContainer()
	{
		if($this->has('gadgetContainer'))
		{
			return $this->get('gadgetContainer');
		}

		return $this->set('gadgetContainer', new Container($this->getRegistry(), $this->getUser()));
	}

	public function getHtmlJs()
	{
		if($this->has('htmlJs'))
		{
			return $this->get('htmlJs');
		}

		return $this->set('htmlJs', new Html\Js($this->config));
	}

	public function getHtmlCss()
	{
		if($this->has('htmlCss'))
		{
			return $this->get('htmlCss');
		}

		return $this->set('htmlCss', new Html\Css($this->config));
	}

	public function getHtmlContent()
	{
		if($this->has('htmlContent'))
		{
			return $this->get('htmlContent');
		}

		return $this->set('htmlContent', new Html\Content());
	}

	public function getTemplate()
	{
		if($this->has('template'))
		{
			return $this->get('template');
		}

		$template = new Template($this->config);

		// assign default template vars
		$template->assign('sql', $this->getSql());
		$template->assign('registry', $this->getRegistry());
		$template->assign('user', $this->getUser());
		$template->assign('page', $this->getPage());
		$template->assign('navigation', $this->getNavigation());
		$template->assign('path', $this->getPath());
		$template->assign('gadget', $this->getGadgetContainer());
		$template->assign('htmlJs', $this->getHtmlJs());
		$template->assign('htmlCss', $this->getHtmlCss());
		$template->assign('htmlContent', $this->getHtmlContent());

		return $this->set('template', $template);
	}
}

