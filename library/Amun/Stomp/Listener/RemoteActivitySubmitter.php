<?php
/*
 *  $Id: RemoteActivitySubmitter.php 635 2012-05-01 19:46:37Z k42b3.x@googlemail.com $
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

namespace Amun\Stomp\Listener;

use Amun\Data\RecordAbstract;
use Amun\Stomp\ListenerAbstract;
use Amun\Registry;
use Amun\Sql\Table;
use AmunService\User\Activity\Receiver;
use AmunService\User\Account;
use PSX\Http;
use PSX\Http\PostRequest;
use PSX\Oauth;
use PSX\Yadis;
use PSX\Sql;
use PSX\Url;
use PSX\DateTime;
use PSX\Data\Writer;
use PSX\Data\ResultSet

/**
 * Amun_Stomp_Listener_RemoteActivitySubmitter
 *
 * Sends activities wich are for an remote friend to the remote website
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   Amun
 * @package    Amun_Stomp
 * @version    $Revision: 635 $
 */
class RemoteActivitySubmitter extends ListenerAbstract
{
	const SERVICE_NS = 'http://ns.amun-project.org/2012/amun/user/activity/message/1.0';

	protected $http;
	protected $oauth;
	protected $yadis;

	public function __construct()
	{
		parent::__construct();

		$this->http  = new Http();
		$this->oauth = new Oauth($this->http);
		$this->yadis = new Yadis($this->http);
	}

	public function run($table, $type, $userId, array $data)
	{
		if($type != RecordAbstract::INSERT || $table != Registry::get('table.user_activity'))
		{
			return;
		}

		// get activities atom feed
		$body = $this->getMessage($data['id']);

		// send request
		$receivers = Table\Registry::get('User_Activity_Receiver')
			->select(array('status', 'activityId', 'userId'))
			->where('activityId', '=', $data['id'])
			->where('status', '=', Receiver::VISIBLE)
			->getAll(Sql::FETCH_OBJECT);

		foreach($receivers as $receiver)
		{
			// get receiver
			$account = $receiver->getUser();
			$host    = $account->getHost();

			if($account->status != Account::REMOTE)
			{
				continue;
			}

			if(empty($host))
			{
				continue;
			}


			// discover host
			$url     = null;
			$hostUrl = new Url($host->url);
			$xrds    = $this->yadis->discover($hostUrl);

			foreach($xrds->service as $service)
			{
				if(in_array(self::SERVICE_NS, $service->getType()))
				{
					$url = new Url($service->getUri());
					break;
				}
			}

			if(empty($url))
			{
				continue;
			}


			// get remote credentials
			$cred = $account->getRemoteCredentials();

			if(empty($cred))
			{
				continue;
			}


			// send oauth signed request
			$header = array(

				'Accept'        => 'application/xml',
				'Content-Type'  => 'application/atom+xml',
				'Authorization' => $this->oauth->getAuthorizationHeader($url, $cred->getConsumerKey(), $cred->getConsumerSecret(), $cred->getToken(), $cred->getTokenSecret(), 'HMAC-SHA1', 'POST'),

			);

			$request  = new PostRequest($url, $header, $body);
			$response = $this->http->request($request);

			if($response->getCode() == 200)
			{
				$resp = simplexml_load_string($response->getBody());

				if($resp->success == 'true')
				{
					// insert successful
				}
			}
		}
	}

	protected function getMessage($activityId)
	{
		// get remote activities for remote host
		$entries  = array();
		$activity = Table\Registry::get('User_Activity')->getRecord($activityId);
		$account  = $activity->getUser();


		// assign user to activity
		$activity->authorGlobalId = $account->globalId;
		$activity->authorName     = $account->name;


		// result set params
		$startIndex = $activity->id;
		$entries[]  = $activity;


		// build request
		$writer = new Writer\Atom();
		$writer->setConfig('Messages for ' . $account->name, 'urn:uuid:' . $account->globalId, new DateTime('NOW'));

		$resultset = new ResultSet(count($entries), $startIndex, count($entries), $entries);

		ob_start();

		$writer->write($resultset);

		$body = ob_get_contents();

		ob_end_clean();


		return $body;
	}
}

