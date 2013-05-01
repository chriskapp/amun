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

namespace AmunService\Forum;

use Amun\Data\ListenerAbstract;
use Amun\Data\RecordAbstract;
use Amun\Sql\TableInterface;
use Amun\DataFactory;
use PSX\Data\RecordInterface;
use AmunService\Comment;

/**
 * AmunService_Log_Listener
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   Amun
 * @package    Amun_Log
 * @version    $Revision: 635 $
 */
class RecordListener extends ListenerAbstract
{
	public function notify($type, TableInterface $table, RecordInterface $record)
	{
		if($type == RecordAbstract::INSERT && $record instanceof Comment\Record)
		{
			// update count and last reply id
			$sql = <<<SQL
UPDATE
	{$this->registry['table.forum']}
SET
	replyUserId = ?,
	replyCount = replyCount + 1,
	replyDate = NOW()
WHERE
	id = ?
SQL;

			$this->sql->execute($sql, array($record->userId, $record->refId));
		}
	}
}

