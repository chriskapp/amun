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

use Amun\Oauth;
use Amun\Dependency;
use Amun\Base;
use PSX\Loader\Location;

/**
 * ApiAbstract
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/gpl.html GPLv3
 * @link    http://amun.phpsx.org
 */
abstract class ApiAbstract extends Oauth
{
	protected $sessionName;
	protected $sessionId;
	protected $userId;

	protected $get;
	protected $registry;
	protected $service;

	public function __construct(Location $location, Base $base, $basePath, array $uriFragments)
	{
		parent::__construct($location, $base, $basePath, $uriFragments);

		// if the authorization header is set follow the oauth
		// authentication process else assign the user from the session
		$authorization = Base::getRequestHeader('Authorization');

		if($authorization !== false)
		{
			$this->doAuthentication();
		}
	}

	public function getDependencies()
	{
		$ct = new Dependency\Api($this->base->getConfig(), array(
			'session.name'   => $this->sessionName,
			'session.userId' => $this->userId,
			'session.id'     => $this->requestToken,
			'api.serviceId'  => $this->location->getServiceId(),
			'api.requestId'  => $this->requestId,
			'api.accessId'   => $this->accessId,
		));

		return $ct;
	}

	public function onAuthenticated()
	{
		$this->sessionName = 'amun_api_' . md5($this->config['psx_url']);
		$this->userId      = $this->claimedUserId;
	}

	public function onLoad()
	{
		// dependencies
		$this->get      = $this->getInputGet();
		$this->registry = $this->getRegistry();
		$this->service  = $this->getService();
	}

	protected function getHandler($table = null)
	{
		return $this->getDataFactory()->getHandlerInstance($table === null ? $this->service->getNamespace() : $table);
	}
}

