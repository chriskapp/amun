<?php
/*
 *  $Id: Relation.php 635 2012-05-01 19:46:37Z k42b3.x@googlemail.com $
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
 * Amun_Relation
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   Amun
 * @package    Amun_Relation
 * @version    $Revision: 635 $
 */
class Amun_Relation
{
	const NS = 'http://ns.amun-project.org/2011/amun/user/friend/relation/1.0';

	const REQUEST = 0x1;
	const ACCEPT  = 0x2;
	const REMOVE  = 0x3;

	private $http;
	private $oauth;
	private $cred;

	public function __construct(PSX_Http $http, PSX_Oauth_Provider_Data_Consumer $consumer = null)
	{
		$this->http = $http;

		if($consumer !== null)
		{
			$this->oauth = new PSX_Oauth($http);
			$this->cred  = $consumer;
		}
	}

	public function request(PSX_Url $url, $mode, $host, $name, $header = array())
	{
		if(empty($mode) || ($mode = self::getMode($mode)) === false)
		{
			throw new Amun_Exception('Invalid mode');
		}


		// discover service
		$url = $this->discover($url);


		// headers
		if($this->oauth !== null)
		{
			$header = PSX_Http_Request::mergeHeader(array(

				'Accept' => 'application/json',
				'Authorization' => $this->oauth->getAuthorizationHeader($url, $this->cred->getConsumerKey(), $this->cred->getConsumerSecret(), $this->cred->getToken(), $this->cred->getTokenSecret(), 'HMAC-SHA1', 'POST'),

			), $header);
		}
		else
		{
			$header = PSX_Http_Request::mergeHeader(array(

				'Accept' => 'application/json',

			), $header);
		}


		// body
		$body = array(

			'relation.ns'   => self::NS,
			'relation.mode' => $mode,
			'relation.host' => $host,
			'relation.name' => $name,

		);


		$request  = new PSX_Http_PostRequest($url, $header, $body);
		$response = $this->http->request($request);

		if($response->getCode() == 200)
		{
			$data = PSX_Json::decode($response->getBody());

			if(isset($data['success']) && $data['success'] === true)
			{
				return true;
			}
			else
			{
				$msg = isset($data['text']) ? $data['text'] : 'An error occured';

				throw new PSX_Data_Exception($msg);
			}
		}
		else
		{
			throw new PSX_Data_Exception('Invalid response code ' . $response->getCode());
		}
	}

	public function discover(PSX_Url $url)
	{
		$yadis = new PSX_Yadis($this->http);
		$xrds  = $yadis->discover($url);

		if($xrds !== false && isset($xrds->service))
		{
			$uri = null;

			foreach($xrds->service as $service)
			{
				if(in_array(self::NS, $service->getType()))
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

	public static function getMode($mode = false)
	{
		$s = array(

			self::REQUEST => 'request',
			self::ACCEPT  => 'accept',
			self::REMOVE  => 'remove',

		);

		if($mode !== false)
		{
			$mode = intval($mode);

			if(array_key_exists($mode, $s))
			{
				return $s[$mode];
			}
			else
			{
				return false;
			}
		}
		else
		{
			return $s;
		}
	}
}
