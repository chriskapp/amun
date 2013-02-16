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
	public function onLoad()
	{
		// set xrds location header
		header('X-XRDS-Location: ' . $this->config['psx_url'] . '/' . $this->config['psx_dispatch'] . 'api/xrds');

		if(!empty($this->page->id))
		{
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
				$this->gadgetContainer->load($this->loader, $this->page, $this->htmlCss);
			}

			// set application template path
			$this->template->setDir($this->config['amun_service_path'] . '/' . $this->page->application . '/template');

			// add meta tags
			$this->loadMetaTags();

			// add default css
			$this->htmlCss->add('default');
			$this->htmlJs->add('amun');
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

		return $ct;
	}

	protected function getHandler($table = null)
	{
		return $this->dataFactory->getHandlerInstance($table === null ? $this->service->namespace : $table);
	}

	protected function loadMetaTags()
	{
		if(!empty($this->page->description))
		{
			$this->htmlContent->add(Amun_Html_Content::META, '<meta name="description" content="' . $this->page->description . '" />');
		}

		if(!empty($this->page->keywords))
		{
			$this->htmlContent->add(Amun_Html_Content::META, '<meta name="keywords" content="' . $this->page->keywords . '" />');
		}

		if($this->page->publishDate != '0000-00-00 00:00:00')
		{
			$publishDate = new DateTime($this->page->publishDate);

			$this->htmlContent->add(Amun_Html_Content::META, '<meta name="date" content="' . $publishDate->format(DateTime::ATOM) . '" />');
		}
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
		$options = new Amun_Option($this->location->getClass()->getName(), $this->registry, $this->user, $this->page);

		foreach($data as $row)
		{
			list($rightName, $title, $url) = $row;

			$options->add($rightName, $title, $url);
		}

		$options->load(array($this->page));

		$this->template->assign('options', $options);
	} 

	protected function getRequestCondition()
	{
		$con          = new PSX_Sql_Condition();
		$filterBy     = isset($_GET['filterBy']) ? $_GET['filterBy'] : null;
		$filterOp     = isset($_GET['filterOp']) ? $_GET['filterOp'] : null;
		$filterValue  = isset($_GET['filterValue']) ? $_GET['filterValue'] : null;
		$updatedSince = isset($_GET['updatedSince']) ? $_GET['updatedSince'] : null;

		switch($filterOp)
		{
			case 'contains':
				$con->add($filterBy, 'LIKE', '%' . $filterValue . '%');
				break;

			case 'equals':
				$con->add($filterBy, '=', $filterValue);
				break;

			case 'startsWith':
				$con->add($filterBy, 'LIKE', $filterValue . '%');
				break;

			case 'present':
				$con->add($filterBy, 'IS NOT', 'NULL', 'AND');
				$con->add($filterBy, 'NOT LIKE', '');
				break;
		}

		if($updatedSince !== null)
		{
			$datetime = new PSX_DateTime($updatedSince);

			$con->add('date', '>', $datetime->format(PSX_DateTime::SQL));
		}

		return $con;
	}
}

