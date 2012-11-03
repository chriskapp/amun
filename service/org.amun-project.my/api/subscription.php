<?php
/*
 *  $Id: subscription.php 875 2012-09-30 13:51:45Z k42b3.x@googlemail.com $
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
 * subscription
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   module
 * @package    api
 * @subpackage user_subscription
 * @version    $Revision: 875 $
 */
class subscription extends Amun_Module_RestAbstract
{
	public function onGet()
	{
		if($this->user->hasRight('service_my_view'))
		{
			try
			{
				// get source
				$topic = $this->get->topic('string', array(new PSX_Filter_Length(3, 256), new PSX_Filter_Url()));

				if(empty($topic))
				{
					throw new PSX_Data_Exception('Invalid topic url');
				}


				// redirect to login
				if($this->user->isAnonymous())
				{
					throw new PSX_Data_Exception('Please sign in to subscribe to an topic');

					/*
					$redirect = urlencode($this->base->getSelf());
					$url      = $this->config['psx_url'] . '/' . $this->config['psx_dispatch'] . 'my.htm/login?redirect=' . $redirect;

					header('Location: ' . $url);
					exit;
					*/
				}


				// subscribe to hub
				$handler = new AmunService_My_Subscription_Handler($this->user);
				$handler->create($topic);


				$msg = new PSX_Data_Message('You have successful subscribe a topic', true);

				$this->setResponse($msg);
			}
			catch(Exception $e)
			{
				$msg = new PSX_Data_Message($e->getMessage(), false);

				$this->setResponse($msg);
			}
		}
		else
		{
			$msg = new PSX_Data_Message('Access not allowed', false);

			$this->setResponse($msg, null, $this->user->isAnonymous() ? 401 : 403);
		}
	}
}

