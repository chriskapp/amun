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

namespace my\application;

use Amun\Module\ApplicationAbstract;
use Amun\Exception;
use Amun\Captcha;
use AmunService\My\Attempt;
use AmunService\User\Account;
use AmunService\My\LoginHandlerFactory;
use AmunService\My\LoginHandlerAbstract;
use AmunService\My\Login\InvalidPasswordException;
use PSX\Filter;
use PSX\Input;

/**
 * login
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/gpl.html GPLv3
 * @link    http://amun.phpsx.org
 */
class login extends ApplicationAbstract
{
	private $attempt;
	private $stage;

	public function onLoad()
	{
		parent::onLoad();

		if($this->user->hasRight('my_view'))
		{
			// assign redirect
			$this->template->assign('redirect', $this->getRedirect($this->get));

			// add path
			$this->path->add('Login', $this->page->url . '/login');

			// check login attempts
			$this->attempt = new Attempt($this->registry);
			$this->stage   = $this->attempt->getStage();

			if($this->stage == Attempt::TRYING)
			{
				$captcha = $this->config['psx_url'] . '/' . $this->config['psx_dispatch'] . 'api/core/captcha';

				$this->template->assign('captcha', $captcha);
			}
			else if($this->stage == Attempt::ABUSE)
			{
				throw new Exception('Your IP ' . $_SERVER['REMOTE_ADDR'] . ' is banned for 30 minutes because of too many wrong logins');
			}

			// template
			$this->htmlCss->add('my');
			$this->htmlJs->add('amun');
			$this->htmlJs->add('my');
		}
		else
		{
			throw new Exception('Access not allowed');
		}
	}

	public function onPost()
	{
		if($this->post->register('string', array(), null, null, false))
		{
			header('Location: ' . $this->page->url . '/register');
			exit;
		}

		$redirect = $this->getRedirect($this->post);
		$identity = $this->post->identity('string', array(new Account\Filter\Identity()));
		$pw       = $this->post->pw('string', array(new Account\Filter\Pw()));
		$captcha  = $this->post->captcha('integer');

		try
		{
			if(empty($identity))
			{
				throw new Exception('Invalid identity');
			}

			// check captcha if needed
			if($this->stage == Attempt::TRYING)
			{
				if(!Captcha::factory($this->config['amun_captcha'])->verify($captcha))
				{
					throw new Exception('Invalid captcha');
				}
			}

			// load handles
			$handles = array_map('trim', explode(',', $this->registry['my.login_provider']));

			foreach($handles as $handler)
			{
				$handler = LoginHandlerFactory::factory($handler);

				if($handler instanceof LoginHandlerAbstract && $handler->isValid($identity))
				{
					$handler->setPageUrl($this->page->url);

					if($handler->hasPassword() && empty($pw))
					{
						throw new Exception('Invalid password');
					}

					try
					{
						if($handler->handle($identity, $pw) === true)
						{
							// clear attempts
							if($this->stage != Attempt::NONE)
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
					catch(InvalidPasswordException $e)
					{
						// increase login attempt
						$this->attempt->increase();

						// if none assign captcha
						if($this->stage == Attempt::NONE)
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
	private function getRedirect(Input $input)
	{
		$redirect = $input->redirect('string', array(new Filter\Urldecode(), new Filter\Length(8, 1024), new Filter\Url()), 'redirect', 'Redirect', false);
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

