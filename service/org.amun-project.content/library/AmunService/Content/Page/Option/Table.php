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

namespace AmunService\Content\Page\Option;

use Amun\Sql\TableAbstract;

/**
 * AmunService_Core_Content_Page_Option_Table
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   Amun
 * @package    Amun_Content_Page
 * @version    $Revision: 635 $
 */
class Table extends TableAbstract
{
	public function getConnections()
	{
		return array(

			'optionId' => $this->registry['table.core_service_option'],
			'rightId' => $this->registry['table.user_right'],
			'srcPageId' => $this->registry['table.content_page'],
			'destPageId' => $this->registry['table.content_page'],

		);
	}

	public function getName()
	{
		return $this->registry['table.content_page_option'];
	}

	public function getColumns()
	{
		return array(

			'id' => self::TYPE_INT | 10 | self::PRIMARY_KEY,
			'optionId' => self::TYPE_INT | 10,
			'rightId' => self::TYPE_INT | 10,
			'srcPageId' => self::TYPE_INT | 10,
			'destPageId' => self::TYPE_INT | 10,
			'name' => self::TYPE_VARCHAR | 32,
			'href' => self::TYPE_VARCHAR | 512,

		);
	}
}

