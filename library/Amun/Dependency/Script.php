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

use Amun\User;
use PSX\Config;

/**
 * Script
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/gpl.html GPLv3
 * @link    http://phpsx.org
 */
class Script extends Request
{
	protected $userId;

	public function __construct(Config $config, array $params = array())
	{
		$this->userId = isset($params['script.userId']) ? $params['script.userId'] : null;

		parent::__construct($config);
	}

	public function setup()
	{
		parent::setup();

		$this->getUser();
	}

	public function getUser()
	{
		if($this->has('user'))
		{
			return $this->get('user');
		}

		return $this->set('user', new User($this->userId, $this->getRegistry()));
	}
}
