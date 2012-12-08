<?php
/*
 *  $Id: Gadget.php 818 2012-08-25 18:52:34Z k42b3.x@googlemail.com $
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
 * Amun_Dependency_Gadget
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://phpsx.org
 * @category   Amun
 * @package    Amun_Dependency
 * @version    $Revision: 818 $
 */
class Amun_Dependency_Gadget extends Amun_Dependency_Default
{
	public function setup()
	{
		parent::setup();

		$this->getSession();
		$this->getUser();
		$this->getPage();
		$this->getHtmlJs();
		$this->getHtmlCss();
		$this->getHtmlContent();
	}

	public function getSession()
	{
		if($this->has('session'))
		{
			return $this->get('session');
		}

		$session = new PSX_Session('amun_' . md5($this->config['psx_url']));
		$session->start();

		return $this->set('session', $session);
	}

	public function getUser()
	{
		if($this->has('user'))
		{
			return $this->get('user');
		}

		$userId = Amun_User::getId($this->getSession(), $this->getRegistry());

		return $this->set('user', new Amun_User($userId, $this->getRegistry()));
	}

	public function getPage()
	{
		if($this->has('page'))
		{
			return $this->get('page');
		}

		return $this->set('page', new Amun_Page($this->pageId, $this->getRegistry(), $this->getUser()));
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
}
