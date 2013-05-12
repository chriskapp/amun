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

namespace profile\application;

use Amun\Module\ApplicationAbstract;
use Amun\Exception;
use Amun\DataFactory;
use Amun\Option;
use Amun\Base;
use Amun\Html;
use AmunService\Pipe;
use AmunService\User\Account;
use PSX\Atom;
use PSX\Html\Paging;
use PSX\Url;
use PSX\Sql;
use PSX\Sql\Condition;
use PSX\Sql\Join;

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
	 * @path /{userName}
	 */
	public function doProfile()
	{
		if($this->user->hasRight('profile_view'))
		{
			$account = $this->getAccount();

			if(!$account instanceof Account\Record)
			{
				throw new Exception('Invalid user');
			}

			$this->template->assign('account', $account);


			// check whether remote profile
			if($account->status == Account\Record::REMOTE)
			{
				Base::setResponseCode(301);
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
			$options = new Option(__CLASS__, $this->registry, $this->user, $this->page);

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
			$this->htmlContent->add(Html\Content::META, Atom\Writer::link('Activity', $this->config['psx_url'] . '/' . $this->config['psx_dispatch'] . 'api/my/activity/' . $account->id . '?format=atom'));
			$this->htmlContent->add(Html\Content::META, '<link rel="alternate" type="application/stream+json" href="' . $this->config['psx_url'] . '/' . $this->config['psx_dispatch'] . 'api/my/activity/' . $account->id . '?format=json" />');
			$this->htmlContent->add(Html\Content::META, '<link rel="meta" type="application/rdf+xml" title="FOAF" href="' . $this->config['psx_url'] . '/' . $this->config['psx_dispatch'] . 'api/my/foaf/' . $account->name . '" />');
			$this->htmlContent->add(Html\Content::META, '<link rel="profile" type="html/text" href="' . $account->profileUrl . '" />');

			$this->template->set(__CLASS__ . '.tpl');
		}
		else
		{
			throw new Exception('Access not allowed');
		}
	}

	private function getAccount()
	{
		// get user id
		$userName = $this->getUriFragments('userName');
		$con      = null;

		if(!empty($userName))
		{
			if(strpos($userName, ':') !== false)
			{
				$con = new Condition(array('globalId', '=', $userName));
			}
			else if(intval($userName) > 0)
			{
				$con = new Condition(array('id', '=', $userName));
			}
			else
			{
				$con = new Condition(array('name', '=', $userName));
			}
		}

		if($con !== null)
		{
			$handler = $this->getHandler('User_Account');
			$record  = $handler->getOneBy($con, array('id', 
				'globalId', 
				'status', 
				'name', 
				'gender', 
				'timezone', 
				'updated', 
				'date', 
				'thumbnailUrl', 
				'profileUrl', 
				'groupTitle', 
				'countryTitle'), Sql::FETCH_OBJECT);

			return $record;
		}
		else
		{
			throw new Exception('Invalid user name');
		}
	}

	private function getActivities(Account\Record $account)
	{
		$con = $this->getRequestCondition();

		$url   = new Url($this->base->getSelf());
		$count = $url->getParam('count') > 0 ? $url->getParam('count') : 8;
		$count = $count > 16 ? 16 : $count;

		$result = $this->getHandler('User_Activity')->getPublicResultSet($account->id,
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

