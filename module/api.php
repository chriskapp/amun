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
class api extends Amun_Module_DefaultAbstract
{
	public function onLoad()
	{
		// set service path for loader
		$this->loader->setPath($this->config['amun_service_path']);

		// validate input path
		$x     = trim($this->config['psx_module_input'], '/');
		$parts = explode('/', $x, 2);

		if(count($parts) < 2)
		{
			throw new PSX_Exception('Invalid request', 400);
		}

		if(!isset($parts[0]) || $parts[0] != 'api')
		{
			throw new PSX_Exception('Invalid request', 400);
		}

		$path = '/' . $parts[1];

		// get service
		$sql = "SELECT
					`id`,
					`source`,
					`path`,
					`namespace`
				FROM
					" . $this->registry['table.core_content_service'] . "
				WHERE
					`path` LIKE SUBSTRING(?, 1, CHAR_LENGTH(`path`))
				LIMIT 1";

		$service = $this->sql->getRow($sql, array($path));

		if(!empty($service))
		{
			// set loader namespace strategy
			$nss = new Amun_Loader_NamespaceStrategy($service['namespace'], $service['source']);

			$this->loader->setNamespaceStrategy($nss);

			// load module
			$path = substr($path, strlen($service['path']) + 1);
			$x    = $service['source'] . '/api/' . $path;

			$this->loader->load($x);
		}
		else
		{
			throw new PSX_Exception('Service not found', 404);
		}
	}
}
