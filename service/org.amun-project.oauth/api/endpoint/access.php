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

namespace oauth\api\endpoint;

use AmunService\Oauth;
use Amun\DataFactory;
use Amun\Dependency;
use Amun\Security;
use Amun\Exception;
use DateInterval;
use PSX\DateTime;
use PSX\Oauth\Provider\AccessAbstract;
use PSX\Oauth\Provider\Data as Provider;
use PSX\Sql\Condition;

/**
 * access
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/gpl.html GPLv3
 * @link    http://amun.phpsx.org
 */
class access extends AccessAbstract
{
	private $requestId;
	private $nonce;
	private $verifier;

	/**
	 * Endpoint to exchange the temporary credentials for an token
	 *
	 * @httpMethod POST
	 * @path /
	 * @nickname doTokenRequest
	 * @responseClass PSX_Data_ResultSet
	 */
	public function doTokenRequest()
	{
		try
		{
			$this->handle();
		}
		catch(\Exception $e)
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

	public function getDependencies()
	{
		$ct = new Dependency\Request($this->base->getConfig());

		return $ct;
	}

	protected function getConsumer($consumerKey, $token)
	{
		$sql = <<<SQL
SELECT

	api.id             AS `apiId`,
	api.callback       AS `apiCallback`,
	api.consumerKey    AS `apiConsumerKey`,
	api.consumerSecret AS `apiConsumerSecret`

	FROM {$this->registry['table.oauth']} api

		WHERE api.consumerKey = ?

		LIMIT 1
SQL;

		$result = $this->sql->getAll($sql, array($consumerKey));

		foreach($result as $row)
		{
			$request = $this->fetchRequestValues($token);

			if(empty($request))
			{
				throw new Exception('Invalid request');
			}

			// check whether the request token was requested
			// from the same ip
			if($request['requestIp'] != $_SERVER['REMOTE_ADDR'])
			{
				throw new Exception('Token was requested from another ip');
			}

			// check whether the request is assigned
			// to this api
			if($row['apiId'] != $request['requestApiId'])
			{
				throw new Exception('Request is not assigned to this API');
			}

			// check expire
			$now  = new DateTime('NOW', $this->registry['core.default_timezone']);
			$date = new DateTime($request['requestDate'], $this->registry['core.default_timezone']);
			$date->add(new DateInterval($request['requestExpire']));

			if($now > $date)
			{
				$con = new Condition(array('token', '=', $token));

				$this->sql->delete($this->registry['table.oauth_request'], $con);

				throw new Exception('The token is expired');
			}


			$this->requestId = $request['requestId'];
			$this->nonce     = $request['requestNonce'];
			$this->verifier  = $request['requestVerifier'];


			return new Provider\Consumer($row['apiConsumerKey'], $row['apiConsumerSecret'], $request['requestToken'], $request['requestTokenSecret']);
		}
	}

	protected function getResponse(Provider\Consumer $consumer, Provider\Request $request)
	{
		if($this->nonce == $request->getNonce())
		{
			throw new Exception('Nonce hasnt changed');
		}

		if($this->verifier != $request->getVerifier())
		{
			throw new Exception('Invalid verifier');
		}


		// the access token can be used six month
		$expire = 'P6M';


		// generate a new access token
		$token       = Security::generateToken();
		$tokenSecret = Security::generateToken();

		$date = new DateTime('NOW', $this->registry['core.default_timezone']);
		$con  = new Condition(array('id', '=', $this->requestId));

		$this->sql->update($this->registry['table.oauth_request'], array(

			'status'      => Oauth\Record::ACCESS,
			'token'       => $token,
			'tokenSecret' => $tokenSecret,
			'expire'      => $expire,
			'date'        => $date->format(DateTime::SQL),

		), $con);


		$response = new Provider\Response();
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

	FROM {$this->registry['table.oauth_request']} request

		WHERE request.token = ?

		AND request.status = ?

			LIMIT 1
SQL;

		return $this->sql->getRow($sql, array($token, Oauth\Record::APPROVED));
	}
}

