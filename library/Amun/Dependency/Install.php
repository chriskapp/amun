<?php
/*
 *  $Id: Default.php 818 2012-08-25 18:52:34Z k42b3.x@googlemail.com $
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
 * Amun_Dependency_Install
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://phpsx.org
 * @category   Amun
 * @package    Amun_Dependency
 * @version    $Revision: 818 $
 */
class Amun_Dependency_Install extends PSX_DependencyAbstract
{
	public function setup()
	{
		parent::setup();

		$this->getSql();
		$this->getValidate();
		$this->getGet();
		$this->getPost();
		$this->getSession();
		$this->getTemplate();

		$this->getEvent();
		$this->getRegistry();
		$this->getUser();
	}

	public function getBase()
	{
		if($this->has('base'))
		{
			return $this->get('base');
		}

		return $this->set('base', new Amun_Base($this->config));
	}

	public function getSql()
	{
		if($this->has('sql'))
		{
			return $this->get('sql');
		}

		return $this->set('sql', new PSX_Sql($this->config['psx_sql_host'],
			$this->config['psx_sql_user'],
			$this->config['psx_sql_pw'],
			$this->config['psx_sql_db'])
		);
	}

	public function getRegistry()
	{
		if($this->has('registry'))
		{
			return $this->get('registry');
		}

		try
		{
			return $this->set('registry', Amun_Registry::initInstance($this->getConfig(), $this->getSql()));
		}
		catch(Exception $e)
		{
			return $this->set('registry', new Amun_Registry_NoDb($this->getConfig(), $this->getSql()));
		}
	}

	public function getEvent()
	{
		if($this->has('event'))
		{
			return $this->get('event');
		}

		return $this->set('event', Amun_Event::initInstance($this->getRegistry()));
	}

	public function getValidate()
	{
		if($this->has('validate'))
		{
			return $this->get('validate');
		}

		return $this->set('validate', new PSX_Validate());
	}

	public function getGet()
	{
		if($this->has('get'))
		{
			return $this->get('get');
		}

		return $this->set('get', new PSX_Input_Get($this->getValidate()));
	}

	public function getPost()
	{
		if($this->has('post'))
		{
			return $this->get('post');
		}

		return $this->set('post', new PSX_Input_Post($this->getValidate()));
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

		try
		{
			$userId = Amun_User::getId($this->getSession(), $this->getRegistry());

			return $this->set('user', new Amun_User($userId, $this->getRegistry()));
		}
		catch(Exception $e)
		{
			return $this->set('user', new Amun_User_NoDb($this->getRegistry()));
		}
	}

	public function getTemplate()
	{
		if($this->has('template'))
		{
			return $this->get('template');
		}

		return $this->set('template', new PSX_Template($this->config));
	}
}
