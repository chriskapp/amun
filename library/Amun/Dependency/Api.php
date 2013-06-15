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

use Amun\DataFactory;
use Amun\Service;
use Amun\User;
use PSX\Config;

/**
 * Api
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/gpl.html GPLv3
 * @link    http://phpsx.org
 */
class Api extends Session
{
	protected $serviceId;
	protected $requestId;
	protected $accessId;

	public function __construct(Config $config, array $params)
	{
		$this->serviceId = isset($params['api.serviceId']) ? $params['api.serviceId'] : null;
		$this->requestId = isset($params['api.requestId']) ? $params['api.requestId'] : null;
		$this->accessId  = isset($params['api.accessId'])  ? $params['api.accessId'] : null;

		parent::__construct($config, $params);
	}

	public function getUser()
	{
		if($this->userId === null)
		{
			$this->userId = User::findUserId($this->get('session'), $this->get('registry'));
		}

		return new User($this->userId, $this->get('registry'), $this->accessId);
	}

	public function getService()
	{
		return new Service($this->serviceId, $this->get('registry'));
	}

	public function getDataFactory()
	{
		return DataFactory::initInstance($this);
	}
}

