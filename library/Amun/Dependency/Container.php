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

use Amun\Base;
use Amun\Registry;
use Amun\Loader\LocationFinder;
use Amun\Event;
use Amun\User;
use Amun\Service;
use Amun\Page;
use Amun\Navigation;
use Amun\Path;
use Amun\Gadget\Container as GadgetContainer;
use Amun\Html;
use Amun\Gadget;
use Amun\HandlerManager;
use Amun\FormManager;
use PSX\Loader;
use PSX\Session;

/**
 * Container
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/gpl.html GPLv3
 * @link    http://phpsx.org
 */
class Container extends \PSX\Dependency\Container
{
	public function getBase()
	{
		return new Base($this->get('config'));
	}

	public function getLoader()
	{
		$loader = new Loader($this);
		$loader->addRoute('/.well-known/host-meta', 'api/hostmeta');
		$loader->setLocationFinder(new LocationFinder($this->get('registry')));

		return $loader;
	}

	public function getRegistry()
	{
		return Registry::initInstance($this->get('config'), $this->get('sql'));
	}

	public function getEvent()
	{
		return new Event($this);
	}

	public function getSession()
	{
		$session = new Session($this->getParameter('session.name'));

		if($this->hasParameter('session.id'))
		{
			$session->setId($this->getParameter('session.id'));
		}

		$session->start();

		return $session;
	}

	public function getUser()
	{
		return new User($this->getParameter('user.id'), $this->get('registry'), $this->getParameter('user.accessId'));
	}

	public function getService()
	{
		return new Service($this->getParameter('service.id'), $this->get('registry'));
	}

	public function getPage()
	{
		return new Page($this->getParameter('page.id'), $this->get('registry'), $this->get('user'));
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
		return new GadgetContainer($this->get('registry'), $this->get('user'));
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

	public function getGadget()
	{
		return new Gadget($this->getParameter('gadget.id'), $this->get('registry'), $this->get('user'));
	}

	public function getHandlerManager()
	{
		return new HandlerManager($this);
	}

	public function getFormManager()
	{
		return new FormManager($this);
	}
}
