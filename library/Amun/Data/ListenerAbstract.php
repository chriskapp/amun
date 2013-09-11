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

namespace Amun\Data;

use Amun\Dependency;
use Amun\User;

/**
 * ListenerAbstract
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/gpl.html GPLv3
 * @link    http://amun.phpsx.org
 */
abstract class ListenerAbstract
{
	protected $container;
	protected $base;
	protected $config;
	protected $sql;
	protected $registry;
	protected $event;
	protected $hm;
	protected $user;

	public function __construct($container, User $user = null)
	{
		$this->container = $container;
		$this->base      = $container->get('base');
		$this->config    = $container->get('config');
		$this->sql       = $container->get('sql');
		$this->registry  = $container->get('registry');
		$this->event     = $container->get('event');
		$this->hm        = $container->get('handlerManager');
		$this->user      = $user !== null ? $user : $container->get('user');
	}
}
