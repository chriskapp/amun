<?php
/*
 *  $Id: Item.php 880 2012-10-27 13:14:26Z k42b3.x@googlemail.com $
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
 * Amun_Gadget_Item
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   Amun
 * @package    Amun_Gadget
 * @version    $Revision: 880 $
 */
class Amun_Gadget_Item
{
	public $id;
	public $title;
	public $path;
	public $cache;
	public $expire;
	public $param;

	private $config;
	private $body;

	public function __construct(PSX_Config $config)
	{
		$this->config = $config;
	}

	public function getTitle()
	{
		return $this->title;
	}

	public function getBody()
	{
		return $this->body;
	}

	public function setBody($body)
	{
		$this->body = $body;
	}

	public function parseContent($service)
	{
		$key   = 'gadget-' . $this->id;
		$cache = new PSX_Cache($key, $this->expire);

		if($this->cache == 0 || ($content = $cache->load()) === false)
		{
			$content = $this->executeContent($service);

			// if caching is enabled write the cache
			if($this->cache == 1)
			{
				$cache->write($content);
			}
		}

		$this->setBody($content);
	}

	private function executeContent($service)
	{
		$class = pathinfo($this->path, PATHINFO_FILENAME);
		$args  = Amun_Gadget_Args::parse($this->param);

		ob_start();

		try
		{
			include_once($this->config['amun_service_path'] . '/' . $this->path);

			if(class_exists($class, false))
			{
				$obj = new $class();
				$obj->onLoad($args);

				$content = ob_get_contents();
			}
			else
			{
				throw new Amun_Gadget_Exception('Could not find class ' . $class . ' in ' . $this->path);
			}
		}
		catch(Exception $e)
		{
			$content = '<p>' . $e->getMessage() . '</p>';

			if($this->config['psx_debug'] === true)
			{
				$content.= '<pre>' . $e->getTraceAsString() . '</pre>';
			}
		}

		ob_end_clean();

		return '<div class="amun-gadget-' . strtolower($class) . '">' . $content . '</div>';
	}

	private function getCache($token, $expire)
	{
		$file = PSX_PATH_CACHE . '/c_g_' . $token . '.htm';

		if(is_file($file))
		{
			if((filemtime($file) + $expire) > time())
			{
				$content = file_get_contents($file);

				return $content;
			}
		}

		return false;
	}
}
