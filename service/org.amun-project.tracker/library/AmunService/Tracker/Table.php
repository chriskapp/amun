<?php
/*
 *  $Id: Table.php 708 2012-06-09 13:42:35Z k42b3.x@googlemail.com $
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
 * Amun_Service_Tracker_Table
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   Amun
 * @package    Amun_Service_Tracker
 * @version    $Revision: 708 $
 */
class AmunService_Tracker_Table extends Amun_Sql_TableAbstract
{
	public function getConnections()
	{
		return array(

			'pageId' => $this->registry['table.core_content_page'],
			'userId' => $this->registry['table.core_user_account'],

		);
	}

	public function getName()
	{
		return $this->registry['table.tracker'];
	}

	public function getColumns()
	{
		return array(

			'id' => self::TYPE_INT | 10 | self::PRIMARY_KEY,
			'globalId' => self::TYPE_VARCHAR | 36,
			'pageId' => self::TYPE_INT | 10,
			'userId' => self::TYPE_INT | 10,
			'urlTitle' => self::TYPE_VARCHAR | 128,
			'title' => self::TYPE_VARCHAR | 128,
			'name' => self::TYPE_VARCHAR | 128,
			'length' => self::TYPE_INT | 10,
			'seeder' => self::TYPE_INT | 10,
			'leecher' => self::TYPE_INT | 10,
			'completed' => self::TYPE_INT | 10,
			'infoHash' => self::TYPE_VARCHAR | 40,
			'torrent' => self::TYPE_VARCHAR | 40,
			'date' => self::TYPE_DATETIME,

		);
	}
}

