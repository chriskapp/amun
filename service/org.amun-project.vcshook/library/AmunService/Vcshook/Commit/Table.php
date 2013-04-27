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

namespace AmunService\Vcshook\Commit;

use Amun\Sql\TableAbstract;

/**
 * Amun_Service_Googleproject_Commit_Table
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   Amun
 * @package    Amun_Service_Googleproject
 * @version    $Revision: 635 $
 */
class Table extends TableAbstract
{
	public function getConnections()
	{
		return array(

			'projectId' => $this->registry['table.vcshook'],
			'authorId'  => $this->registry['table.vcshook_author'],

		);
	}

	public function getName()
	{
		return $this->registry['table.vcshook_commit'];
	}

	public function getColumns()
	{
		return array(

			'id' => self::TYPE_INT | 10 | self::PRIMARY_KEY,
			'globalId' => self::TYPE_VARCHAR | 36,
			'projectId' => self::TYPE_INT | 10,
			'authorId' => self::TYPE_INT | 10,
			'url' => self::TYPE_VARCHAR | 256,
			'message' => self::TYPE_VARCHAR | 512,
			'commitDate' => self::TYPE_DATETIME,
			'date' => self::TYPE_DATETIME,

		);
	}
}

