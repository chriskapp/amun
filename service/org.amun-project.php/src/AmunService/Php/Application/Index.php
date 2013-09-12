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

namespace AmunService\Php\Application;

use Amun\Module\ApplicationAbstract;
use Amun\Option;
use Amun\Exception;
use AmunService\Php\Record;
use PSX\Sql;

/**
 * Index
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/gpl.html GPLv3
 * @link    http://amun.phpsx.org
 */
class Index extends ApplicationAbstract
{
	/**
	 * @httpMethod GET
	 * @path /
	 */
	public function doGet()
	{
		$this->handle();
	}

	/**
	 * @httpMethod POST
	 * @path /
	 */
	public function doPost()
	{
		$this->handle();
	}

	protected function handle()
	{
		if($this->user->hasRight('php_view'))
		{
			// load php
			$fields    = array('id', 'globalId', 'pageId', 'content', 'date', 'authorName', 'authorProfileUrl', 'pagePath');
			$recordPhp = $this->getHandler()->getOneByPageId($this->page->getId(), $fields, Sql::FETCH_OBJECT);

			$this->template->assign('recordPhp', $recordPhp);


			// options
			if($recordPhp instanceof Record)
			{
				$url = $this->service->getApiEndpoint() . '/form?format=json&method=update&id=' . $recordPhp->id;
			}
			else
			{
				$url = $this->service->getApiEndpoint() . '/form?format=json&method=create&pageId=' . $this->page->getId();
			}

			$options = new Option(__CLASS__, $this->registry, $this->user, $this->page);
			$options->add('php_edit', 'Edit', 'javascript:amun.services.php.showForm(\'' . $url . '\')');
			$options->load(array($this->page));

			$this->template->assign('options', $options);


			// parse content
			$phpResponse = null;
			$phpError    = null;

			if($recordPhp instanceof Record)
			{
				ob_start();

				try
				{
					$return = eval($recordPhp->content);

					$phpResponse = ob_get_contents();
				}
				catch(\Exception $e)
				{
					// build message
					$phpError = '<p>' . $e->getMessage() . '</p>';

					if($this->config['psx_debug'] === true)
					{
						$phpError.= '<pre>' . $e->getTraceAsString() . '</pre>';
					}
				}

				ob_end_clean();
			}

			$this->template->assign('phpResponse', $phpResponse);
			$this->template->assign('phpError', $phpError);


			// template
			$this->htmlCss->add('php');
			$this->htmlJs->add('php');
			$this->htmlJs->add('ace-php');
			$this->htmlJs->add('bootstrap');
			$this->htmlJs->add('prettify');
		}
		else
		{
			throw new Exception('Access not allowed');
		}
	}
}

