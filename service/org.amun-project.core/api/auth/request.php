<?php
/*
 *  $Id: request.php 880 2012-10-27 13:14:26Z k42b3.x@googlemail.com $
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
 * request
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   module
 * @package    api
 * @subpackage auth
 * @version    $Revision: 880 $
 */
class request extends PSX_Oauth_Provider_RequestAbstract
{
	protected $sql;
	protected $registry;
	protected $validate;
	protected $get;
	protected $post;

	private $apiId;
	private $callback;

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

	protected function getConsumer($consumerKey)
	{
		$sql = <<<SQL
SELECT

	api.id             AS `apiId`,
	api.consumerKey    AS `apiConsumerKey`,
	api.consumerSecret AS `apiConsumerSecret`

	FROM {$this->registry['table.core_system_api']} api

		WHERE api.consumerKey = ?

		LIMIT 1
SQL;

		$result = $this->sql->getAll($sql, array($consumerKey));

		foreach($result as $row)
		{
			$this->apiId = $row['apiId'];

			return new PSX_Oauth_Provider_Data_Consumer($row['apiConsumerKey'], $row['apiConsumerSecret']);
		}
	}

	protected function getResponse(PSX_Oauth_Provider_Data_Consumer $consumer, PSX_Oauth_Provider_Data_Request $request)
	{
		// we check how often this ip has requested an token ... because
		// of security reasons each consumer can have max 5 request tokens
		$maxCount = 5;
		$ip       = $_SERVER['REMOTE_ADDR'];
		$con      = new PSX_Sql_Condition(array('ip', '=', $ip), array('status', '=', AmunService_Core_System_Api_Record::TEMPORARY));
		$count    = $this->sql->count($this->registry['table.core_system_api_request'], $con);

		if($count >= $maxCount)
		{
			$conDelete = new PSX_Sql_Condition();
			$result    = $this->sql->select($this->registry['table.core_system_api_request'], array('id', 'expire', 'date'), $con, PSX_Sql::SELECT_ALL);

			foreach($result as $row)
			{
				$now  = new DateTime('NOW', $this->registry['core.default_timezone']);
				$date = new DateTime($row['date'], $this->registry['core.default_timezone']);
				$date->add(new DateInterval($row['expire']));

				if($now > $date)
				{
					$conDelete->add('id', '=', $row['id'], 'OR');
				}
			}

			if($conDelete->hasCondition())
			{
				$this->sql->delete($this->registry['table.core_system_api_request'], $conDelete);
			}

			throw new PSX_Oauth_Exception('You can only have max. ' . $maxCount . ' active request tokens');
		}


		// get nonce
		$nonce = $request->getNonce();


		// assign callback
		$callback = $request->getCallback();


		// generate tokens
		$token       = Amun_Security::generateToken();
		$tokenSecret = Amun_Security::generateToken();


		// we save the timestamp in the request but because it comes from
		// the user we doesnt use them to check the expire date
		$timestamp = $request->getTimestamp();


		// you have 30 minutes to authorize the request token and to exchange
		// them for an access token
		$expire = 'PT30M';


		$date = new DateTime('NOW', $this->registry['core.default_timezone']);

		$this->sql->insert($this->registry['table.core_system_api_request'], array(

			'apiId'       => $this->apiId,
			'status'      => AmunService_Core_System_Api_Record::TEMPORARY,
			'ip'          => $ip,
			'nonce'       => $nonce,
			'callback'    => $callback,
			'token'       => $token,
			'tokenSecret' => $tokenSecret,
			'timestamp'   => $timestamp,
			'expire'      => $expire,
			'date'        => $date->format(PSX_DateTime::SQL),

		));


		$response = new PSX_Oauth_Provider_Data_Response();
		$response->setToken($token);
		$response->setTokenSecret($tokenSecret);

		return $response;
	}
}
