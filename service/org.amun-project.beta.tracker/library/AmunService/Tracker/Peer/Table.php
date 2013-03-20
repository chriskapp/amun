<?php
/*
 *  $Id: Table.php 709 2012-06-09 13:55:52Z k42b3.x@googlemail.com $
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
 * Amun_Service_Tracker_Peer_Table
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   Amun
 * @package    Amun_Service_Tracker
 * @version    $Revision: 709 $
 */
class AmunService_Tracker_Peer_Table extends Amun_Sql_TableAbstract
{
	public function getConnections()
	{
		return array(

			'pageId' => $this->registry['table.content_page'],
			'userId' => $this->registry['table.user_account'],

		);
	}

	public function getName()
	{
		return $this->registry['table.tracker_peer'];
	}

	public function getColumns()
	{
		return array(

			'id' => self::TYPE_INT | 10 | self::PRIMARY_KEY,
			'infoHash' => self::TYPE_VARCHAR | 40,
			'peerId' => self::TYPE_VARCHAR | 40,
			'ip' => self::TYPE_VARCHAR | 32,
			'port' => self::TYPE_INT | 10,
			'uploaded' => self::TYPE_INT | 10,
			'downloaded' => self::TYPE_INT | 10,
			'left' => self::TYPE_INT | 10,
			'event' => self::TYPE_VARCHAR | 12,
			'key' => self::TYPE_VARCHAR | 64,
			'status' => self::TYPE_VARCHAR | 32,
			'client' => self::TYPE_VARCHAR | 128,
			'priority' => self::TYPE_INT | 10,
			'date' => self::TYPE_DATETIME,

		);
	}
}

