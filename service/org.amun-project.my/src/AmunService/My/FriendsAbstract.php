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

namespace AmunService\My;

use Amun\Option;
use AmunService\User\Friend;
use PSX\Sql\Condition;

/**
 * FriendsAbstract
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/gpl.html GPLv3
 * @link    http://amun.phpsx.org
 */
abstract class FriendsAbstract extends MyAbstract
{
	public function onLoad()
	{
		parent::onLoad();

		// friend request count
		$con = new Condition();
		$con->add('friendId', '=', $this->user->getId());
		$con->add('status', '=', Friend\Record::REQUEST);

		$requestCount = $this->getSql()->count($this->registry['table.user_friend'], $con);

		$this->template->assign('requestCount', $requestCount);

		// pending count
		$con = new Condition();
		$con->add('userId', '=', $this->user->getId());
		$con->add('status', '=', Friend\Record::REQUEST);

		$pendingCount = $this->getSql()->count($this->registry['table.user_friend'], $con);

		$this->template->assign('pendingCount', $pendingCount);

		// load groups
		$groupList = $this->getGroups();

		$this->template->assign('groupList', $groupList);

		// options
		$friends = new Option('friends', $this->registry, $this->user, $this->page);
		$friends->add('my_view', 'Friends', $this->page->getUrl() . '/friends');

		if($requestCount > 0)
		{
			$friends->add('my_view', 'Request (' . $requestCount . ')', $this->page->getUrl() . '/friends/request');
		}

		if($pendingCount > 0)
		{
			$friends->add('my_view', 'Pending (' . $pendingCount . ')', $this->page->getUrl() . '/friends/pending');
		}

		$friends->add('my_view', 'Groups', $this->page->getUrl() . '/friends/group');
		$friends->load(array($this->page));

		$this->template->assign('optionsFriends', $friends);
	}

	private function getGroups()
	{
		return $this->hm->getTable('AmunService\User\Friend\Group')
			->select(array('id', 'title', 'date'))
			->where('userId', '=', $this->user->getId())
			->getAll();
	}
}

