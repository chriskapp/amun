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

namespace my\application\login;

use Amun\Module\ApplicationAbstract;
use Amun\Security;
use Amun\Mail;
use Amun\Exception;
use AmunService\User\Account;
use PSX\DateTime;
use PSX\Filter;

/**
 * resetPw
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/gpl.html GPLv3
 * @link    http://amun.phpsx.org
 */
class resetPw extends ApplicationAbstract
{
	public function onLoad()
	{
		parent::onLoad();

		// add path
		$this->path->add('Login', $this->page->url . '/login');
		$this->path->add('Reset Password', $this->page->url . '/login/resetPw');

		// template
		$this->htmlCss->add('my');
	}

	public function onGet()
	{
		try
		{
			$token = $this->get->token('string', array(new Filter\Length(40, 40), new Filter\Xdigit()));

			if($token !== false)
			{
				$handler = $this->getHandler('User_Account');
				$account = $handler->getRecoverByToken($token);

				if($account instanceof Account\Record)
				{
					if(!empty($account->email))
					{
						if($_SERVER['REMOTE_ADDR'] == $account->ip)
						{
							$pw   = Security::generatePw();
							$date = new DateTime('NOW', $this->registry['core.default_timezone']);

							$account->setStatus(Account\Record::NORMAL);
							$account->setPw($pw);

							$handler->update($account);

							// send mail
							$values = array(

								'account.name' => $account->name,
								'account.pw'   => $pw,
								'host.name'    => $this->base->getHost(),
								'recover.link' => $this->page->url . '/login',
								'recover.date' => $date->format($this->registry['core.format_date']),

							);

							$mail = new Mail($this->registry);
							$mail->send('MY_RECOVER_SUCCESS', $account->email, $values);

							$this->template->assign('success', true);
						}
						else
						{
							throw new Exception('Recover process was requested from another IP');
						}
					}
					else
					{
						throw new Exception('No public email address is set for this account');
					}
				}
				else
				{
					throw new Exception('Invalid token');
				}
			}
			else
			{
				throw new Exception('Token not set');
			}
		}
		catch(\Exception $e)
		{
			$this->template->assign('error', $e->getMessage());
		}
	}
}
