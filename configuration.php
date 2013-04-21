<?php
/*
 *  $Id: configuration.php 801 2012-07-08 21:17:10Z k42b3.x@googlemail.com $
 *
 * psx
 * A object oriented and modular based PHP framework for developing
 * dynamic web applications. For the current version and informations
 * visit <http://phpsx.org>
 *
 * Copyright (c) 2011 Christoph Kappestein <k42b3.x@gmail.com>
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

$config = array(

	// amun
	'amun_captcha'            => 'image',
	'amun_table_registry'     => 'core_registry',
	'amun_table_prefix'       => 'amun_',
	'amun_service_path'       => '../service',
	'amun_salt'               => '4ec656bfdee95a3596e31c3d36e49dda',

	// psx
	'psx_url'                 => 'http://127.0.0.1',
	'psx_dispatch'            => 'index.php/',
	'psx_timezone'            => 'Europe/Berlin',
	'psx_gzip'                => false,
	'psx_debug'               => true,
	'psx_autoload'            => false,
	'psx_include_path'        => false,

	'psx_module_default'      => '',
	'psx_module_input'        => isset($_GET['x']) ? $_GET['x'] : (isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : (isset($_SERVER['argv'][1]) ? $_SERVER['argv'][1] : '')),
	'psx_module_input_length' => 256,

	'psx_sql_host'            => 'localhost',
	'psx_sql_user'            => 'root',
	'psx_sql_pw'              => '',
	'psx_sql_db'              => 'amun_dev',

	'psx_cache_enabled'       => true,
	'psx_cache_expire'        => 60 * 30,

	'psx_template_dir'        => 'default',
	'psx_template_default'    => false,

	'psx_path_cache'          => '../cache',
	'psx_path_library'        => '../library',
	'psx_path_module'         => '../module',
	'psx_path_template'       => '../template',

);

