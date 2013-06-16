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
use Amun\Filter as AmunFilter;
use Amun\Util;
use AmunService\Media\Filter as MediaFilter;
use PSX\Data\WriterInterface;
use PSX\Data\WriterResult;
use PSX\DateTime;
use PSX\Filter;
use PSX\Util\Markdown;
use PSX\Upload\File;

/**
 * Record
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/gpl.html GPLv3
 * @link    http://amun.phpsx.org
 */
class Record extends RecordAbstract
{
	protected $_user;

	public function getFields()
	{
		$fields = parent::getFields();

		// add folder field
		$fields['folder'] = isset($this->folder) ? $this->folder : null;

		return $fields;
	}

	public function setId($id)
	{
		$id = $this->_validate->apply($id, 'integer', array(new AmunFilter\Id($this->_table)), 'id', 'Id');

		if(!$this->_validate->hasError())
		{
			$this->id = $id;
		}
		else
		{
			throw new Exception($this->_validate->getLastError());
		}
	}

	public function setRightId($rightId)
	{
		$rightId = $this->_validate->apply($rightId, 'integer', array(new AmunFilter\Id($this->_hm->getTable('User_Right'))), 'rightId', 'Right Id');

		if(!$this->_validate->hasError())
		{
			$this->rightId = $rightId;
		}
		else
		{
			throw new Exception($this->_validate->getLastError());
		}
	}

	public function setFolder($folder)
	{
		$folder = $this->_validate->apply($folder, 'string', array(new MediaFilter\Folder($this->_registry)), 'folder', 'Folder');

		if(!$this->_validate->hasError())
		{
			$this->folder = $folder;
		}
		else
		{
			throw new Exception($this->_validate->getLastError());
		}
	}

	public function setPath(array $path)
	{
		$file = new File($path);

		$this->name     = $file->getName();
		$this->mimeType = $file->getType();
		$this->size     = $file->getSize();
		$this->path     = $file;
	}

	public function getId()
	{
		return $this->_base->getUrn('media', $this->id);
	}

	public function getDate()
	{
		if($this->_date === null)
		{
			$this->_date = new DateTime($this->date, $this->_registry['core.default_timezone']);
		}

		return $this->_date;
	}

	public static function getType($type = false)
	{
		$t = array(

			'application' => 'Application',
			'audio'       => 'Audio',
			'text'        => 'Text',
			'image'       => 'Image',
			'video'       => 'Video',

		);

		if($type !== false)
		{
			if(array_key_exists($type, $t))
			{
				return $t[$type];
			}
			else
			{
				return false;
			}
		}
		else
		{
			return $t;
		}
	}
}


