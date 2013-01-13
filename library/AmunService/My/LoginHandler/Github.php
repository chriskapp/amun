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
 * AmunService_My_LoginHandler_Github
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   Amun
 * @package    Amun_Service_My
 * @version    $Revision: 635 $
 */
class AmunService_My_LoginHandler_Github extends AmunService_My_LoginHandlerAbstract implements AmunService_My_LoginHandler_CallbackInterface
{
	const CLIENT_ID      = '';
	const CLIENT_SECRET  = '';

	const AUTHENTICATE   = 'https://github.com/login/oauth/authorize';
	const ACCESS_TOKEN   = 'https://graph.facebook.com/oauth/access_token';
	const VERIFY_ACCOUNT = 'https://api.github.com/users';

	protected $http;
	protected $oauth;

	public function __construct()
	{
		parent::__construct();

		$this->http  = new PSX_Http();
		$this->oauth = new PSX_Oauth2();
	}

	public function isValid($identity)
	{
		return filter_var($identity, FILTER_VALIDATE_EMAIL) !== false && strpos($identity, '@github.com') !== false;
	}

	public function handle($identity, $password)
	{
		// build callback
		$callback = $this->pageUrl . '/login/callback/github';

		PSX_Oauth2_Authorization_AuthorizationCode::redirect(new PSX_Url(self::AUTHENTICATE), self::CLIENT_ID, $callback);
	}

	public function callback()
	{
		$code = new PSX_Oauth2_Authorization_AuthorizationCode($this->http, new PSX_Url(self::ACCESS_TOKEN));
		$code->setClientPassword(self::CLIENT_ID, self::CLIENT_SECRET, PSX_Oauth2_Authorization_AuthorizationCode::AUTH_POST);

		$accessToken = $code->getAccessToken($redirect);

		// request user informations
		$url    = new PSX_Url(self::VERIFY_ACCOUNT);
		$header = array(
			'Authorization' => $this->oauth->getAuthorizationHeader($accessToken),
		);

		$request  = new PSX_Http_GetRequest($url, $header);
		$response = $this->http->request();
		
		if($response->getCode() == 200)
		{
			$acc = PSX_Json::decode($response->getBody());

			if(empty($acc))
			{
				throw new Amun_Exception('No user informations provided');
			}

			if(empty($acc['id']))
			{
				throw new Amun_Exception('No user id provided');
			}

			$identity = $acc['id'];	
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

				if(empty($acc['name']))
				{
					throw new Amun_Exception('No username provided');
				}

				$name = $this->normalizeName($acc['name']);

				// create user account
				$handler = new AmunService_User_Account_Handler($this->user);

				$account = Amun_Sql_Table_Registry::get('User_Account')->getRecord();
				$account->setGroupId($this->registry['core.default_user_group']);
				$account->setStatus(AmunService_User_Account_Record::NORMAL);
				$account->setIdentity($identity);
				$account->setName($name);
				$account->setPw(Amun_Security::generatePw());
				$account->setTimezone($acc['timezone']);

				$account->profileUrl   = isset($acc['html_url']) ? $acc['html_url'] : null;
				$account->thumbnailUrl = isset($acc['avatar_url']) ? $acc['avatar_url'] : null;

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

			// redirect
			header('Location: ' . $this->config['psx_url']);
			exit;
		}
		else
		{
			throw new Amun_Exception('Authentication failed');
		}
	}
}
