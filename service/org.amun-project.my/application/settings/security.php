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

namespace my\application\settings;

use Amun\Filter;
use Amun\Exception;
use AmunService\User\Account\Filter as AccountFilter;
use AmunService\User\Account;
use AmunService\My\SettingsAbstract;
use PSX\Sql;
use PSX\Url;
use PSX\Html\Paging;

/**
 * security
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/gpl.html GPLv3
 * @link    http://amun.phpsx.org
 */
class security extends SettingsAbstract
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
	}

	public function onPost()
	{
		try
		{
			$currentPw = $this->post->current_password('string', array(new AccountFilter\Pw(), new Filter\Salt()), 'Current password');
			$newPw     = $this->post->new_password('string', array(new AccountFilter\Pw()), 'New password');
			$verifyPw  = $this->post->verify_password('string', array(new AccountFilter\Pw()), 'Verify password');

			if(!$this->validate->hasError())
			{
				if(strcmp($newPw, $verifyPw) !== 0)
				{
					throw new Exception('Passwords doesnt match');
				}

				$handler = $this->getHandler('User_Account');
				$user    = $handler->getOneById($this->user->id, 
					array('id', 'pw'), 
					Sql::FETCH_OBJECT
				);

				if(strcmp($currentPw, $user->pw) === 0)
				{
					$user->setPw($newPw);
					$handler->update($user);

					$this->template->assign('success', true);
				}
				else
				{
					throw new Exception('Invalid password');
				}
			}
			else
			{
				throw new Exception($this->validate->getLastError());
			}
		}
		catch(\Exception $e)
		{
			$this->template->assign('error', $e->getMessage());
		}
	}
}

