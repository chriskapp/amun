<?php
/*
 *  $Id: host.php 683 2012-06-03 11:52:32Z k42b3.x@googlemail.com $
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

namespace hostmeta\api;

use AmunService_Core_Host_Record;
use Amun_Module_ApiAbstract;
use Amun_Sql_Table_Registry;
use Exception;
use PSX_Data_Message;
use XMLWriter;

/**
 * host
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   module
 * @version    $Revision: 683 $
 */
class index extends Amun_Module_ApiAbstract
{
	private $writer;

	/**
	 * Returns hostmeta informations
	 *
	 * @httpMethod GET
	 * @path /
	 * @nickname getHostmeta
	 * @responseClass PSX_Data_ResultSet
	 */
	public function getHostmeta()
	{
		try
		{
			header('Content-type: application/xrd+xml');

			$this->writer = new XMLWriter();
			$this->writer->openURI('php://output');
			$this->writer->setIndent(true);
			$this->writer->startDocument('1.0', 'UTF-8');

			$this->writer->startElement('XRD');
			$this->writer->writeAttribute('xmlns', 'http://docs.oasis-open.org/ns/xri/xrd-1.0');

			// subject
			$this->writer->writeElement('Subject', $this->config['psx_url']);

			// host
			$this->writer->writeElementNs('hm', 'Host', 'http://host-meta.net/xrd/1.0', $this->base->getHost());

			// title
			$this->writer->startElement('Property');
			$this->writer->writeAttribute('type', 'http://ns.amun-project.org/2011/meta/title');
			$this->writer->text($this->registry['core.title']);
			$this->writer->endElement();

			// sub title
			$this->writer->startElement('Property');
			$this->writer->writeAttribute('type', 'http://ns.amun-project.org/2011/meta/subTitle');
			$this->writer->text($this->registry['core.sub_title']);
			$this->writer->endElement();

			// timezone
			$this->writer->startElement('Property');
			$this->writer->writeAttribute('type', 'http://ns.amun-project.org/2011/meta/timezone');
			$this->writer->text($this->registry['core.default_timezone']->getName());
			$this->writer->endElement();


			$this->event->notifyListener('hostmeta.request', array($this->writer));


			$this->writer->endElement();
			$this->writer->endDocument();
			$this->writer->flush();
		}
		catch(Exception $e)
		{
			$msg = new PSX_Data_Message($e->getMessage(), false);

			$this->setResponse($msg);
		}
	}
}
