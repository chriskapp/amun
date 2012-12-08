<?php
/*
 *  $Id: ApplicationAbstract.php 712 2012-06-18 22:02:46Z k42b3.x@googlemail.com $
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
 * Amun_Module_ApplicationAbstract
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   Amun
 * @package    Amun_Module
 * @version    $Revision: 712 $
 */
abstract class Amun_Module_ApplicationAbstract extends PSX_Module_ViewAbstract
{
	public function __construct(PSX_Loader_Location $location, PSX_Base $base, $basePath, array $uriFragments)
	{
		parent::__construct($location, $base, $basePath, $uriFragments);

		// assign default objects
		$container  = new Amun_Dependency_Default($this->base->getConfig());
		$parameters = $container->getParameters();

		foreach($parameters as $k => $obj)
		{
			$this->$k = $obj;
		}
	}

	public function onLoad()
	{
		// set xrds location header
		header('X-XRDS-Location: ' . $this->config['psx_url'] . '/' . $this->config['psx_dispatch'] . 'api/xrds');

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

			// set application template path
			$this->template->setDir($this->config['amun_service_path'] . '/' . $this->page->application . '/template');

			// add default css
			$this->htmlCss->add('default');
		}
		else
		{
			throw new Amun_Page_Exception('Invalid page');
		}
	}

	public function processResponse($content)
	{
		if(empty($content))
		{
			if(!($response = $this->template->transform()))
			{
				throw new PSX_Exception('Error while transforming template');
			}
		}
		else
		{
			$response = $content;
		}

		// set custom template if any
		$this->template->assign('content', $response);
		$this->template->setDir(PSX_PATH_TEMPLATE . '/' . $this->config['psx_template_dir']);

		if(!empty($this->page->template))
		{
			$this->template->set($this->page->template);
		}
		else
		{
			$this->template->set('page.tpl');
		}

		return parent::processResponse(null);
	}

	public function getDependencies()
	{
		$ct = new Amun_Dependency_Application($this->base->getConfig(), array(
			'application.pageId' => $this->location->getServiceId()
		));

		Amun_DataFactory::setContainer($ct);

		return $ct;
	}

	protected function getProvider($name = null)
	{
		$name = $name === null ? $this->service->namespace : $name;

		return Amun_DataFactory::getProvider($name);
	}

	protected function getTable($name = null)
	{
		return $this->getProvider($name)->getTable();
	}

	protected function getHandler($name = null)
	{
		return $this->getProvider($name)->getHandler();
	}

	/**
	 * Helper method to build the options for an application. Using the option
	 * class has the advantage that other services can easily extend the service
	 * by injecting links into the option menu
	 *
	 * @param array $data
	 * @return void
	 */
	protected function setOptions(array $data)
	{
		$options = new Amun_Option(__CLASS__, $this->registry, $this->user, $this->page);

		foreach($data as $row)
		{
			list($rightName, $title, $url) = $row;

			$options->add($rightName, $title, $url);
		}

		$options->load(array($this->page));

		$this->template->assign('options', $options);
	} 
}

