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

namespace AmunService\Asset\Provider;

use Amun\Registry;
use AmunService\Asset\ProviderInterface;

/**
 * Css
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/gpl.html GPLv3
 * @link    http://amun.phpsx.org
 */
class Css implements ProviderInterface
{
	private $config;
	private $sql;
	private $registry;

	public function __construct(Registry $registry)
	{
		$this->config   = $registry->getConfig();
		$this->sql      = $registry->getSql();
		$this->registry = $registry;
	}

	public function getContentType()
	{
		return 'text/css';
	}

	public function getServices()
	{
		$services = array();

		$services['default'] = array(
			PSX_PATH_TEMPLATE . '/' . $this->config['psx_template_dir'] . '/css/bootstrap/bootstrap.css',
			PSX_PATH_TEMPLATE . '/' . $this->config['psx_template_dir'] . '/css/bootstrap/bootstrap-theme.css',
			PSX_PATH_TEMPLATE . '/' . $this->config['psx_template_dir'] . '/css/prettify/prettify.css',
			PSX_PATH_TEMPLATE . '/' . $this->config['psx_template_dir'] . '/css/default.css',
		);

		$services = array_merge($services, $this->getContentServices());

		return $services;
	}

	private function getContentServices()
	{
		$sql = <<<SQL
SELECT
	`autoloadPath`,
	`name`,
	`namespace`
FROM
	{$this->registry['table.core_service']}
SQL;

		$result = $this->sql->getAll($sql);

		foreach($result as $row)
		{
			$parts = explode('/', $row['name']);
			$name  = end($parts);

			$services[$name] = array(
				'../' . $row['autoloadPath'] . '/' . $row['namespace'] . '/Resource/default.css',
			);
		}

		return $services;
	}
}
