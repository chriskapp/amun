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
 * Amun_Dependency_Api
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://phpsx.org
 * @category   Amun
 * @package    Amun_Dependency
 * @version    $Revision: 818 $
 */
class Amun_Dependency_Api extends Amun_Dependency_Session
{
	protected $serviceId;
	protected $requestId;
	protected $accessId;

	public function __construct(PSX_Config $config, array $params)
	{
		$this->serviceId = isset($params['api.serviceId']) ? $params['api.serviceId'] : null;
		$this->requestId = isset($params['api.requestId']) ? $params['api.requestId'] : null;
		$this->accessId  = isset($params['api.accessId'])  ? $params['api.accessId'] : null;

		parent::__construct($config, $params);
	}

	public function setup()
	{
		parent::setup();

		$this->getService();
		$this->getDataFactory();
	}

	public function getUser()
	{
		if($this->has('user'))
		{
			return $this->get('user');
		}

		if($this->userId === null)
		{
			$this->userId = Amun_User::getId($this->getSession(), $this->getRegistry());
		}

		return $this->set('user', new Amun_User($this->userId, $this->getRegistry(), $this->accessId));
	}

	public function getService()
	{
		if($this->has('service'))
		{
			return $this->get('service');
		}

		return $this->set('service', new Amun_Service($this->serviceId, $this->getRegistry()));
	}

	public function getDataFactory()
	{
		if($this->has('dataFactory'))
		{
			return $this->get('dataFactory');
		}

		return $this->set('dataFactory', Amun_DataFactory::initInstance($this));
	}
}

