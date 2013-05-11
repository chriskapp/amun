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
use Amun\Gadget as AmunGadget;
use Amun\Service;
use PSX\Config;

/**
 * Gadget
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/gpl.html GPLv3
 * @link    http://phpsx.org
 */
class Gadget extends Session
{
	protected $gadgetId;

	public function __construct(Config $config, array $params)
	{
		$this->gadgetId = isset($params['gadget.id']) ? $params['gadget.id'] : null;;

		parent::__construct($config, $params);
	}

	public function setup()
	{
		parent::setup();

		$this->getGadget();
		$this->getArgs();
		$this->getService();
		$this->getDataFactory();
	}

	public function getGadget()
	{
		return $this->set('gadget', new AmunGadget($this->gadgetId, $this->getRegistry(), $this->getUser()));
	}

	public function getArgs()
	{
		return $this->set('args', $this->getGadget()->getArgs());		
	}

	public function getService()
	{
		if($this->has('service'))
		{
			return $this->get('service');
		}

		return $this->set('service', new Service($this->getGadget()->getServiceId(), $this->getRegistry()));
	}

	public function getDataFactory()
	{
		if($this->has('dataFactory'))
		{
			return $this->get('dataFactory');
		}

		return $this->set('dataFactory', DataFactory::initInstance($this));
	}
}
