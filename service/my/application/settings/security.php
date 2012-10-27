<?php
/*
 *  $Id: security.php 875 2012-09-30 13:51:45Z k42b3.x@googlemail.com $
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
 * security
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   module
 * @package    application
 * @subpackage my
 * @version    $Revision: 875 $
 */
class security extends Amun_Service_My_SettingsAbstract
{
	public function onLoad()
	{
		parent::onLoad();


		// add path
		$this->path->add('Settings', $this->page->url . '/settings');
		$this->path->add('Security', $this->page->url . '/settings/security');


		// template
		$this->htmlCss->add('my');
		$this->htmlJs->add('amun');
		$this->htmlJs->add('my');

		$this->template->set('settings/' . __CLASS__ . '.tpl');
	}

	public function onPost()
	{
		try
		{
			$currentPw = $this->post->current_password('string', array(new Amun_User_Account_Filter_Pw(), new Amun_Filter_Salt()), 'Current password');
			$newPw     = $this->post->new_password('string', array(new Amun_User_Account_Filter_Pw()), 'New password');
			$verifyPw  = $this->post->verify_password('string', array(new Amun_User_Account_Filter_Pw()), 'Verify password');

			if(!$this->validate->hasError())
			{
				if(strcmp($newPw, $verifyPw) !== 0)
				{
					throw new Amun_Exception('Passwords doesnt match');
				}

				$user = Amun_Sql_Table_Registry::get('User_Account')->getRecord($this->user->id);

				if(strcmp($currentPw, $user->pw) === 0)
				{
					$user->setPw($newPw);

					$handler = new Amun_User_Account_Handler($this->user);
					$handler->update($user);

					$this->template->assign('success', true);
				}
				else
				{
					throw new Amun_Exception('Invalid password');
				}
			}
			else
			{
				throw new Amun_Exception($this->validate->getLastError());
			}
		}
		catch(Exception $e)
		{
			$this->template->assign('error', $e->getMessage());
		}
	}
}

