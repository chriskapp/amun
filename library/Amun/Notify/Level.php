<?php
/*
 *  $Id: Level.php 635 2012-05-01 19:46:37Z k42b3.x@googlemail.com $
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
 * Amun_Notify_Level
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   Amun
 * @package    Amun_Notify
 * @version    $Revision: 635 $
 */
class Amun_Notify_Level extends Amun_NotifyAbstract
{
	public function notify($type, PSX_Data_RecordInterface $record)
	{
		// @todo not implement yet because the level column was removed from
		// the user_account table
		/*
		$con   = new PSX_Sql_Condition(array('userId', '=', $this->user->id));
		$count = $this->sql->count($this->registry['table.system_log'], $con);

		$sql   = <<<SQL
SELECT

	`level`.`title`

	FROM {$this->registry['table.core_user_level']} `level`

		WHERE `level`.`groupId` = ?

		AND `level`.`min` <= ?

			AND `level`.`title` <> ?

			ORDER BY `level`.`min` DESC

				LIMIT 1
SQL;

		$level = $this->sql->getField($sql, array($this->user->groupId, $count, $this->user->level));

		if(!empty($level))
		{
			$this->sql->update($this->registry['table.core_user_account'], array('level' => $level), $con);
		}
		*/
	}
}

