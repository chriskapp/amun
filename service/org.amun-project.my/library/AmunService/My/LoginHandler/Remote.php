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
class AmunService_My_LoginHandler_Remote extends AmunService_My_LoginHandler_OpenId
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
		$http   = new PSX_Http(new PSX_Http_Handler_Curl());
		$store  = new PSX_OpenId_Store_Sql($this->sql, $this->registry['table.core_assoc']);
		$openid = new PSX_OpenId($http, $this->config['psx_url'], $store);

		// check whether identity is an url if not it is an email
		$filter = new PSX_Filter_Url();

		if($filter->apply($identity) === false)
		{
			$pos      = strpos($identity, '@');
			$provider = substr($identity, $pos + 1);

			// check whether the provider belongs to an connected website. If
			// yes we also try to get an token and tokenSecret for the user
			$host = Amun_Sql_Table_Registry::get('Core_Host')
				->select(array('id', 'consumerKey', 'url', 'template'))
				->where('name', '=', $provider)
				->where('status', '=', AmunService_Core_Host_Record::NORMAL)
				->getRow();

			if(!empty($host))
			{
				// make webfinger request
				$http       = new PSX_Http(new PSX_Http_Handler_Curl());
				$webfinger  = new PSX_Webfinger($http);

				$acct = 'acct:' . $identity;
				$xrd  = $webfinger->getLrdd($acct, $host['template']);

				// check subject
				if(strcmp($xrd->getSubject(), $acct) !== 0)
				{
					throw new Amun_Exception('Invalid subject');
				}

				// get profile url
				$profileUrl = $xrd->getLinkHref('profile');

				if(empty($profileUrl))
				{
					throw new Amun_Exception('Could not find profile');
				}

				// get global id
				$globalId = $xrd->getPropertyValue('http://ns.amun-project.org/2011/meta/id');

				// initalize openid
				$openid->initialize($profileUrl, $callback);

				// if the provider is connected with the website and
				// supports the oauth extension request an token
				$identity = sha1(Amun_Security::getSalt() . PSX_OpenId::normalizeIdentifier($profileUrl));
				$con      = new PSX_Sql_Condition(array('identity', '=', $identity));
				$userId   = Amun_Sql_Table_Registry::get('User_Account')->getField('id', $con);
				$oauth    = false;

				if(!empty($userId))
				{
					$con = new PSX_Sql_Condition();
					$con->add('hostId', '=', $host['id']);
					$con->add('userId', '=', $userId);

					$requestId = Amun_Sql_Table_Registry::get('Core_Host_Request')->getField('id', $con);

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
					$oauth = new PSX_OpenId_Extension_Oauth($host['consumerKey']);

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
}
