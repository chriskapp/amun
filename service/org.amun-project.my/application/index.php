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

namespace my\application;

use Amun\Html;
use AmunService\My\MyAbstract;
use AmunService\User\Account;
use AmunService\User\Activity\Receiver;
use PSX\Atom;
use PSX\DateTime;
use PSX\Sql;
use PSX\Url;
use PSX\Sql\Condition;
use PSX\Html\Paging;

/**
 * index
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/gpl.html GPLv3
 * @link    http://amun.phpsx.org
 */
class index extends MyAbstract
{
	public function onLoad()
	{
		parent::onLoad();

		// get user details
		$account = $this->getHandler('User_Account')->getOneById($this->user->id, 
			array('id', 'status', 'name', 'gender', 'thumbnailUrl', 'timezone', 'updated', 'date', 'countryTitle'),
			Sql::FETCH_OBJECT);

		$this->template->assign('account', $account);

		// check whether remote profile
		if($account->status == Account\Record::REMOTE)
		{
			header('HTTP/1.1 301 Moved Permanently');
			header('Location: ' . $account->profileUrl);
			exit;
		}

		// get acctivites
		$activities = $this->getActivities();

		$this->template->assign('activities', $activities);

		// load groups
		$groups = $this->getHandler('User_Friend_Group')->getAll(array(), 
			0, 
			16, 
			'id', 
			Sql::SORT_DESC, 
			new Condition(array('userId', '=', $this->user->id)));

		$this->template->assign('groups', $groups);

		// form url
		$activityUrl = $this->config['psx_url'] . '/' . $this->config['psx_dispatch'] . 'api/user/activity';
		$receiverUrl = $this->config['psx_url'] . '/' . $this->config['psx_dispatch'] . 'api/user/activity/receiver';

		$this->template->assign('activityUrl', $activityUrl);
		$this->template->assign('receiverUrl', $receiverUrl);

		// template
		$this->htmlCss->add('my');
		$this->htmlJs->add('amun');
		$this->htmlJs->add('my');
		$this->htmlContent->add(Html\Content::META, Atom\Writer::link($this->page->title, $this->config['psx_url'] . '/' . $this->config['psx_dispatch'] . 'api/my/activity/' . $this->user->name . '?format=atom'));
	}

	private function getActivities()
	{
		$con = $this->getRequestCondition();

		$url   = new Url($this->base->getSelf());
		$count = $url->getParam('count') > 0 ? $url->getParam('count') : 8;
		$count = $count > 16 ? 16 : $count;

		$result = $this->getHandler('User_Activity')->getPrivateResultSet($this->user->id,
			array(),
			$url->getParam('startIndex'), 
			$count, 
			$url->getParam('sortBy'), 
			$url->getParam('sortOrder'),
			$con,
			Sql::FETCH_OBJECT);


		$paging = new Paging($url, $result, 0);

		$this->template->assign('pagingActivities', $paging);


		return $result;
	}
}
