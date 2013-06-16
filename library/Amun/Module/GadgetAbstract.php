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

use Amun\Dependency;
use PSX\Module\ViewAbstract;

/**
 * GadgetAbstract
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/gpl.html GPLv3
 * @link    http://amun.phpsx.org
 */
abstract class GadgetAbstract extends ViewAbstract
{
	protected $get;
	protected $post;
	protected $registry;
	protected $session;
	protected $user;
	protected $gadget;

	protected $hm;

	public function onLoad()
	{
		// set parameters
		$this->container->setParameter('gadget.id', $this->location->getServiceId());

		// dependencies
		$this->get      = $this->getInputGet();
		$this->post     = $this->getInputPost();
		$this->registry = $this->getRegistry();
		$this->session  = $this->getSession();
		$this->user     = $this->getUser();
		$this->gadget   = $this->getGadget();

		// manager
		$this->hm = $this->getHandlerManager();
	}

	protected function getHandler($name = null)
	{
		return $this->hm->getHandler($name === null ? $this->service->getNamespace() : $name);
	}
}

