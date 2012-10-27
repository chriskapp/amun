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
class Amun_Stomp_Listener_RemoteActivitySubmitter extends Amun_Stomp_ListenerAbstract
{
	const SERVICE_NS = 'http://ns.amun-project.org/2012/amun/user/activity/message/1.0';

	protected $http;
	protected $oauth;
	protected $yadis;

	public function __construct()
	{
		parent::__construct();

		$this->http  = new PSX_Http(new PSX_Http_Handler_Curl());
		$this->oauth = new PSX_Oauth($this->http);
		$this->yadis = new PSX_Yadis($this->http);
	}

	public function run($table, $type, $userId, array $data)
	{
		if($type != Amun_Data_RecordAbstract::INSERT || $table != Amun_Registry::get('table.user_activity'))
		{
			return;
		}

		// get activities atom feed
		$body = $this->getMessage($data['id']);

		// send request
		$receivers = Amun_Sql_Table_Registry::get('User_Activity_Receiver')
			->select(array('status', 'activityId', 'userId'))
			->where('activityId', '=', $data['id'])
			->where('status', '=', Amun_User_Activity_Receiver::VISIBLE)
			->getAll(PSX_Sql::FETCH_OBJECT);

		foreach($receivers as $receiver)
		{
			// get receiver
			$account = $receiver->getUser();
			$host    = $account->getHost();

			if($account->status != Amun_User_Account::REMOTE)
			{
				continue;
			}

			if(empty($host))
			{
				continue;
			}


			// discover host
			$url     = null;
			$hostUrl = new PSX_Url($host->url);
			$xrds    = $this->yadis->discover($hostUrl);

			foreach($xrds->service as $service)
			{
				if(in_array(self::SERVICE_NS, $service->getType()))
				{
					$url = new PSX_Url($service->getUri());
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

			$request  = new PSX_Http_PostRequest($url, $header, $body);
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
		$activity = Amun_Sql_Table_Registry::get('User_Activity')->getRecord($activityId);
		$account  = $activity->getUser();


		// assign user to activity
		$activity->authorGlobalId = $account->globalId;
		$activity->authorName     = $account->name;


		// result set params
		$startIndex = $activity->id;
		$entries[]  = $activity;


		// build request
		$writer = new PSX_Data_Writer_Atom();
		$writer->setConfig('Messages for ' . $account->name, 'urn:uuid:' . $account->globalId, new DateTime('NOW'));

		$resultset = new PSX_Data_ResultSet(count($entries), $startIndex, count($entries), $entries);

		ob_start();

		$writer->write($resultset);

		$body = ob_get_contents();

		ob_end_clean();


		return $body;
	}
}

