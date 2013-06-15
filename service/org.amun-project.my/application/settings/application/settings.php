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

namespace my\application\settings\application;

use AmunService\My\SettingsAbstract;
use AmunService\Oauth\Access;
use Amun\Exception;

/**
 * settings
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/gpl.html GPLv3
 * @link    http://amun.phpsx.org
 */
class settings extends SettingsAbstract
{
	private $application;
	private $userRights;

	public function onLoad()
	{
		parent::onLoad();

		// add path
		$this->path->add('Settings', $this->page->getUrl() . '/settings');
		$this->path->add('Application', $this->page->getUrl() . '/settings/application');

		// load application
		$this->application = $this->getHandler('Oauth_Access')->getAllowedApplication(
			$this->get->appId('integer'),
			$this->user->id
		);

		if($this->application instanceof Access\Record)
		{
			// add path
			$this->path->add($this->application->apiTitle, $this->page->getUrl() . '/settings/application/settings?appId=' . $this->application->id);

			// get app rights
			$appRights = $this->application->getRights();

			$this->template->assign('appRights', $appRights);

			// load user rights
			$this->userRights = $this->getHandler('User_Group_Right')->getByGroupId($this->user->groupId);

			$this->template->assign('userRights', $this->userRights);
		}
		else
		{
			throw new Exception('Invalid application');
		}

		$this->template->assign('application', $this->application);

		// form url
		$url = $this->config['psx_url'] . '/' . $this->config['psx_dispatch'] . 'api/oauth/access';

		$this->template->assign('accessUrl', $url);

		// template
		$this->htmlCss->add('my');
		$this->htmlJs->add('amun');
		$this->htmlJs->add('my');
	}

	public function onPost()
	{
		// insert rigts
		$rights = array();

		foreach($this->userRights as $right)
		{
			$key = 'right-' . $right['rightId'];
			$set = isset($_POST[$key]) ? (boolean) $_POST[$key] : false;

			if($set)
			{
				$rights[] = (integer) $right['rightId'];
			}
		}

		if(!empty($rights))
		{
			$this->getHandler('Oauth_Access')->setRights($this->application->id, $rights);
		}

		// redirect
		header('Location: ' . $this->page->getUrl() . '/settings/application/settings?appId=' . $this->application->id . '#expand-rights');
		exit;
	}
}
