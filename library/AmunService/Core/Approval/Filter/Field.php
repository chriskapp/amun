<?php
/*
 *  $Id: Field.php 635 2012-05-01 19:46:37Z k42b3.x@googlemail.com $
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

namespace AmunService\Core\Approval\Filter;

use PSX\Sql;
use PSX\FilterAbstract;

/**
 * AmunService_Core_System_Approval_Filter_Field
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   Amun
 * @package    Amun_System_Approval
 * @version    $Revision: 635 $
 */
class Field extends FilterAbstract
{
	private $sql;
	private $table;

	public function __construct(Sql $sql, $table)
	{
		$this->sql   = $sql;
		$this->table = $table;
	}

	public function apply($value)
	{
		$result = $this->sql->getAll('DESCRIBE `' . $this->table . '`');
		$fields = array();

		foreach($result as $row)
		{
			$fields[] = $row['Field'];
		}

		return in_array($value, $fields);
	}

	public function getErrorMsg()
	{
		return '%s is not valid approval field';
	}
}
