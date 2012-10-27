<?php
/*
 *  $Id: login.php 875 2012-09-30 13:51:45Z k42b3.x@googlemail.com $
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
 * login
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   module
 * @package    application
 * @subpackage my
 * @version    $Revision: 875 $
 */
class login extends Amun_Module_ApplicationAbstract
{
	private $host = null;
	private $isRegistered = false;

	private $attempt;
	private $stage;

	public function onLoad()
	{
		if($this->user->hasRight('service_my_view'))
		{
			// assign redirect
			$this->template->assign('redirect', $this->getRedirect($this->get));


			// add path
			$this->path->add('Login', $this->page->url . '/login');


			// load supported provider
			$defaultProvider = array_map('trim', explode(',', $this->registry['my.openid_provider']));
			$hostProvider    = Amun_Sql_Table_Registry::get('System_Host')
				->select(array('name'))
				->where('status', '=', Amun_System_Host::NORMAL)
				->getCol();

			$provider = array_merge($defaultProvider, $hostProvider);

			$this->template->assign('provider', $provider);


			// check login attempts
			$this->attempt = new Amun_Service_My_Attempt($this->registry);
			$this->stage   = $this->attempt->getStage();

			if($this->stage == Amun_Service_My_Attempt::TRYING)
			{
				$captcha = $this->config['psx_url'] . '/' . $this->config['psx_dispatch'] . 'api/system/captcha';

				$this->template->assign('captcha', $captcha);
			}
			else if($this->stage == Amun_Service_My_Attempt::ABUSE)
			{
				throw new Amun_Exception('Your IP ' . $_SERVER['REMOTE_ADDR'] . ' is banned for 30 minutes because of too many wrong logins');
			}


			// template
			$this->htmlCss->add('my');
			$this->htmlJs->add('amun');
			$this->htmlJs->add('my');

			$this->template->set(__CLASS__ . '.tpl');
		}
		else
		{
			throw new Amun_Exception('Access not allowed');
		}
	}

	public function onPost()
	{
		if($this->post->register('string'))
		{
			header('Location: ' . $this->page->url . '/register');
			exit;
		}

		$redirect = $this->getRedirect($this->post);
		$identity = $this->post->identity('string', array(new Amun_User_Account_Filter_Identity()));
		$pw       = $this->post->pw('string', array(new Amun_User_Account_Filter_Pw()));
		$captcha  = $this->post->captcha('integer');

		try
		{
			if(empty($identity))
			{
				throw new Amun_Exception('Invalid identity');
			}

			if(($openid = $this->isOpenidProvider($identity)) === false)
			{
				// check captcha if needed
				if($this->stage == Amun_Service_My_Attempt::TRYING)
				{
					if(!Amun_Captcha::factory($this->config['amun_captcha'])->verify($captcha))
					{
						throw new PSX_Data_Exception('Invalid captcha');
					}
				}

				// we have given an email address if a password is set we check
				// whether the user exists
				$identity = sha1(Amun_Security::getSalt() . $identity);

				if(!empty($pw))
				{
					$row = Amun_Sql_Table_Registry::get('User_Account')
						->select(array('id', 'status', 'pw'))
						->where('identity', '=', $identity)
						->getRow();

					if(!empty($row))
					{
						if($row['pw'] == sha1(Amun_Security::getSalt() . $pw))
						{
							if(($error = $this->isValidStatus($row['status'])) === true)
							{
								// set user id
								$this->session->set('amun_id', $row['id']);
								$this->session->set('amun_t', time());

								// clear attempts
								if($this->stage != Amun_Service_My_Attempt::NONE)
								{
									$this->attempt->clear();
								}

								// redirect
								$url = $redirect === false ? $this->config['psx_url'] : $redirect;

								header('Location: ' . $url);
								exit;
							}
							else
							{
								throw new Amun_Exception($error);
							}
						}
						else
						{
							// increase login attempt
							$this->attempt->increase();

							// if none assign captcha
							if($this->stage == Amun_Service_My_Attempt::NONE)
							{
								$captcha = $this->config['psx_url'] . '/' . $this->config['psx_dispatch'] . 'api/system/captcha';

								$this->template->assign('captcha', $captcha);
							}
						}
					}
				}

				throw new Amun_Exception('Invalid credentials');
			}
			else
			{
				$identity = $openid->getIdentifier();

				if(!empty($identity))
				{
					// here we can add addition extensions despite what
					// informations we need from the user
					$sreg = new PSX_OpenId_Extension_Sreg(array('fullname', 'nickname', 'gender', 'timezone'));

					if($openid->hasExtension($sreg->getNs()))
					{
						$openid->add($sreg);
					}
					else
					{
						$ax = new PSX_OpenId_Extension_Ax(array(

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
					throw new Amun_Exception('Invalid identity');
				}
			}
		}
		catch(Exception $e)
		{
			$this->template->assign('error', $e->getMessage());
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
	private function isOpenidProvider($identity)
	{
		// add http prefix if its not an email
		if(strpos($identity, '@') === false && substr($identity, 0, 7) != 'http://' && substr($identity, 0, 8) != 'https://')
		{
			$identity = 'http://' . $identity;
		}


		// build callback
		$callback = $this->page->url . '/login/callback';


		// create an openid object
		$http   = new PSX_Http(new PSX_Http_Handler_Curl());
		$store  = new PSX_OpenId_Store_Sql($this->sql, $this->registry['table.system_assoc']);
		$openid = new PSX_OpenId($http, $this->config['psx_url'], $store);


		// check whether identity is an url if not it is an email
		$filter = new PSX_Filter_Url();

		if($filter->apply($identity) !== false)
		{
			$openid->initialize($identity, $callback);

			return $openid;
		}
		else
		{
			$pos      = strpos($identity, '@');
			$provider = substr($identity, $pos + 1);

			// we check whether the email provider is an known openid porivder
			switch($provider)
			{
				case 'googlemail.com':
				case 'gmail.com':

					$openid = new PSX_OpenId_Op_Google($http, $this->config['psx_url'], $store);

					$openid->initialize($identity, $callback);

					return $openid;

					break;

				case 'yahoo.com':

					$openid = new PSX_OpenId_Op_Yahoo($http, $this->config['psx_url'], $store);

					$openid->initialize($identity, $callback);

					return $openid;

					break;

				case 'aol.com':
				case 'aim.com':

					$openid = new PSX_OpenId_Op_Aol($http, $this->config['psx_url'], $store);

					$openid->initialize($identity, $callback);

					return $openid;

					break;

				default:

					// check whether the provider belongs to an connected website. If
					// yes we also try to get an token and tokenSecret for the user
					$host = Amun_Sql_Table_Registry::get('System_Host')
						->select(array('id', 'consumerKey', 'url', 'template'))
						->where('name', '=', $provider)
						->where('status', '=', Amun_System_Host::NORMAL)
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

							$requestId = Amun_Sql_Table_Registry::get('System_Host_Request')->getField('id', $con);

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

					break;
			}

			// @todo we could make an webfinger request to get more informations
			// about the user ...
		}

		return false;
	}

	private function isValidStatus($status)
	{
		switch($status)
		{
			case Amun_User_Account::NORMAL:
			case Amun_User_Account::ADMINISTRATOR:

				return true;

				break;

			case Amun_User_Account::NOT_ACTIVATED:

				return 'Account is not activated';

				break;

			case Amun_User_Account::BANNED:

				return 'Account is banned';

				break;

			case Amun_User_Account::RECOVER:

				return 'Account is under recovery';

				break;

			default:

				return 'Unknown status';

				break;
		}
	}

	/**
	 * Validates whether the redirect url has the same base url as defined in
	 * the config because else this would be a vector for fishing since we can
	 * set the redirect with an get param i.e. ?redirect=[url]
	 *
	 * @return string|false
	 */
	private function getRedirect(PSX_Input $input)
	{
		$redirect = $input->redirect('string', array(new PSX_Filter_Urldecode(), new PSX_Filter_Length(8, 1024), new PSX_Filter_Url()));
		$base     = $this->config['psx_url'];

		if(!empty($redirect) && strcasecmp(substr($redirect, 0, strlen($base)), $base) == 0)
		{
			return $redirect;
		}
		else
		{
			return false;
		}
	}
}

