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

namespace Amun\Gadget;

use PSX\Cache;
use PSX\Config;
use PSX\Loader;

/**
 * Item
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/gpl.html GPLv3
 * @link    http://amun.phpsx.org
 */
class Item
{
	public $id;
	public $name;
	public $title;
	public $path;
	public $cache;
	public $expire;

	private $config;
	private $loader;
	private $body;

	public function __construct(Config $config, Loader $loader)
	{
		$this->config = $config;
		$this->loader = $loader;
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

	public function buildContent()
	{
		switch($this->type)
		{
			case 'iframe':
				$content = $this->getIframeContent();
				break;

			case 'ajax':
				$content = $this->getAjaxContent();
				break;

			default:
			case 'inline':
				$content = $this->getInlineContent();
				break;
		}

		$class   = pathinfo($this->path, PATHINFO_FILENAME);
		$content = '<div class="amun-gadget-' . strtolower($class) . '">' . $content . '</div>';

		$this->setBody($content);
	}

	public function getInlineContent()
	{
		$key    = 'gadget-' . $this->id;
		$expire = (integer) $this->expire;
		$cache  = new Cache($key, $expire);

		if($this->cache == 0 || ($content = $cache->load()) === false)
		{
			$content = $this->executeContent();

			// if caching is enabled write the cache
			if($this->cache == 1)
			{
				$cache->write($content);
			}
		}

		return $content;
	}

	public function getIframeContent()
	{
		$content = '<iframe src="' . $this->config['psx_url'] . '/' . $this->config['psx_dispatch'] . 'gadget/' . $this->name . '" id="gadget-' . $this->id . '" style="width:100%"></iframe>';

		return $content;
	}

	public function getAjaxContent()
	{
		$content = '<div id="gadget-' . $this->id . '"></div>';
		$content.= '<script type="text/javascript">amun.gadget.load("' . $this->name . '", "gadget-' . $this->id . '");</script>';

		return $content;
	}

	private function executeContent()
	{
		ob_start();

		try
		{
			$this->loader->load('/gadget/' . $this->name);

			$content = ob_get_contents();
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

		return $content;
	}
}
