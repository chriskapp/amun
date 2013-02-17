<?php
/*
 *  $Id: signon.php 875 2012-09-30 13:51:45Z k42b3.x@googlemail.com $
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

namespace openid\api;

use Amun_DataFactory;
use Amun_Dependency_Session;
use Amun_Exception;
use DateTime;
use Exception;
use PSX_DateTime;
use PSX_OpenId_ProviderAbstract;
use PSX_OpenId_Provider_Data_Association;
use PSX_OpenId_Provider_Data_ResRequest;
use PSX_OpenId_Provider_Data_SetupRequest;
use PSX_Url;

/**
 * signon
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   module
 * @package    api
 * @subpackage service_my
 * @version    $Revision: 875 $
 */
class signon extends PSX_OpenId_ProviderAbstract
{
	const EXPIRE = 46800; // seconds

	/**
	 * OpenId endpoint for indirect communication like HTTP redirect
	 *
	 * @httpMethod GET
	 * @path /
	 * @nickname indirectCommunication
	 * @responseClass PSX_Data_ResultSet
	 */
	public function indirectCommunication()
	{
		$this->handle();
	}

	/**
	 * OpenId endpoint for direct communication or HTML form submission
	 *
	 * @httpMethod POST
	 * @path /
	 * @nickname directCommunication
	 * @responseClass PSX_Data_ResultSet
	 */
	public function directCommunication()
	{
		$this->handle();
	}

	public function getDependencies()
	{
		$ct = new Amun_Dependency_Session($this->base->getConfig());

		return $ct;
	}

	public function onAsocciation(PSX_OpenId_Provider_Data_Association $assoc)
	{
		$sql = <<<SQL
SELECT

	`assoc`.`id`,
	`assoc`.`assocHandle`,
	`assoc`.`expires`,
	`assoc`.`date`

	FROM {$this->registry['table.openid_assoc']} `assoc`

		WHERE `assoc`.`assocHandle` = ?
SQL;

		$row = $this->sql->getRow($sql, array($assoc->getAssocHandle()));

		if(empty($row))
		{
			$date = new DateTime('NOW', $this->registry['core.default_timezone']);

			$this->sql->insert($this->registry['table.openid_assoc'], array(

				'assocHandle' => $assoc->getAssocHandle(),
				'assocType'   => $assoc->getAssocType(),
				'sessionType' => $assoc->getSessionType(),
				'secret'      => $assoc->getSecret(),
				'expires'     => self::EXPIRE,
				'date'        => $date->format(PSX_DateTime::SQL),

			));

			return self::EXPIRE;
		}
		else
		{
			throw new Amun_Exception('Association already exists');
		}
	}

	public function onCheckidSetup(PSX_OpenId_Provider_Data_SetupRequest $request)
	{
		// check whether authenticated
		if(!$this->isAuthenticated())
		{
			$loginUrl = $this->config['psx_url'] . '/' . $this->config['psx_dispatch'] . 'my.htm/login';
			$selfUrl  = new PSX_Url($this->base->getSelf());
			$values   = array_merge($_GET, $_POST);

			foreach($values as $key => $value)
			{
				$selfUrl->addParam($key, $value);
			}

			//$selfUrl->addParam('openid.mode', 'checkid_setup');
			//$selfUrl->addParam('openid.ns', self::NS);

			header('Location: ' . $loginUrl . '?redirect=' . urlencode(strval($selfUrl)));
			exit;
		}


		// check association
		$sql = <<<SQL
SELECT

	`assoc`.`id`,
	`assoc`.`expires`,
	`assoc`.`date`

	FROM {$this->registry['table.openid_assoc']} `assoc`

		WHERE `assoc`.`assocHandle` = ?
SQL;

		$row = $this->sql->getRow($sql, array($request->getAssocHandle()));

		if(!empty($row))
		{
			// check expire
			$now    = new DateTime('NOW', $this->registry['core.default_timezone']);
			$expire = (integer) $row['expires'];

			if(time() > $now->getTimestamp() + $expire)
			{
				throw new Amun_Exception('Association is expired');
			}
		}
		else
		{
			if(!$request->isImmediate())
			{
				// create association
				$date        = new DateTime('NOW', $this->registry['core.default_timezone']);
				$assocHandle = PSX_OpenId_ProviderAbstract::generateHandle();
				$secret      = base64_encode(PSX_OpenId_ProviderAbstract::randomBytes(20));

				$this->sql->insert($this->registry['table.openid_assoc'], array(

					'assocHandle' => $assocHandle,
					'assocType'   => 'HMAC-SHA1',
					'sessionType' => 'DH-SHA1',
					'secret'      => $secret,
					'expires'     => self::EXPIRE,
					'date'        => $date->format(PSX_DateTime::SQL),

				));

				// set assoc handle
				$request->setAssocHandle($assocHandle);
			}
			else
			{
				throw new Amun_Exception('Invalid association');
			}
		}


		// count connect requests
		/*
		$maxCount = 5;
		$con      = new PSX_Sql_Condition(array('userId', '=', $this->user->id), array('status', '=', AmunService_Oauth_Record::TEMPORARY));
		$count    = $this->sql->count($this->registry['table.oauth_request'], $con);

		if($count > $maxCount)
		{
			$conDelete = new PSX_Sql_Condition();
			$result    = $this->sql->select($this->registry['table.oauth_request'], array('id', 'expire', 'date'), $con, PSX_Sql::SELECT_ALL);

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
				$this->sql->delete($this->registry['table.oauth_request'], $conDelete);
			}

			throw new Amun_Exception('You can have max ' . $maxCount . ' temporary account connect requests. Each request expires after 30 hour');
		}
		*/


		// save request params
		$_SESSION['amun_openid_request'] = $request;


		// redirect
		header('Location: ' . $this->config['psx_url'] . '/' . $this->config['psx_dispatch'] . 'my.htm/connect');
		exit;
	}

	public function onCheckAuthentication(PSX_OpenId_Provider_Data_ResRequest $request)
	{
		$sql = <<<SQL
SELECT

	`assoc`.`id`,
	`assoc`.`assocHandle`,
	`assoc`.`assocType`,
	`assoc`.`sessionType`,
	`assoc`.`secret`,
	`assoc`.`expires`,
	`assoc`.`date`

	FROM {$this->registry['table.openid_assoc']} `assoc`

		WHERE `assoc`.`assocHandle` = ?
SQL;

		$row = $this->sql->getRow($sql, array($request->getAssocHandle()));

		if(!empty($row))
		{
			return $request->isValidSignature($row['secret'], $row['assocType']);
		}
		else
		{
			throw new Amun_Exception('Invalid association');
		}
	}

	protected function handle()
	{
		try
		{
			parent::handle();
		}
		catch(Exception $e)
		{
			echo $e->getMessage();

			if($this->config['psx_debug'] === true)
			{
				echo "\n\n" . $e->getTraceAsString();
			}

			exit;
		}
	}

	protected function isAuthenticated()
	{
		return $this->session->has('amun_id') && !$this->user->isAnonymous();
	}
}

