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

namespace AmunService\Lrdd\Api;

use Amun\Module\ApiAbstract;
use PSX\Data\Message;
use PSX\Filter;
use XMLWriter;

/**
 * Index
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/gpl.html GPLv3
 * @link    http://amun.phpsx.org
 */
class Index extends ApiAbstract
{
	private $writer;
	private $uri;

	/**
	 * Get informations about an uri
	 *
	 * @httpMethod GET
	 * @path /
	 * @nickname getLrdd
	 * @parameter query uri string
	 * @responseClass PSX_Data_ResultSet
	 */
	public function getLrdd()
	{
		try
		{
			header('Content-type: application/xml');

			$this->writer = new XMLWriter();
			$this->writer->openURI('php://output');
			$this->writer->setIndent(true);
			$this->writer->startDocument('1.0', 'UTF-8');

			$this->writer->startElement('XRD');
			$this->writer->writeAttribute('xmlns', 'http://docs.oasis-open.org/ns/xri/xrd-1.0');


			$uri = $this->get->uri('string', array(new Filter\Length(3, 256)));

			$this->getEvent()->notifyListener('lrdd.resource_discovery', array($this->writer, $uri));


			$this->writer->endElement();
			$this->writer->endDocument();
		}
		catch(\Exception $e)
		{
			$msg = new Message($e->getMessage(), false);

			$this->setResponse($msg);
		}
	}
}
