<?php
/*
 *  $Id: dispatch.php 880 2012-10-27 13:14:26Z k42b3.x@googlemail.com $
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
 * dispatch
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   module
 * @version    $Revision: 880 $
 */
class dispatch extends Amun_Module_ApplicationAbstract
{
	public function onLoad()
	{
		// set xrds location header
		header('X-XRDS-Location: ' . $this->config['psx_url'] . '/' . $this->config['psx_dispatch'] . 'api/core/meta/xrds');

		if(!empty($this->page->id))
		{
			// load extra rights
			if($this->page->hasRight())
			{
				$this->page->loadExtraRights();
			}

			// load nav
			if($this->page->hasNav())
			{
				$this->nav->load();
			}

			// load path
			if($this->page->hasPath())
			{
				$this->path->load();
			}

			// load gadgets
			if($this->page->hasGadget())
			{
				$this->gadget->load($this->page);
			}

			// load application
			$content = $this->loadApplication();

			$this->template->assign('content', $content);

			// set custom template if any
			$this->template->setDir(PSX_PATH_TEMPLATE . '/' . $this->config['psx_template_dir']);

			if(!empty($this->page->template))
			{
				$this->template->set($this->page->template);
			}
			else
			{
				$this->template->set('page.tpl');
			}
		}
		else
		{
			throw new Amun_Page_Exception('Invalid page');
		}
	}

	private function loadApplication()
	{
		// set application template path
		$this->template->setDir($this->config['amun_service_path'] . '/' . $this->page->application . '/template');

		// add default css
		$this->htmlCss->add('default');

		// set service path for loader
		$this->loader->setPath($this->config['amun_service_path']);

		// build module path
		$path = $this->page->application . '/application/' . $this->page->applicationPath;

		if($this->page->cache)
		{
			$key   = 'page-' . $this->page->applicationPath;
			$cache = new PSX_Cache($key, $this->page->expire);

			if(($content = $cache->load()) === false)
			{
				$content = $this->executeApplication($path);

				$cache->write($content);
			}
			else
			{
				# we have a cached content
				$content.= "\n" . '<!-- cached -->';
			}
		}
		else
		{
			$content = $this->executeApplication($path);
		}

		return $content;
	}

	public function executeApplication($path)
	{
		ob_start();

		try
		{
			$service = $this->loader->load($path);

			$content = ob_get_contents();

			// proccess response
			if($service instanceof Amun_Module_ApplicationAbstract)
			{
				$response = $service->processResponse($content);
			}
			else
			{
				throw new Amun_Page_Exception('Page not found', 404);
			}
		}
		catch(Exception $e)
		{
			// set response code
			$code = isset(PSX_Http::$codes[$e->getCode()]) ? $e->getCode() : null;

			if(!empty($code))
			{
				PSX_Base::setResponseCode($code);
			}

			// build message
			$response = '<p>' . $e->getMessage() . '</p>';

			if($this->config['psx_debug'] === true)
			{
				$response.= '<pre>' . $e->getTraceAsString() . '</pre>';
			}
		}

		ob_end_clean();

		return $response;
	}
}

