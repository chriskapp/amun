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
	/**
	 * @httpMethod GET
	 * @path /{userName}
	 */
	public function doProfile()
	{
		if($this->user->hasRight('profile_view'))
		{
			$account = $this->getAccount();

			if(!$account instanceof AmunService_User_Account_Record)
			{
				throw new Amun_Exception('Invalid user');
			}

			$this->template->assign('account', $account);


			// check whether remote profile
			if($account->status == AmunService_User_Account_Record::REMOTE)
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
				$options->add('profile_view', 'Add as friend', 'javascript:amun.services.profile.friendshipRequest(' . $this->user->id . ', ' . $account->id . ', \'' . $url . '\', this)');
			}

			$options->load(array($this->page, $account));

			$this->template->assign('options', $options);


			// template
			$this->htmlCss->add('profile');
			$this->htmlJs->add('amun');
			$this->htmlJs->add('profile');
			$this->htmlContent->add(Amun_Html_Content::META, PSX_Data_Writer_Atom::link('Activity', $this->config['psx_url'] . '/' . $this->config['psx_dispatch'] . 'api/my/activity/' . $account->id . '?format=atom'));
			$this->htmlContent->add(Amun_Html_Content::META, '<link rel="alternate" type="application/stream+json" href="' . $this->config['psx_url'] . '/' . $this->config['psx_dispatch'] . 'api/my/activity/' . $account->id . '?format=json" />');
			$this->htmlContent->add(Amun_Html_Content::META, '<link rel="meta" type="application/rdf+xml" title="FOAF" href="' . $this->config['psx_url'] . '/' . $this->config['psx_dispatch'] . 'api/my/foaf/' . $account->name . '" />');
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
		$userName = $this->getUriFragments('userName');

		if(!empty($userName))
		{
			if(strpos($userName, ':') !== false)
			{
				$column = 'globalId';
			}
			else if(intval($userName) > 0)
			{
				$column = 'id';
			}
			else
			{
				$column = 'name';
			}
		}
		else
		{
			throw new Amun_Exception('No user given');
		}


		return Amun_Sql_Table_Registry::get('User_Account')
			->select(array('id', 'status', 'name', 'gender', 'timezone', 'updated', 'date', 'thumbnailUrl', 'profileUrl'))
			->join(PSX_Sql_Join::INNER, Amun_Sql_Table_Registry::get('User_Group')
				->select(array('title'), 'group')
			)
			->join(PSX_Sql_Join::INNER, Amun_Sql_Table_Registry::get('Country')
				->select(array('title'), 'country')
			)
			->where($column, '=', $userName)
			->getRow(PSX_Sql::FETCH_OBJECT);
	}

	private function getActivities(AmunService_User_Account_Record $account)
	{
		$con = $this->getRequestCondition();

		$url   = new PSX_Url($this->base->getSelf());
		$count = $url->getParam('count') > 0 ? $url->getParam('count') : 8;
		$count = $count > 16 ? 16 : $count;

		$result = $this->getHandler('User_Activity')->getPublicResultSet($account->id,
			array(),
			$url->getParam('startIndex'), 
			$count, 
			$url->getParam('sortBy'), 
			$url->getParam('sortOrder'), 
			$con, 
			PSX_SQL::FETCH_OBJECT);


		$paging = new PSX_Html_Paging($url, $result, 0);

		$this->template->assign('pagingActivities', $paging);


		return $result;
	}
}

