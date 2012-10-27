<?php
/*
 *  $Id: Ext.php 880 2012-10-27 13:14:26Z k42b3.x@googlemail.com $
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
 * Amun_Ext
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   Amun
 * @package    Amun_Ext
 * @version    $Revision: 880 $
 */
class Amun_Ext
{
	private $config;
	private $provider;

	private $services;

	private $loaded = array();
	private $expire = 86400;

	public function __construct(PSX_Config $config, Amun_Ext_ProviderInterface $provider)
	{
		$this->config   = $config;
		$this->provider = $provider;

		$this->services = $this->provider->getServices();
	}

	public function serve($services)
	{
		$services = $this->parse($services);

		if(empty($services))
		{
			throw new Amun_Exception('No service selected');
		}

		$key   = 'ext-' . implode('-', $services);
		$cache = new PSX_Cache($key, $this->expire);

		if(($content = $cache->load()) === false)
		{
			foreach($services as $srv)
			{
				$content.= $this->getContent($srv);
			}


			if($this->config['psx_debug'] !== true)
			{
				$cache->write($content);
			}
		}


		header('Content-type: ' . $this->provider->getContentType());

		return $content;
	}

	private function getContent($service)
	{
		// load content
		$content = '';

		if(isset($this->services[$service]))
		{
			foreach($this->services[$service] as $srv)
			{
				if(!in_array($srv, $this->loaded) && PSX_File::exists($srv))
				{
					$content.= file_get_contents($srv) . "\n\n";

					$this->loaded[] = $srv;
				}
			}
		}

		return $content;
	}

	private function parse($raw)
	{
		$services = array();
		$raw      = explode('|', $raw);

		foreach($raw as $srv)
		{
			if(isset($this->services[$srv]))
			{
				$services[] = $srv;
			}
		}

		return $services;
	}
}

