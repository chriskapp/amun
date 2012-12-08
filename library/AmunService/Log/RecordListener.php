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
class AmunService_Log_RecordListener extends Amun_Module_ListenerAbstract
{
	public function notify($type, Amun_Sql_TableInterface $table, PSX_Data_RecordInterface $record)
	{
		$log = Amun_Sql_Table_Registry::get('Log')->getRecord();
		$log->setRefId(isset($record->id) ? $record->id : 0);
		$log->setType(Amun_Data_RecordAbstract::getType($type));
		$log->setTable($table->getName());

		$handler = new AmunService_Log_Handler($this->user);
		$handler->create($log);
	}
}

