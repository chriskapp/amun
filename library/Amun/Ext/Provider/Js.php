<?php
/*
 *  $Id: Js.php 880 2012-10-27 13:14:26Z k42b3.x@googlemail.com $
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
 * Amun_Ext_Provider_Js
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   Amun
 * @package    Amun_Ext
 * @version    $Revision: 880 $
 */
class Amun_Ext_Provider_Js implements Amun_Ext_ProviderInterface
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
		return 'application/x-javascript';
	}

	public function getServices()
	{
		$services = array();

		$services['jquery'] = array(

			PSX_PATH_TEMPLATE . '/' . $this->config['psx_template_dir'] . '/js/jquery/jquery.js',

		);

		$services['ace'] = array(

			PSX_PATH_TEMPLATE . '/' . $this->config['psx_template_dir'] . '/js/ace/ace.js',
			PSX_PATH_TEMPLATE . '/' . $this->config['psx_template_dir'] . '/js/ace/theme-eclipse.js',
			PSX_PATH_TEMPLATE . '/' . $this->config['psx_template_dir'] . '/js/ace/mode-html.js',
			PSX_PATH_TEMPLATE . '/' . $this->config['psx_template_dir'] . '/js/ace/mode-markdown.js',
			PSX_PATH_TEMPLATE . '/' . $this->config['psx_template_dir'] . '/js/ace/mode-php.js',

		);

		$services['prettify'] = array(

			PSX_PATH_TEMPLATE . '/' . $this->config['psx_template_dir'] . '/js/prettify/prettify.js',

		);

		$services['bootstrap'] = array(

			PSX_PATH_TEMPLATE . '/' . $this->config['psx_template_dir'] . '/js/jquery/jquery.js',
			PSX_PATH_TEMPLATE . '/' . $this->config['psx_template_dir'] . '/js/bootstrap/bootstrap.js',

		);

		$services['amun'] = array(

			PSX_PATH_TEMPLATE . '/' . $this->config['psx_template_dir'] . '/js/jquery/jquery.js',
			PSX_PATH_TEMPLATE . '/' . $this->config['psx_template_dir'] . '/js/amun/amun.js',

		);

		$services = array_merge($services, $this->getContentServices());

		return $services;
	}

	private function getContentServices()
	{
		$result = Amun_Sql_Table_Registry::get('Core_Service')->getAll(array('name', 'source'));

		foreach($result as $row)
		{
			$services[$row['name']] = array(

				$this->config['amun_service_path'] . '/' . $row['source'] . '/template/default.js',

			);
		}

		return $services;
	}
}

