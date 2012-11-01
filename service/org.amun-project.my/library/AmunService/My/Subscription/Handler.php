<?php
/*
 *  $Id: Handler.php 880 2012-10-27 13:14:26Z k42b3.x@googlemail.com $
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
 * Amun_Service_My_Subscription_Handler
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   Amun
 * @package    Amun_Service_My
 * @version    $Revision: 880 $
 */
class AmunService_My_Subscription_Handler extends Amun_Data_HandlerAbstract
{
	public function create(PSX_Data_RecordInterface $record)
	{
		if($record->hasFields('topic'))
		{
			// request pshb subscription
			$record = $this->request($record->topic);


			$record->userId = $this->user->id;
			$record->status = Amun_PubSubHubBub_CallbackAbstract::PENDING;

			$date = new DateTime('NOW', $this->registry['core.default_timezone']);

			$record->date = $date->format(PSX_DateTime::SQL);


			$this->table->insert($record->getData());


			$record->id = $this->sql->getLastInsertId();

			$this->notify(Amun_Data_RecordAbstract::INSERT, $record);


			return $record;
		}
		else
		{
			throw new PSX_Data_Exception('Missing field in record');
		}
	}

	public function update(PSX_Data_RecordInterface $record)
	{
		if($record->hasFields('id'))
		{
			$con = new PSX_Sql_Condition(array('id', '=', $record->id));

			$this->table->update($record->getData(), $con);


			$this->notify(Amun_Data_RecordAbstract::UPDATE, $record);


			return $record;
		}
		else
		{
			throw new PSX_Data_Exception('Missing field in record');
		}
	}

	public function delete(PSX_Data_RecordInterface $record)
	{
		if($record->hasFields('id'))
		{
			$con = new PSX_Sql_Condition(array('id', '=', $record->id));

			$this->table->delete($con);


			$this->notify(Amun_Data_RecordAbstract::DELETE, $record);


			return $record;
		}
		else
		{
			throw new PSX_Data_Exception('Missing field in record');
		}
	}

	/**
	 * We try to discover the hub url of an specific topic and then send an
	 * pshb subscription request. The method returns an record containing all
	 * informations about the subscription.
	 *
	 * @param string $url
	 * @return Amun_Service_My_Subscription
	 */
	protected function request($url)
	{
		// request
		$http = new PSX_Http(new PSX_Http_Handler_Curl());
		$pshb = new PSX_PubSubHubBub($http);

		$url  = new PSX_Url($url);
		$hub  = $this->discoverHubUrl($pshb, $url);

		if($hub === false)
		{
			$response = PSX_Http_Response::convert($http->getResponse());

			// parse html
			$parse = new PSX_Html_Parse($response->getBody());
			$href  = $parse->fetchAttrFromHead(new PSX_Html_Parse_Element('link', array(

				'rel'  => 'alternate',
				'type' => 'application/atom+xml',

			)), 'href');

			if(!empty($href))
			{
				$url = new PSX_Url($href);
				$hub = $this->discoverHubUrl($pshb, $url);
			}
		}


		// subscribe if hub available
		if($hub instanceof PSX_Url)
		{
			$callback    = new PSX_Url($this->config['psx_url'] . '/' . $this->config['psx_dispatch'] . 'api/service/my/subscription/callback');
			$topic       = $url;
			$secret      = Amun_Security::generateToken();
			$verifyToken = Amun_Security::generateToken(32);


			// request subscription
			$pshb->request($hub, $callback, 'subscribe', $topic, 'async', false, $secret, $verifyToken);


			// insert
			$subscription = new Amun_Service_My_Subscription($this->table);
			$subscription->setHub((string) $hub);
			$subscription->setTopic((string) $topic);
			$subscription->setSecret($secret);
			$subscription->setVerifyToken($verifyToken);

			return $subscription;
		}
		else
		{
			throw new PSX_Data_Exception('Could not discover hub');
		}
	}

	protected function discoverHubUrl(PSX_PubSubHubBub $pshb, $url)
	{
		try
		{
			$hub = $pshb->discover($url);

			if(!empty($hub))
			{
				return new PSX_Url($hub);
			}
			else
			{
				throw new PSX_Data_Exception('Could not discover url');
			}
		}
		catch(Exception $e)
		{
			return false;
		}
	}
}


