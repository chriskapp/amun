<?php
/*
 *  $Id: Handler.php 880 2012-10-27 13:14:26Z k42b3.x@googlemail.com $
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
 * Amun_Service_Page_Handler
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   Amun
 * @package    Amun_Service_Page
 * @version    $Revision: 880 $
 */
class AmunService_Explorer_Handler extends Amun_Data_HandlerAbstract
{
	public function create(PSX_Data_RecordInterface $record)
	{
		if($record->hasFields('path', 'content'))
		{
			if(is_file($record->getPath()))
			{
				throw new PSX_Data_Exception('File already exist');
			}


			file_put_contents($record->getPath(), $record->getContent());


			$this->notify(Amun_Data_RecordAbstract::INSERT, $record);


			return $record;
		}
		else
		{
			throw new PSX_Data_Exception('Missing field in record');
		}
	}

	public function update(PSX_Data_RecordInterface $record)
	{
		if($record->hasFields('path', 'content'))
		{
			if(!is_file($record->getPath()))
			{
				throw new PSX_Data_Exception('File does not exist');
			}


			file_put_contents($record->getPath(), $record->getContent());


			$this->notify(Amun_Data_RecordAbstract::UPDATE, $record);


			return $record;
		}
		else
		{
			throw new PSX_Data_Exception('Missing field in record');
		}
	}

	public function delete(PSX_Data_RecordInterface $record)
	{
		if($record->hasFields('path'))
		{
			if(!is_file($record->getPath()))
			{
				throw new PSX_Data_Exception('File does not exist');
			}


			unlink($record->getPath());


			return $record;
		}
		else
		{
			throw new PSX_Data_Exception('Missing field in record');
		}
	}
}

