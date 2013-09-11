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

namespace AmunService\Login\Handler;

use Amun\Exception;
use Amun\Security;
use AmunService\Login\HandlerAbstract;
use AmunService\Login\InvalidPasswordException;
use AmunService\User\Account;
use PSX\Sql\Condition;

/**
 * Handles authentication against an LDAP server. The handler was tested with
 * the OpenDS (http://opends.java.net/) server. If you have problems with other 
 * LDAP implementations please contact us to increase the interoperability of 
 * the handler 
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/gpl.html GPLv3
 * @link    http://amun.phpsx.org
 */
class Ldap extends HandlerAbstract
{
	const LDAP_HOST = 'localhost';
	const USER_DN   = ''; // user i.e. cn=Foo
	const USER_PW   = ''; // password
	const SALT_SIZE = 8;

	protected $res;

	public function __construct($container)
	{
		parent::__construct($container);

		$this->res = ldap_connect(self::LDAP_HOST);

		if(!$this->res)
		{
			throw new Exception('Ldap connection failed');
		}

		if(!ldap_bind($this->res, self::USER_DN, self::USER_PW))
		{
			throw new Exception('Could not bind Ldap');
		}
	}

	public function isValid($identity)
	{
		return true;
	}

	public function hasPassword()
	{
		return true;
	}

	public function handle($identity, $password)
	{
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
				throw new Exception('Mail not set');
			}

			if(empty($name))
			{
				throw new Exception('Given name not set');
			}

			if(empty($pw))
			{
				throw new Exception('User password not set');
			}

			if($this->comparePassword($pw, $password) === true)
			{
				$identity = $mail;
				$con      = new Condition(array('identity', '=', sha1($this->config['amun_salt'] . $identity)));
				$userId   = $this->hm->getTable('AmunService\User\Account')->getField('id', $con);

				if(empty($userId))
				{
					// user doesnt exist so register a new user check whether 
					// registration is enabled
					if(!$this->registry['login.registration_enabled'])
					{
						throw new Exception('Registration is disabled');
					}

					// normalize name
					$name = $this->normalizeName($name);

					// create user account
					$security = new Security($this->registry);
					$handler  = $this->hm->getHandler('AmunService\User\Account', $this->user);

					$account = $handler->getRecord();
					$account->setGroupId($this->registry['core.default_user_group']);
					$account->setStatus(Account\Record::NORMAL);
					$account->setIdentity($identity);
					$account->setName($name);
					$account->setPw($security->generatePw());

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
						throw new Exception('Could not create account');
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
				throw new InvalidPasswordException('Invalid password');
			}
		}
	}

	/**
	 * Compares the password from an attribute userPasssword with the given 
	 * password. Returns true if the password are equal. The ldap password 
	 * looks like: {SSHA}Gkau0n8WLjvQUOPVPET2xJo/2YlVHC1YaSk6FQ==
	 *
	 * @param string $ldapPassword
	 * @param string $password
	 * @return boolean
	 */
	protected function comparePassword($ldapPassword, $password)
	{
		$pos  = strpos($ldapPassword, '}');
		$type = substr($ldapPassword, 1, $pos - 1);
		$pw   = substr($ldapPassword, $pos + 1);
		$pw   = base64_decode($pw);
		$algo = 'md5';
		$salt = false;

		switch(strtoupper($type))
		{
			case 'SSHA':
				$salt = true;

			case 'SHA':
				$algo = 'sha1';
				break;

			case 'SMD5':
				$salt = true;

			case 'MD5':
				$algo = 'md5';
				break;
		}

		if($salt === true)
		{
			$salt = substr($pw, self::SALT_SIZE * -1);
			$pw   = substr($pw, 0, self::SALT_SIZE * -1);

			return strcasecmp(base64_encode($pw), base64_encode(hash($algo, $password . $salt, true))) === 0;
		}
		else
		{
			return strcasecmp(base64_encode($pw), base64_encode(hash($algo, $password, true))) === 0;
		}

		return false;
	}
}
