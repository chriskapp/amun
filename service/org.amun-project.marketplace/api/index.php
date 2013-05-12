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

namespace marketplace\api;

use Amun\Base;
use Amun\Module\RestAbstract;
use PSX\DateTime;
use PSX\Data\WriterInterface;
use PSX\Data\WriterResult;

/**
 * index
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/gpl.html GPLv3
 * @link    http://amun.phpsx.org
 */
class index extends RestAbstract
{
	/**
	 * Insert a new record
	 *
	 * @httpMethod POST
	 * @path /
	 * @nickname insertRecord
	 * @responseClass PSX_Data_Message
	 */
	public function insertRecord()
	{
		$msg = new Message('Create a marketplace record is not possible', false);

		$this->setResponse($msg, null, 500);
	}

	/**
	 * Update an existing record
	 *
	 * @httpMethod PUT
	 * @path /
	 * @nickname updateRecord
	 * @responseClass PSX_Data_Message
	 */
	public function updateRecord()
	{
		$msg = new Message('Update a marketplace record is not possible', false);

		$this->setResponse($msg, null, 500);
	}

	/**
	 * Delete an existing record
	 *
	 * @httpMethod PUT
	 * @path /
	 * @nickname updateRecord
	 * @responseClass PSX_Data_Message
	 */
	public function deleteRecord()
	{
		$msg = new Message('Delete a marketplace record is not possible', false);

		$this->setResponse($msg, null, 500);
	}

	protected function setWriterConfig(WriterResult $writer)
	{
		switch($writer->getType())
		{
			case WriterInterface::ATOM:

				// get last inserted date
				$updated = $this->sql->getField('SELECT `date` FROM ' . $this->registry['table.provider'] . ' ORDER BY `date` DESC LIMIT 1');

				$title   = 'API';
				$id      = 'urn:uuid:' . $this->base->getUUID('provider');
				$updated = new DateTime($updated, $this->registry['core.default_timezone']);

				$writer = $writer->getWriter();
				$writer->setConfig($title, $id, $updated);
				$writer->setGenerator('amun ' . Base::getVersion());

				if(!empty($this->config['amun_hub']))
				{
					$writer->addLink($this->config['amun_hub'], 'hub');
				}

				break;
		}
	}
}
