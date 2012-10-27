<?php
/*
 *  $Id: ReCaptcha.php 635 2012-05-01 19:46:37Z k42b3.x@googlemail.com $
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
 * Amun_Captcha_Provider_ReCaptcha
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   Amun
 * @package    Amun_Captcha
 * @version    $Revision: 635 $
 */
class Amun_Captcha_Provider_ReCaptcha implements Amun_Captcha_ProviderInterface
{
	private $publicKey  = '';
	private $privateKey = '';

	public function verify($result)
	{
		if(isset($_SESSION['amun_recaptcha_challenge']))
		{
			$challenge = $_SESSION['amun_recaptcha_challenge'];
		}
		else
		{
			throw new Amun_Captcha_Exception('Challenge not set');
		}

		if(!empty($challenge))
		{
			$http    = new PSX_Http(new PSX_Http_Handler_Curl());
			$url     = new PSX_Url('http://www.google.com/recaptcha/api/verify');
			$request = new PSX_Http_PostRequest($url, array(), array(

				'privatekey' => $this->privateKey,
				'remoteip'   => $_SERVER['REMOTE_ADDR'],
				'challenge'  => $challenge,
				'response'   => $result,

			));

			$response = $http->request($request);

			return strcmp(trim($response->getBody()), 'true') == 0;
		}
		else
		{
			return false;
		}
	}

	public function serve()
	{
		$http    = new PSX_Http(new PSX_Http_Handler_Curl());
		$url     = new PSX_Url('http://www.google.com/recaptcha/api/challenge?k=' . $this->publicKey . '&ajax=1&cachestop=' . time());
		$request = new PSX_Http_GetRequest($url);

		$response = $http->request($request);

		if($response->getCode() == 200)
		{
			// parse response body
			$data = $this->parseResponseBody($response->getBody());

			if(isset($data['programming_error']))
			{
				throw new Amun_Captcha_Exception($data['programming_error']);
			}

			if(isset($data['error_message']))
			{
				throw new Amun_Captcha_Exception($data['error_message']);
			}

			if(isset($data['site']) && isset($data['challenge']))
			{
				$_SESSION['amun_recaptcha_challenge'] = $data['challenge'];

				header('HTTP/1.1 307 Temporary Redirect');
				header('Location: http://www.google.com/recaptcha/api/image?c=' . urlencode($data['challenge']) . '&cachestop=' . time());
			}
			else
			{
				throw new Amun_Captcha_Exception('Couldnt parse response body');
			}
		}
		else
		{
			throw new Amun_Captcha_Exception('Invalid recaptcha response code');
		}
	}

	private function parseResponseBody($body)
	{
		$data = array();

		$s_pos = strpos($body, '{');
		$e_pos = strpos($body, '}');

		if($s_pos !== false && $e_pos !== false)
		{
			$content  = substr($body, $s_pos + 1, $e_pos - $s_pos - 1);
			$lines    = explode(',', $content);
			$charlist = " " . "\t" . "\n" . "\r" . "\0" . "\x0B" . "'";

			foreach($lines as $line)
			{
				$pair = explode(':', $line, 2);

				if(count($pair) == 2)
				{
					$k = trim($pair[0], $charlist);
					$v = trim($pair[1], $charlist);

					if(!empty($v))
					{
						$data[$k] = $v;
					}
				}
			}
		}

		return $data;
	}
}
