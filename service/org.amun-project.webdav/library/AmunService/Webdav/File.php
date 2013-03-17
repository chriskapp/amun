<?php
/*
 *  $Id: File.php 635 2012-05-01 19:46:37Z k42b3.x@googlemail.com $
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

namespace AmunService\Webdav;

use PSX\Data\RecordInterface;
use PSX\Data\Writer;

/**
 * AmunService_Webdav_FileAbstract
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   Amun
 * @package    AmunService_Webdav
 * @version    $Revision: 635 $
 */
class File extends FileAbstract
{
	protected $service;
	protected $record;
	protected $writer;

	protected $content;

	public function __construct($service, RecordInterface $record)
	{
		$this->service = $service;
		$this->record  = $record;
		$this->writer  = new Writer\Xml();
	}

	public function getName()
	{
		return $this->service . '-' . $this->record->id . '.xml';
	}

	public function getLastModified()
	{
		if(isset($this->record->date))
		{
			return strtotime($this->record->date);
		}
		else
		{
			return time();
		}
	}

	public function get()
	{
		if($this->content === null)
		{
			ob_start();

			$this->writer->write($this->record);

			$this->content = ob_get_contents();

			ob_end_clean();
		}

		return $this->content;
	}

	public function getETag()
	{
		return md5($this->get());
	}

	public function getContentType()
	{
		return Writer\Xml::$mime;
	}

	public function getSize()
	{
		return strlen($this->get());
	}
}

