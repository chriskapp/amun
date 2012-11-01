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
 * AmunService_Core_Content_Media_Handler
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   Amun
 * @package    Amun_Content_Media
 * @version    $Revision: 880 $
 */
class AmunService_Core_Content_Media_Handler extends Amun_Data_HandlerAbstract
{
	private $mimeTypes;

	public function create(PSX_Data_RecordInterface $record)
	{
		if($record->hasFields('name', 'mimeType', 'size', 'path'))
		{
			$record->globalId = $this->base->getUUID('content:media:' . $record->path . ':' . uniqid());

			if($record->path instanceof PSX_Upload_File)
			{
				// check mime type
				$type = $this->getType($record->path->getType());

				if($type === false)
				{
					throw new PSX_Data_Exception('Invalide type');
				}

				// check size
				$size = $record->path->getTmpSize();

				if($size < 1 || $size > $this->registry['core.media_upload_size'])
				{
					throw new PSX_Data_Exception('Invalid upload size');
				}

				// move file
				$folder = isset($record->folder) ? $record->folder : '.';
				$name   = $record->name;
				$path   = $this->registry['core.media_path'] . '/' . $folder . '/' . $name;

				if(is_file($path))
				{
					throw new PSX_Data_Exception('File already exists');
				}

				if($record->path->move($path))
				{
					$record->type = $type;
					$record->path = $folder == '.' ? $name : $folder . '/' . $name;
				}
				else
				{
					throw new PSX_Data_Exception('Could not move file');
				}
			}
			else if(!is_file($record->path))
			{
				throw new PSX_Data_Exception('Invalid path');
			}

			$date = new DateTime('NOW', $this->registry['core.default_timezone']);

			$record->date = $date->format(PSX_DateTime::SQL);


			$this->table->insert($record->getData());


			$record->id = $this->sql->getLastInsertId();

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
		if($record->hasFields('id'))
		{
			$con = new PSX_Sql_Condition(array('id', '=', $record->id));

			$this->table->update($record->getData(), $con);


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
		if($record->hasFields('id'))
		{
			$con = new PSX_Sql_Condition(array('id', '=', $record->id));

			$this->table->delete($con);


			$this->notify(Amun_Data_RecordAbstract::DELETE, $record);


			return $record;
		}
		else
		{
			throw new PSX_Data_Exception('Missing field in record');
		}
	}

	/**
	 * Scans recursively an folder and imports all files to the media table.
	 * Tries to detect the mime type based on the file extension.
	 *
	 * @return void
	 */
	public function import($path, $rightId = null)
	{
		if(!is_dir($path))
		{
			throw new PSX_Data_Exception('Path is not a valid dir');
		}

		PSX_Log::info('Scan ' . $path);

		$files = scandir($path);
		$count = 0;

		foreach($files as $f)
		{
			if($f[0] != '.')
			{
				$item = $path . '/' . $f;

				if(is_dir($item))
				{
					$this->import($item, $rightId);
				}

				if(is_file($item))
				{
					$mimeType = $this->getMimeTypeByExtension($item);
					$type     = $this->getType($mimeType);

					if($type !== false)
					{
						try
						{
							$record = new Amun_Content_Media($this->table);
							$record->name = $f;
							$record->path = realpath($item);
							$record->type = $type;
							$record->size = filesize($item);
							$record->mimeType = $mimeType;

							if(!empty($rightId))
							{
								$record->setRightId($rightId);
							}

							$this->create($record);

							$count++;
						}
						catch(Exception $e)
						{
							PSX_Log::error($e->getMessage());
						}
					}
				}
			}
		}

		PSX_Log::info('Imported ' . $count . ' files');
	}

	private function getType($mimeType)
	{
		$mimeType = strtolower($mimeType);
		$types    = Amun_Content_Media::getType();

		foreach($types as $type => $val)
		{
			if(strpos($mimeType, $type) !== false)
			{
				return $type;
			}
		}

		return false;
	}

	private function getMimeTypeByExtension($path)
	{
		$ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));

		if($this->mimeTypes === null)
		{
			$this->mimeTypes = $this->getMimeTypes();
		}

		foreach($this->mimeTypes as $mime => $exts)
		{
			if(in_array($ext, $exts))
			{
				return $mime;
			}
		}

		return 'application/octet-stream';
	}

	private function getMimeTypes()
	{
		$url      = new PSX_Url('http://svn.apache.org/repos/asf/httpd/httpd/trunk/docs/conf/mime.types');
		$http     = new PSX_Http(new PSX_Http_Handler_Curl());
		$request  = new PSX_Http_GetRequest($url);
		$response = $http->request($request);
		$types    = array();

		if($response->getCode() == 200)
		{
			$lines = explode("\n", $response->getBody());

			foreach($lines as $line)
			{
				if($line[0] != '#')
				{
					$pos  = strpos($line, "\t");
					$mime = trim(substr($line, 0, $pos));
					$ext  = explode(' ', trim(substr($line, $pos)));

					$types[$mime] = $ext;
				}
			}
		}

		return $types;
	}
}

