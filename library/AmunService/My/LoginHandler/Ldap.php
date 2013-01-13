<?php
/*
 *  $Id: FriendsAbstract.php 635 2012-05-01 19:46:37Z k42b3.x@googlemail.com $
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
 * AmunService_My_LoginHandler_Ldap
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   Amun
 * @package    Amun_Service_My
 * @version    $Revision: 635 $
 */
class AmunService_My_LoginHandler_Ldap extends AmunService_My_LoginHandlerAbstract
{
	const LDAP_HOST = 'localhost';
	const USER_DN   = 'cn=Foo';
	const USER_PW   = '009900';
	const SALT      = '';

	protected $res;

	public function __construct()
	{
		$this->res = ldap_connect(self::LDAP_HOST);

		if(!$this->res)
		{
			throw new Amun_Exception('Ldap connection failed');
		}

		if(!ldap_bind($this->res, self::USER_DN, self::USER_PW))
		{
			throw new Amun_Exception('Could not bind LDAP');
		}
	}

	public function isValid($identity)
	{
		return true;
	}

	public function handle($identity, $password)
	{
		// user.106 / pw=test

		$result  = ldap_search($this->res, '', 'uid=' . $identity);
		$entries = ldap_get_entries($this->res, $result);
		$count   = isset($entries['count']) ? $entries['count'] : 0;

		if($count == 1)
		{
			$acc  = $entries[0];

			$mail = isset($acc['mail'][0]) ? $acc['mail'][0] : null;
			$name = isset($acc['givenname'][0]) ? $acc['givenname'][0] : null;
			$pw   = isset($acc['userpassword'][0]) ? $acc['userpassword'][0] : null;

			if(empty($mail))
			{
				throw new Amun_Exception('Mail not set');
			}

			if(empty($name))
			{
				throw new Amun_Exception('Given name not set');
			}

			if(empty($pw))
			{
				throw new Amun_Exception('User password not set');
			}

			$pos  = strpos($pw, '}');
			$type = substr($pw, 1, $pos - 1);

			switch(strtoupper($type))
			{
				case 'SSHA':
					$password = '{SSHA}' . base64_encode(sha1($password . self::SALT, true));
					break;

				case 'SHA':
					$password = '{SHA}' . base64_encode(sha1($password, true));
					break;

				case 'CRYPT':
					$password = '{CRYPT}' . crypt($password, self::SALT);
					break;

				case 'SMD5':
					$password = '{SMD5}' . base64_encode(md5($password . self::SALT, true));
					break;

				case 'MD5':
					$password = '{MD5}' . base64_encode(md5($password, true));
					break;

				default:
					throw new Amun_Exception('LDAP invalid hash method');
					break;
			}

			if(strcasecmp($password, $pw) === 0)
			{
				$identity = $mail;
				$con      = new PSX_Sql_Condition(array('identity', '=', sha1(Amun_Security::getSalt() . $identity)));
				$userId   = Amun_Sql_Table_Registry::get('User_Account')->getField('id', $con);

				if(empty($userId))
				{
					// user doesnt exist so register a new user check whether 
					// registration is enabled
					if(!$this->registry['my.registration_enabled'])
					{
						throw new Amun_Exception('Registration is disabled');
					}

					// normalize name
					$name = $this->normalizeName($name);

					// create user account
					$handler = new AmunService_User_Account_Handler($this->user);

					$account = Amun_Sql_Table_Registry::get('User_Account')->getRecord();
					$account->setGroupId($this->registry['core.default_user_group']);
					$account->setStatus(AmunService_User_Account_Record::NORMAL);
					$account->setIdentity($identity);
					$account->setName($name);
					$account->setPw(Amun_Security::generatePw());

					$account = $handler->create($account);
					$userId  = $account->id;

					// if the id is not set the account was probably added to
					// the approval table
					if(!empty($userId))
					{
						$this->setUserId($userId);
					}
					else
					{
						throw new Amun_Exception('Could not create account');
					}
				}
				else
				{
					$this->setUserId($userId);
				}

				return true;
			}
			else
			{
				throw new AmunService_My_Login_InvalidPasswordException('Invalid password');
			}
		}
	}
}
