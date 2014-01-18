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

namespace Amun\Module;

use Amun\Base;
use Amun\Oauth;
use Amun\Dependency;
use Amun\Dispatch\RequestFilter\OauthAuthentication;
use Amun\User;
use PSX\Loader\Location;

/**
 * ApiAbstract
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/gpl.html GPLv3
 * @link    http://amun.phpsx.org
 */
abstract class ApiAbstract extends \PSX\Module\ApiAbstract
{
	protected $get;
	protected $post;
	protected $dm;
	protected $user;
	protected $registry;
	protected $service;

	public function getRequestFilter()
	{
		$con    = $this->getContainer();
		$config = $this->config;
		$oauth  = new OauthAuthentication($this->getRegistry());

		$oauth->onSuccess(function($userId, $accessId, $token) use ($con, $config){
			$con->setParameter('session.name', 'amun-api-' . md5($config['psx_url']));
			$con->setParameter('session.id', md5($token));
			$con->setParameter('user.id', $userId);
			$con->setParameter('user.accessId', $accessId);
		});

		$oauth->onMissing(function(){
			// we dont throw an exception since the user has probably an session
			// wich can be used for authentication
		});

		return array($oauth);
	}

	public function onLoad()
	{
		// set parameters
		if(!$this->container->hasParameter('session.name'))
		{
			$this->container->setParameter('session.name', 'amun-' . md5($this->config['psx_url']));
		}

		if(!$this->container->hasParameter('user.id'))
		{
			$this->container->setParameter('user.id', User::findUserId($this->getSession(), $this->getRegistry()));
		}

		$this->container->setParameter('service.id', $this->location->getServiceId());

		// dependencies
		$this->get      = $this->getInputGet();
		$this->post     = $this->getInputPost();
		$this->dm       = $this->getDomainManager();
		$this->registry = $this->getRegistry();
		$this->session  = $this->getSession();
		$this->user     = $this->getUser();
		$this->service  = $this->getService();
	}

	protected function getDomain($name = null)
	{
		return $this->dm->getDomain($name === null ? $this->service->getNamespace() . '\Domain' : $name);
	}
}

