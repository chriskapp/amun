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
use Amun\DataFactory;

/**
 * FormAbstract
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/gpl.html GPLv3
 * @link    http://amun.phpsx.org
 */
abstract class FormAbstract
{
	protected $ct;
	protected $base;
	protected $config;
	protected $sql;
	protected $registry;
	protected $user;
	protected $url;

	public function __construct(Dependency\Request $ct, $url = null)
	{
		$this->ct       = $ct;
		$this->base     = $ct->getBase();
		$this->config   = $ct->getConfig();
		$this->sql      = $ct->getSql();
		$this->registry = $ct->getRegistry();
		$this->user     = $ct->getUser();
		$this->url      = $url;
	}
}
