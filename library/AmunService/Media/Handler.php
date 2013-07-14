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

namespace AmunService\Media;

use Amun\DataFactory;
use Amun\Data\HandlerAbstract;
use Amun\Data\RecordAbstract;
use Amun\Exception;
use AmunService\Core\Approval;
use DirectoryIterator;
use PSX\DateTime;
use PSX\Data\RecordInterface;
use PSX\Data\ResultSet;
use PSX\Upload\File;
use PSX\Sql\Condition;
use PSX\Sql\Join;
use PSX\Log;
use PSX\Url;
use PSX\Http;
use PSX\Http\GetRequest;

/**
 * Handler
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/gpl.html GPLv3
 * @link    http://amun.phpsx.org
 */
class Handler extends HandlerAbstract
{
	private $mimeTypes;

	public function getByGlobalId($globalId, $mode = 0, $class = null, array $args = array())
	{
		if($mode == Sql::FETCH_OBJECT && $class === null)
		{
			$class = $this->getClassName();
		}

		if($mode == Sql::FETCH_OBJECT && empty($args))
		{
			$args = $this->getClassArgs();
		}

		return $this->getSelect()
			->where('globalId', '=', $globalId)
			->getRow($mode, $class, $args);
	}

	public function create(RecordInterface $record)
	{
		if($record->hasFields('name', 'mimeType', 'size', 'path'))
		{
			$record->globalId = $this->base->getUUID('content:media:' . $record->path . ':' . uniqid());

			if($record->path instanceof File)
			{
				// check mime type
				$type = $this->getType($record->path->getType());

				if($type === false)
				{
					throw new Exception('Invalide type');
				}

				// check size
				$size = $record->path->getTmpSize();

				if($size < 1 || $size > $this->registry['media.upload_size'])
				{
					throw new Exception('Invalid upload size');
				}

				// move file
				$folder = isset($record->folder) ? $record->folder : '.';
				$name   = $record->name;
				$path   = $this->registry['media.path'] . '/' . $folder . '/' . $name;

				if(is_file($path))
				{
					throw new Exception('File already exists');
				}

				if($record->path->move($path))
				{
					$record->type = $type;
					$record->path = $folder == '.' ? $name : $folder . '/' . $name;
				}
				else
				{
					throw new Exception('Could not move file');
				}
			}
			else if(!is_file($record->path))
			{
				throw new Exception('Invalid path');
			}

			$date = new DateTime('NOW', $this->registry['core.default_timezone']);

			$record->date = $date->format(DateTime::SQL);


			$this->table->insert($record->getData());


			$record->id = $this->sql->getLastInsertId();

			$this->notify(RecordAbstract::INSERT, $record);


			return $record;
		}
		else
		{
			throw new Exception('Missing field in record');
		}
	}

	public function update(RecordInterface $record)
	{
		if($record->hasFields('id'))
		{
			$con = new Condition(array('id', '=', $record->id));

			$this->table->update($record->getData(), $con);


			$this->notify(RecordAbstract::UPDATE, $record);


			return $record;
		}
		else
		{
			throw new Exception('Missing field in record');
		}
	}

	public function delete(RecordInterface $record)
	{
		if($record->hasFields('id'))
		{
			$con = new Condition(array('id', '=', $record->id));

			$this->table->delete($con);


			$this->notify(RecordAbstract::DELETE, $record);


			return $record;
		}
		else
		{
			throw new Exception('Missing field in record');
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
			throw new Exception('Path is not a valid dir');
		}

		$count  = 0;
		$logger = $this->container->get('logger');
		$it     = new DirectoryIterator($path);

		foreach($it as $file)
		{
			if(!$file->isDot() && $file->isFile())
			{
				$mimeType = $this->getMimeTypeByExtension($file->getFilename());
				$type     = $this->getType($mimeType);

				if($type !== false)
				{
					try
					{
						$record = new Record($this->table, $this->container);
						$record->name = $file->getFilename();
						$record->path = $file->getRealPath();
						$record->type = $type;
						$record->size = $file->getSize();
						$record->mimeType = $mimeType;

						if(!empty($rightId))
						{
							$record->setRightId($rightId);
						}

						$this->create($record);

						$logger->info('Added ' . $record->path);

						$count++;
					}
					catch(\Exception $e)
					{
						$logger->error($e->getMessage());
					}
				}
			}
		}

		$logger->info('Imported ' . $count . ' files');
	}

	protected function getDefaultSelect()
	{
		return $this->table
			->select(array('id', 'globalId', 'rightId', 'name', 'path', 'type', 'size', 'mimeType', 'date'));
	}

	private function getType($mimeType)
	{
		$mimeType = strtolower($mimeType);
		$types    = Record::getType();

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
		$url      = new Url('http://svn.apache.org/repos/asf/httpd/httpd/trunk/docs/conf/mime.types');
		$http     = new Http();
		$request  = new GetRequest($url);
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

