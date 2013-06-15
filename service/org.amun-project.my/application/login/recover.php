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
use Amun\Captcha;
use Amun\Security;
use Amun\Exception;
use Amun\Mail;
use AmunService\User\Account;
use PSX\DateTime;
use PSX\Filter;
use PSX\Sql;

/**
 * recover
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/gpl.html GPLv3
 * @link    http://amun.phpsx.org
 */
class recover extends ApplicationAbstract
{
	public function onLoad()
	{
		parent::onLoad();

		// add path
		$this->path->add('Login', $this->page->getUrl() . '/login');
		$this->path->add('Recover', $this->page->getUrl() . '/login/recover');

		// captcha
		$captcha = $this->config['psx_url'] . '/' . $this->config['psx_dispatch'] . 'api/core/captcha';

		$this->template->assign('captcha', $captcha);

		// template
		$this->htmlCss->add('my');
	}

	public function onPost()
	{
		try
		{
			$email   = $this->post->email('string', array(new Filter\Length(3, 64), new Filter\Email()));
			$captcha = $this->post->captcha('string');

			// check captcha if anonymous
			$captchaProvider = Captcha::factory($this->config['amun_captcha']);

			if(!$captchaProvider->verify($captcha))
			{
				throw new Exception('Invalid captcha');
			}

			if(!$this->validate->hasError())
			{
				$handler = $this->getHandler('User_Account');
				$account = $handler->getOneByIdentity(sha1(Security::getSalt() . $email), array(), Sql::FETCH_OBJECT);

				if($account instanceof Account\Record)
				{
					if(!in_array($account->status, array(Account\Record::NORMAL, Account\Record::ADMINISTRATOR)))
					{
						throw new Exception('Account has an invalid status');
					}

					if(!empty($account->email))
					{
						$token = Security::generateToken();
						$link  = $this->page->getUrl() . '/login/resetPw?token=' . $token;
						$date  = new DateTime('NOW', $this->registry['core.default_timezone']);

						// update status
						$account->setStatus(Account\Record::RECOVER);
						$account->setToken($token);

						$handler->update($account);

						// send mail
						$values = array(

							'account.name' => $account->name,
							'host.name'    => $this->base->getHost(),
							'recover.ip'   => $_SERVER['REMOTE_ADDR'],
							'recover.link' => $this->page->getUrl() . '/login/resetPw?token=' . $token,
							'recover.date' => $date->format($this->registry['core.format_date']),

						);

						$mail = new Mail($this->registry);
						$mail->send('MY_RECOVER', $account->email, $values);


						$this->template->assign('success', true);
					}
					else
					{
						throw new Exception('No public email address is set for this account');
					}
				}
				else
				{
					throw new Exception('Account does not exist');
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

