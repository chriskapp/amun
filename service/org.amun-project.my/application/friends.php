<?php
/*
 *  $Id: friends.php 875 2012-09-30 13:51:45Z k42b3.x@googlemail.com $
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
 * friends
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   module
 * @package    application
 * @subpackage my
 * @version    $Revision: 875 $
 */
class friends extends AmunService_My_FriendsAbstract
{
	public function onLoad()
	{
		parent::onLoad();

		// add path
		$this->path->add('Friends', $this->page->url . '/friends');

		// get friends
		$friends = $this->getFriends();

		$this->template->assign('friends', $friends);

		// form url
		$url = $this->config['psx_url'] . '/' . $this->config['psx_dispatch'] . 'api/user/friend';

		$this->template->assign('friendUrl', $url);

		// template
		$this->htmlCss->add('my');
		$this->htmlJs->add('amun');
		$this->htmlJs->add('my');
		$this->htmlContent->add(Amun_Html_Content::META, PSX_Data_Writer_Atom::link($this->page->title, $this->config['psx_url'] . '/' . $this->config['psx_dispatch'] . 'api/user/friend?format=atom&filterBy=authorId&filterOp=equals&filterValue=' . $this->user->id));

		$this->template->set(__CLASS__ . '.tpl');
	}

	private function getFriends()
	{
		$con = $this->getRequestCondition();
		$con->add('authorId', '=', $this->user->id);
		$con->add('status', '=', AmunService_User_Friend_Record::NORMAL);

		// search
		$search = $this->post->search('string');

		if(strlen($search) >= 3 && strlen($search) <= 16)
		{
			$con->add('friendName', 'LIKE', '%' . $search . '%');
		}

		$url   = new PSX_Url($this->base->getSelf());
		$count = $url->getParam('count') > 0 ? $url->getParam('count') : 8;
		$count = $count > 16 ? 16 : $count;

		$result = $this->getHandler('User_Friend')->getResultSet(array(),
			$url->getParam('startIndex'), 
			$count, 
			$url->getParam('sortBy'), 
			$url->getParam('sortOrder'),
			$con,
			PSX_Sql::FETCH_OBJECT);


		$paging = new PSX_Html_Paging($url, $result);

		$this->template->assign('pagingFriends', $paging, 0);


		return $result;
	}
}

