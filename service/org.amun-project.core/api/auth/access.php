<?php
/*
 *  $Id: access.php 880 2012-10-27 13:14:26Z k42b3.x@googlemail.com $
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
 * access
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   module
 * @package    api
 * @subpackage auth
 * @version    $Revision: 880 $
 */
class access extends PSX_Oauth_Provider_AccessAbstract
{
	private $requestId;
	private $nonce;
	private $verifier;

	public function getDependencies()
	{
		return new Amun_Dependency_Default();
	}

	public function onLoad()
	{
		try
		{
			$this->handle();
		}
		catch(Exception $e)
		{
			header('HTTP/1.1 500 Internal Server Error');

			echo $e->getMessage();

			if($this->config['psx_debug'] === true)
			{
				echo "\n\n" . $e->getTraceAsString();
			}

			exit;
		}
	}

	protected function getConsumer($consumerKey, $token)
	{
		$sql = <<<SQL
SELECT

	api.id             AS `apiId`,
	api.callback       AS `apiCallback`,
	api.consumerKey    AS `apiConsumerKey`,
	api.consumerSecret AS `apiConsumerSecret`

	FROM {$this->registry['table.core_system_api']} api

		WHERE api.consumerKey = ?

		LIMIT 1
SQL;

		$result = $this->sql->getAll($sql, array($consumerKey));

		foreach($result as $row)
		{
			$request = $this->fetchRequestValues($token);

			if(empty($request))
			{
				throw new PSX_Oauth_Exception('Invalid request');
			}

			// check whether the request token was requested
			// from the same ip
			if($request['requestIp'] != $_SERVER['REMOTE_ADDR'])
			{
				throw new PSX_Oauth_Exception('Token was requested from another ip');
			}

			// check whether the request is assigned
			// to this api
			if($row['apiId'] != $request['requestApiId'])
			{
				throw new PSX_Oauth_Exception('Request is not assigned to this API');
			}

			// check expire
			$now  = new DateTime('NOW', $this->registry['core.default_timezone']);
			$date = new DateTime($request['requestDate'], $this->registry['core.default_timezone']);
			$date->add(new DateInterval($request['requestExpire']));

			if($now > $date)
			{
				$con = new PSX_Sql_Condition(array('token', '=', $token));

				$this->sql->delete($this->registry['table.core_system_api_request'], $con);

				throw new PSX_Oauth_Exception('The token is expired');
			}


			$this->requestId = $request['requestId'];
			$this->nonce     = $request['requestNonce'];
			$this->verifier  = $request['requestVerifier'];


			return new PSX_Oauth_Provider_Data_Consumer($row['apiConsumerKey'], $row['apiConsumerSecret'], $request['requestToken'], $request['requestTokenSecret']);
		}
	}

	protected function getResponse(PSX_Oauth_Provider_Data_Consumer $consumer, PSX_Oauth_Provider_Data_Request $request)
	{
		if($this->nonce == $request->getNonce())
		{
			throw new PSX_Oauth_Exception('Nonce hasnt changed');
		}

		if($this->verifier != $request->getVerifier())
		{
			throw new PSX_Oauth_Exception('Invalid verifier');
		}


		// the access token can be used six month
		$expire = 'P6M';


		// generate a new access token
		$token       = Amun_Security::generateToken();
		$tokenSecret = Amun_Security::generateToken();

		$date = new DateTime('NOW', $this->registry['core.default_timezone']);
		$con  = new PSX_Sql_Condition(array('id', '=', $this->requestId));

		$this->sql->update($this->registry['table.core_system_api_request'], array(

			'status'      => Amun_System_Api::ACCESS,
			'token'       => $token,
			'tokenSecret' => $tokenSecret,
			'expire'      => $expire,
			'date'        => $date->format(PSX_DateTime::SQL),

		), $con);


		$response = new PSX_Oauth_Provider_Data_Response();
		$response->setToken($token);
		$response->setTokenSecret($tokenSecret);

		return $response;
	}

	private function fetchRequestValues($token)
	{
		$sql = <<<SQL
SELECT

	request.id          AS `requestId`,
	request.apiId       AS `requestApiId`,
	request.status      AS `requestStatus`,
	request.ip          AS `requestIp`,
	request.nonce       AS `requestNonce`,
	request.callback    AS `requestCallback`,
	request.token       AS `requestToken`,
	request.tokenSecret AS `requestTokenSecret`,
	request.verifier    AS `requestVerifier`,
	request.expire      AS `requestExpire`,
	request.date        AS `requestDate`

	FROM {$this->registry['table.core_system_api_request']} request

		WHERE request.token = ?

		AND request.status = ?

			LIMIT 1
SQL;

		return $this->sql->getRow($sql, array($token, Amun_System_Api::APPROVED));
	}
}

