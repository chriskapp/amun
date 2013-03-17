<?php
/*
 *  $Id: Oauth.php 652 2012-05-06 19:01:25Z k42b3.x@googlemail.com $
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

namespace Amun;

use Amun\Dependency;
use AmunService\Oauth\Record;
use DateInterval;
use DateTime;
use PSX\Oauth\ProviderAbstract;
use PSX\Oauth\Provider\Data\Consumer;
use PSX\Loader\Location;

/**
 * Amun_Oauth
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   Amun
 * @package    Amun_Oauth
 * @version    $Revision: 652 $
 */
class Oauth extends ProviderAbstract
{
	protected $claimedUserId;
	protected $requestId;
	protected $requestToken;
	protected $accessId;

	protected $config;
	protected $sql;
	protected $registry;

	public function __construct(Location $location, \PSX\Base $base, $basePath, array $uriFragments)
	{
		parent::__construct($location, $base, $basePath, $uriFragments);

		$container = new Dependency\Request($this->base->getConfig());

		$this->config   = $container->getConfig();
		$this->sql      = $container->getSql();
		$this->registry = $container->getRegistry();
	}

	public function doAuthentication()
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

	public function getConsumer($consumerKey, $token)
	{
		$sql = <<<SQL
SELECT
	`api`.`id`             AS `apiId`,
	`api`.`consumerKey`    AS `apiConsumerKey`,
	`api`.`consumerSecret` AS `apiConsumerSecret`
FROM 
	{$this->registry['table.oauth']} api
WHERE 
	`api`.`consumerKey` = ?
LIMIT 1
SQL;

		$row = $this->sql->getRow($sql, array($consumerKey));

		if(!empty($row))
		{
			$request = $this->fetchRequestValues($token);

			// check whether the request token was requested
			// from the same ip
			if($request['requestIp'] != $_SERVER['REMOTE_ADDR'])
			{
				// we can not do this so strictly because most applications
				// changes often the ip
				//throw new PSX_Oauth_Exception('Token was requested from another ip');
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
				$this->sql->delete($this->registry['table.oauth_request'], 'token', $token);

				throw new Exception('The token is expired');
			}

			$this->claimedUserId = $request['requestUserId'];

			return new Consumer($row['apiConsumerKey'], $row['apiConsumerSecret'], $request['requestToken'], $request['requestTokenSecret']);
		}
		else
		{
			throw new Exception('Invalid consumer key');
		}
	}

	private function fetchRequestValues($token)
	{
		$sql = <<<SQL
SELECT
	`request`.`id`          AS `requestId`,
	`request`.`apiId`       AS `requestApiId`,
	`request`.`userId`      AS `requestUserId`,
	`request`.`status`      AS `requestStatus`,
	`request`.`ip`          AS `requestIp`,
	`request`.`nonce`       AS `requestNonce`,
	`request`.`callback`    AS `requestCallback`,
	`request`.`token`       AS `requestToken`,
	`request`.`tokenSecret` AS `requestTokenSecret`,
	`request`.`verifier`    AS `requestVerifier`,
	`request`.`expire`      AS `requestExpire`,
	`request`.`date`        AS `requestDate`
FROM 
	{$this->registry['table.oauth_request']} `request`
WHERE 
	`request`.`token` = ?
AND 
	`request`.`status` = ?
LIMIT 1
SQL;

		$row = $this->sql->getRow($sql, array($token, Record::ACCESS));

		if(!empty($row))
		{
			$this->requestId    = $row['requestId'];
			$this->requestToken = $row['requestToken'];


			// check whether request is allowed
			$sql = <<<SQL
SELECT
	`access`.`id`      AS `accessId`,
	`access`.`allowed` AS `accessAllowed`
FROM 
	{$this->registry['table.oauth_access']} `access`
WHERE 
	`access`.`apiId` = ?
AND 
	`access`.`userId` = ?
SQL;

			$access = $this->sql->getRow($sql, array($row['requestApiId'], $row['requestUserId']));

			if($access['accessAllowed'] === '1')
			{
				$this->accessId = $access['accessId'];

				return $row;
			}
			else
			{
				throw new PSX_Oauth_Exception('Access was rejected');
			}
		}
		else
		{
			throw new PSX_Oauth_Exception('Invalid request');
		}
	}

	public function onAuthenticated()
	{
	}
}
