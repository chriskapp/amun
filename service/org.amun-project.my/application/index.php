<?php
/*
 *  $Id: index.php 875 2012-09-30 13:51:45Z k42b3.x@googlemail.com $
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
 * index
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   module
 * @package    application
 * @subpackage my
 * @version    $Revision: 875 $
 */
class index extends AmunService_My_MyAbstract
{
	public function onLoad()
	{
		parent::onLoad();

		// get user details
		$account = $this->getAccount();

		$this->template->assign('account', $account);

		// check whether remote profile
		if($account->status == AmunService_User_Account_Record::REMOTE)
		{
			header('HTTP/1.1 301 Moved Permanently');
			header('Location: ' . $account->profileUrl);
			exit;
		}

		// get acctivites
		$activities = $this->getActivities();

		$this->template->assign('activities', $activities);

		// load groups
		$groups = $this->getGroups();

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
		$this->htmlContent->add(Amun_Html_Content::META, PSX_Data_Writer_Atom::link($this->page->title, $this->config['psx_url'] . '/' . $this->config['psx_dispatch'] . 'api/my/activity/' . $this->user->name . '?format=atom'));

		$this->template->set(__CLASS__ . '.tpl');
	}

	private function getAccount()
	{
		return Amun_Sql_Table_Registry::get('User_Account')
			->select(array('id', 'status', 'name', 'gender', 'timezone', 'updated', 'date', 'profileUrl', 'thumbnailUrl'))
			->join(PSX_Sql_Join::INNER, Amun_Sql_Table_Registry::get('User_Group')
				->select(array('title'), 'group')
			)
			->join(PSX_Sql_Join::INNER, Amun_Sql_Table_Registry::get('Country')
				->select(array('title'), 'country')
			)
			->where('id', '=', $this->user->id)
			->getRow(PSX_Sql::FETCH_OBJECT);
	}

	private function getActivities()
	{
		$select = Amun_Sql_Table_Registry::get('User_Activity')
			->select(array('id', 'parentId', 'status', 'verb', 'summary', 'date'))
			->join(PSX_Sql_Join::INNER, Amun_Sql_Table_Registry::get('User_Activity_Receiver')
				->select(array('id', 'status', 'activityId', 'userId', 'date'), 'receiver'),
				'1:n'
			)
			->join(PSX_Sql_Join::INNER, Amun_Sql_Table_Registry::get('User_Account')
				->select(array('name', 'profileUrl', 'thumbnailUrl'), 'author')
			)
			->where('receiverUserId', '=', $this->user->id)
			->where('parentId', '=', 0)
			->orderBy('date', PSX_Sql::SORT_DESC);


		$url    = new PSX_Url($this->base->getSelf());
		$count  = $url->getParam('count') > 0 ? $url->getParam('count') : 8;

		$result = $select->getResultSet($url->getParam('startIndex'), $count, $url->getParam('sortBy'), $url->getParam('sortOrder'), $url->getParam('filterBy'), $url->getParam('filterOp'), $url->getParam('filterValue'), $url->getParam('updatedSince'), PSX_Sql::FETCH_OBJECT);


		$paging = new PSX_Html_Paging($url, $result, 0);

		$this->template->assign('pagingActivities', $paging);


		return $result;
	}

	private function getGroups()
	{
		return Amun_Sql_Table_Registry::get('User_Friend_Group')
			->select(array('id', 'title', 'date'))
			->where('userId', '=', $this->user->id)
			->getAll();
	}
}
