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

namespace AmunService\My\LoginHandler;

use Amun\DataFactory;
use Amun\Exception;
use Amun\Security;
use AmunService\Core\Host;
use PSX\Filter;
use PSX\Http;
use PSX\Url;
use PSX\Webfinger;
use PSX\Sql\Condition;
use PSX\OpenId\Store;
use PSX\OpenId\Extension;
use PSX\OpenId\ProviderAbstract;

/**
 * This is an experimental login handler to create an federated social network
 * between multiple amun instances
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   Amun
 * @package    Amun_Service_My
 * @version    $Revision: 635 $
 */
class Remote extends Openid
{
	public function isValid($identity)
	{
		// not complete tested
		return false;
	}

	protected function isOpenidProvider($identity)
	{
		// add http prefix if its not an email
		if(strpos($identity, '@') === false && substr($identity, 0, 7) != 'http://' && substr($identity, 0, 8) != 'https://')
		{
			$identity = 'http://' . $identity;
		}

		// build callback
		$callback = $this->pageUrl . '/login/callback/remote';

		// create an openid object
		$openid = new \PSX\OpenId($this->http, $this->config['psx_url'], $this->store);

		// check whether identity is an url if not it is an email
		$filter = new Filter\Url();

		if($filter->apply($identity) === false)
		{
			$pos      = strpos($identity, '@');
			$provider = substr($identity, $pos + 1);

			// check whether the provider belongs to an connected website. If
			// yes we also try to get an token and tokenSecret for the user
			$host = DataFactory::getTable('Core_Host')
				->select(array('id', 'consumerKey', 'url', 'template'))
				->where('name', '=', $provider)
				->where('status', '=', Host\Record::NORMAL)
				->getRow();

			if(!empty($host))
			{
				// make webfinger request
				$webfinger  = new Webfinger($this->http);

				$acct = 'acct:' . $identity;
				$xrd  = $webfinger->getLrdd($acct, $host['template']);

				// check subject
				if(strcmp($xrd->getSubject(), $acct) !== 0)
				{
					throw new Exception('Invalid subject');
				}

				// get profile url
				$profileUrl = $xrd->getLinkHref('profile');

				if(empty($profileUrl))
				{
					throw new Exception('Could not find profile');
				}

				// get global id
				$globalId = $xrd->getPropertyValue('http://ns.amun-project.org/2011/meta/id');

				// initalize openid
				$openid->initialize($profileUrl, $callback);

				// if the provider is connected with the website and supports 
				// the oauth extension request an token
				$identity = sha1(Security::getSalt() . OpenId::normalizeIdentifier($profileUrl));
				$con      = new Condition(array('identity', '=', $identity));
				$userId   = DataFactory::getTable('User_Account')->getField('id', $con);
				$oauth    = false;

				if(!empty($userId))
				{
					$con = new Condition();
					$con->add('hostId', '=', $host['id']);
					$con->add('userId', '=', $userId);

					$requestId = DataFactory::getTable('Core_Host_Request')->getField('id', $con);

					if(empty($requestId))
					{
						$oauth = true;
					}
				}
				else
				{
					$oauth = true;
				}

				if($oauth)
				{
					$oauth = new Extension\Oauth($host['consumerKey']);

					if($openid->hasExtension($oauth->getNs()))
					{
						$this->session->set('openid_register_user_host_id', $host['id']);
						$this->session->set('openid_register_user_global_id', $globalId);

						$openid->add($oauth);
					}
				}

				return $openid;
			}
		}

		return false;
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
				$con    = new Condition(array('identity', '=', sha1(Security::getSalt() . $openid->getIdentifier())));
				$userId = DataFactory::getTable('User_Account')->getField('id', $con);

				if(empty($userId))
				{
					// user doesnt exist so register a new user check whether 
					// registration is enabled
					if(!$this->registry['my.registration_enabled'])
					{
						throw new Exception('Registration is disabled');
					}

					$hostId   = $this->session->get('openid_register_user_host_id');
					$globalId = $this->session->get('openid_register_user_global_id');

					if(empty($hostId))
					{
						throw new Exception('No host id provided');
					}

					if(empty($globalId))
					{
						throw new Exception('No global id provided');
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
					$handler = new Account\Handler($this->user);

					$account = DataFactory::getTable('User_Account')->getRecord();
					$account->setGlobalId($globalId);
					$account->setGroupId($this->registry['core.default_user_group']);
					$account->setHostId($hostId);
					$account->setStatus(Account\Record::REMOTE);
					$account->setIdentity($identity);
					$account->setName($name);
					$account->setPw(Security::generatePw());
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

	private function getOauthAccessToken($hostId, array $data)
	{
		$data = ProviderAbstract::getExtension($data, Extension\Oauth::NS);

		$token    = isset($data['request_token']) ? $data['request_token'] : null;
		$verifier = isset($data['verifier'])      ? $data['verifier']      : null;

		if($hostId > 0 && !empty($token) && !empty($verifier))
		{
			$row = DataFactory::getTable('Core_Host')
				->select(array('consumerKey', 'consumerSecret', 'url'))
				->where('id', '=', $hostId)
				->where('status', '=', Host\Record::NORMAL)
				->getRow();

			if(!empty($row))
			{
				$url   = $this->discoverOauthAcessUrl(new Url($row['url']));
				$oauth = new Oauth($this->http);

				return $oauth->accessToken($url, $row['consumerKey'], $row['consumerSecret'], $token, '', $verifier);
			}
			else
			{
				throw new Exception('Invalid host id');
			}
		}
	}
}
