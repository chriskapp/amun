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
class AmunService_Explorer_Handler implements PSX_Data_HandlerInterface
{
	public function getAll(array $fields, $startIndex = 0, $count = 16, $sortBy = null, $sortOrder = null, PSX_Sql_Condition $con = null, $mode = 0, $class = null, array $args = array())
	{
		$start     = $startIndex !== null ? (integer) $startIndex : 0;
		$count     = $count      !== null ? (integer) $count      : 16;
		$sortOrder = $sortOrder  !== null ? (strcasecmp($sortOrder, 'ascending') == 0 ? \SORT_ASC : \SORT_DESC) : \SORT_ASC;

		$path   = $this->registry['explorer.path'];
		$files  = scandir($path);
		$result = array();

		$j      = 0;
		$keys   = array();
		$dKeys  = array();

		foreach($files as $i => $file)
		{
			if($file != '.')
			{
				if($i < $start)
				{
					continue;
				}

				if($j > $count)
				{
					break;
				}

				$item  = $path . '/' . $file;
				$name  = $file;
				$size  = filesize($item);
				$perms = $this->getFilePerms($item);
				$date  = filemtime($item);

				$row = array(
					'name'  => $name,
					'size'  => $size,
					'perms' => $perms,
					'date'  => date(PSX_DateTime::SQL, $date),
				);

				// check conditions
				if($con !== null && !$con->verify($row))
				{
					continue;
				}

				// add row
				$result[] = $row;

				$keys[$i]  = isset($row[$sortBy]) ? $row[$sortBy] : $row['name'];
				$dKeys[$i] = $perms[0] == 'd' ? 1 : 0;

				$j++;
			}
		}

		// sort
		array_multisort($dKeys, \SORT_DESC, $keys, $sortOrder, $result);

		return new PSX_Data_ResultSet(count($files), $start, $count, $result);
	}

	public function create(PSX_Data_RecordInterface $record)
	{
		if($record->hasFields('path', 'content'))
		{
			if(is_file($record->getPath()))
			{
				throw new PSX_Data_Exception('File already exist');
			}


			file_put_contents($record->getPath(), $record->getContent());


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

	/**
	 * Returns the permissions of an specific file
	 *
	 * @see http://php.net/manual/en/function.fileperms.php
	 * @return string
	 */
	private function getFilePerms($file)
	{
		$perms = fileperms($file);

		if(($perms & 0xC000) == 0xC000)
		{
			// Socket
			$info = 's';
		}
		elseif(($perms & 0xA000) == 0xA000)
		{
			// Symbolic Link
			$info = 'l';
		}
		elseif(($perms & 0x8000) == 0x8000)
		{
			// Regular
			$info = '-';
		}
		elseif(($perms & 0x6000) == 0x6000)
		{
			// Block special
			$info = 'b';
		}
		elseif(($perms & 0x4000) == 0x4000)
		{
			// Directory
			$info = 'd';
		}
		elseif(($perms & 0x2000) == 0x2000)
		{
			// Character special
			$info = 'c';
		}
		elseif(($perms & 0x1000) == 0x1000)
		{
			// FIFO pipe
			$info = 'p';
		}
		else
		{
			// Unknown
			$info = 'u';
		}

		// Owner
		$info.= (($perms & 0x0100) ? 'r' : '-');
		$info.= (($perms & 0x0080) ? 'w' : '-');
		$info.= (($perms & 0x0040) ? (($perms & 0x0800) ? 's' : 'x' ) : (($perms & 0x0800) ? 'S' : '-'));

		// Group
		$info.= (($perms & 0x0020) ? 'r' : '-');
		$info.= (($perms & 0x0010) ? 'w' : '-');
		$info.= (($perms & 0x0008) ? (($perms & 0x0400) ? 's' : 'x' ) : (($perms & 0x0400) ? 'S' : '-'));

		// World
		$info.= (($perms & 0x0004) ? 'r' : '-');
		$info.= (($perms & 0x0002) ? 'w' : '-');
		$info.= (($perms & 0x0001) ? (($perms & 0x0200) ? 't' : 'x' ) : (($perms & 0x0200) ? 'T' : '-'));

		return $info;
	}
}

