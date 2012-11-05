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

/**
 * host
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   module
 * @version    $Revision: 683 $
 */
class host extends Amun_Module_ApiAbstract
{
	private $writer;

	public function onLoad()
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

			// hub
			if(!empty($this->config['amun_hub']))
			{
				$this->writer->startElement('Link');
				$this->writer->writeAttribute('rel', 'hub');
				$this->writer->writeAttribute('href', $this->config['amun_hub']);
				$this->writer->endElement();
			}

			// lrdd
			$this->writer->startElement('Link');
			$this->writer->writeAttribute('rel', 'lrdd');
			$this->writer->writeAttribute('type', 'application/xrd+xml');
			$this->writer->writeAttribute('template', $this->config['psx_url'] . '/' . $this->config['psx_dispatch'] . 'api/core/meta/lrdd?uri={uri}');
			$this->writer->endElement();

			// connected hosts
			$result = Amun_Sql_Table_Registry::get('Core_System_Host')
				->select(array('name', 'url'))
				->where('status', '=', AmunService_Core_System_Host_Record::NORMAL)
				->getAll();

			foreach($result as $row)
			{
				$this->writer->startElement('Link');
				$this->writer->writeAttribute('rel', 'http://ns.amun-project.org/2011/host');
				$this->writer->writeAttribute('href', $row['url']);
				$this->writer->writeElement('Title', $row['name']);
				$this->writer->endElement();
			}

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
