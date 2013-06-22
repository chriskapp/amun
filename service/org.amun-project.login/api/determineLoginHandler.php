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

namespace login\api;

use AmunService\Login\HandlerAbstract;
use AmunService\Login\HandlerFactory;
use Amun\Module\ApiAbstract;
use PSX\Json;

/**
 * determineLoginHandler
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/gpl.html GPLv3
 * @link    http://amun.phpsx.org
 */
class determineLoginHandler extends ApiAbstract
{
	/**
	 * Returns whether the given identity needs an password or not
	 *
	 * @httpMethod GET
	 * @path /
	 * @nickname needPassword
	 * @parameter query identity string
	 * @responseClass PSX_Data_Message
	 */
	public function needPassword()
	{
		$identity = $this->get->identity('string');
		$handles  = array_map('trim', explode(',', $this->registry['login.provider']));
		$result   = array();

		foreach($handles as $key)
		{
			$handler = HandlerFactory::factory($key, $this->container);

			if($handler instanceof HandlerAbstract && $handler->isValid($identity))
			{
				$result = array(
					'handler'      => $key,
					'icon'         => $this->config['psx_url'] . '/img/icons/login/' . $key . '.png',
					'needPassword' => $handler->hasPassword(),
				);

				break;
			}
		}

		echo Json::encode($result);
	}
}



