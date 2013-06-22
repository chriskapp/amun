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

namespace AmunService\Login;

/**
 * HandlerFactory
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/gpl.html GPLv3
 * @link    http://amun.phpsx.org
 */
class HandlerFactory
{
	/**
	 * Returns the fitting login handler for the $handler name
	 *
	 * @return AmunService\Login\HandlerAbstract
	 */
	public static function factory($handler, $container)
	{
		$handler = strtolower($handler);

		switch($handler)
		{
			case 'facebook':
			case 'github':
			case 'google':
			case 'ldap':
			case 'openid':
			case 'remote':
			case 'system':
			case 'twitter':
			case 'webfinger':
			case 'yahoo':
				$class = '\AmunService\Login\Handler\\' . ucfirst($handler);
				return new $class($container);
				break;
		}
	}
}
