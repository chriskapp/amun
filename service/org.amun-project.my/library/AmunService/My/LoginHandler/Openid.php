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

namespace AmunService\My\LoginHandler;

use Amun\Exception;
use Amun\Security;
use Amun\DataFactory;
use AmunService\My\LoginHandlerAbstract;
use AmunService\User\Account;
use PSX\Http;
use PSX\Url;
use PSX\OpenId\Store;
use PSX\OpenId\Extension;
use PSX\OpenId\ProviderAbstract;
use PSX\Sql\Condition;
use DateTimeZone;

/**
 * Openid
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/gpl.html GPLv3
 * @link    http://amun.phpsx.org
 */
class Openid extends LoginHandlerAbstract implements CallbackInterface
{
	protected $http;
	protected $store;

	public function __construct($container)
	{
		parent::__construct($container);

		$this->http  = $container->get('http');
		$this->store = new Store\Sql($this->sql, $this->registry['table.core_assoc']);
	}

	public function isValid($identity)
	{
		// add http prefix if its not an email
		if(strpos($identity, '@') === false && substr($identity, 0, 7) != 'http://' && substr($identity, 0, 8) != 'https://')
		{
			$identity = 'http://' . $identity;
		}

		if(strpos($identity, '.') === false)
		{
			return false;
		}

		return filter_var($identity, FILTER_VALIDATE_URL) !== false;
	}

	public function hasPassword()
	{
		return false;
	}

	public function handle($identity, $password)
	{
		$openid = $this->getOpenidProvider($identity);

		if($openid !== false)
		{
			$identity = $openid->getIdentifier();

			if(!empty($identity))
			{
				// here we can add addition extensions depending what 
				// informations we need from the user
				$sreg = new Extension\Sreg(array('fullname', 'nickname', 'gender', 'timezone'));

				if($openid->hasExtension($sreg->getNs()))
				{
					$openid->add($sreg);
				}
				else
				{
					$ax = new Extension\Ax(array(

						'fullname'  => 'http://axschema.org/namePerson',
						'firstname' => 'http://axschema.org/namePerson/first',
						'lastname'  => 'http://axschema.org/namePerson/last',
						'gender'    => 'http://axschema.org/person/gender',
						'timezone'  => 'http://axschema.org/pref/timezone',

					));

					if($openid->hasExtension($ax->getNs()))
					{
						$openid->add($ax);
					}
				}

				// redirect
				$openid->redirect();
			}
			else
			{
				throw new Exception('Invalid identity');
			}
		}
		else
		{
			throw new Exception('Invalid openid identity');
		}
	}

	public function callback()
	{
		// initialize openid
		$openid = new \PSX\OpenId($this->http, $this->config['psx_url'], $this->store);

		if($openid->verify() === true)
		{
			$identity = $openid->getIdentifier();

			if(!empty($identity))
			{
				// check whether user is already registered
				$data   = $openid->getData();
				$con    = new Condition(array('identity', '=', sha1($this->config['amun_salt'] . $openid->getIdentifier())));
				$userId = $this->hm->getTable('User_Account')->getField('id', $con);

				if(empty($userId))
				{
					// user doesnt exist so register a new user check whether 
					// registration is enabled
					if(!$this->registry['my.registration_enabled'])
					{
						throw new Exception('Registration is disabled');
					}

					// get data for account
					$acc = $this->getAccountData($data);

					if(empty($acc))
					{
						throw new Exception('No user informations provided');
					}

					if(empty($acc['name']))
					{
						throw new Exception('No username provided');
					}

					$name = $this->normalizeName($acc['name']);

					// create user account
					$security = new Security($this->registry);
					$handler  = $this->hm->getHandler('User_Account', $this->user);

					$account = $handler->getRecord();
					$account->setGroupId($this->registry['core.default_user_group']);
					$account->setStatus(Account\Record::NORMAL);
					$account->setIdentity($identity);
					$account->setName($name);
					$account->setPw($security->generatePw());
					$account->setGender($acc['gender']);
					$account->setTimezone($acc['timezone']);

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

				// redirect
				header('Location: ' . $this->config['psx_url']);
				exit;
			}
			else
			{
				throw new Exception('Invalid identity');
			}
		}
		else
		{
			throw new Exception('Authentication failed');
		}
	}

	/**
	 * If $identity is an url we assume that this is an openid url and try to
	 * discover the provider. If $identity is an email address we look first at
	 * the provider and check whether it is also an OpenID provider in any other
	 * case we return false
	 *
	 * @param string $identity
	 * @return false|PSX_OpenId_ProviderInterface
	 */
	protected function getOpenidProvider($identity)
	{
		// add http prefix if its not an email
		if(strpos($identity, '@') === false && substr($identity, 0, 7) != 'http://' && substr($identity, 0, 8) != 'https://')
		{
			$identity = 'http://' . $identity;
		}

		// build callback
		$callback = $this->pageUrl . '/login/callback/openid';

		$openid = new \PSX\OpenId($this->http, $this->config['psx_url'], $this->store);
		$openid->initialize($identity, $callback);

		return $openid;
	}

	protected function getAccountData(array $data)
	{
		$account = array();

		// sreg extension
		$params = ProviderAbstract::getExtension($data, Extension\Sreg::NS);

		if(!empty($params))
		{
			if(isset($params['fullname']))
			{
				$account['name'] = $params['fullname'];
			}
			else if(isset($params['nickname']))
			{
				$account['name'] = $params['nickname'];
			}

			if(isset($params['gender']))
			{
				$params['gender'] = strtoupper($params['gender']);

				$account['gender'] = $params['gender'] == 'M' ? 'male' : ($params['gender'] == 'F' ? 'female' : 'undisclosed');
			}
			else
			{
				$account['gender'] = 'undisclosed';
			}

			if(isset($params['timezone']) && in_array($params['timezone'], DateTimeZone::listIdentifiers()))
			{
				$account['timezone'] = $params['timezone'];
			}
			else
			{
				$account['timezone'] = 'UTC';
			}

			return $account;
		}

		// ax extension
		$params = ProviderAbstract::getExtension($data, Extension\Ax::NS);

		if(!empty($params))
		{
			$keys   = array();
			$values = array(

				'fullname'  => 'http://axschema.org/namePerson',
				'firstname' => 'http://axschema.org/namePerson/first',
				'lastname'  => 'http://axschema.org/namePerson/last',
				'gender'    => 'http://axschema.org/person/gender',
				'timezone'  => 'http://axschema.org/pref/timezone',

			);

			foreach($params as $k => $v)
			{
				foreach($values as $key => $ns)
				{
					if($v == $ns)
					{
						$keys[$key] = str_replace('type', 'value', $k);
					}
				}
			}

			if(isset($keys['firstname']) && $keys['lastname'] && isset($params[$keys['firstname']]) && isset($params[$keys['lastname']]))
			{
				$account['name'] = $params[$keys['firstname']] . ' ' . $params[$keys['lastname']];
			}
			elseif(isset($keys['fullname']) && isset($params[$keys['fullname']]))
			{
				$account['name'] = $params[$keys['fullname']];
			}

			if(isset($keys['gender']) && isset($params[$keys['gender']]))
			{
				$params[$keys['gender']] = strtoupper($params[$keys['gender']]);

				$account['gender'] = $params[$keys['gender']] == 'M' ? 'male' : ($params[$keys['gender']] == 'F' ? 'female' : 'undisclosed');
			}
			else
			{
				$account['gender'] = 'undisclosed';
			}

			if(isset($keys['timezone']) && isset($params[$keys['timezone']]) && in_array($params[$keys['timezone']], DateTimeZone::listIdentifiers()))
			{
				$account['timezone'] = $params[$keys['timezone']];
			}
			else
			{
				$account['timezone'] = 'UTC';
			}

			return $account;
		}
	}
}
