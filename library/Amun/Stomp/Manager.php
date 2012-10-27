<?php
/*
 *  $Id: Manager.php 635 2012-05-01 19:46:37Z k42b3.x@googlemail.com $
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
 * Amun_Stomp_Manager
 *
 * Subscribes to the stomp destination and handles all incomming frames. All
 * listeners are called if an frame is send from the broker. Unfortunatly we
 * have no threading like in java so each listeners is executed in sequence.
 * Iam planing to write a stomp listener in java so that each listener is
 * executed in its own thread. Also sending xmpp or mail notification is much
 * better within java
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   Amun
 * @package    Amun_Stomp
 * @version    $Revision: 635 $
 */
class Amun_Stomp_Manager
{
	const WAIT = 4;

	private $config;
	private $stomp;

	private $i = 1;
	private $listener = array();

	public function __construct(PSX_Config $config)
	{
		$this->config = $config;
		$this->stomp  = new Stomp($config['amun_stomp_broker'], $config['amun_stomp_user'], $config['amun_stomp_pw']);
	}

	public function add(Amun_Stomp_ListenerAbstract $listener)
	{
		$this->listener[] = $listener;
	}

	public function run()
	{
		$this->stomp->subscribe($this->config['amun_stomp_destination']);

		while(true)
		{
			$msg = $this->stomp->readFrame();

			if($msg instanceof StompFrame)
			{
				$table  = isset($msg->headers['amun-table'])   ? $msg->headers['amun-table']   : null;
				$type   = isset($msg->headers['amun-type'])    ? $msg->headers['amun-type']    : null;
				$userId = isset($msg->headers['amun-user-id']) ? $msg->headers['amun-user-id'] : null;
				$data   = PSX_Json::decode($msg->body);

				// call registered listener
				foreach($this->listener as $listener)
				{
					try
					{
						$listener->run($table, $type, $userId, $data);

						echo 'R';
					}
					catch(Exception $e)
					{
						echo 'E';
					}
				}

				$this->stomp->ack($msg);
			}
			else
			{
				echo '.';
			}

			if($this->i % 32 == 0)
			{
				echo "\n";
			}

			$this->i++;

			sleep(self::WAIT);
		}

		unset($this->stomp);
	}
}


