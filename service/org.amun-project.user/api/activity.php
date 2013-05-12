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

namespace user\api;

use Amun\Base;
use Amun\Module\RestAbstract;
use PSX\DateTime;
use PSX\Data\WriterInterface;
use PSX\Data\WriterResult;

/**
 * activity
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/gpl.html GPLv3
 * @link    http://amun.phpsx.org
 */
class activity extends RestAbstract
{
	protected function getHandler($table = null)
	{
		return parent::getHandler($table === null ? 'User_Activity' : $table);
	}

	protected function setWriterConfig(WriterResult $writer)
	{
		switch($writer->getType())
		{
			case WriterInterface::ATOM:

				$updated = $this->sql->getField('SELECT `date` FROM ' . $this->registry['table.user_activity'] . ' ORDER BY `date` DESC LIMIT 1');

				$title   = 'Activity';
				$id      = 'urn:uuid:' . $this->base->getUUID('user:activity');
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
