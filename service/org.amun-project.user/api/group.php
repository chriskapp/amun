<?php
/*
 *  $Id: group.php 683 2012-06-03 11:52:32Z k42b3.x@googlemail.com $
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

namespace user\api;

use Amun\Module\RestAbstract;
use Amun\Exception;
use PSX\Data\WriterInterface;
use PSX\Data\WriterResult;

/**
 * group
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   module
 * @package    admin
 * @subpackage user_group
 * @version    $Revision: 683 $
 */
class group extends RestAbstract
{
	protected function getHandler($table = null)
	{
		return parent::getHandler($table === null ? 'User_Group' : $table);
	}

	protected function setWriterConfig(WriterResult $writer)
	{
		switch($writer->getType())
		{
			case WriterInterface::ATOM:
				throw new Exception('Atom not supported');
				break;
		}
	}
}