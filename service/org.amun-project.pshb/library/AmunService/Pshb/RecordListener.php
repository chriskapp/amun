<?php
/*
 *  $Id: Log.php 635 2012-05-01 19:46:37Z k42b3.x@googlemail.com $
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
 * AmunService_Pshb_Listener
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   Amun
 * @package    Amun_Pshb
 * @version    $Revision: 635 $
 */
class AmunService_Pshb_RecordListener extends Amun_Module_ListenerAbstract
{
	public function notify($type, Amun_Sql_TableInterface $table, PSX_Data_RecordInterface $record)
	{
		$hub = $this->registry['pshb.hub'];

		if(!empty($hub) && $type == Amun_Data_RecordAbstract::INSERT)
		{
			// @todo probably implement a table with a mapping wich table 
			// supports pshb notifications. So we need to known when to send a 
			// new content notification request and the atom endpoint url where 
			// the content has changed

			/*
			$url = $this->config['psx_url'] . '/' . $this->config['psx_dispatch'] . 'api/' . $path . '?format=atom';

			$http = new PSX_Http(new PSX_Http_Handler_Curl());
			$pshb = new PSX_PubSubHubBub($http);

			$pshb->notification(new PSX_Url($hub), $url);
			*/
		}
	}
}
