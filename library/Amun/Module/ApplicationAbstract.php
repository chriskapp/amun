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

namespace Amun\Module;

use DateInterval;
use Amun\Dependency;
use Amun\Exception;
use Amun\Html;
use Amun\Option;
use Amun\User;
use PSX\Base;
use PSX\DateTime;
use PSX\Loader\Location;
use PSX\Module\ViewAbstract;
use PSX\Sql\Condition;

/**
 * ApplicationAbstract
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/gpl.html GPLv3
 * @link    http://amun.phpsx.org
 */
abstract class ApplicationAbstract extends ViewAbstract
{
	protected $get;
	protected $post;
	protected $registry;
	protected $session;
	protected $user;
	protected $page;
	protected $service;
	protected $template;

	protected $hm;

	protected $navigation;
	protected $path;
	protected $gadgetContainer;

	protected $htmlJs;
	protected $htmlCss;
	protected $htmlContent;

	public function onLoad()
	{
		// set parameters
		$this->container->setParameter('session.name', 'amun-' . md5($this->config['psx_url']));
		$this->container->setParameter('user.id', User::findUserId($this->getSession(), $this->getRegistry()));
		$this->container->setParameter('page.id', $this->location->getServiceId());
		$this->container->setParameter('service.id', $this->getPage()->getServiceId());

		// set xrds location header
		header('X-XRDS-Location: ' . $this->config['psx_url'] . '/' . $this->config['psx_dispatch'] . 'api/xrds');

		// dependencies
		$this->validate = $this->getValidate();
		$this->get      = $this->getInputGet();
		$this->post     = $this->getInputPost();
		$this->registry = $this->getRegistry();
		$this->session  = $this->getSession();
		$this->user     = $this->getUser();
		$this->page     = $this->getPage();
		$this->service  = $this->getService();
		$this->template = $this->getTemplate();

		// manager
		$this->hm = $this->getHandlerManager();

		// load nav
		if($this->page->hasNav())
		{
			$this->navigation = $this->getNavigation();
			$this->navigation->load();
		}

		// load path
		if($this->page->hasPath())
		{
			$this->path = $this->getPath();
			$this->path->load();
		}

		// load gadgets
		if($this->page->hasGadget())
		{
			$this->gadgetContainer = $this->getGadgetContainer();
			$this->gadgetContainer->load($this->getLoader(), $this->page, $this->getHtmlCss());
		}

		// load cache
		if($this->page->hasCache() && $this->user->isAnonymous() && Base::getRequestMethod() == 'GET')
		{
			$expire   = $this->page->getExpire();
			$expire   = $expire instanceof DateInterval ? $expire : new DateInterval('P1D');
			$modified = new DateTime();
			$expires  = clone $modified;
			$expires->add($expire);

			$type     = $this->user->isAnonymous() ? 'public' : 'private';
			$maxAge   = DateTime::convertIntervalToSeconds($expire);

			header('Expires: ' . $expires->format(DateTime::RFC1123));
			header('Last-Modified: ' . $modified->format(DateTime::RFC1123));
			header('Cache-Control: ' . $type . ', max-age=' . $maxAge);
			header('Pragma:'); // remove pragma header
		}

		// template dependencies
		$this->htmlJs      = $this->getHtmlJs();
		$this->htmlCss     = $this->getHtmlCss();
		$this->htmlContent = $this->getHtmlContent();

		// set application template path
		$this->template->setDir($this->config['amun_service_path'] . '/' . $this->page->getApplication() . '/template');

		// add html fragments
		$this->loadHtmlFragments();

		// add default css
		$this->htmlCss->add('default');
		$this->htmlJs->add('amun');
	}

	public function processResponse($content)
	{
		// assign default template vars
		$config = $this->getConfig();
		$self   = isset($_SERVER['QUERY_STRING']) && !empty($_SERVER['QUERY_STRING']) ? $_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING'] : $_SERVER['PHP_SELF'];
		$url    = $config['psx_url'] . '/' . $config['psx_dispatch'];
		$base   = parse_url($config['psx_url'], PHP_URL_PATH);

		$this->template->assign('config', $config);
		$this->template->assign('self', htmlspecialchars($self));
		$this->template->assign('url', $url);
		$this->template->assign('location', $this->config['amun_service_path'] . '/' . $this->page->getApplication() . '/template');
		$this->template->assign('base', $base);

		$this->template->assign('registry', $this->registry);
		$this->template->assign('user', $this->user);
		$this->template->assign('page', $this->page);
		$this->template->assign('navigation', $this->navigation);
		$this->template->assign('path', $this->path);
		$this->template->assign('gadget', $this->gadgetContainer);
		$this->template->assign('htmlJs', $this->htmlJs);
		$this->template->assign('htmlCss', $this->htmlCss);
		$this->template->assign('htmlContent', $this->htmlContent);

		// transform
		if(empty($content))
		{
			// if we havent set an template file 
			if(!$this->template->hasFile())
			{
				$file = substr(get_class($this), strlen($this->service->getNamespace()) + 13);
				$file = str_replace('\\', '/', $file);

				$this->template->set($file . '.tpl');
			}

			if(!($response = $this->template->transform()))
			{
				throw new Exception('Error while transforming template');
			}
		}
		else
		{
			$response = $content;
		}

		// set custom template if any
		$this->template->assign('content', $response);
		$this->template->setDir(PSX_PATH_TEMPLATE . '/' . $this->config['psx_template_dir']);

		$template = $this->page->getTemplate();
		if(!empty($template))
		{
			$this->template->set($template);
		}
		else
		{
			$this->template->set('page.tpl');
		}

		return parent::processResponse(null);
	}

	protected function loadHtmlFragments()
	{
		// meta tags
		$description = $this->page->getDescription();
		if(!empty($description))
		{
			$this->htmlContent->add(Html\Content::META, '<meta name="description" content="' . $description . '" />');
		}

		$keywords = $this->page->getKeywords();
		if(!empty($keywords))
		{
			$this->htmlContent->add(Html\Content::META, '<meta name="keywords" content="' . $keywords . '" />');
		}

		$publishDate = $this->page->getPublishDate();
		if(!empty($publishDate) && $publishDate != '0000-00-00 00:00:00')
		{
			$publishDate = new DateTime($publishDate);

			$this->htmlContent->add(Html\Content::META, '<meta name="date" content="' . $publishDate->format(DateTime::ATOM) . '" />');
		}

		// form window
		$window = <<<HTML
<div id="amun-form-window" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="amun-form-window-label" aria-hidden="true">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
		<h3 id="amun-form-window-label">Form</h3>
	</div>
	<div class="modal-body">
		<div id="amun-form-window-body">
			<div id="amun-form-window-response"></div>
			<div id="amun-form-window-form"></div>
			<div id="amun-form-window-preview" class="amun-preview"></div>
		</div>
	</div>
	<div class="modal-footer" id="amun-form-window-buttons"></div>
</div>
HTML;

		$this->htmlContent->add(Html\Content::FOOTER, $window);
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
		$options = new Option($this->location->getClass()->getName(), $this->registry, $this->user, $this->page);

		foreach($data as $row)
		{
			list($rightName, $title, $url) = $row;

			$options->add($rightName, $title, $url);
		}

		$options->load(array($this->page));

		$this->template->assign('options', $options);
	}

	protected function getHandler($name = null)
	{
		return $this->hm->getHandler($name === null ? $this->service->getNamespace() : $name);
	}

	protected function getRequestCondition()
	{
		$con          = new Condition();
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
			$datetime = new DateTime($updatedSince);

			$con->add('date', '>', $datetime->format(DateTime::SQL));
		}

		return $con;
	}
}

