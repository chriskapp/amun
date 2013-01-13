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
	private $attempt;
	private $stage;

	public function onLoad()
	{
		parent::onLoad();

		if($this->getProvider()->hasViewRight())
		{
			// assign redirect
			$this->template->assign('redirect', $this->getRedirect($this->get));

			// add path
			$this->path->add('Login', $this->page->url . '/login');

			// load supported provider
			$provider = array_map('json_encode', array_map('trim', explode(',', $this->registry['my.openid_provider'])));

			$this->template->assign('provider', $provider);

			// check login attempts
			$this->attempt = new AmunService_My_Attempt($this->registry);
			$this->stage   = $this->attempt->getStage();

			if($this->stage == AmunService_My_Attempt::TRYING)
			{
				$captcha = $this->config['psx_url'] . '/' . $this->config['psx_dispatch'] . 'api/core/captcha';

				$this->template->assign('captcha', $captcha);
			}
			else if($this->stage == AmunService_My_Attempt::ABUSE)
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
		$identity = $this->post->identity('string', array(new AmunService_User_Account_Filter_Identity()));
		$pw       = $this->post->pw('string', array(new AmunService_User_Account_Filter_Pw()));
		$captcha  = $this->post->captcha('integer');

		try
		{
			if(empty($identity))
			{
				throw new Amun_Exception('Invalid identity');
			}

			// check captcha if needed
			if($this->stage == AmunService_My_Attempt::TRYING)
			{
				if(!Amun_Captcha::factory($this->config['amun_captcha'])->verify($captcha))
				{
					throw new PSX_Data_Exception('Invalid captcha');
				}
			}

			$handles = array('ldap');//, 'system', 'github', 'twitter', 'google', 'yahoo', 'openid');

			foreach($handles as $handler)
			{
				$handler = AmunService_My_LoginHandlerFactory::factory($handler);

				if($handler instanceof AmunService_My_LoginHandlerAbstract && $handler->isValid($identity))
				{
					$handler->setPageUrl($this->page->url);

					try
					{
						if($handler->handle($identity, $pw) === true)
						{
							// clear attempts
							if($this->stage != AmunService_My_Attempt::NONE)
							{
								$this->attempt->clear();
							}

							// redirect
							$url = $redirect === false ? $this->config['psx_url'] : $redirect;

							header('Location: ' . $url);
							exit;

							break;
						}
					}
					catch(AmunService_My_Login_InvalidPasswordException $e)
					{
						// increase login attempt
						$this->attempt->increase();

						// if none assign captcha
						if($this->stage == AmunService_My_Attempt::NONE)
						{
							$captcha = $this->config['psx_url'] . '/' . $this->config['psx_dispatch'] . 'api/core/captcha';

							$this->template->assign('captcha', $captcha);
						}
					}
				}
			}

			throw new Exception('Authentication failed');
		}
		catch(Exception $e)
		{
			$this->template->assign('error', $e->getMessage());
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

