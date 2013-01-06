<?php
/*
 *  $Id: xrds.php 799 2012-07-08 05:52:43Z k42b3.x@googlemail.com $
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

namespace explorer\api;

use AmunService_Explorer_Handler;
use AmunService_Explorer_Record;
use Amun_Module_ApiAbstract;
use Exception;
use PSX_Data_Array;
use PSX_Data_Message;
use PSX_Data_ResultSet;
use PSX_DateTime;

/**
 * xrds
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   module
 * @package    api
 * @subpackage system_xrds
 * @version    $Revision: 799 $
 */
class index extends Amun_Module_ApiAbstract
{
	/**
	 * Returns all registered services
	 *
	 * @httpMethod GET
	 * @path /
	 * @nickname getExplorer
	 * @responseClass PSX_Data_ResultSet
	 */
	public function getExplorer()
	{
		if($this->getProvider()->hasViewRight())
		{
			try
			{
				$params    = $this->getRequestParams();
				$resultSet = $this->getExplorerResult($params['fields'], $params['startIndex'], $params['count'], $params['sortBy'], $params['sortOrder'], $params['filterBy'], $params['filterOp'], $params['filterValue'], $params['updatedSince']);

				$this->setResponse($resultSet);
			}
			catch(Exception $e)
			{
				$msg = new PSX_Data_Message($e->getMessage(), false);

				$this->setResponse($msg);
			}
		}
		else
		{
			$msg = new PSX_Data_Message('Access not allowed', false);

			$this->setResponse($msg, null, $this->user->isAnonymous() ? 401 : 403);
		}
	}

	/**
	 * Returns all available fields 
	 *
	 * @httpMethod GET
	 * @path /@supportedFields
	 * @nickname getSupportedFields
	 * @responseClass PSX_Data_Array
	 */
	public function getSupportedFields()
	{
		if($this->getProvider()->hasViewRight())
		{
			try
			{
				$array = new PSX_Data_Array(array('name', 'size', 'perms', 'date'));

				$this->setResponse($array);
			}
			catch(Exception $e)
			{
				$msg = new PSX_Data_Message($e->getMessage(), false);

				$this->setResponse($msg);
			}
		}
		else
		{
			$msg = new PSX_Data_Message('Access not allowed', false);

			$this->setResponse($msg, null, $this->user->isAnonymous() ? 401 : 403);
		}
	}

	/**
	 * Create a file
	 *
	 * @httpMethod POST
	 * @path /
	 * @nickname createFile
	 * @responseClass PSX_Data_Message
	 */
	public function createFile()
	{
		if($this->getProvider()->hasAddRight())
		{
			try
			{
				$record = new AmunService_Explorer_Record();
				$record->import($this->getRequest());

				// insert
				$this->getHandler()->create($record);


				$msg = new PSX_Data_Message('You have successful create a file', true);

				$this->setResponse($msg);
			}
			catch(Exception $e)
			{
				$msg = new PSX_Data_Message($e->getMessage(), false);

				$this->setResponse($msg);
			}
		}
		else
		{
			$msg = new PSX_Data_Message('Access not allowed', false);

			$this->setResponse($msg, null, $this->user->isAnonymous() ? 401 : 403);
		}
	}

	/**
	 * Update a file
	 *
	 * @httpMethod PUT
	 * @path /
	 * @nickname updateFile
	 * @responseClass PSX_Data_Message
	 */
	public function updateFile()
	{
		if($this->getProvider()->hasEditRight())
		{
			try
			{
				$record = new AmunService_Explorer_Record();
				$record->import($this->getRequest());

				// update
				$this->getHandler()->update($record);


				$msg = new PSX_Data_Message('You have successful update a file', true);

				$this->setResponse($msg);
			}
			catch(Exception $e)
			{
				$msg = new PSX_Data_Message($e->getMessage(), false);

				$this->setResponse($msg);
			}
		}
		else
		{
			$msg = new PSX_Data_Message('Access not allowed', false);

			$this->setResponse($msg, null, $this->user->isAnonymous() ? 401 : 403);
		}
	}

	/**
	 * Delete a file
	 *
	 * @httpMethod DELETE
	 * @path /
	 * @nickname deleteFile
	 * @responseClass PSX_Data_Message
	 */
	public function deleteFile()
	{
		if($this->getProvider()->hasDeleteRight())
		{
			try
			{
				$record = new AmunService_Explorer_Record();
				$record->import($this->getRequest());

				// delete
				$this->getHandler()->delete($record);


				$msg = new PSX_Data_Message('You have successful delete a file', true);

				$this->setResponse($msg);
			}
			catch(Exception $e)
			{
				$msg = new PSX_Data_Message($e->getMessage(), false);

				$this->setResponse($msg);
			}
		}
		else
		{
			$msg = new PSX_Data_Message('Access not allowed', false);

			$this->setResponse($msg, null, $this->user->isAnonymous() ? 401 : 403);
		}
	}

	private function getExplorerResult($fields, $startIndex, $count, $sortBy, $sortOrder, $filterBy, $filterOp, $filterValue, $updatedSince)
	{
		$start     = $startIndex !== null ? (integer) $startIndex : 0;
		$count     = $count      !== null ? (integer) $count      : 16;
		$sortOrder = $sortOrder  !== null ? (strcasecmp($sortOrder, 'ascending') == 0 ? \SORT_ASC : \SORT_DESC) : \SORT_ASC;

		$path   = $this->get->path('string');
		$path   = empty($path) ? '' : $path;
		$path   = $this->registry['explorer.path'] . '/' . $path;
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

				// filter if set
				if(isset($row[$filterBy]) && !empty($filterValue))
				{
					$filtered = false;

					switch($filterOp)
					{
						case 'contains':
							$filtered = stripos($row[$filterBy], $filterValue) === false;
							break;

						case 'equals':
							$filtered = strcmp($row[$filterBy], $filterValue) !== 0;
							break;

						case 'startsWith':
							$filtered = substr($row[$filterBy], 0, strlen($filterValue)) != $filterValue;
							break;

						case 'present':
							$filtered = !isset($row[$filterBy]);
							break;
					}

					if($filtered)
					{
						continue;
					}
				}

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

	protected function getHandler($name = null)
	{
		return new AmunService_Explorer_Handler();
	}
}

