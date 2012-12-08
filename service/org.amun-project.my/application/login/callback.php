<?php
/*
 *  $Id: callback.php 875 2012-09-30 13:51:45Z k42b3.x@googlemail.com $
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
 * callback
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   module
 * @package    application
 * @subpackage my
 * @version    $Revision: 875 $
 */
class callback extends Amun_Module_ApplicationAbstract
{
	public function onLoad()
	{
		parent::onLoad();

		// http
		$this->http = new PSX_Http(new PSX_Http_Handler_Curl());

		// initialize openid
		$store  = new PSX_OpenId_Store_Sql($this->sql, $this->registry['table.core_assoc']);
		$openid = new PSX_OpenId($this->http, $this->config['psx_url'], $store);

		if($openid->verify() === true)
		{
			$identity = $openid->getIdentifier();

			if(!empty($identity))
			{
				// check whether user is already registered
				$data     = $openid->getData();
				$hostId   = $this->session->openid_register_user_host_id;
				$globalId = $this->session->openid_register_user_global_id;
				$con      = new PSX_Sql_Condition(array('identity', '=', sha1(Amun_Security::getSalt() . $openid->getIdentifier())));
				$userId   = Amun_Sql_Table_Registry::get('User_Account')->getField('id', $con);

				if(empty($userId))
				{
					// user doesnt exist so register a new user check
					// whether registration is enabled
					if(!$this->registry['my.registration_enabled'])
					{
						throw new Amun_Exception('Registration is disabled');
					}

					// get data for account
					$acc = $this->getAccountData($data);

					if(empty($acc))
					{
						throw new Amun_Exception('No user informations provided');
					}

					if(empty($acc['name']))
					{
						throw new Amun_Exception('No username provided');
					}

					$name = $this->normalizeName($acc['name']);

					// get global id this could be null if the webfinger
					// discovered XRD has not the property http://ns.amun-project.org/2011/meta/id
					// in this case we generate a new global id
					if(!empty($globalId))
					{
						$con = new PSX_Sql_Condition();
						$con->add('globalId', '=', $globalId);

						$userId = Amun_Sql_Table_Registry::get('User_Account')->getField('id', $con);
					}

					// create user account
					if(empty($userId))
					{
						$handler = new AmunService_User_Account_Handler($this->user);

						$account = Amun_Sql_Table_Registry::get('User_Account')->getRecord();
						$account->setGroupId($this->registry['core.default_user_group']);
						$account->setHostId($hostId);
						$account->setStatus($hostId > 0 ? AmunService_User_Account_Record::REMOTE : AmunService_User_Account_Record::NORMAL);
						$account->setIdentity($identity);
						$account->setName($name);
						$account->setPw(Amun_Security::generatePw());
						$account->setGender($acc['gender']);
						$account->setTimezone($acc['timezone']);

						if(!empty($globalId))
						{
							$account->globalId = $globalId;
						}

						$account = $handler->create($account);
						$userId  = $account->id;
					}

					// if the id is not set the account was probably added to
					// the approval table
					if(!empty($userId))
					{
						$this->session->set('amun_id', $userId);
						$this->session->set('amun_t', time());
					}
					else
					{
						throw new Amun_Exception('Could not create account');
					}
				}
				else
				{
					$this->session->set('amun_id', $userId);
					$this->session->set('amun_t', time());
				}

				// if an oauth extension was used exchange the token for an
				// access token
				if(!empty($hostId))
				{
					$resp = $this->getOauthAccessToken($hostId, $data);

					// insert access tokens if requested
					if($resp instanceof PSX_Oauth_Provider_Data_Response && !empty($userId))
					{
						$date = new DateTime('NOW', $this->registry['core.default_timezone']);

						$this->sql->insert($this->registry['table.core_host_request'], array(

							'hostId'      => $hostId,
							'userId'      => $userId,
							'ip'          => $_SERVER['REMOTE_ADDR'],
							'token'       => $resp->getToken(),
							'tokenSecret' => $resp->getTokenSecret(),
							'expire'      => 'P6M',
							'date'        => $date->format(PSX_Time::SQL),

						));
					}
				}

				// redirect
				header('Location: ' . $this->config['psx_url']);
				exit;
			}
			else
			{
				throw new Amun_Exception('Invalid identity');
			}
		}
		else
		{
			throw new Amun_Exception('Authentication failed');
		}

		// add path
		$this->path->add('Login', $this->page->url . '/login');
		$this->path->add('Callback', $this->page->url . '/login/callback');

		// template
		$this->htmlCss->add('my');

		$this->template->set('login/' . __CLASS__ . '.tpl');
	}

	private function getAccountData(array $data)
	{
		$account = array();

		// sreg extension
		$params = PSX_OpenId_ProviderAbstract::getExtension($data, PSX_OpenId_Extension_Sreg::NS);

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
		$params = PSX_OpenId_ProviderAbstract::getExtension($data, PSX_OpenId_Extension_Ax::NS);

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

	private function getOauthAccessToken($hostId, array $data)
	{
		$data = PSX_OpenId_ProviderAbstract::getExtension($data, PSX_OpenId_Extension_Oauth::NS);

		$token    = isset($data['request_token']) ? $data['request_token'] : null;
		$verifier = isset($data['verifier'])      ? $data['verifier']      : null;

		if($hostId > 0 && !empty($token) && !empty($verifier))
		{
			$row = Amun_Sql_Table_Registry::get('Core_Host')
				->select(array('consumerKey', 'consumerSecret', 'url'))
				->where('id', '=', $hostId)
				->where('status', '=', AmunService_Core_Host_Record::NORMAL)
				->getRow();

			if(!empty($row))
			{
				$url   = $this->discoverOauthAcessUrl(new PSX_Url($row['url']));
				$oauth = new PSX_Oauth($this->http);

				return $oauth->accessToken($url, $row['consumerKey'], $row['consumerSecret'], $token, '', $verifier);
			}
			else
			{
				throw new Amun_Exception('Invalid host id');
			}
		}
	}

	public function discoverOauthAcessUrl(PSX_Url $url)
	{
		$yadis = new PSX_Yadis($this->http);
		$xrds  = $yadis->discover($url);

		if($xrds !== false && isset($xrds->service))
		{
			$uri = null;

			foreach($xrds->service as $service)
			{
				if(in_array('http://oauth.net/core/1.0/endpoint/access', $service->getType()))
				{
					$uri = $service->getUri();
				}
			}

			if(!empty($uri))
			{
				return new PSX_Url($uri);
			}
			else
			{
				throw new Amun_Exception('Could not find service');
			}
		}
		else
		{
			throw new Amun_Exception('Could not find xrds');
		}
	}

	/**
	 * normalizeName
	 *
	 * We receive an name from an OpenID provider the name is handled as an
	 * untrsuted value because of that we go through each sign and check
	 * whether it is valid. The string wich returned by this method is a
	 * valid name
	 *
	 * @param string $name
	 * @return string
	 */
	private function normalizeName($name)
	{
		$norm = '';
		$len  = strlen($name);

		// replace white space with period
		$name = str_replace(' ', '.', $name);

		// name can only contain A-Z a-z 0-9 .
		$period = false;

		for($i = 0; $i < $len; $i++)
		{
			$ascii = ord($name[$i]);

			# alpha (A - Z / a - z / 0 - 9 / .)
			if(($ascii >= 0x41 && $ascii <= 0x5A) || ($ascii >= 0x61 && $ascii <= 0x7A) || ($ascii >= 0x30 && $ascii <= 0x39))
			{
				$norm.= $name[$i];
			}

			if($period === false && $ascii == 0x2E)
			{
				$norm.= $name[$i];

				$period = true;
			}
		}

		return $norm;
	}
}
