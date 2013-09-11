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

namespace AmunService\Country;

use Amun\Data\HandlerAbstract;
use Amun\Data\RecordAbstract;
use Amun\Exception;
use Amun\Filter as AmunFilter;
use Amun\Util;
use PSX\Data\WriterInterface;
use PSX\Data\WriterResult;
use PSX\DateTime;
use PSX\Filter;
use PSX\Util\Markdown;

/**
 * Record
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/gpl.html GPLv3
 * @link    http://amun.phpsx.org
 */
class Record extends RecordAbstract
{
	public function getName()
	{
		return 'country';
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

	public function setTitle($title)
	{
		$title = $this->_validate->apply($title, 'string', array(new Filter\Length(3, 64)), 'title', 'Title');

		if(!$this->_validate->hasError())
		{
			$this->title = $title;
		}
		else
		{
			throw new Exception($this->_validate->getLastError());
		}
	}

	public function setCode($code)
	{
		$code = $this->_validate->apply($code, 'string', array(new Filter\Alpha(), new Filter\Length(2)), 'code', 'Code');

		if(!$this->_validate->hasError())
		{
			$this->code = strtoupper($code);
		}
		else
		{
			throw new Exception($this->_validate->getLastError());
		}
	}

	public function setLongitude($longitude)
	{
		$longitude = $this->_validate->apply($longitude, 'float', array(new Filter\Length(-180, 180)), 'longitude', 'Longitude');

		if(!$this->_validate->hasError())
		{
			$this->longitude = $longitude;
		}
		else
		{
			throw new Exception($this->_validate->getLastError());
		}
	}

	public function setLatitude($latitude)
	{
		$latitude = $this->_validate->apply($latitude, 'float', array(new Filter\Length(-90, 90)), 'latitude', 'Latitude');

		if(!$this->_validate->hasError())
		{
			$this->latitude = $latitude;
		}
		else
		{
			throw new Exception($this->_validate->getLastError());
		}
	}

	public function getId()
	{
		return $this->_base->getUrn('country', $this->id);
	}
}


