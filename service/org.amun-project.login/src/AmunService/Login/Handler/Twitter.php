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

use Amun\Security;
use Amun\DataFactory;
use Amun\Exception;
use AmunService\Login\HandlerAbstract;
use AmunService\User\Account;
use PSX\Http;
use PSX\Http\GetRequest;
use PSX\Oauth;
use PSX\Url;
use PSX\Json;
use PSX\Sql\Condition;

/**
 * In order to use the twitter handler you have to create a new twitter 
 * application and enter the consumer key and secret in this class
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/gpl.html GPLv3
 * @link    http://amun.phpsx.org
 */
class Twitter extends HandlerAbstract implements CallbackInterface
{
	const CONSUMER_KEY    = '';
	const CONSUMER_SECRET = '';

	const REQUEST_TOKEN   = 'http://api.twitter.com/oauth/request_token';
	const ACCESS_TOKEN    = 'http://api.twitter.com/oauth/access_token';
	const AUTHENTICATE    = 'https://api.twitter.com/oauth/authenticate';
	const VERIFY_ACCOUNT  = 'https://api.twitter.com/1.1/account/verify_credentials.json?skip_status=1';

	protected $http;
	protected $oauth;

	public function __construct($container)
	{
		parent::__construct($container);

		$this->http  = $container->get('http');
		$this->oauth = new Oauth($this->http);
	}

	public function isValid($identity)
	{
		return filter_var($identity, FILTER_VALIDATE_EMAIL) !== false && strpos($identity, '@twitter.com') !== false;
	}

	public function hasPassword()
	{
		return false;
	}

	public function handle($identity, $password)
	{
		// build callback
		$callback = $this->pageUrl . '/callback/twitter';
		$response = $this->oauth->requestToken(new Url(self::REQUEST_TOKEN), self::CONSUMER_KEY, self::CONSUMER_SECRET, 'HMAC-SHA1', $callback);

		$token       = $response->getToken();
		$tokenSecret = $response->getTokenSecret();

		$this->session->set('oauth_login_token', $token);
		$this->session->set('oauth_login_token_secret', $tokenSecret);

		// redirect user to twitter
		$this->oauth->userAuthorization(new Url(self::AUTHENTICATE), array('oauth_token' => $token));
	}

	public function callback()
	{
		// get access token
		$token       = $this->session->get('oauth_login_token');
		$tokenSecret = $this->session->get('oauth_login_token_secret');
		$verifier    = isset($_GET['oauth_verifier']) ? $_GET['oauth_verifier'] : null;

		if(empty($token) || empty($tokenSecret))
		{
			throw new Exception('Token not set');
		}

		$response = $this->oauth->accessToken(new Url(self::ACCESS_TOKEN), self::CONSUMER_KEY, self::CONSUMER_SECRET, $token, $tokenSecret, $verifier, 'HMAC-SHA1');

		$token       = $response->getToken();
		$tokenSecret = $response->getTokenSecret();

		// check access token
		if(empty($token) || empty($tokenSecret))
		{
			throw new Exception('Could not request access token');
		}

		// request user informations
		$url    = new Url(self::VERIFY_ACCOUNT);
		$header = array(
			'Authorization' => $this->oauth->getAuthorizationHeader($url, self::CONSUMER_KEY, self::CONSUMER_SECRET, $token, $tokenSecret, $method = 'HMAC-SHA1'),
		);

		$request  = new GetRequest($url, $header);
		$response = $this->http->request($request);

		if($response->getCode() == 200)
		{
			$acc = Json::decode($response->getBody());

			if(empty($acc))
			{
				throw new Exception('No user informations provided');
			}

			if(empty($acc['screen_name']))
			{
				throw new Exception('No username provided');
			}

			$identity = $acc['screen_name'] . '@twitter.com';	
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
				$name = $this->normalizeName($acc['screen_name']);

				// create user account
				$security = new Security($this->registry);
				$handler  = $this->hm->getHandler('AmunService\User\Account', $this->user);

				$account = $handler->getRecord();
				$account->setGroupId($this->registry['core.default_user_group']);
				$account->setStatus(Account\Record::NORMAL);
				$account->setIdentity($identity);
				$account->setName($name);
				$account->setPw($security->generatePw());

				$account->profileUrl   = 'https://twitter.com/' . $acc['screen_name'];
				$account->thumbnailUrl = isset($acc['profile_image_url']) ? $acc['profile_image_url'] : null;

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
			throw new Exception('Authentication failed');
		}
	}
}
