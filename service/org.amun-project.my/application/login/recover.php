<?php
/*
 *  $Id: recover.php 875 2012-09-30 13:51:45Z k42b3.x@googlemail.com $
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
 * recover
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   module
 * @package    application
 * @subpackage my
 * @version    $Revision: 875 $
 */
class recover extends Amun_Module_ApplicationAbstract
{
	public function onLoad()
	{
		// add path
		$this->path->add('Login', $this->page->url . '/login');
		$this->path->add('Recover', $this->page->url . '/login/recover');


		// captcha
		$captcha = $this->config['psx_url'] . '/' . $this->config['psx_dispatch'] . 'api/system/captcha';

		$this->template->assign('captcha', $captcha);


		// template
		$this->htmlCss->add('my');

		$this->template->set('login/' . __CLASS__ . '.tpl');
	}

	public function onPost()
	{
		try
		{
			$email   = $this->post->email('string', array(new PSX_Filter_Length(3, 64), new PSX_Filter_Email()));
			$captcha = $this->post->captcha('string');


			// check captcha if anonymous
			$captchaProvider = Amun_Captcha::factory($this->config['amun_captcha']);

			if(!$captchaProvider->verify($captcha))
			{
				throw new Amun_Exception('Invalid captcha');
			}


			if(!$this->validate->hasError())
			{
				$account = Amun_Sql_Table_Registry::get('User_Account')
					->select(array('id', 'status', 'name', 'email'))
					->where('identity', '=', sha1(Amun_Security::getSalt() . $email))
					->getRow(PSX_Sql::FETCH_OBJECT);

				if($account instanceof Amun_User_Account)
				{
					if(!in_array($account->status, array(Amun_User_Account::NORMAL, Amun_User_Account::ADMINISTRATOR)))
					{
						throw new Amun_Exception('Account has an invalid status');
					}

					if(!empty($account->email))
					{
						$token = Amun_Security::generateToken();
						$link  = $this->page->url . '/login/resetPw?token=' . $token;
						$date  = new DateTime('NOW', $this->registry['core.default_timezone']);


						// update status
						$account->setStatus(Amun_User_Account::RECOVER);
						$account->setToken($token);

						$handler = new Amun_User_Account_Handler($this->user);
						$handler->update($account);


						// send mail
						$values = array(

							'account.name' => $account->name,
							'host.name'    => $this->base->getHost(),
							'recover.ip'   => $_SERVER['REMOTE_ADDR'],
							'recover.link' => $this->page->url . '/login/resetPw?token=' . $token,
							'recover.date' => $date->format($this->registry['core.format_date']),

						);

						$mail = new Amun_Mail($this->registry);
						$mail->send('SERVICE_MY_RECOVER', $account->email, $values);


						$this->template->assign('success', true);
					}
					else
					{
						throw new Amun_Exception('No public email address is set for this account');
					}
				}
				else
				{
					throw new Amun_Exception('Account does not exist');
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

