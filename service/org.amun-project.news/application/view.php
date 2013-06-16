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

namespace news\application;

use Amun\Base;
use Amun\Exception;
use Amun\Module\ApplicationAbstract;
use Amun\Html;
use PSX\DateTime;
use PSX\Sql;
use PSX\Sql\Condition;
use PSX\Url;
use PSX\Data\Writer;
use PSX\Html\Paging;
use DateInterval;

/**
 * view
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/gpl.html GPLv3
 * @link    http://amun.phpsx.org
 */
class view extends ApplicationAbstract
{
	private $id;
	private $title;

	/**
	 * @httpMethod GET
	 * @path /{newsId}/{newsTitle}
	 */
	public function doView()
	{
		if($this->user->hasRight('news_view'))
		{
			// get news id
			$fragments   = $this->getUriFragments();
			$this->id    = isset($fragments['newsId']) ? intval($fragments['newsId']) : $this->get->id('integer');
			$this->title = isset($fragments['newsTitle']) ? $fragments['newsTitle'] : null;

			// load news
			$recordNews = $this->getNews();

			$this->template->assign('recordNews', $recordNews);

			// load comments
			$resultComments = $this->getComments();

			$this->template->assign('resultComments', $resultComments);

			// add path
			$this->path->add($recordNews->title, $this->page->getUrl() . '/view/' . $recordNews->id . '/' . $recordNews->urlTitle);

			// options
			$url = $this->service->getApiEndpoint() . '/form?format=json&method=update&id=' . $this->id;

			$this->setOptions(array(
				array('news_edit', 'Edit', 'javascript:amun.services.news.showForm(\'' . $url . '\')')
			));

			// form url
			$url = $this->config['psx_url'] . '/' . $this->config['psx_dispatch'] . 'api/comment/form?format=json&method=create&pageId=' . $this->page->getId() . '&refId=' . $this->id;

			$this->template->assign('formUrl', $url);

			// template
			$this->htmlCss->add('news');
			$this->htmlCss->add('comment');
			$this->htmlJs->add('news');
			$this->htmlJs->add('ace');
			$this->htmlJs->add('bootstrap');
			$this->htmlJs->add('prettify');
		}
		else
		{
			throw new Exception('Access not allowed');
		}
	}

	private function getNews()
	{
		$result = $this->getHandler()->getOneById($this->id, 
			array(), 
			Sql::FETCH_OBJECT);

		if(empty($result))
		{
			throw new Exception('Invalid news id');
		}

		$this->id = $result->id;

		// redirect to correct url
		if(empty($this->title) || $this->title != $result->urlTitle)
		{
			Base::setResponseCode(301);
			header('Location: ' . $this->page->getUrl() . '/view/' . $result->id . '/' . $result->urlTitle);
		}

		return $result;
	}

	private function getComments()
	{
		$con = new Condition();
		$con->add('pageId', '=', $this->page->getId());
		$con->add('refId', '=', $this->id);

		$url   = new Url($this->base->getSelf());
		$count = $url->getParam('count') > 0 ? $url->getParam('count') : 8;
		$count = $count > 16 ? 16 : $count;

		$result = $this->getHandler('Comment')->getResultSet(array(),
			$url->getParam('startIndex'),
			$count,
			$url->getParam('sortBy'),
			$url->getParam('sortOrder'),
			$con,
			Sql::FETCH_OBJECT);


		$paging = new Paging($url, $result);

		$this->template->assign('pagingComments', $paging, 0);


		return $result;
	}
}

