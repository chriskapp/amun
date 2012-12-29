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
class Amun_Dependency_Application extends Amun_Dependency_Session
{
	protected $pageId;

	public function __construct(PSX_Config $config, array $params)
	{
		$this->pageId = isset($params['application.pageId']) ? $params['application.pageId'] : null;;

		parent::__construct($config, $params);
	}

	public function setup()
	{
		parent::setup();

		$this->getPage();
		$this->getService();
		$this->getNav();
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

		return $this->set('page', new Amun_Page($this->pageId, $this->getRegistry(), $this->getUser()));
	}

	public function getService()
	{
		if($this->has('service'))
		{
			return $this->get('service');
		}

		return $this->set('service', new Amun_Service($this->getPage()->getServiceId(), $this->getRegistry()));
	}

	public function getNav()
	{
		if($this->has('nav'))
		{
			return $this->get('nav');
		}

		return $this->set('nav', new Amun_Nav($this->getRegistry(), $this->getUser(), $this->getPage()));
	}

	public function getPath()
	{
		if($this->has('path'))
		{
			return $this->get('path');
		}

		return $this->set('path', new Amun_Path($this->getRegistry(), $this->getPage()));
	}

	public function getGadgetContainer()
	{
		if($this->has('gadgetContainer'))
		{
			return $this->get('gadgetContainer');
		}

		return $this->set('gadgetContainer', new Amun_Gadget_Container($this->getRegistry()));
	}

	public function getHtmlJs()
	{
		if($this->has('htmlJs'))
		{
			return $this->get('htmlJs');
		}

		return $this->set('htmlJs', new Amun_Html_Js($this->config));
	}

	public function getHtmlCss()
	{
		if($this->has('htmlCss'))
		{
			return $this->get('htmlCss');
		}

		return $this->set('htmlCss', new Amun_Html_Css($this->config));
	}

	public function getHtmlContent()
	{
		if($this->has('htmlContent'))
		{
			return $this->get('htmlContent');
		}

		return $this->set('htmlContent', new Amun_Html_Content());
	}

	public function getTemplate()
	{
		if($this->has('template'))
		{
			return $this->get('template');
		}

		$template = new PSX_Template($this->config);

		// assign default template vars
		$template->assign('sql', $this->getSql());
		$template->assign('registry', $this->getRegistry());
		$template->assign('user', $this->getUser());
		$template->assign('page', $this->getPage());
		$template->assign('nav', $this->getNav());
		$template->assign('path', $this->getPath());
		$template->assign('gadget', $this->getGadgetContainer());
		$template->assign('htmlJs', $this->getHtmlJs());
		$template->assign('htmlCss', $this->getHtmlCss());
		$template->assign('htmlContent', $this->getHtmlContent());

		return $this->set('template', $template);
	}
}

