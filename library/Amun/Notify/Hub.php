<?php
/*
 *  $Id: Hub.php 635 2012-05-01 19:46:37Z k42b3.x@googlemail.com $
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
 * Amun_Notify_Hub
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   Amun
 * @package    Amun_Notify
 * @version    $Revision: 635 $
 */
class Amun_Notify_Hub extends Amun_NotifyAbstract
{
	public function notify($type, PSX_Data_RecordInterface $record)
	{
		if(!empty($this->config['amun_hub']))
		{
			if($type == Amun_Data_RecordAbstract::INSERT)
			{
				// remove the prefix amun_ and the suffix _handler to
				// get the path
				$path = $table;
				$path = substr($path, 5);
				$path = str_replace('_', '/', $path);

				if(!empty($path))
				{
					$url = $this->config['psx_url'] . '/' . $this->config['psx_dispatch'] . 'api/' . $path . '?format=atom';

					$http = new PSX_Http(new PSX_Http_Handler_Curl());
					$pshb = new PSX_PubSubHubBub($http);

					$pshb->notification(new PSX_Url($this->config['amun_hub']), $url);
				}
			}
		}
	}
}

