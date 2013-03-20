<?php
/*
 *  $Id: view.php 875 2012-09-30 13:51:45Z k42b3.x@googlemail.com $
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

namespace forum\application;

use Amun\Base;
use Amun\Module\ApplicationAbstract;
use PSX\Url;
use PSX\Sql;
use PSX\Sql\Condition;
use PSX\Html\Paging;

/**
 * view
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   module
 * @package    application
 * @subpackage forum
 * @version    $Revision: 875 $
 */
class view extends ApplicationAbstract
{
	private $id;
	private $title;

	/**
	 * @httpMethod GET
	 * @path /{forumId}/{forumTitle}
	 */
	public function doView()
	{
		if($this->user->hasRight('forum_view'))
		{
			// get forum id
			$fragments   = $this->getUriFragments();
			$this->id    = isset($fragments['forumId']) ? intval($fragments['forumId']) : $this->get->id('integer');
			$this->title = isset($fragments['forumTitle']) ? $fragments['forumTitle'] : null;

			// load forum
			$recordForum = $this->getForum();

			$this->template->assign('recordForum', $recordForum);

			// load comments
			$resultComments = $this->getComments();

			$this->template->assign('resultComments', $resultComments);

			// add path
			$this->path->add($recordForum->title, $this->page->url . '/view?id=' . $this->id);

			// options
			$options = array();
			$options[] = array('forum_edit', 'Edit', $this->page->url . '/edit?id=' . $this->id);

			if($recordForum->isSticky())
			{
				$options[] = array('forum_sticky', 'Unstick', 'javascript:amun.services.forum.setSticky(' . $this->id . ',0,\'' . $this->service->getApiEndpoint() . '\',this)');
			}
			else
			{
				$options[] = array('forum_sticky', 'Sticky', 'javascript:amun.services.forum.setSticky(' . $this->id . ',1,\'' . $this->service->getApiEndpoint() . '\',this)');
			}

			if($recordForum->isClosed())
			{
				$options[] = array('forum_close', 'Open', 'javascript:amun.services.forum.setClosed(' . $this->id . ',0,\'' . $this->service->getApiEndpoint() . '\',this)');
			}
			else
			{
				$options[] = array('forum_close', 'Close', 'javascript:amun.services.forum.setClosed(' . $this->id . ',1,\'' . $this->service->getApiEndpoint() . '\',this)');
			}

			$this->setOptions($options);

			// form url
			$url = $this->config['psx_url'] . '/' . $this->config['psx_dispatch'] . 'api/comment/form?format=json&method=create&pageId=' . $this->page->id . '&refId=' . $this->id;

			$this->template->assign('formUrl', $url);

			// template
			$this->htmlCss->add('forum');
			$this->htmlCss->add('comment');
			$this->htmlJs->add('amun');
			$this->htmlJs->add('forum');
			$this->htmlJs->add('prettify');
			$this->htmlJs->add('ace');
		}
		else
		{
			throw new Exception('Access not allowed');
		}
	}

	private function getForum()
	{
		$result = $this->getHandler()->getById($this->id, 
			array(), 
			Sql::FETCH_OBJECT);

		if(empty($result))
		{
			throw new Exception('Invalid forum id');
		}

		$this->id = $result->id;

		// redirect to correct url
		if(empty($this->title) || $this->title != $result->urlTitle)
		{
			Base::setResponseCode(301);
			header('Location: ' . $this->page->url . '/view/' . $result->id . '/' . $result->urlTitle);
		}

		return $result;
	}

	private function getComments()
	{
		$con = new Condition();
		$con->add('pageId', '=', $this->page->id);
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

