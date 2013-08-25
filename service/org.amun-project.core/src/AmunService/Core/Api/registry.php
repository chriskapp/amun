<?php
/*
 * amun
 * A social content managment system based on the psx framework. For
 * the current version and informations visit <http://amun.phpsx.org>
 *
 * Copyright (c) 2010-2013 Christoph Kappestein <k42b3.x@gmail.com>
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

namespace core\api;

use Amun\Module\RestAbstract;
use PSX\Data\Message;

/**
 * registry
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/gpl.html GPLv3
 * @link    http://amun.phpsx.org
 */
class registry extends RestAbstract
{
	/**
	 * Insert a new record
	 *
	 * @httpMethod POST
	 * @path /
	 * @nickname updateRecord
	 * @responseClass PSX_Data_Message
	 */
	public function insertRecord()
	{
		$msg = new Message('Create a registry record is not possible', false);

		$this->setResponse($msg, null, 500);
	}

	/**
	 * Delete an existing record
	 *
	 * @httpMethod DELETE
	 * @path /
	 * @nickname deleteRecord
	 * @responseClass PSX_Data_Message
	 */
	public function deleteRecord()
	{
		$msg = new Message('Delete a registry record is not possible', false);

		$this->setResponse($msg, null, 500);
	}

	protected function getHandler($table = null)
	{
		return parent::getHandler($table === null ? 'Core_Registry' : $table);
	}
}
