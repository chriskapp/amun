<?php
/*
 *  $Id: CallbackAbstract.php 689 2012-06-06 20:30:55Z k42b3.x@googlemail.com $
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
 * Amun_PubSubHubBub_CallbackAbstract
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   Amun
 * @package    Amun_PubSubHubBub
 * @version    $Revision: 689 $
 */
abstract class Amun_PubSubHubBub_CallbackAbstract extends PSX_PubSubHubBub_CallbackAbstract
{
	const PENDING     = 0x1;
	const SUBSCRIBE   = 0x2;
	const UNSUBSCRIBE = 0x3;

	public function __construct(PSX_Base $base, $basePath, array $uriFragments)
	{
		parent::__construct($base, $basePath, $uriFragments);

		try
		{
			$this->handle();
		}
		catch(Exception $e)
		{
			header('HTTP/1.1 404 Not Found');

			echo $e->getMessage();

			if($this->config['psx_debug'] === true)
			{
				echo "\n\n" . $e->getTraceAsString();
			}

			exit;
		}
	}

	public function getDependencies()
	{
		return new Amun_Dependency_Default();
	}
}

