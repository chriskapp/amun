<?php
/*
 *  $Id: Table.php 635 2012-05-01 19:46:37Z k42b3.x@googlemail.com $
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
 * Amun_Service_My_Contact_Table
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   Amun
 * @package    Amun_Service_My
 * @version    $Revision: 635 $
 */
class Amun_Service_My_Contact_Table extends Amun_Sql_TableAbstract
{
	public function getConnections()
	{
		return array(

			'userId' => $this->registry['table.user_account'],

		);
	}

	public function getName()
	{
		return $this->registry['table.service_my_contact'];
	}

	public function getColumns()
	{
		return array(

			'id' => self::TYPE_INT | 10 | self::PRIMARY_KEY,
			'userId' => self::TYPE_INT | 10,
			'status' => self::TYPE_INT | 10,
			'type' => self::TYPE_ENUM,
			'value' => self::TYPE_VARCHAR | 64,
			'date' => self::TYPE_DATETIME,

		);
	}
}

