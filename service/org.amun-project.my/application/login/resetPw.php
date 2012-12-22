<?php
/*
 *  $Id: resetPw.php 875 2012-09-30 13:51:45Z k42b3.x@googlemail.com $
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
 * resetPw
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   module
 * @package    application
 * @subpackage my
 * @version    $Revision: 875 $
 */
class resetPw extends Amun_Module_ApplicationAbstract
{
	public function onLoad()
	{
		parent::onLoad();

		// add path
		$this->path->add('Login', $this->page->url . '/login');
		$this->path->add('Reset Password', $this->page->url . '/login/resetPw');

		// template
		$this->htmlCss->add('my');

		$this->template->set('login/' . __CLASS__ . '.tpl');
	}

	public function onGet()
	{
		try
		{
			$token = $this->get->token('string', array(new PSX_Filter_Length(40, 40), new PSX_Filter_Xdigit()));

			if($token !== false)
			{
				$account = Amun_Sql_Table_Registry::get('User_Account')
					->select(array('id', 'name', 'ip', 'email', 'date'))
					->where('token', '=', $token)
					->where('status', '=', AmunService_User_Account_Record::RECOVER)
					->getRow(PSX_Sql::FETCH_OBJECT);

				if($account instanceof AmunService_User_Account_Record)
				{
					if(!empty($account->email))
					{
						if($_SERVER['REMOTE_ADDR'] == $account->ip)
						{
							$pw   = Amun_Security::generatePw();
							$date = new DateTime('NOW', $this->registry['core.default_timezone']);

							$account->setStatus(AmunService_User_Account_Record::NORMAL);
							$account->setPw($pw);

							$handler = new AmunService_User_Account_Handler($this->user);
							$handler->update($account);

							// send mail
							$values = array(

								'account.name' => $account->name,
								'account.pw'   => $pw,
								'host.name'    => $this->base->getHost(),
								'recover.link' => $this->page->url . '/login',
								'recover.date' => $date->format($this->registry['core.format_date']),

							);

							$mail = new Amun_Mail($this->registry);
							$mail->send('MY_RECOVER_SUCCESS', $account->email, $values);

							$this->template->assign('success', true);
						}
						else
						{
							throw new Amun_Exception('Recover process was requested from another IP');
						}
					}
					else
					{
						throw new Amun_Exception('No public email address is set for this account');
					}
				}
				else
				{
					throw new Amun_Exception('Invalid token');
				}
			}
			else
			{
				throw new Amun_Exception('Token not set');
			}
		}
		catch(Exception $e)
		{
			$this->template->assign('error', $e->getMessage());
		}
	}
}
