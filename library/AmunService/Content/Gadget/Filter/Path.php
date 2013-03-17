<?php
/*
 *  $Id: Path.php 838 2012-08-27 20:20:36Z k42b3.x@googlemail.com $
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

namespace AmunService\Content\Gadget\Filter;

use PSX\Config;

/**
 * AmunService_Core_Content_Gadget_Filter_Path
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   Amun
 * @package    Amun_Content_Gadget
 * @version    $Revision: 838 $
 */
class Path extends \PSX\Filter\Url
{
	private $config;

	public function __construct(Config $config)
	{
		$this->config = $config;
	}

	public function apply($value)
	{
		$value = trim($value, '/');

		return strpos($value, '..') === false && is_file($this->config['amun_service_path'] . '/' . $value);
	}

	public function getErrorMsg()
	{
		return '%s is invalid';
	}
}
