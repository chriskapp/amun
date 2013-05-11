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
use Amun\Loader\LocationFinder;
use Amun\Registry;
use Amun\Event;
use PSX\DependencyAbstract;
use PSX\Loader;
use PSX\Sql;
use PSX\Validate;
use PSX\Input;

/**
 * Request
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/gpl.html GPLv3
 * @link    http://phpsx.org
 */
class Request extends DependencyAbstract
{
	public function setup()
	{
		parent::setup();

		$this->getSql();
		$this->getRegistry();
		$this->getEvent();
		$this->getValidate();
		$this->getGet();
		$this->getPost();
	}

	public function getBase()
	{
		if($this->has('base'))
		{
			return $this->get('base');
		}

		return $this->set('base', new Base($this->config));
	}

	public function getLoader()
	{
		if($this->has('loader'))
		{
			return $this->get('loader');
		}

		$loader = new Loader($this->getBase());
		$loader->setLocationFinder(new LocationFinder($this->getRegistry()));
		$loader->addRoute('/.well-known/host-meta', 'api/hostmeta');

		return $this->set('loader', $loader);
	}

	public function getSql()
	{
		if($this->has('sql'))
		{
			return $this->get('sql');
		}

		return $this->set('sql', new Sql($this->config['psx_sql_host'],
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

		return $this->set('registry', Registry::initInstance($this->getConfig(), $this->getSql()));
	}

	public function getEvent()
	{
		if($this->has('event'))
		{
			return $this->get('event');
		}

		return $this->set('event', Event::initInstance($this));
	}

	public function getValidate()
	{
		if($this->has('validate'))
		{
			return $this->get('validate');
		}

		return $this->set('validate', new Validate());
	}

	public function getGet()
	{
		if($this->has('get'))
		{
			return $this->get('get');
		}

		return $this->set('get', new Input\Get($this->getValidate()));
	}

	public function getPost()
	{
		if($this->has('post'))
		{
			return $this->get('post');
		}

		return $this->set('post', new Input\Post($this->getValidate()));
	}
}
