<?php
/*
 *  $Id: Table.php 840 2012-09-11 22:19:37Z k42b3.x@googlemail.com $
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
 * AmunService_Core_Content_Page_Table
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   Amun
 * @package    Amun_Content_Page
 * @version    $Revision: 840 $
 */
class AmunService_Content_Page_Table extends Amun_Sql_TableAbstract
{
	public function getConnections()
	{
		return array(

			'parentId' => $this->registry['table.content_page'],
			'serviceId' => $this->registry['table.core_service'],

		);
	}

	public function getName()
	{
		return $this->registry['table.content_page'];
	}

	public function getColumns()
	{
		return array(

			'id' => self::TYPE_INT | 10 | self::PRIMARY_KEY,
			'parentId' => self::TYPE_INT | 10,
			'globalId' => self::TYPE_VARCHAR | 36,
			'serviceId' => self::TYPE_INT | 10,
			'rightId' => self::TYPE_INT | 10,
			'status' => self::TYPE_INT | 10,
			'load' => self::TYPE_INT | 10,
			'sort' => self::TYPE_INT | 10,
			'path' => self::TYPE_VARCHAR | 256,
			'urlTitle' => self::TYPE_VARCHAR | 32,
			'title' => self::TYPE_VARCHAR | 32,
			'template' => self::TYPE_VARCHAR | 256,
			'description' => self::TYPE_VARCHAR | 256,
			'keywords' => self::TYPE_VARCHAR | 256,
			'cache' => self::TYPE_TINYINT | 1,
			'expire' => self::TYPE_VARCHAR | 25,
			'publishDate' => self::TYPE_DATETIME,
			'date' => self::TYPE_DATETIME,

		);
	}
}

