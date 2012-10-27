<?php
/*
 *  $Id: dispatch.php 716 2012-06-19 19:41:56Z k42b3.x@googlemail.com $
 *
 * amun
 * A social content managment system based on the psx framework. For
 * the current version and informations visit <http://amun.phpsx.org>
 *
 * Copyright (c) 2010-2012 Christoph Kappestein <k42b3.x@gmail.com>
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

/**
 * api
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   module
 * @version    $Revision: 716 $
 */
class api extends PSX_ModuleAbstract
{
	public function onLoad()
	{
		// set service path for loader
		$this->loader->setPath($this->config['amun_service_path']);

		// validate input path
		$x     = trim($this->config['psx_module_input'], '/');
		$parts = explode('/', $x, 4);

		if(count($parts) < 3)
		{
			throw new PSX_Exception('Invalid request', 400);
		}

		if(!isset($parts[0]) || $parts[0] != 'api')
		{
			throw new PSX_Exception('Invalid request', 400);
		}

		if(!isset($parts[1]) || $parts[1] != 'service')
		{
			throw new PSX_Exception('Invalid request', 400);
		}

		$con     = new PSX_Sql_Condition(array('name', '=', $parts[2]));
		$service = Amun_Sql_Table_Registry::get('Content_Service')->getField('name', $con);

		if(!empty($service))
		{
			if(isset($parts[3]))
			{
				$path = $service . '/api/' . $parts[3];
			}
			else
			{
				$path = $service . '/api';
			}

			// load api
			$this->loader->load($path);
		}
		else
		{
			throw new PSX_Exception('Service not found', 404);
		}
	}
}
