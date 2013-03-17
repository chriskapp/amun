<?php
/*
 *  $Id: request.php 875 2012-09-30 13:51:45Z k42b3.x@googlemail.com $
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

namespace my\application\friends;

use AmunService\My\FriendsAbstract;
use PSX\Url;
use PSX\Sql;
use PSX\Html\Paging;

/**
 * request
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   module
 * @package    application
 * @subpackage my
 * @version    $Revision: 875 $
 */
class request extends FriendsAbstract
{
	public function onLoad()
	{
		parent::onLoad();

		// add path
		$this->path->add('Friends', $this->page->url . '/friends');
		$this->path->add('Request', $this->page->url . '/friends/request');

		// get requests
		$requests = $this->getRequests();

		$this->template->assign('requests', $requests);

		// form url
		$url = $this->config['psx_url'] . '/index.php/api/user/friend';

		$this->template->assign('requestUrl', $url);

		// template
		$this->htmlCss->add('my');
		$this->htmlJs->add('amun');
		$this->htmlJs->add('my');
	}

	public function getRequests()
	{
		$con = $this->getRequestCondition();

		$url   = new Url($this->base->getSelf());
		$count = $url->getParam('count') > 0 ? $url->getParam('count') : 8;
		$count = $count > 16 ? 16 : $count;

		$result = $this->getHandler('User_Friend')->getRequestResultSet($this->user->id,
			array(),
			$url->getParam('startIndex'), 
			$count, 
			$url->getParam('sortBy'), 
			$url->getParam('sortOrder'), 
			$con, 
			SQL::FETCH_OBJECT);


		$paging = new Paging($url, $result);

		$this->template->assign('pagingRequests', $paging, 0);


		return $result;
	}
}

