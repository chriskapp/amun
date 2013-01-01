<?php
/*
 *  $Id: application.php 875 2012-09-30 13:51:45Z k42b3.x@googlemail.com $
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
 * applications
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   module
 * @package    application
 * @subpackage my
 * @version    $Revision: 875 $
 */
class settings extends AmunService_My_SettingsAbstract
{
	private $application;
	private $userRights;

	public function onLoad()
	{
		parent::onLoad();

		// add path
		$this->path->add('Settings', $this->page->url . '/settings');
		$this->path->add('Application', $this->page->url . '/settings/application');

		// load application
		$this->application = $this->getApplication();

		if($this->application instanceof AmunService_Oauth_Access_Record)
		{
			// add path
			$this->path->add($this->application->apiTitle, $this->page->url . '/settings/application/settings?appId=' . $this->application->id);

			// get app rights
			$appRights = $this->application->getRights();

			$this->template->assign('appRights', $appRights);

			// load user rights
			$this->userRights = Amun_Sql_Table_Registry::get('User_Group_Right')
				->select(array('rightId'))
				->join(PSX_Sql_Join::INNER, Amun_Sql_Table_Registry::get('User_Right')
					->select(array('name', 'description'), 'right')
				)
				->where('groupId', '=', $this->user->groupId)
				->getAll();

			$this->template->assign('userRights', $this->userRights);
		}
		else
		{
			throw new Amun_Exception('Invalid application');
		}

		$this->template->assign('application', $this->application);

		// form url
		$url = $this->config['psx_url'] . '/' . $this->config['psx_dispatch'] . 'api/oauth/access';

		$this->template->assign('accessUrl', $url);

		// template
		$this->htmlCss->add('my');
		$this->htmlJs->add('amun');
		$this->htmlJs->add('my');

		$this->template->set('settings/application/' . __CLASS__ . '.tpl');
	}

	public function onPost()
	{
		// delete any existing rights
		$con = new PSX_Sql_Condition(array('accessId', '=', $this->application->id));

		$this->sql->delete($this->registry['table.oauth_access_right'], $con);

		// insert rigts
		$rights = array();

		foreach($this->userRights as $right)
		{
			$key = 'right-' . $right['rightId'];
			$set = isset($_POST[$key]) ? (boolean) $_POST[$key] : false;

			if($set)
			{
				$accessId = (integer) $this->application->id;
				$rightId  = (integer) $right['rightId'];

				$rights[] = '(' . $accessId . ',' . $rightId . ')';
			}
		}

		if(!empty($rights))
		{
			$sql = implode(',', $rights);
			$sql = <<<SQL
INSERT INTO 
	{$this->registry['table.oauth_access_right']} (accessId, rightId)
VALUES
	{$sql}
SQL;

			$this->sql->query($sql);
		}

		// redirect
		header('Location: ' . $this->page->url . '/settings/application/settings?appId=' . $this->application->id . '#expand-rights');
		exit;
	}

	public function getApplication()
	{
		$access = Amun_Sql_Table_Registry::get('Oauth_Access')
			->select(array('id', 'date'))
			->join(PSX_Sql_Join::INNER, Amun_Sql_Table_Registry::get('Oauth')
				->select(array('id', 'status', 'url', 'title', 'description'), 'api')
			)
			->join(PSX_Sql_Join::INNER, Amun_Sql_Table_Registry::get('User_Account')
				->select(array('id', 'name'), 'author')
			)
			->where('id', '=', $this->get->appId('integer'))
			->where('authorId', '=', $this->user->id)
			->where('allowed', '=', 1)
			->getRow(PSX_Sql::FETCH_OBJECT);

		return $access;
	}
}
