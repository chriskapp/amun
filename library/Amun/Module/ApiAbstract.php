<?php
/*
 *  $Id: ApiAbstract.php 835 2012-08-26 21:37:35Z k42b3.x@googlemail.com $
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

/**
 * Amun_Module_ApiAbstract
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   Amun
 * @package    Amun_Module
 * @version    $Revision: 835 $
 */
abstract class Amun_Module_ApiAbstract extends Amun_Oauth
{
	protected $session;
	protected $user;
	protected $service;

	public function __construct(PSX_Base $base, $basePath, array $uriFragments)
	{
		parent::__construct($base, $basePath, $uriFragments);

		// if the authorization header is set follow the oauth
		// authentication process else assign the user from the session
		$authorization = PSX_Base::getRequestHeader('Authorization');

		if($authorization !== false)
		{
			$this->doAuthentication();
		}
		else
		{
			$this->session = new PSX_Session('amun_' . md5($this->config['psx_url']));
			$this->session->start();

			$this->user = $this->base->setUser(Amun_User::getId($this->session, $this->registry));

			$this->setService();
		}
	}

	public function getDependencies()
	{
		return new Amun_Dependency_Default();
	}

	public function onAuthenticated()
	{
		$this->session = new PSX_Session('amun_api_' . md5($this->config['psx_url']));
		$this->session->setId($this->requestToken);
		$this->session->start();

		$this->user = $this->base->setUser($this->claimedUserId);
		$this->user->requestId = $this->requestId;

		$this->setService();
	}

	public function setService()
	{
		// set service
		$parts = explode('/', $this->basePath, 2);

		$this->service  = $this->base->getService($parts[0]);
		$this->basePath = 'api' . $this->service->path;
	}
}
