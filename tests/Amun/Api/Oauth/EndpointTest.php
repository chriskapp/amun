<?php
/*
 *  $Id: RequestTest.php 637 2012-05-01 19:58:47Z k42b3.x@googlemail.com $
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

namespace Amun\Api\Oauth;

use Amun\Api\RestTest;
use Amun\DataFactory;
use Amun\Security;
use AmunService\Oauth;
use PSX\Yadis;
use PSX\Url;
use PSX\Sql\Condition;

/**
 * Amun_Api_System_Api_RequestTest
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   tests
 * @version    $Revision: 637 $
 * @backupStaticAttributes disabled
 */
class EndpointTest extends RestTest
{
	protected function setUp()
	{
		parent::setUp();

		if(!$this->hasService('org.amun-project.oauth'))
		{
			$this->markTestSkipped('Service oauth not installed');
		}
	}

	public function getDataSet()
	{
		return $this->createMySQLXMLDataSet('tests/amun.xml');
	}

	public function getEndpoint()
	{
		return $this->config['psx_url'] . '/' . $this->config['psx_dispatch'] . 'api/oauth';
	}

	public function getTable()
	{
		return DataFactory::getTable('Oauth');
	}

	public function testRequest()
	{
		// discover endpoints
		$yadis = new Yadis($this->http);
		$xrds  = $yadis->discover(new Url($this->config['psx_url']));

		// get oauth request uri
		$requestUri = null;

		foreach($xrds->service as $service)
		{
			if(in_array('http://oauth.net/core/1.0/endpoint/request', $service->getType()))
			{
				$requestUri = $service->getUri();
				break;
			}
		}

		$this->assertEquals(true, !empty($requestUri), 'Could not find http://oauth.net/core/1.0/endpoint/request in xrds');

		// get request token
		$response = $this->oauth->requestToken(new Url($requestUri), CONSUMER_KEY, CONSUMER_SECRET);

		$this->assertEquals(true, strlen($response->getToken()) > 4, $this->http->getResponse());
		$this->assertEquals(true, strlen($response->getTokenSecret()) > 4, $this->http->getResponse());

		$token       = $response->getToken();
		$tokenSecret = $response->getTokenSecret();

		// since we can not login and approve the request we do this manually in 
		// the table
		$verifier = Security::generateToken(32);
		$con      = new Condition(array('token', '=', $token));

		$this->sql->update($this->registry['table.oauth_request'], array(

			'userId'   => 1,
			'status'   => Oauth\Record::APPROVED,
			'verifier' => $verifier,

		), $con);

		// get oauth access uri
		$accessUri = null;

		foreach($xrds->service as $service)
		{
			if(in_array('http://oauth.net/core/1.0/endpoint/access', $service->getType()))
			{
				$accessUri = $service->getUri();
				break;
			}
		}

		$this->assertEquals(true, !empty($accessUri), 'Could not find http://oauth.net/core/1.0/endpoint/access in xrds');

		// get access token
		$response = $this->oauth->accessToken(new Url($accessUri), CONSUMER_KEY, CONSUMER_SECRET, $token, $tokenSecret, $verifier);

		$this->assertEquals(true, strlen($response->getToken()) > 4, $this->http->getResponse());
		$this->assertEquals(true, strlen($response->getTokenSecret()) > 4, $this->http->getResponse());
	}
}

