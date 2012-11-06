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
class Amun_Dependency_Application extends Amun_Dependency_Default
{
	protected function setup()
	{
		parent::setup();

		// session
		if(!$this->registry->offsetExists('session'))
		{
			$session = new PSX_Session('amun_' . md5($this->config['psx_url']));
			$session->start();
			$this->registry->offsetSet('session', $session);
		}

		// user
		if(!$this->registry->offsetExists('user'))
		{
			$userId = Amun_User::getId($this->registry->offsetGet('session'), $this->registry->offsetGet('registry'));
			$this->registry->offsetSet('user', $this->base->setUser($userId));
		}

		// page
		if(!$this->registry->offsetExists('page'))
		{
			$page = new Amun_Page($this->registry->offsetGet('registry'), $this->registry->offsetGet('user'));
			$this->registry->offsetSet('page', $page);
		}

		// service
		if(!$this->registry->offsetExists('service'))
		{
			$service = new Amun_Service($this->registry->offsetGet('page')->serviceId, $this->registry->offsetGet('registry'));
			$this->registry->offsetSet('service', $service);
		}

		// nav
		if(!$this->registry->offsetExists('nav'))
		{
			$nav = new Amun_Nav($this->registry->offsetGet('registry'), $this->registry->offsetGet('user'), $this->registry->offsetGet('page'));
			$this->registry->offsetSet('nav', $nav);
		}

		// path
		if(!$this->registry->offsetExists('path'))
		{
			$path = new Amun_Path($this->registry->offsetGet('registry'), $this->registry->offsetGet('page'));
			$this->registry->offsetSet('path', $path);
		}

		// gadget
		if(!$this->registry->offsetExists('gadget'))
		{
			$gadget = new Amun_Gadget($this->registry->offsetGet('registry'));
			$this->registry->offsetSet('gadget', $gadget);
		}

		// html js
		if(!$this->registry->offsetExists('htmlJs'))
		{
			$htmlJs = new Amun_Html_Js($this->config);
			$this->registry->offsetSet('htmlJs', $htmlJs);
		}

		// html css
		if(!$this->registry->offsetExists('htmlCss'))
		{
			$htmlCss = new Amun_Html_Css($this->config);
			$this->registry->offsetSet('htmlCss', $htmlCss);
		}

		// html content
		if(!$this->registry->offsetExists('htmlContent'))
		{
			$htmlContent = new Amun_Html_Content();
			$this->registry->offsetSet('htmlContent', $htmlContent);
		}

		// template
		if(!$this->registry->offsetExists('template'))
		{
			$template = new PSX_Template($this->config);
			$this->registry->offsetSet('template', $template);

			// assign default template vars
			$template->assign('sql', $this->registry->offsetGet('sql'));
			$template->assign('registry', $this->registry->offsetGet('registry'));
			$template->assign('user', $this->registry->offsetGet('user'));
			$template->assign('page', $this->registry->offsetGet('page'));
			$template->assign('nav', $this->registry->offsetGet('nav'));
			$template->assign('path', $this->registry->offsetGet('path'));
			$template->assign('gadget', $this->registry->offsetGet('gadget'));
			$template->assign('htmlJs', $this->registry->offsetGet('htmlJs'));
			$template->assign('htmlCss', $this->registry->offsetGet('htmlCss'));
			$template->assign('htmlContent', $this->registry->offsetGet('htmlContent'));
		}
	}

	public function getParameters()
	{
		return array_merge(parent::getParameters(), array(
			'session' => $this->registry->offsetGet('session'),
			'user' => $this->registry->offsetGet('user'),
			'page' => $this->registry->offsetGet('page'),
			'service' => $this->registry->offsetGet('service'),
			'nav' => $this->registry->offsetGet('nav'),
			'path' => $this->registry->offsetGet('path'),
			'gadget' => $this->registry->offsetGet('gadget'),
			'template' => $this->registry->offsetGet('template'),
			'htmlJs' => $this->registry->offsetGet('htmlJs'),
			'htmlCss' => $this->registry->offsetGet('htmlCss'),
			'htmlContent' => $this->registry->offsetGet('htmlContent'),
		));
	}
}

