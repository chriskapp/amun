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
 * @subpackage profile
 * @version    $Revision: 875 $
 */
class index extends Amun_Module_ApplicationAbstract
{
	public function onLoad()
	{
		if($this->user->hasRight('service_profile_view'))
		{
			$account = $this->getAccount();

			if(!$account instanceof Amun_User_Account)
			{
				throw new Amun_Exception('Invalid user');
			}

			$this->template->assign('account', $account);


			// check whether remote profile
			if($account->status == Amun_User_Account::REMOTE)
			{
				PSX_Base::setResponseCode(301);
				header('Location: ' . $account->profileUrl);
				exit;
			}


			// add path
			$this->path->add($account->name, $this->page->url . '/' . $account->name);


			// get activities
			$activities = $this->getActivities($account);

			$this->template->assign('activities', $activities);


			// options
			$url     = $this->config['psx_url'] . '/' . $this->config['psx_dispatch'] . 'api/user/friend';
			$options = new Amun_Option(__CLASS__, $this->registry, $this->user, $this->page);

			if(!$this->user->isAnonymous() && !$this->user->hasFriend($account))
			{
				$options->add('service_profile_view', 'Add as friend', 'javascript:amun.services.profile.friendshipRequest(' . $this->user->id . ', ' . $account->id . ', \'' . $url . '\', this)');
			}

			$options->load(array($this->page, $account));

			$this->template->assign('options', $options);


			// template
			$this->htmlCss->add('profile');
			$this->htmlJs->add('amun');
			$this->htmlJs->add('profile');
			$this->htmlContent->add(Amun_Html_Content::META, PSX_Data_Writer_Atom::link('Activity', $this->config['psx_url'] . '/' . $this->config['psx_dispatch'] . 'api/service/my/activity/' . $account->name . '?format=atom'));
			$this->htmlContent->add(Amun_Html_Content::META, '<link rel="alternate" type="application/stream+json" href="' . $this->config['psx_url'] . '/' . $this->config['psx_dispatch'] . 'api/service/my/activity/' . $account->name . '?format=json" />');
			$this->htmlContent->add(Amun_Html_Content::META, '<link rel="meta" type="application/rdf+xml" title="FOAF" href="' . $this->config['psx_url'] . '/' . $this->config['psx_dispatch'] . 'api/service/my/foaf/' . $account->name . '" />');
			$this->htmlContent->add(Amun_Html_Content::META, '<link rel="profile" type="html/text" href="' . $account->profileUrl . '" />');

			$this->template->set(__CLASS__ . '.tpl');
		}
		else
		{
			throw new Amun_Exception('Access not allowed');
		}
	}

	private function getAccount()
	{
		// get user id
		$fragments = $this->getUriFragments();

		if(count($fragments) > 0)
		{
			$col = 'name';
			$val = end($fragments);

			if(strpos($val, ':') !== false)
			{
				$col = 'globalId';
			}
			else if(intval($val) > 0)
			{
				$col = 'id';
				$val = intval($val);
			}
		}
		else
		{
			throw new Amun_Exception('No user given');
		}


		return Amun_Sql_Table_Registry::get('Core_User_Account')
			->select(array('id', 'status', 'name', 'gender', 'timezone', 'updated', 'date', 'thumbnailUrl', 'profileUrl'))
			->join(PSX_Sql_Join::INNER, Amun_Sql_Table_Registry::get('Core_User_Group')
				->select(array('title'), 'group')
			)
			->join(PSX_Sql_Join::INNER, Amun_Sql_Table_Registry::get('Core_System_Country')
				->select(array('title'), 'country')
			)
			->where($col, '=', $val)
			->getRow(PSX_Sql::FETCH_OBJECT);
	}

	private function getActivities(AmunService_Core_User_Account_Record $account)
	{
		$select = Amun_Sql_Table_Registry::get('Core_User_Activity')
			->select(array('id', 'userId', 'status', 'scope', 'verb', 'summary', 'date'))
			->join(PSX_Sql_Join::INNER, Amun_Sql_Table_Registry::get('Core_User_Activity_Receiver')
				->select(array('id', 'status', 'activityId', 'userId', 'date'), 'receiver'),
				'1:n'
			)
			->join(PSX_Sql_Join::INNER, Amun_Sql_Table_Registry::get('Core_User_Account')
				->select(array('name', 'profileUrl', 'thumbnailUrl'), 'author')
			)
			->where('parentId', '=', 0)
			->where('userId', '=', $account->id)
			->where('scope', '=', 0)
			->where('receiverUserId', '=', $account->id)
			->where('receiverStatus', '=', AmunService_Core_User_Activity_Receiver_Record::VISIBLE)
			->orderBy('date', PSX_Sql::SORT_DESC);


		$url    = new PSX_Url($this->base->getSelf());
		$count  = $url->getParam('count') > 0 ? $url->getParam('count') : 8;

		$result = $select->getResultSet($url->getParam('startIndex'), $count, $url->getParam('sortBy'), $url->getParam('sortOrder'), $url->getParam('filterBy'), $url->getParam('filterOp'), $url->getParam('filterValue'), $url->getParam('updatedSince'), PSX_SQL::FETCH_OBJECT);


		$paging = new PSX_Html_Paging($url, $result, 0);

		$this->template->assign('pagingActivities', $paging);


		return $result;
	}
}

