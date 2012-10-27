<?php
/*
 *  $Id: Css.php 880 2012-10-27 13:14:26Z k42b3.x@googlemail.com $
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
 * Amun_Ext_Provider_Css
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   Amun
 * @package    Amun_Ext
 * @version    $Revision: 880 $
 */
class Amun_Ext_Provider_Css implements Amun_Ext_ProviderInterface
{
	private $config;
	private $sql;
	private $registry;

	public function __construct(Amun_Registry $registry)
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

			PSX_PATH_TEMPLATE . '/' . $this->config['psx_template_dir'] . '/css/default.css',

		);

		$services = array_merge($services, $this->getContentServices());

		return $services;
	}

	private function getContentServices()
	{
		$result = Amun_Sql_Table_Registry::get('Content_Service')->getCol('name');

		foreach($result as $srv)
		{
			$services[$srv] = array(

				$this->config['amun_service_path'] . '/' . $srv . '/template/default.css',

			);
		}

		return $services;
	}
}

