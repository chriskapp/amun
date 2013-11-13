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

namespace AmunService\Mail;

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

	public function setName($name)
	{
		$name = $this->_validate->apply($name, 'string', array(new Filter\Length(3, 32)), 'name', 'Name');

		if(!$this->_validate->hasError())
		{
			$this->name = strtoupper($name);
		}
		else
		{
			throw new Exception($this->_validate->getLastError());
		}
	}

	public function setFrom($from)
	{
		// replace hostname
		$from = str_replace('%host%', $this->_base->getHost(), $from);
		$from = $this->_validate->apply($from, 'string', array(new Filter\Length(3, 64)), 'from', 'From');

		if(!$this->_validate->hasError())
		{
			$this->from = $from;
		}
		else
		{
			throw new Exception($this->_validate->getLastError());
		}
	}

	public function setSubject($subject)
	{
		$subject = $this->_validate->apply($subject, 'string', array(new Filter\Length(3, 255)), 'subject', 'Subject');

		if(!$this->_validate->hasError())
		{
			$this->subject = $subject;
		}
		else
		{
			throw new Exception($this->_validate->getLastError());
		}
	}

	public function setText($text)
	{
		$text = $this->_validate->apply($text, 'string', array(new Filter\Length(3, 4096)), 'text', 'Text');

		if(!$this->_validate->hasError())
		{
			$this->text = $text;
		}
		else
		{
			throw new Exception($this->_validate->getLastError());
		}
	}

	public function setHtml($html)
	{
		$html = $this->_validate->apply($html, 'string', array(new Filter\Length(3, 4096)), 'html', 'Html');

		if(!$this->_validate->hasError())
		{
			$this->html = $html;
		}
		else
		{
			throw new Exception($this->_validate->getLastError());
		}
	}

	public function setValues($values)
	{
		$data  = array();
		$parts = explode(';', $values);

		foreach($parts as $part)
		{
			$part = trim($part);

			if(!empty($part))
			{
				$data[] = $part;
			}
		}

		$this->values = implode(';', $data);
	}
}

