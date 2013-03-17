<?php
/*
 *  $Id: Id.php 635 2012-05-01 19:46:37Z k42b3.x@googlemail.com $
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

namespace Amun\Filter;

use Amun\Sql\TableInterface;
use PSX\FilterAbstract;
use PSX\Sql;
use PSX\Sql\Condition;

/**
 * Amun_Filter_Id
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   Amun
 * @package    Amun_Filter
 * @version    $Revision: 635 $
 */
class Id extends FilterAbstract
{
	protected $table;
	protected $sql;
	protected $registry;

	protected $zeroAllowed;

	public function __construct(TableInterface $table, $zeroAllowed = false)
	{
		$this->table    = $table;
		$this->sql      = $table->getRegistry()->getSql();
		$this->registry = $table->getRegistry();

		$this->zeroAllowed = $zeroAllowed;
	}

	public function apply($value)
	{
		$value = (integer) $value;

		if($value > 0)
		{
			$con = new Condition(array($this->table->getPrimaryKey(), '=', $value));

			return $this->sql->select($this->table->getName(), array($this->table->getPrimaryKey()), $con, Sql::SELECT_FIELD);
		}
		else if($value === 0 && $this->zeroAllowed)
		{
			return 0;
		}

		return false;
	}

	public function getErrorMsg()
	{
		return '%s is not a valid id';
	}
}

