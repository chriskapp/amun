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

namespace forum\application;

use Amun\Module\ApplicationAbstract;
use Amun\Exception;
use Amun\Html;
use PSX\Atom;
use PSX\Sql;
use PSX\Url;
use PSX\Html\Paging;

/**
 * index
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/gpl.html GPLv3
 * @link    http://amun.phpsx.org
 */
class index extends ApplicationAbstract
{
	/**
	 * @httpMethod GET
	 * @path /
	 */
	public function doIndex()
	{
		if($this->user->hasRight('forum_view'))
		{
			// load forum
			$resultForum = $this->getForum();

			$this->template->assign('resultForum', $resultForum);

			// options
			$url = $this->service->getApiEndpoint() . '/form?format=json&method=create&pageId=' . $this->page->getId();

			$this->setOptions(array(
				array('forum_add', 'Add', 'javascript:amun.services.forum.showForm(\'' . $url . '\')')
			));

			// template
			$this->htmlCss->add('forum');
			$this->htmlJs->add('forum');
			$this->htmlJs->add('ace');
			$this->htmlJs->add('bootstrap');
			$this->htmlJs->add('prettify');
			$this->htmlContent->add(Html\Content::META, Atom\Writer::link($this->page->getTitle(), $this->service->getApiEndpoint() . '?format=atom&filterBy=pageId&filterOp=equals&filterValue=' . $this->page->getId()));
		}
		else
		{
			throw new Exception('Access not allowed');
		}
	}

	private function getForum()
	{
		$con = $this->getRequestCondition();
		$con->add('pageId', '=', $this->page->getId());

		$url   = new Url($this->base->getSelf());
		$count = $url->getParam('count') > 0 ? $url->getParam('count') : 8;
		$count = $count > 16 ? 16 : $count;

		$result = $this->getHandler()->getResultSet(array(),
			$url->getParam('startIndex'), 
			$count, 
			$url->getParam('sortBy'), 
			$url->getParam('sortOrder'), 
			$con, 
			SQL::FETCH_OBJECT);


		$paging = new Paging($url, $result);

		$this->template->assign('pagingForum', $paging, 0);


		return $result;
	}
}

